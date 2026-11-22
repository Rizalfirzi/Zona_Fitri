<?php
/**
 * Zona Fitri - Multi-Brand Coffee Native API Service
 * 
 * Terhubung langsung ke 4 Dedicated Brand APIs (Nuxt Serverless):
 * - Kopi Kenangan: https://kopkenapi.swamptechnology.id
 * - Janji Jiwa:    https://jiwaapi.swamptechnology.id
 * - Fore Coffee:   https://foreapi.swamptechnology.id
 * - Tomoro Coffee: https://tomoroapi.swamptechnology.id
 * 
 * Vercel Serverless Optimized:
 * 1. Zero Database overhead pada request menu & outlet.
 * 2. Caching layer pada Edge CDN dan temporary container directory (/tmp).
 * 3. Strict 4s cURL timeout sehingga tidak membebani fungsi serverless Vercel.
 * 4. Master snapshot fallback instan (< 1ms) untuk zero downtime.
 */

class CoffeeApi
{
    protected array $brandApis;
    protected int $outletsTtl;
    protected int $stockTtl;
    protected string $cacheDir;

    public function __construct()
    {
        $this->brandApis = config('brand_apis', [
            'kopken' => 'https://kopkenapi.swamptechnology.id',
            'jiwa'   => 'https://jiwaapi.swamptechnology.id',
            'fore'   => 'https://foreapi.swamptechnology.id',
            'tomoro' => 'https://tomoroapi.swamptechnology.id',
        ]);
        $this->outletsTtl = (int) config('cache_ttl.outlets', 1800);
        $this->stockTtl = (int) config('cache_ttl.menu', 20);
        $this->cacheDir = defined('CACHE_PATH') ? CACHE_PATH : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Normalize brand key (e.g. 'kopikenangan' -> 'kopken')
     */
    public function normalizeBrand(string $brand): string
    {
        $b = strtolower(trim($brand));
        if ($b === 'kopikenangan' || $b === 'kopi-kenangan' || $b === 'kk') {
            return 'kopken';
        }
        if ($b === 'tomorocoffee' || $b === 'tomoro-coffee') {
            return 'tomoro';
        }
        if ($b === 'forecoffee' || $b === 'fore-coffee') {
            return 'fore';
        }
        if ($b === 'janjijiwa' || $b === 'janji-jiwa' || $b === 'janjiw' || $b === 'jiwa' || $b === 'jj') {
            return 'jiwa';
        }
        return in_array($b, ['kopken', 'tomoro', 'fore', 'jiwa'], true) ? $b : 'kopken';
    }

    /**
     * Normalize outlet code aliases (e.g. Jombang outlet codes)
     */
    public function normalizeOutletCode(string $brand, string $code): string
    {
        $code = trim($code);
        if ($brand === 'kopken') {
            if ($code === 'KK.JMB.RKWHDHSYM' || $code === 'KK.JMB.RKWD.SYM' || stripos($code, 'JMB') !== false) {
                return 'KK.JBG.RKWHDHSYM';
            }
        }
        return $code;
    }

    /**
     * Get real-time outlets for a brand with search and GPS distance calculation
     */
    public function getOutlets(string $brand = 'kopken', ?string $query = null, ?float $lat = null, ?float $lng = null): array
    {
        $brand = $this->normalizeBrand($brand);
        $query = trim((string) $query);
        $cacheKey = "outlets_{$brand}_all";

        // 1. Load full outlet list from container cache or live brand API
        $allOutlets = $this->getCache($cacheKey, $this->outletsTtl);
        if ($allOutlets === null || empty($allOutlets['outlets'])) {
            $liveOutlets = $this->fetchOutletsFromApi($brand);
            $masterOutlets = $this->getFallbackOutlets($brand);

            $outletMap = [];
            foreach ($masterOutlets as $o) {
                $code = $o['code'] ?? $o['id'] ?? '';
                if ($code) {
                    $outletMap[$code] = $o;
                }
            }
            foreach ($liveOutlets as $o) {
                $code = $o['code'] ?? $o['id'] ?? '';
                if ($code) {
                    if (isset($outletMap[$code])) {
                        $outletMap[$code] = array_merge($outletMap[$code], array_filter($o, fn($v) => $v !== null && $v !== ''));
                    } else {
                        $outletMap[$code] = $o;
                    }
                }
            }

            $mergedList = array_values($outletMap);
            if (!empty($mergedList)) {
                $allOutlets = [
                    'brand' => $brand,
                    'outlets' => $mergedList,
                    'source' => !empty($liveOutlets) ? 'live+snapshot' : 'snapshot',
                    'updatedAt' => date('c'),
                ];
                $this->setCache($cacheKey, $allOutlets);
            }
        }

        // 2. Fallback to local snapshot file if live API failed
        if (empty($allOutlets['outlets'])) {
            $fallback = $this->getFallbackOutlets($brand);
            $allOutlets = [
                'brand' => $brand,
                'outlets' => $fallback,
                'source' => 'snapshot',
                'updatedAt' => date('c'),
            ];
        }

        $outlets = $allOutlets['outlets'] ?? [];

        // 3. Filter by search query if specified
        if ($query !== '') {
            $qLower = strtolower($query);
            $qWords = array_values(array_filter(explode(' ', $qLower), fn($w) => strlen($w) > 0));
            $outlets = array_values(array_filter($outlets, function ($o) use ($qLower, $qWords) {
                $haystack = strtolower(
                    ($o['name'] ?? '') . ' ' .
                    ($o['address'] ?? '') . ' ' .
                    ($o['city'] ?? '') . ' ' .
                    ($o['code'] ?? '')
                );
                if (str_contains($haystack, $qLower)) {
                    return true;
                }
                if (count($qWords) > 1) {
                    $matchAll = true;
                    foreach ($qWords as $qw) {
                        if (!str_contains($haystack, $qw)) {
                            $matchAll = false;
                            break;
                        }
                    }
                    if ($matchAll) return true;
                }
                return false;
            }));

            // Sort by relevance
            usort($outlets, function ($a, $b) use ($qLower) {
                $scoreA = 999;
                $cityA = strtolower($a['city'] ?? '');
                $nameA = strtolower($a['name'] ?? '');
                $addrA = strtolower($a['address'] ?? '');
                if (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $nameA)) $scoreA = 1;
                elseif (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $cityA)) $scoreA = 2;
                elseif (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $addrA)) $scoreA = 3;
                elseif (str_contains($nameA, $qLower)) $scoreA = 4;
                elseif (str_contains($cityA . ' ' . $addrA, $qLower)) $scoreA = 5;

                $scoreB = 999;
                $cityB = strtolower($b['city'] ?? '');
                $nameB = strtolower($b['name'] ?? '');
                $addrB = strtolower($b['address'] ?? '');
                if (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $nameB)) $scoreB = 1;
                elseif (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $cityB)) $scoreB = 2;
                elseif (preg_match('/\b' . preg_quote($qLower, '/') . '\b/i', $addrB)) $scoreB = 3;
                elseif (str_contains($nameB, $qLower)) $scoreB = 4;
                elseif (str_contains($cityB . ' ' . $addrB, $qLower)) $scoreB = 5;

                return $scoreA <=> $scoreB;
            });
        }

        // 4. Calculate GPS Distance if coordinates provided
        if ($lat !== null && $lng !== null) {
            foreach ($outlets as &$outlet) {
                if (!empty($outlet['lat']) && !empty($outlet['lng'])) {
                    $outlet['distanceKm'] = $this->calculateDistance(
                        $lat,
                        $lng,
                        (float) $outlet['lat'],
                        (float) $outlet['lng']
                    );
                }
            }
            unset($outlet);

            usort($outlets, function ($a, $b) {
                $distA = $a['distanceKm'] ?? 999999;
                $distB = $b['distanceKm'] ?? 999999;
                return $distA <=> $distB;
            });

            if (count($outlets) > 40) {
                $outlets = array_slice($outlets, 0, 40);
            }
        } elseif ($query === '' && count($outlets) > 50) {
            $outlets = array_slice($outlets, 0, 50);
        }

        // 5. Evaluate operating hours
        foreach ($outlets as &$outlet) {
            $outlet = $this->evaluateOutletHours($outlet, $brand);
        }
        unset($outlet);

        return [
            'success' => true,
            'brand' => $brand,
            'source' => empty($allOutlets['source']) ? 'live' : $allOutlets['source'],
            'count' => count($outlets),
            'outlets' => $outlets,
            'timestamp' => date('c'),
        ];
    }

    /**
     * Fetch outlets directly from dedicated brand API
     */
    protected function fetchOutletsFromApi(string $brand): array
    {
        $apiBase = $this->brandApis[$brand] ?? null;
        if (!$apiBase) return [];

        $outlets = [];

        try {
            if ($brand === 'kopken') {
                $res = $this->httpRequest("{$apiBase}/api/store/query_pageable_store", ['pageSize' => 1500]);
                $rawStores = $res['data']['store'] ?? [];
                foreach ($rawStores as $s) {
                    $code = $s['code'] ?? '';
                    $name = $s['alias'] ?: ($s['name'] ?? '');
                    $addr = $s['address'] ?? '';
                    $openTime = $s['open'] ?? '07:00';
                    $closeTime = $s['close'] ?? '22:00';

                    $outlets[] = [
                        'code' => $code,
                        'id' => $code,
                        'name' => $name,
                        'address' => $addr,
                        'city' => $this->extractCity($addr . ' ' . $name),
                        'lat' => (float) ($s['latitude'] ?? 0),
                        'lng' => (float) ($s['longitude'] ?? 0),
                        'isOpen' => !empty($s['is_open']) && !empty($s['available']),
                        'hours' => "{$openTime} - {$closeTime} WIB",
                        'img' => $s['image_url'] ?? $this->getDefaultOutletImage('kopken'),
                    ];
                }
            } elseif ($brand === 'jiwa') {
                $res = $this->httpRequest("{$apiBase}/api/store/all", ['limit' => 1000]);
                $rawStores = $res['payload'] ?? [];
                foreach ($rawStores as $s) {
                    $oid = (string) ($s['id_outlet'] ?? $s['outlet_code'] ?? '');
                    $name = $s['outlet_name'] ?? '';
                    $addr = $s['outlet_address'] ?? '';
                    $outlets[] = [
                        'code' => $oid,
                        'id' => $oid,
                        'outlet_code' => $s['outlet_code'] ?? '',
                        'name' => $name,
                        'address' => $addr,
                        'city' => $this->extractCity($addr . ' ' . $name),
                        'lat' => (float) ($s['outlet_lat'] ?? 0),
                        'lng' => (float) ($s['outlet_long'] ?? 0),
                        'isOpen' => ($s['is_open'] == 1 || ($s['outlet_status'] ?? '') === 'ACTIVE'),
                        'hours' => '07:00 - 22:00 WIB',
                        'img' => $this->getDefaultOutletImage('jiwa'),
                    ];
                }
            } elseif ($brand === 'fore') {
                $res = $this->httpRequest("{$apiBase}/api/store/all", ['limit' => 1000]);
                $rawStores = $res['payload'] ?? [];
                foreach ($rawStores as $s) {
                    $code = $s['st_code'] ?? (string) ($s['st_id'] ?? '');
                    $name = $s['st_name'] ?? '';
                    $addr = $s['st_address'] ?? '';
                    $openTime = $s['st_open_wib'] ?: ($s['st_open'] ?? '07:00');
                    $closeTime = $s['st_close_wib'] ?: ($s['st_close'] ?? '22:00');
                    $outlets[] = [
                        'code' => $code,
                        'id' => (string) ($s['st_id'] ?? $code),
                        'name' => $name,
                        'address' => $addr,
                        'city' => $this->extractCity($addr . ' ' . $name),
                        'lat' => (float) ($s['st_lat'] ?? 0),
                        'lng' => (float) ($s['st_long'] ?? 0),
                        'isOpen' => !empty($s['is_open']) || strtolower((string)($s['st_status'] ?? '')) === 'active',
                        'hours' => "{$openTime} - {$closeTime} WIB",
                        'img' => $this->getDefaultOutletImage('fore'),
                    ];
                }
            } elseif ($brand === 'tomoro') {
                $res = $this->httpRequest("{$apiBase}/api/store/list", ['pageSize' => 1000]);
                $rawStores = $res['data']['records'] ?? [];
                if (empty($rawStores)) {
                    // Fallback to cingops.com for Tomoro outlets
                    $cingopsRes = $this->httpRequest("https://cingops.com/api/outlets", ['brand' => 'tomoro', 'pageSize' => 50]);
                    $rawStores = $cingopsRes['stores'] ?? [];
                }
                foreach ($rawStores as $s) {
                    $code = $s['storeCode'] ?? $s['code'] ?? $s['id'] ?? '';
                    $name = $s['storeName'] ?? $s['name'] ?? '';
                    $addr = $s['storeAddress'] ?? $s['address'] ?? '';
                    $outlets[] = [
                        'code' => $code,
                        'id' => $code,
                        'name' => $name,
                        'address' => $addr,
                        'city' => $s['city'] ?? $this->extractCity($addr . ' ' . $name),
                        'lat' => (float) ($s['latitude'] ?? $s['lat'] ?? 0),
                        'lng' => (float) ($s['longitude'] ?? $s['lng'] ?? 0),
                        'isOpen' => isset($s['businessStatus']) ? ($s['businessStatus'] == 1) : (!empty($s['isOpen'])),
                        'hours' => $s['hours'] ?? '07:00 - 22:00 WIB',
                        'img' => $s['storePicture'] ?? $s['imageUrl'] ?? $this->getDefaultOutletImage('tomoro'),
                    ];
                }
            }
        } catch (Throwable $e) {
            error_log("Failed to fetch outlets for {$brand}: " . $e->getMessage());
        }

        return $outlets;
    }

    /**
     * Get real-time menu and stock per outlet
     */
    public function getMenu(string $brand = 'kopken', ?string $outletCode = null, bool $forceRefresh = false): array
    {
        $brand = $this->normalizeBrand($brand);
        $outletCode = trim((string) $outletCode);
        if (str_contains($outletCode, ',')) {
            $outletCode = trim(explode(',', $outletCode)[0]);
        }
        $outletCode = $this->normalizeOutletCode($brand, $outletCode);
        if ($outletCode === '') {
            $rawDef = config("brands.{$brand}.defaultOutlet", config("brands.{$brand}.default_outlet", ''));
            $outletCode = trim(explode(',', (string)$rawDef)[0]);
            if ($outletCode === '') {
                $outletCode = match ($brand) {
                    'kopken' => 'KK.SBY.MERRNGKTDT',
                    'jiwa'   => '2248',
                    'fore'   => 'KOKAS',
                    'tomoro' => 'JKT0001',
                    default  => 'KK.SBY.MERRNGKTDT',
                };
            }
        }

        $safeOutlet = preg_replace('/[^A-Za-z0-9_-]/', '_', $outletCode);
        $cacheKey = "menu_{$brand}_{$safeOutlet}";

        if ($forceRefresh) {
            $this->deleteCache($cacheKey);
            $cached = null;
        } else {
            $cached = $this->getCache($cacheKey, $this->stockTtl);
        }

        if ($cached !== null) {
            return $this->formatMenuStats($cached, $brand, $outletCode);
        }

        // 1. Fetch live menu directly from brand API
        $liveMenu = $this->fetchMenuFromApi($brand, $outletCode);

        // 2. Fallback to local snapshot file if live API failed or empty
        if (!$liveMenu || empty($liveMenu['items'])) {
            $liveMenu = $this->getFallbackMenu($brand, $outletCode);
        }

        if ($liveMenu && !empty($liveMenu['items'])) {
            $result = $this->formatMenuStats($liveMenu, $brand, $outletCode);
            $this->setCache($cacheKey, $result);
            return $result;
        }

        return [
            'brand' => $brand,
            'outletCode' => $outletCode,
            'categories' => [],
            'items' => [],
            'stats' => ['total' => 0, 'available' => 0, 'soldOut' => 0],
            'updatedAt' => date('c'),
        ];
    }

    /**
     * Fetch menu directly from dedicated brand API
     */
    protected function fetchMenuFromApi(string $brand, string $outletCode): ?array
    {
        $apiBase = $this->brandApis[$brand] ?? null;
        if (!$apiBase) return null;

        try {
            if ($brand === 'kopken') {
                $res = $this->httpRequest("{$apiBase}/api/product/query_product_menu", ['store_code' => $outletCode]);
                $groups = $res['data']['menu_groups'] ?? [];
                if (empty($groups)) return null;

                $items = [];
                $categories = [];
                $seenKeys = [];

                foreach ($groups as $g) {
                    $gname = $g['group_name'] ?? 'Menu';
                    $catId = 'cat-' . $this->slugify($gname);
                    $catItems = [];

                    foreach ($g['menu_products'] ?? [] as $p) {
                        $name = $p['name'] ?? '';
                        $key = $this->slugify($name);
                        if (isset($seenKeys[$key])) continue;
                        $seenKeys[$key] = true;

                        $origPrice = (int) ($p['orig_price'] ?? $p['price'] ?? 0);
                        $price = (int) ($p['price'] ?? $origPrice);
                        $isSoldOut = !empty($p['is_sold_out']);
                        $isFood = (bool) preg_match('/toast|food|panini|croissant|bagel|roti|bread|cookie|muffin|danish|cake/i', $name . ' ' . $gname);

                        $items[] = [
                            'key' => $key,
                            'id' => (string) ($p['id'] ?? $key),
                            'code' => (string) ($p['product_code'] ?? $p['id'] ?? $key),
                            'name' => $name,
                            'category' => $gname,
                            'outletPrice' => $origPrice > 0 ? $origPrice : $price,
                            'normalPrice' => $origPrice > 0 ? $origPrice : $price,
                            'price' => $origPrice > 0 ? $origPrice : $price,
                            'img' => $p['image'] ?? '',
                            'isSoldOut' => $isSoldOut,
                            'notCarried' => false,
                            'isFood' => $isFood,
                            'desc' => $p['description'] ?? '',
                            'options' => [
                                'sizes' => $isFood ? ['Regular'] : ['Regular', 'Large'],
                                'temps' => ['Ice', 'Hot'],
                                'ices' => ['Normal Ice', 'Less Ice', 'No Ice'],
                                'sugars' => ['Normal Sugar', 'Less Sugar', 'No Sugar'],
                            ],
                        ];
                        $catItems[] = $key;
                    }

                    if (!empty($catItems)) {
                        $categories[] = [
                            'id' => $catId,
                            'name' => $gname,
                            'kind' => 'item',
                            'items' => $catItems,
                        ];
                    }
                }

                return [
                    'brand' => 'kopken',
                    'outletCode' => $outletCode,
                    'categories' => $categories,
                    'items' => $items,
                    'live' => true,
                    'updatedAt' => date('c'),
                ];
            } elseif ($brand === 'jiwa') {
                $res = $this->httpRequest("{$apiBase}/api/product/menu", ['outlet_id' => $outletCode]);
                $rawItems = $res['payload'] ?? [];
                if (empty($rawItems)) return null;

                $items = [];
                $catMap = [];
                $seenKeys = [];

                foreach ($rawItems as $p) {
                    $name = $p['product_name'] ?? '';
                    $key = $this->slugify($name);
                    if (isset($seenKeys[$key])) continue;
                    $seenKeys[$key] = true;

                    $catName = $p['category_name'] ?? 'Menu';
                    $price = (int) ($p['price'] ?? $p['discounted_price'] ?? 0);
                    $isFood = (bool) preg_match('/toast|food|bun|bread|roti/i', $name . ' ' . $catName);

                    $items[] = [
                        'key' => $key,
                        'id' => (string) ($p['id_product'] ?? $key),
                        'code' => (string) ($p['id_product'] ?? $key),
                        'name' => $name,
                        'category' => $catName,
                        'outletPrice' => $price,
                        'normalPrice' => $price,
                        'price' => $price,
                        'img' => $p['product_photo'] ?? '',
                        'isSoldOut' => ($p['is_inactive'] == 1),
                        'isFood' => $isFood,
                        'desc' => $p['product_description'] ?? '',
                        'options' => [
                            'sizes' => ['Regular'],
                            'temps' => ['Ice', 'Hot'],
                            'ices' => ['Normal Ice', 'Less Ice', 'No Ice'],
                            'sugars' => ['Normal Sugar', 'Less Sugar', 'No Sugar'],
                        ],
                    ];

                    $catMap[$catName][] = $key;
                }

                $categories = [];
                foreach ($catMap as $cn => $citems) {
                    $categories[] = [
                        'id' => 'cat-' . $this->slugify($cn),
                        'name' => $cn,
                        'kind' => 'item',
                        'items' => $citems,
                    ];
                }

                return [
                    'brand' => 'jiwa',
                    'outletCode' => $outletCode,
                    'categories' => $categories,
                    'items' => $items,
                    'live' => true,
                    'updatedAt' => date('c'),
                ];
            } elseif ($brand === 'fore') {
                $res = $this->httpRequest("{$apiBase}/api/product/menu", ['store_id' => $outletCode]);
                $rawItems = $res['payload'] ?? [];
                if (empty($rawItems)) return null;

                $items = [];
                $catMap = [];
                $seenKeys = [];

                foreach ($rawItems as $p) {
                    $name = $p['pd_name'] ?? '';
                    $key = $this->slugify($name);
                    if (isset($seenKeys[$key])) continue;
                    $seenKeys[$key] = true;

                    $catName = $p['cat_name'] ?? 'Menu';
                    $price = (int) ($p['pd_final_price'] ?? $p['pd_final_price_ori'] ?? 0);
                    $origPrice = (int) ($p['pd_final_price_ori'] ?? $price);
                    $isFood = (bool) preg_match('/pastry|croissant|panini|sandwich|toast|food|cake|bread/i', $name . ' ' . $catName);

                    $items[] = [
                        'key' => $key,
                        'id' => (string) ($p['pd_id'] ?? $key),
                        'code' => (string) ($p['pd_code'] ?? $p['pd_id'] ?? $key),
                        'name' => $name,
                        'category' => $catName,
                        'outletPrice' => $origPrice,
                        'normalPrice' => $origPrice,
                        'price' => $price,
                        'img' => $p['image_url'] ?? $p['pd_img'] ?? '',
                        'isSoldOut' => strtolower((string)($p['stpd_status'] ?? $p['pd_status'] ?? 'active')) !== 'active',
                        'isFood' => $isFood,
                        'desc' => $p['pd_desc'] ?? '',
                        'options' => [
                            'sizes' => $isFood ? ['Regular'] : ['Regular', 'Large'],
                            'temps' => ['Ice', 'Hot'],
                            'ices' => ['Normal Ice', 'Less Ice', 'No Ice'],
                            'sugars' => ['Normal Sugar', 'Less Sugar', 'No Sugar'],
                        ],
                    ];

                    $catMap[$catName][] = $key;
                }

                $categories = [];
                foreach ($catMap as $cn => $citems) {
                    $categories[] = [
                        'id' => 'cat-' . $this->slugify($cn),
                        'name' => $cn,
                        'kind' => 'item',
                        'items' => $citems,
                    ];
                }

                return [
                    'brand' => 'fore',
                    'outletCode' => $outletCode,
                    'categories' => $categories,
                    'items' => $items,
                    'live' => true,
                    'updatedAt' => date('c'),
                ];
            } elseif ($brand === 'tomoro') {
                $res = $this->httpRequest("{$apiBase}/api/product/menu", [
                    'storeCode' => $outletCode,
                    'mainMenuType' => 1,
                ]);
                $vos = $res['data']['menuVos'] ?? [];
                if (empty($vos)) return null;

                $items = [];
                $categories = [];
                $seenKeys = [];

                foreach ($vos as $vo) {
                    $cname = $vo['menuName'] ?? 'Menu';
                    // Skip food categories for Tomoro per client revision
                    if (preg_match('/food|pastry|snack|bakery|toast|bread/i', $cname)) {
                        continue;
                    }
                    $catId = 'cat-' . $this->slugify($cname);
                    $catItems = [];

                    foreach ($vo['items'] ?? [] as $p) {
                        $name = $p['name'] ?? '';
                        $key = $this->slugify($name);
                        if (isset($seenKeys[$key])) continue;
                        $seenKeys[$key] = true;

                        $price = (int) ($p['price'] ?? $p['linePrice'] ?? 0);
                        $origPrice = (int) ($p['linePrice'] ?? $price);

                        $items[] = [
                            'key' => $key,
                            'id' => (string) ($p['goodId'] ?? $key),
                            'code' => (string) ($p['code'] ?? $p['goodId'] ?? $key),
                            'name' => $name,
                            'category' => $cname,
                            'outletPrice' => $origPrice,
                            'normalPrice' => $origPrice,
                            'price' => $price,
                            'img' => $p['goodPic'] ?? '',
                            'isSoldOut' => !empty($p['isSoldOut']),
                            'isFood' => false,
                            'desc' => $p['desc'] ?? '',
                            'options' => [
                                'sizes' => ['Small'],
                                'temps' => ['Ice', 'Hot'],
                                'ices' => ['Normal Ice', 'Less Ice', 'No Ice'],
                                'sugars' => ['Normal Sugar', 'Less Sugar', 'No Sugar'],
                            ],
                        ];
                        $catItems[] = $key;
                    }

                    if (!empty($catItems)) {
                        $categories[] = [
                            'id' => $catId,
                            'name' => $cname,
                            'kind' => 'item',
                            'items' => $catItems,
                        ];
                    }
                }

                return [
                    'brand' => 'tomoro',
                    'outletCode' => $outletCode,
                    'categories' => $categories,
                    'items' => $items,
                    'live' => true,
                    'updatedAt' => date('c'),
                ];
            }
        } catch (Throwable $e) {
            error_log("Failed to fetch menu for {$brand} @ {$outletCode}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Format menu statistics, ensure standard categories and client adjustments
     */
    protected function formatMenuStats(array $menuData, string $brand, string $outletCode): array
    {
        // 1. Client revision: Hapus menu food / snack di Tomoro Coffee
        if ($brand === 'tomoro' && isset($menuData['items']) && is_array($menuData['items'])) {
            $removedKeys = [];
            $drinksOnly = [];
            foreach ($menuData['items'] as $it) {
                $itCat = strtolower(trim((string)($it['category'] ?? '')));
                $itName = strtolower(trim((string)($it['name'] ?? '')));
                $isFood = !empty($it['isFood'])
                    || str_contains($itCat, 'food')
                    || str_contains($itCat, 'pastry')
                    || str_contains($itCat, 'snack')
                    || str_contains($itCat, 'bakery')
                    || str_contains($itCat, 'toast')
                    || str_contains($itCat, 'bread');

                if ($isFood) {
                    if (!empty($it['key'])) $removedKeys[$it['key']] = true;
                    if (!empty($it['id'])) $removedKeys[$it['id']] = true;
                } else {
                    $drinksOnly[] = $it;
                }
            }
            $menuData['items'] = $drinksOnly;

            if (isset($menuData['categories']) && is_array($menuData['categories'])) {
                $cleanCats = [];
                foreach ($menuData['categories'] as $cat) {
                    $catName = strtolower(trim((string)($cat['name'] ?? '')));
                    if (str_contains($catName, 'food') || str_contains($catName, 'pastry') || str_contains($catName, 'snack')) {
                        continue;
                    }
                    if (!empty($cat['items']) && is_array($cat['items'])) {
                        $cat['items'] = array_values(array_filter($cat['items'], fn($k) => !isset($removedKeys[$k])));
                    }
                    if (!empty($cat['items'])) {
                        $cleanCats[] = $cat;
                    }
                }
                $menuData['categories'] = array_values($cleanCats);
            }
        }

        // 2. Client revision: Hapus menu Fried Chicken Kenangan (FCK) & Salt Bread Original pada Kopi Kenangan
        if ($brand === 'kopken' && isset($menuData['items']) && is_array($menuData['items'])) {
            $removedKeys = [];
            $filteredItems = [];
            foreach ($menuData['items'] as $it) {
                $itCat = strtolower(trim((string)($it['category'] ?? '')));
                $itName = strtolower(trim((string)($it['name'] ?? '')));
                $itKey = strtolower(trim((string)($it['key'] ?? '')));

                $isFck = str_contains($itCat, 'fried chicken')
                    || str_contains($itCat, 'fck')
                    || str_contains($itName, 'bungeoppang')
                    || str_contains($itName, 'spaghetti')
                    || str_contains($itName, 'burger')
                    || str_contains($itName, 'gyoza')
                    || str_contains($itName, 'fried chicken')
                    || str_contains($itName, 'nugget')
                    || str_contains($itName, 'mie judes')
                    || str_contains($itName, 'judes kremes')
                    || str_contains($itName, 'fish ball')
                    || str_contains($itName, 'fish cake')
                    || str_contains($itName, 'boneless chicken')
                    || str_contains($itName, 'chicken skin')
                    || str_contains($itName, 'chicken ball')
                    || preg_match('/\bnasi\b/i', $itName)
                    || $itKey === 'nasi'
                    || str_contains($itName, 'salt bread original')
                    || $itKey === 'saltbreadoriginal';

                if ($isFck) {
                    if (!empty($it['key'])) $removedKeys[$it['key']] = true;
                    if (!empty($it['id'])) $removedKeys[$it['id']] = true;
                } else {
                    $filteredItems[] = $it;
                }
            }
            $menuData['items'] = $filteredItems;

            if (isset($menuData['categories']) && is_array($menuData['categories'])) {
                $cleanCats = [];
                foreach ($menuData['categories'] as $cat) {
                    $catName = strtolower(trim((string)($cat['name'] ?? '')));
                    if (str_contains($catName, 'fried chicken') || str_contains($catName, 'fck')) {
                        continue;
                    }
                    if (!empty($cat['items']) && is_array($cat['items'])) {
                        $cat['items'] = array_values(array_filter($cat['items'], fn($k) => !isset($removedKeys[$k])));
                    }
                    if (!empty($cat['items'])) {
                        $cleanCats[] = $cat;
                    }
                }
                $menuData['categories'] = array_values($cleanCats);
            }
        }

        // 2b. Client revision: Hapus Tumblr, Literan, Drip Bag, Steep Coffee, Beans, Merchandise, dan Promo lama
        if ($brand === 'kopken' && isset($menuData['items']) && is_array($menuData['items'])) {
            $removedKeys = [];
            $filteredItems = [];
            $legacyPromos = [
                'freeupsizelarge', 'buy2disc25', '2kkmhanya29rban', '3rotidiscount15', 'beli3hanya47rb',
                'bundleeverydaycollectionlidtumbler', 'peka1', 'peka2', '2sweettoasts', '2savorytoasts',
                'pahe1', 'pahe2', 'pahe3'
            ];

            foreach ($menuData['items'] as $it) {
                $itCat = strtolower(trim((string)($it['category'] ?? '')));
                $itName = strtolower(trim((string)($it['name'] ?? '')));
                $itKey = strtolower(trim((string)($it['key'] ?? '')));

                $isTumblrLiteran = str_contains($itName, 'seliter')
                    || str_contains($itName, 'liter')
                    || str_contains($itName, 'tumbler')
                    || str_contains($itName, 'tumblr')
                    || str_contains($itName, 'bottle')
                    || str_contains($itName, 'cuddly pals')
                    || str_contains($itName, 'sunshine friends')
                    || str_contains($itName, 'teddy bear')
                    || str_contains($itName, 'drip bag')
                    || str_contains($itName, 'steep coffee')
                    || str_contains($itName, 'coffee bag')
                    || str_contains($itCat, 'merchandise')
                    || str_contains($itCat, 'manual brew')
                    || str_contains($itCat, 'kenangan at home')
                    || in_array($itKey, $legacyPromos, true);

                if ($isTumblrLiteran) {
                    if (!empty($it['key'])) $removedKeys[$it['key']] = true;
                    if (!empty($it['id'])) $removedKeys[$it['id']] = true;
                } else {
                    $filteredItems[] = $it;
                }
            }
            $menuData['items'] = $filteredItems;

            if (isset($menuData['categories']) && is_array($menuData['categories'])) {
                $cleanCats = [];
                foreach ($menuData['categories'] as $cat) {
                    $catName = strtolower(trim((string)($cat['name'] ?? '')));
                    if (str_contains($catName, 'merchandise') || str_contains($catName, 'manual brew') || str_contains($catName, 'kenangan at home')) {
                        continue;
                    }
                    if (!empty($cat['items']) && is_array($cat['items'])) {
                        $cat['items'] = array_values(array_filter($cat['items'], fn($k) => !isset($removedKeys[$k])));
                    }
                    if (!empty($cat['items'])) {
                        $cleanCats[] = $cat;
                    }
                }
                $menuData['categories'] = array_values($cleanCats);
            }
        }

        // 3. Normalizing item image, options, and size rules
        $items = $menuData['items'] ?? [];
        $totalCount = count($items);
        $availableCount = 0;
        $soldOutCount = 0;

        foreach ($items as &$item) {
            $isSoldOut = !empty($item['isSoldOut']) || !empty($item['notCarried']);
            $item['isSoldOut'] = $isSoldOut;
            if ($isSoldOut) {
                $soldOutCount++;
            } else {
                $availableCount++;
            }

            if (empty($item['img'])) {
                $item['img'] = $this->getDefaultItemImage($brand, $item['name'] ?? '');
            }

            // Food items check across all brands: food items don't have size, temperature, ice, sugar
            $itCat = strtolower(trim((string)($item['category'] ?? '')));
            $itName = strtolower(trim((string)($item['name'] ?? '')));
            $isFood = !empty($item['isFood'])
                || str_contains($itCat, 'food')
                || str_contains($itCat, 'toast')
                || (bool) preg_match('/toast|food|panini|croissant|bagel|roti|bread|cookie|muffin|danish|cake|donut|donat/i', $itName);

            $item['isFood'] = $isFood;
            if ($isFood) {
                $item['options'] = [];
                unset($item['largePrice'], $item['largeNormal'], $item['largeOutletPrice'], $item['jumboPrice'], $item['jumboNormal']);
            } else {
                // Remove Jumbo size option across all brands
                if (!empty($item['options']['sizes']) && is_array($item['options']['sizes'])) {
                    $item['options']['sizes'] = array_values(array_filter(
                        $item['options']['sizes'],
                        fn($sz) => stripos((string)$sz, 'jumbo') === false
                    ));
                }
                unset($item['jumboPrice'], $item['jumboNormal'], $item['jumboOutletPrice']);

                // Tomoro small size only
                if ($brand === 'tomoro') {
                    $item['options']['sizes'] = ['Small'];
                    unset($item['largePrice'], $item['largeNormal'], $item['largeOutletPrice']);
                }
            }
        }
        unset($item);

        // 4. Apply client pricing formulas & bundling
        $this->applyClientPricingAndBundling($menuData, $brand);

        // 5. Kopken Category standard grouping matching official app
        if ($brand === 'kopken' && isset($menuData['items']) && is_array($menuData['items'])) {
            $catBuckets = [
                'bundle' => [],
                'kopi' => [],
                'non-kopi' => [],
                'tea' => [],
                'frappe' => [],
                'food' => [],
            ];

            $teaKeys = [
                'tarocloudmilktea', 'ogthaitea', 'thaitealoaded', 
                'hazelnutchocomilktea', 'lemonblacktea', 'kenanganmilktea',
                'earlgreytea', 'earlgreymilktea', 'earlgreymilkteaboba', 'milkteaboba', 'raspberryhibiscus'
            ];

            foreach ($menuData['items'] as &$it) {
                $k = strtolower(trim((string)($it['key'] ?? '')));
                $c = strtolower(trim((string)($it['category'] ?? '')));
                $n = strtolower(trim((string)($it['name'] ?? '')));

                if (!empty($it['isBundle'])) {
                    $it['category'] = 'Promo & Combo';
                    $catBuckets['bundle'][] = $k;
                    continue;
                }

                if (str_contains($c, 'frappe') || str_contains($n, 'frappe')) {
                    $it['category'] = 'Kenangan Frappe';
                    $catBuckets['frappe'][] = $k;
                } elseif (in_array($k, $teaKeys, true) || (str_contains($c, 'tea') || (str_contains($n, 'tea') && !str_contains($n, 'coffee') && !str_contains($n, 'latte')))) {
                    $it['category'] = 'Tea';
                    $catBuckets['tea'][] = $k;
                } elseif (str_contains($c, 'food') || str_contains($c, 'toast') || str_contains($n, 'toast') || preg_match('/croissant|bagel|donut|muffin|panini|pastry|cookie|bread|roti|bun|bungeoppang|mie|spaghetti|canele|cromboloni|pie|danish/i', $n)) {
                    $it['category'] = 'Food';
                    $catBuckets['food'][] = $k;
                } elseif (str_contains($c, 'non') || preg_match('/coklat|choco|milo|taro|oreo|lemonade|susu/i', $n)) {
                    $it['category'] = 'Non-Kopi';
                    $catBuckets['non-kopi'][] = $k;
                } else {
                    $it['category'] = 'Kopi';
                    $catBuckets['kopi'][] = $k;
                }
            }
            unset($it);

            // Ordered frappes matching official Kenangan Frappe menu
            $desiredFrappeOrder = [
                'butterscotchkenanganfrappe', 'matchakenanganfrappe', 'kopikenanganmantanfrappe',
                'vanillakenanganfrappe', 'dutchchocokenanganfrappe', 'chococaramelfrappe'
            ];
            $catBuckets['frappe'] = array_values(array_unique(array_merge(
                array_intersect($desiredFrappeOrder, $catBuckets['frappe']),
                $catBuckets['frappe']
            )));

            $menuData['categories'] = [
                ['id' => 'bundle-promo-bundling', 'name' => 'Promo & Combo', 'kind' => 'bundle', 'items' => !empty($catBuckets['bundle']) ? $catBuckets['bundle'] : ['zf-b-4pcs-35k', 'zf-b-3-35k', 'zf-b-large2-32k']],
                ['id' => 'item-kopi', 'name' => 'Kopi', 'kind' => 'item', 'items' => array_values(array_unique($catBuckets['kopi']))],
                ['id' => 'item-non-kopi', 'name' => 'Non-Kopi', 'kind' => 'item', 'items' => array_values(array_unique($catBuckets['non-kopi']))],
                ['id' => 'item-kenangan-frappe', 'name' => 'Kenangan Frappe', 'kind' => 'item', 'items' => $catBuckets['frappe']],
                ['id' => 'item-tea', 'name' => 'Tea', 'kind' => 'item', 'items' => array_values(array_unique($catBuckets['tea']))],
                ['id' => 'item-food', 'name' => 'Food', 'kind' => 'item', 'items' => array_values(array_unique($catBuckets['food']))],
            ];
        }

        $items = $menuData['items'] ?? [];
        $totalCount = count($items);
        $availableCount = 0;
        $soldOutCount = 0;
        foreach ($items as $it) {
            if (!empty($it['isSoldOut'])) $soldOutCount++;
            else $availableCount++;
        }

        $menuData['brand'] = $brand;
        $menuData['outletCode'] = $outletCode;
        $menuData['items'] = $items;
        $menuData['stats'] = [
            'total' => $totalCount,
            'available' => $availableCount,
            'soldOut' => $soldOutCount,
        ];

        return $menuData;
    }

    /**
     * Apply client specific pricing formulas and bundling rules
     */
    protected function applyClientPricingAndBundling(array &$menuData, string $brand): void
    {
        $brand = $this->normalizeBrand($brand);
        $items = &$menuData['items'];

        $candidates1719 = [];
        $candidates1719Drinks = [];
        $candidates19Drinks = [];

        foreach ($items as &$item) {
            if (!empty($item['isBundle'])) continue;

            $key = $item['key'] ?? '';
            $outletPrice = (int) ($item['outletPrice'] ?? $item['normalPrice'] ?? $item['price'] ?? 0);
            if ($outletPrice <= 0) $outletPrice = (int) ($item['price'] ?? 0);

            $isSoldOut = !empty($item['isSoldOut']) || !empty($item['notCarried']);
            $isFood = !empty($item['isFood']) || (isset($item['category']) && strtolower($item['category']) === 'food');

            // Client Formulas:
            $calcPrice = $outletPrice;
            $largePrice = null;

            if ($brand === 'kopken') {
                // KOPKEN: HARGA ASLI - 50% + 4.500/cup, Large: +5.000
                $calcPrice = (int) round(($outletPrice * 0.5) + 4500);
                $largePrice = $calcPrice + 5000;
            } elseif ($brand === 'tomoro') {
                // TOMORO: HARGA ASLI - 50% + 5.000/cup (Small only)
                $calcPrice = (int) round(($outletPrice * 0.5) + 5000);
                $largePrice = null;
            } elseif ($brand === 'fore') {
                // FORE: HARGA ASLI - 25% + 2.000/cup, Large: +5.000
                $calcPrice = (int) (round((($outletPrice * 0.75) + 2000) / 500) * 500);
                $largePrice = $calcPrice + 5000;
            } elseif ($brand === 'jiwa') {
                // JANJI JIWA: all menu disc 50% max 20k, fee per menu 10k
                $discount = min($outletPrice * 0.5, 20000);
                $calcPrice = (int) round(($outletPrice - $discount) + 10000);
                $normalPrice = max($outletPrice, (int) round(($calcPrice - 5000) * 2));
                if ($normalPrice <= $calcPrice) $normalPrice = $calcPrice + 6000;
                $outletPrice = $normalPrice;
            }

            $item['price'] = $calcPrice;
            $item['normalPrice'] = $outletPrice;

            if ($largePrice !== null && !$isFood && (empty($item['options']['sizes']) || in_array('Large', $item['options']['sizes'], true))) {
                $item['largePrice'] = $largePrice;
                $item['largeNormal'] = $item['largeOutletPrice'] ?? ($outletPrice + 6000);
            } else {
                unset($item['largePrice'], $item['largeNormal'], $item['largeOutletPrice']);
            }

            // Bundling eligibility (Kopken)
            $isExcludedFromBundling = (bool) preg_match('/avocado|oreo|dutch|frappe|1\s*l|liter/i', ($item['name'] ?? '') . ' ' . $key);
            $base1719Keys = [
                'kopikenanganmantan', 'spanishlatte', 'pistachioarenlatte', 'bananaamericano', 
                'bluberiamericano', 'blueberryamericano', 'americano', 'taromilk', 'tarocloudmilktea', 
                'ogthaitea', 'earlgreytea', 'earlgreymilktea', 'lemonblacktea', 'freshlemonade',
                'classicpeanutbutterpanini', 'triplecheesebagel', 'doublechocobagel', 'wahyusapitoast', 
                'oboycoffeetoast', 'chocolatecroissant', 'sweethoneysoftbakedcookie', 
                'oatmealraisinsoftbakedcookie', 'friendchipcookie', 'saltbreadabon'
            ];

            if ($brand === 'kopken' && !$isExcludedFromBundling && (($outletPrice >= 16000 && $outletPrice <= 19500) || in_array($key, $base1719Keys, true))) {
                $candItem = [
                    'key' => $key,
                    'name' => $item['name'],
                    'img' => $item['img'],
                    'category' => $item['category'] ?? 'Menu',
                    'isSoldOut' => $isSoldOut,
                    'outletPrice' => $outletPrice,
                    'bundlePrice' => null,
                    'premium' => 0,
                    'options' => $item['options'] ?? [],
                ];
                $candidates1719[] = $candItem;
                if (!$isFood) $candidates1719Drinks[] = $candItem;
            }

            // Bundling Large 2 (Rp32.000)
            if ($brand === 'kopken' && !$isFood && !preg_match('/baby|cino|black\s*aren|kopsu.*black|avocado|oreo|dutch|frappe|1\s*l|liter/i', ($item['name'] ?? '') . ' ' . $key)) {
                if (($outletPrice >= 18000 && $outletPrice <= 19500) || in_array($key, ['kopikenanganmantan', 'spanishlatte', 'pistachioarenlatte', 'bananaamericano', 'bluberiamericano'], true)) {
                    $largeOpt = $item['options'] ?? [];
                    $largeOpt['sizes'] = ['Large'];
                    $candidates19Drinks[] = [
                        'key' => $key,
                        'name' => $item['name'],
                        'img' => $item['img'],
                        'category' => $item['category'] ?? 'Kopi',
                        'isSoldOut' => $isSoldOut,
                        'outletPrice' => $outletPrice,
                        'largeOutletPrice' => $outletPrice + 6000,
                        'bundlePrice' => 16000,
                        'premium' => 0,
                        'options' => $largeOpt,
                    ];
                }
            }
        }
        unset($item);

        // Inject dynamic Kopken Bundling bundles
        if ($brand === 'kopken') {
            $bundleItems = [];

            // Bundle 1: 4 Pcs (2 Minuman + 2 Roti) - Rp35.000
            if (!empty($candidates1719Drinks) || !empty($candidates1719)) {
                $candList = !empty($candidates1719Drinks) ? $candidates1719Drinks : $candidates1719;
                $bundleItems[] = [
                    'key' => 'zf-b-4pcs-35k',
                    'id' => 'zf-b-4pcs-35k',
                    'name' => 'Paket 2 Roti + 2 Minuman (Bundling 4 Pcs)',
                    'category' => 'Promo & Combo',
                    'price' => 35000,
                    'normalPrice' => 58000,
                    'outletPrice' => 58000,
                    'img' => '/img/bundling-4pcs-35k.png',
                    'desc' => 'Bebas pilih 2 minuman reguler (menu 17k–19k) + 2 Roti Coklat Klasik (Total 4 item).',
                    'isSoldOut' => count(array_filter($candList, fn($c) => !$c['isSoldOut'])) < 2,
                    'isBundle' => true,
                    'slots' => 2,
                    'bundleSlots' => 2,
                    'fixedItems' => ['2 Roti Coklat Klasik'],
                    'bundlePricePerSlot' => 17500,
                    'candidates' => $candList,
                    'tag' => '4 Pcs',
                    'group' => 'Bundling 4 Pcs (2 Minuman + 2 Roti)',
                ];
            }

            // Bundle 2: 3 Menu (Reguler / Food) - Rp35.000
            if (!empty($candidates1719)) {
                $bundleItems[] = [
                    'key' => 'zf-b-3-35k',
                    'id' => 'zf-b-3-35k',
                    'name' => 'Paket 3 Menu Bebas Pilih (Reguler / Food)',
                    'category' => 'Promo & Combo',
                    'price' => 35000,
                    'normalPrice' => 57000,
                    'outletPrice' => 57000,
                    'img' => '/img/bundling-3-cup-reguler.png',
                    'desc' => 'Bebas pilih 3 item (minuman reguler atau food / roti) dari menu harga outlet Rp 17.000 - Rp 19.000.',
                    'isSoldOut' => count(array_filter($candidates1719, fn($c) => !$c['isSoldOut'])) < 3,
                    'isBundle' => true,
                    'slots' => 3,
                    'bundleSlots' => 3,
                    'bundlePricePerSlot' => 35000 / 3,
                    'candidates' => $candidates1719,
                    'tag' => 'Hemat Rp 22.000',
                    'group' => 'Bundling 3 (Food & Drinks)',
                ];
            }

            // Bundle 3: Large 2 - Rp32.000
            if (!empty($candidates19Drinks)) {
                $bundleItems[] = [
                    'key' => 'zf-b-large2-32k',
                    'id' => 'zf-b-large2-32k',
                    'name' => 'Paket 2 Cup Large Bebas Pilih',
                    'category' => 'Promo & Combo',
                    'price' => 32000,
                    'normalPrice' => 50000,
                    'outletPrice' => 50000,
                    'img' => '/img/bundling-2-cup-large.png',
                    'desc' => 'Bebas pilih 2 minuman size Large favoritmu (Kenangan Mantan, Spanish Latte, Pistachio, dll).',
                    'isSoldOut' => count(array_filter($candidates19Drinks, fn($c) => !$c['isSoldOut'])) < 2,
                    'isBundle' => true,
                    'slots' => 2,
                    'bundleSlots' => 2,
                    'size' => 'Large',
                    'bundlePricePerSlot' => 16000,
                    'candidates' => $candidates19Drinks,
                    'tag' => 'Hemat Rp 18.000',
                    'group' => 'Bundling 2 Large',
                ];
            }

            // Remove any old non-bundling items from Promo & Combo
            $cleanItems = [];
            foreach ($items as $it) {
                if (!empty($it['isBundle'])) continue;
                $itCat = strtolower(trim((string)($it['category'] ?? '')));
                if ($itCat === 'promo & combo' || $itCat === 'promo' || $itCat === 'combo') {
                    continue;
                }
                $cleanItems[] = $it;
            }

            // Prepend bundles to items
            $items = array_merge($bundleItems, $cleanItems);
        }
    }

    /**
     * Get item detail & options
     */
    public function getItemDetail(string $brand = 'kopken', ?string $outletCode = null, ?string $code = null, ?string $key = null): array
    {
        $brand = $this->normalizeBrand($brand);
        $outletCode = $this->normalizeOutletCode($brand, trim((string) $outletCode));
        $menu = $this->getMenu($brand, $outletCode);

        $matchedItem = null;
        foreach ($menu['items'] ?? [] as $mi) {
            if (($key !== null && ($mi['key'] ?? '') === $key) || ($code !== null && ($mi['code'] ?? '') === $code)) {
                $matchedItem = $mi;
                break;
            }
        }

        if (!$matchedItem) {
            return ['isSoldOut' => false, 'groups' => [], 'prices' => []];
        }

        $basePrice = (int) ($matchedItem['price'] ?? 0);

        // Food items don't have size, temperature, ice, sugar options per client revision
        if (!empty($matchedItem['isFood'])) {
            return [
                'isSoldOut' => !empty($matchedItem['isSoldOut']),
                'groups' => [],
                'prices' => ['default' => $basePrice],
                'desc' => $matchedItem['desc'] ?? '',
            ];
        }

        $opts = $matchedItem['options'] ?? [];
        $groups = [];

        if (!empty($opts['sizes']) && is_array($opts['sizes'])) {
            $vals = array_map(fn($s) => ['id' => $s, 'text' => $s], $opts['sizes']);
            $groups[] = ['id' => 'size', 'name' => 'Ukuran', 'values' => $vals, 'default' => $opts['sizes'][0]];
        }
        if (!empty($opts['temps']) && is_array($opts['temps'])) {
            $vals = array_map(fn($s) => ['id' => $s, 'text' => $s], $opts['temps']);
            $groups[] = ['id' => 'temp', 'name' => 'Suhu', 'values' => $vals, 'default' => $opts['temps'][0]];
        }
        if (!empty($opts['ices']) && is_array($opts['ices'])) {
            $vals = array_map(fn($s) => ['id' => $s, 'text' => $s], $opts['ices']);
            $groups[] = ['id' => 'ice', 'name' => 'Level Es', 'values' => $vals, 'default' => $opts['ices'][0]];
        }
        if (!empty($opts['sugars']) && is_array($opts['sugars'])) {
            $vals = array_map(fn($s) => ['id' => $s, 'text' => $s], $opts['sugars']);
            $groups[] = ['id' => 'sugar', 'name' => 'Level Gula', 'values' => $vals, 'default' => $opts['sugars'][0]];
        }

        $basePrice = (int) ($matchedItem['price'] ?? 0);
        $largePrice = (int) ($matchedItem['largePrice'] ?? ($basePrice + 5000));

        $prices = ['default' => $basePrice];
        if (!empty($opts['sizes']) && in_array('Large', $opts['sizes'], true)) {
            $prices['Large'] = $largePrice;
            $prices['Regular'] = $basePrice;
        }

        return [
            'isSoldOut' => !empty($matchedItem['isSoldOut']),
            'groups' => $groups,
            'prices' => $prices,
            'desc' => $matchedItem['desc'] ?? '',
        ];
    }

    /**
     * Default outlet image based on brand
     */
    public function getDefaultOutletImage(string $brand = 'kopken'): string
    {
        return match ($this->normalizeBrand($brand)) {
            'tomoro' => 'https://images.unsplash.com/photo-1509785307050-d4066910ec1e?q=80&w=400&auto=format&fit=crop',
            'fore'   => 'https://images.unsplash.com/photo-1442512595331-e89e73853f31?q=80&w=400&auto=format&fit=crop',
            'jiwa'   => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?q=80&w=400&auto=format&fit=crop',
            default  => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=400&auto=format&fit=crop',
        };
    }

    /**
     * Default product image based on brand & menu name
     */
    protected function getDefaultItemImage(string $brand, string $name): string
    {
        return 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop';
    }

    /**
     * Evaluate outlet open/closed status based on current WIB time
     */
    public function evaluateOutletHours(array $outlet, string $brand = 'kopken'): array
    {
        $brandConfig = config("brands.{$brand}", []);
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $currentMinutes = ((int) $now->format('G')) * 60 + ((int) $now->format('i'));

        $hoursStr = (string) ($outlet['hours'] ?? $brandConfig['shop']['hours'] ?? '07:00 - 22:00 WIB');
        $isOpen = $outlet['isOpen'] ?? true;
        $statusText = 'Buka';

        if (preg_match('/(\d{1,2})[:.](\d{2})\s*[-–—]\s*(\d{1,2})[:.](\d{2})/', $hoursStr, $m)) {
            $openMinutes = ((int) $m[1]) * 60 + ((int) $m[2]);
            $closeMinutes = ((int) $m[3]) * 60 + ((int) $m[4]);

            if ($closeMinutes > $openMinutes) {
                $isOpen = ($currentMinutes >= $openMinutes && $currentMinutes < $closeMinutes);
            } else {
                $isOpen = ($currentMinutes >= $openMinutes || $currentMinutes < $closeMinutes);
            }
        }

        $outlet['isOpen'] = $isOpen;
        $outlet['canOrder'] = $isOpen;
        $outlet['statusText'] = $isOpen ? 'Buka' : 'Tutup';
        $outlet['hours'] = $hoursStr;

        return $outlet;
    }

    /**
     * Calculate Haversine distance in kilometers
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Save order & generate WhatsApp checkout format
     */
    public function saveOrder(array $data): array
    {
        $prefix = (string) config('store.order_prefix', 'ZF');
        $randomCode = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $orderCode = $prefix . '-' . $randomCode;

        $items = $data['items'] ?? [];
        $totalQty = 0;
        foreach ($items as $it) {
            $totalQty += (int) ($it['qty'] ?? 1);
        }

        $singleItemFeeConfig = (int) config('fees.single_item_fee', 2000);
        $hasSingleItemFee = ($totalQty === 1) || !empty($data['hasSingleItemFee']);
        $singleItemFee = $hasSingleItemFee ? $singleItemFeeConfig : 0;

        $plasticBagFeeConfig = (int) config('fees.plastic_bag_fee', 1000);
        $hasPlasticBag = !empty($data['plasticBag']);
        $plasticBagFee = $hasPlasticBag ? $plasticBagFeeConfig : 0;

        $subtotal = (float) ($data['subtotal'] ?? 0);
        $total = $subtotal + $singleItemFee + $plasticBagFee;

        $pickupTime = $data['pickupTime'] ?? 'Sekarang';
        $brand = $this->normalizeBrand($data['brand'] ?? 'kopken');
        $brandConfig = config("brands.{$brand}", []);
        $brandName = $brandConfig['name'] ?? 'Kopi Kenangan';

        $order = [
            'orderCode' => $orderCode,
            'brand' => $brand,
            'brandName' => $brandName,
            'customerName' => trim($data['customerName'] ?? 'Pelanggan'),
            'customerPhone' => trim($data['customerPhone'] ?? ''),
            'outletCode' => $data['outletCode'] ?? '',
            'outletName' => $data['outletName'] ?? 'Outlet Pilihan',
            'pickupTime' => $pickupTime,
            'notes' => trim($data['notes'] ?? ''),
            'hasPlasticBag' => $hasPlasticBag,
            'plasticBagFee' => $plasticBagFee,
            'hasSingleItemFee' => $hasSingleItemFee,
            'singleItemFee' => $singleItemFee,
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $total,
            'createdAt' => date('c'),
        ];

        // Format WhatsApp Message
        $adminPhone = (string) config('store.admin_phone', '628973034455');
        $header = (string) config('wa_format.header', '── .✦ FORMAT ORDER ZONA FITRI✦.──');
        $footer = (string) config('wa_format.footer', '');

        $waMessage = "{$header}\n";
        $waMessage .= "Brand: *{$brandName}*\n";
        $waMessage .= "Outlet: *{$order['outletName']}*\n";
        $waMessage .= "Nama Pemesan: *{$order['customerName']}*\n";
        if (!empty($order['customerPhone'])) {
            $waMessage .= "No. WhatsApp: {$order['customerPhone']}\n";
        }
        $waMessage .= "Waktu Pickup: *{$order['pickupTime']}*\n";
        $waMessage .= "--------------------------------------\n";
        $waMessage .= "*Daftar Pesanan:*\n";

        foreach ($order['items'] as $idx => $item) {
            $options = [];
            $isFood = !empty($item['isFood']) || preg_match('/food|toast|roti|bread|donut|donat|croissant|bagel|panini|pastry|muffin|danish|cookie|bun|cake|pie/i', ($item['name'] ?? '') . ' ' . ($item['category'] ?? ''));
            if (!$isFood && !empty($item['choices']) && is_array($item['choices'])) {
                foreach ($item['choices'] as $c) {
                    $val = is_array($c) ? ($c['value'] ?? '') : (string)$c;
                    if ($val !== '') $options[] = $val;
                }
            } elseif (!$isFood) {
                if (!empty($item['size'])) $options[] = $item['size'];
                if (!empty($item['temp'])) $options[] = $item['temp'];
                if (!empty($item['ice']) && ($item['temp'] ?? '') !== 'Hot') $options[] = $item['ice'];
                if (!empty($item['sugar'])) $options[] = $item['sugar'];
            }
            $optStr = (!empty($options) && empty($item['isBundle'])) ? ' (' . implode(' • ', $options) . ')' : '';

            $itemQty = (int) ($item['qty'] ?? 1);
            $itemSubtotal = ($item['subtotal'] ?? 0) > 0 ? $item['subtotal'] : (($item['price'] ?? 0) * $itemQty);
            $subtotalFormatted = number_format($itemSubtotal, 0, ',', '.');

            $waMessage .= ($idx + 1) . ". *" . ($item['name'] ?? 'Item') . "*" . $optStr . " — " . $itemQty . "x Rp " . $subtotalFormatted . "\n";
            if (!empty($item['isBundle'])) {
                foreach ($item['fixedItems'] ?? [] as $f) {
                    $waMessage .= "    • Termasuk: {$f}\n";
                }
                foreach ($item['selections'] ?? [] as $s) {
                    $sOpt = [];
                    $sFood = !empty($s['isFood']) || preg_match('/food|toast|roti|bread|donut|donat|croissant|bagel|panini|pastry|muffin|danish|cookie|bun|cake|pie/i', ($s['name'] ?? ''));
                    if (!$sFood && !empty($s['choices']) && is_array($s['choices'])) {
                        foreach ($s['choices'] as $sc) {
                            $scVal = is_array($sc) ? ($sc['value'] ?? '') : (string)$sc;
                            if ($scVal !== '') $sOpt[] = $scVal;
                        }
                    }
                    $sOptStr = !empty($sOpt) ? ' (' . implode(' • ', $sOpt) . ')' : '';
                    $waMessage .= "    • " . ($s['name'] ?? 'Menu') . $sOptStr . "\n";
                }
            }
        }

        $waMessage .= "--------------------------------------\n";
        $waMessage .= "Subtotal: Rp " . number_format($subtotal, 0, ',', '.') . "\n";
        if ($singleItemFee > 0) {
            $waMessage .= "Biaya 1 Cup: Rp " . number_format($singleItemFee, 0, ',', '.') . "\n";
        }
        if ($plasticBagFee > 0) {
            $waMessage .= "Kantong Plastik: Rp " . number_format($plasticBagFee, 0, ',', '.') . "\n";
        }
        $waMessage .= "*Total Pembayaran: Rp " . number_format($total, 0, ',', '.') . "*\n";
        if (!empty($order['notes'])) {
            $waMessage .= "Catatan: " . $order['notes'] . "\n";
        }
        $waMessage .= "Kode Order: *" . $orderCode . "*\n";
        if (!empty($footer)) {
            $waMessage .= "{$footer}\n";
        }

        $waUrl = "https://api.whatsapp.com/send?phone={$adminPhone}&text=" . rawurlencode($waMessage);

        return [
            'success' => true,
            'order' => $order,
            'waUrl' => $waUrl,
        ];
    }

    /**
     * Fast cURL HTTP GET request
     */
    protected function httpRequest(string $url, array $params = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $timeout = (int) config('api_timeout', 4);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json, text/plain, */*',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if (ini_get('allow_url_fopen')) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => $timeout,
                    'user_agent' => $userAgent,
                    'header' => "Accept: application/json\r\n",
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Cache helpers (Container /tmp directory)
     */
    protected function getCache(string $key, int $ttl): ?array
    {
        $filePath = $this->cacheDir . DIRECTORY_SEPARATOR . $key . '.json';
        if (file_exists($filePath)) {
            $mtime = @filemtime($filePath);
            if ($mtime && (time() - $mtime < $ttl)) {
                $raw = @file_get_contents($filePath);
                $data = json_decode((string) $raw, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        return null;
    }

    protected function setCache(string $key, array $data): bool
    {
        $filePath = $this->cacheDir . DIRECTORY_SEPARATOR . $key . '.json';
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return @file_put_contents($filePath, $json, LOCK_EX) !== false;
    }

    protected function deleteCache(string $key): void
    {
        $filePath = $this->cacheDir . DIRECTORY_SEPARATOR . $key . '.json';
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Fallback outlets from local master snapshot file
     */
    protected function getFallbackOutlets(string $brand): array
    {
        $masterFile = dirname(__DIR__) . "/data/outlets_master_{$brand}.json";
        if (file_exists($masterFile)) {
            $json = json_decode((string)file_get_contents($masterFile), true);
            if (is_array($json) && !empty($json)) {
                return $json;
            }
        }
        return [];
    }

    /**
     * Fallback menu from local master snapshot file
     */
    protected function getFallbackMenu(string $brand, string $outletCode): array
    {
        $masterFile = dirname(__DIR__) . "/data/menu_master_{$brand}.json";
        if (file_exists($masterFile)) {
            $json = json_decode((string)file_get_contents($masterFile), true);
            if (is_array($json) && !empty($json['items'])) {
                $json['outletCode'] = $outletCode;
                $json['source'] = 'snapshot';
                $json['live'] = true;
                return $json;
            }
        }

        return [
            'brand' => $brand,
            'outletCode' => $outletCode,
            'categories' => [],
            'items' => [],
            'stats' => ['total' => 0, 'available' => 0, 'soldOut' => 0],
            'updatedAt' => date('c'),
        ];
    }

    /**
     * Helper to slugify item name
     */
    protected function slugify(string $text): string
    {
        $s = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($text));
        return $s ?: 'item';
    }

    /**
     * Helper to guess city from address/name
     */
    protected function extractCity(string $text): string
    {
        $t = strtolower($text);
        if (str_contains($t, 'jombang')) return 'Jombang';
        if (str_contains($t, 'surabaya')) return 'Surabaya';
        if (str_contains($t, 'sidoarjo')) return 'Sidoarjo';
        if (str_contains($t, 'gresik')) return 'Gresik';
        if (str_contains($t, 'malang')) return 'Malang';
        if (str_contains($t, 'jakarta')) return 'Jakarta';
        if (str_contains($t, 'tangerang')) return 'Tangerang';
        if (str_contains($t, 'bekasi')) return 'Bekasi';
        if (str_contains($t, 'depok')) return 'Depok';
        if (str_contains($t, 'bogor')) return 'Bogor';
        if (str_contains($t, 'bandung')) return 'Bandung';
        if (str_contains($t, 'semarang')) return 'Semarang';
        if (str_contains($t, 'solo') || str_contains($t, 'surakarta')) return 'Solo';
        if (str_contains($t, 'jogja') || str_contains($t, 'yogyakarta')) return 'Yogyakarta';
        if (str_contains($t, 'denpasar') || str_contains($t, 'bali')) return 'Bali';
        if (str_contains($t, 'medan')) return 'Medan';
        if (str_contains($t, 'palembang')) return 'Palembang';
        if (str_contains($t, 'makassar')) return 'Makassar';
        return '';
    }
}
