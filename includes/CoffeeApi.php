<?php
/**
 * Zona Fitri - Multi-Brand Coffee Native API Service
 * 
 * Mendukung Kopi Kenangan, Tomoro Coffee, & Fore Coffee:
 * 1. Mengambil daftar outlet secara real-time berdasarkan brand & query pencarian.
 * 2. Menghitung jarak GPS terdekat menggunakan Haversine Formula.
 * 3. Menghitung status buka/tutup outlet berdasarkan jam operasional WIB.
 * 4. Mengambil menu lengkap & status ketersediaan stok real-time (Ready vs Habis).
 * 5. Fitur force refresh untuk pembaruan stok instan.
 * 6. Format pemesanan otomatis terintegrasi ke WhatsApp admin.
 * 7. Caching mandiri di temporary directory serverless.
 * 8. Fallback data cerdas jika remote API offline.
 */

class CoffeeApi
{
    protected string $primaryBaseUrl;
    protected string $backupBaseUrl;
    protected int $outletsTtl;
    protected int $stockTtl;
    protected string $cacheDir;

    public function __construct()
    {
        $this->primaryBaseUrl = rtrim((string) config('api_base_url', 'https://ridstore.developerngantuk.my.id/api'), '/');
        $this->backupBaseUrl = rtrim((string) config('api_backup_url', 'https://tixandcoffee.developerngantuk.my.id/api'), '/');
        $this->outletsTtl = (int) config('cache_ttl.outlets', 1800);
        $this->stockTtl = (int) config('cache_ttl.menu', 60);
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
        return in_array($b, ['kopken', 'tomoro', 'fore'], true) ? $b : 'kopken';
    }

    /**
     * Get real-time outlets for a brand with search and GPS distance calculation
     */
    public function getOutlets(string $brand = 'kopken', ?string $query = null, ?float $lat = null, ?float $lng = null): array
    {
        $brand = $this->normalizeBrand($brand);
        $query = trim((string) $query);
        $cacheKey = "outlets_{$brand}_" . md5($query . '|' . ($lat ? round($lat, 3) : '') . '|' . ($lng ? round($lng, 3) : ''));

        $cached = $this->getCache($cacheKey, $this->outletsTtl);
        if ($cached !== null) {
            foreach ($cached['outlets'] as &$o) {
                $o = $this->evaluateOutletHours($o);
            }
            unset($o);
            return $cached;
        }

        $params = [];
        if ($query !== '') {
            $params['q'] = $query;
        }
        if ($lat !== null && $lng !== null) {
            $params['lat'] = $lat;
            $params['lng'] = $lng;
        }

        // Try primary URL first, then backup
        $url = $this->primaryBaseUrl . "/{$brand}/outlets";
        $response = $this->httpRequest($url, $params);

        if (!$response || empty($response['outlets'])) {
            $backupUrl = $this->backupBaseUrl . "/{$brand}/outlets";
            $response = $this->httpRequest($backupUrl, $params);
        }

        if ($response && !empty($response['outlets'])) {
            $outlets = $response['outlets'];

            // Calculate GPS distance locally if coordinates provided
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
            }

            // Evaluate operating hours for each outlet
            foreach ($outlets as &$outlet) {
                $outlet = $this->evaluateOutletHours($outlet);
            }
            unset($outlet);

            $result = [
                'success' => true,
                'brand' => $brand,
                'source' => 'live',
                'count' => count($outlets),
                'outlets' => $outlets,
                'timestamp' => date('c'),
            ];

            $this->setCache($cacheKey, $result);
            return $result;
        }

        // Fallback to local catalog if remote API is unreachable
        $fallback = $this->getFallbackOutlets($brand, $query, $lat, $lng);
        foreach ($fallback as &$o) {
            $o = $this->evaluateOutletHours($o);
        }
        unset($o);

        return [
            'success' => true,
            'brand' => $brand,
            'source' => 'fallback',
            'count' => count($fallback),
            'outlets' => $fallback,
            'timestamp' => date('c'),
        ];
    }

    /**
     * Get real-time menu and stock per outlet
     */
    public function getMenu(string $brand = 'kopken', ?string $outletCode = null, bool $forceRefresh = false): array
    {
        $brand = $this->normalizeBrand($brand);
        $outletCode = trim((string) $outletCode);
        if ($outletCode === '') {
            $outletCode = config("brands.{$brand}.default_outlet", 'LA.CODEFIN');
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

        // Primary API request
        $url = $this->primaryBaseUrl . "/{$brand}/menu";
        $response = $this->httpRequest($url, ['outlet' => $outletCode]);

        if (!$response || empty($response['items'])) {
            $backupUrl = $this->backupBaseUrl . "/{$brand}/menu";
            $response = $this->httpRequest($backupUrl, ['outlet' => $outletCode]);
        }

        if ($response && isset($response['items'])) {
            $result = [
                'success' => true,
                'live' => $response['live'] ?? true,
                'isClosed' => $response['isClosed'] ?? false,
                'brand' => $brand,
                'outletCode' => $outletCode,
                'categories' => $response['categories'] ?? [],
                'items' => $response['items'],
                'updatedAt' => date('c'),
            ];

            $this->setCache($cacheKey, $result);
            return $this->formatMenuStats($result, $brand, $outletCode);
        }

        // Offline fallback menu
        $fallback = $this->getFallbackMenu($brand, $outletCode);
        return $this->formatMenuStats($fallback, $brand, $outletCode);
    }

    /**
     * Format menu statistics and ensure standard category items
     */
    protected function formatMenuStats(array $menuData, string $brand, string $outletCode): array
    {
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

            // Normalizing image url
            if (empty($item['img'])) {
                $item['img'] = $this->getDefaultItemImage($brand, $item['name'] ?? '');
            }
        }
        unset($item);

        // Apply Zona Fitri client pricing formulas and live-stock bundling
        $this->applyClientPricingAndBundling($menuData, $brand);

        // Client request v2: Thai Tea harus ada di kategori Non-Kopi Kopi Kenangan
        if ($brand === 'kopken' && isset($menuData['categories'])) {
            foreach ($menuData['categories'] as &$cat) {
                if (($cat['id'] ?? '') === 'item-non-kopi' || strtolower($cat['name'] ?? '') === 'non-kopi' || strtolower($cat['name'] ?? '') === 'non coffee') {
                    if (!in_array('ogthaitea', $cat['items'], true)) {
                        $bPos = array_search('bananachoco', $cat['items'], true);
                        if ($bPos !== false) {
                            array_splice($cat['items'], $bPos + 1, 0, 'ogthaitea');
                        } else {
                            $cat['items'][] = 'ogthaitea';
                        }
                    }
                    if (!in_array('thaitealoaded', $cat['items'], true)) {
                        $cat['items'][] = 'thaitealoaded';
                    }
                }
            }
            unset($cat);
        }

        // Client request v2: Sinkronisasi Tomoro (Siwalankerto & kategori Signature/Classic)
        if ($brand === 'tomoro') {
            $isSiwalankerto = stripos($outletCode, 'siwalankerto') !== false || stripos($outletCode, 'TOM-343') !== false;

            if (isset($menuData['categories'])) {
                foreach ($menuData['categories'] as &$cat) {
                    if (($cat['id'] ?? '') === 'item-tomoro-signature' || strtolower($cat['name'] ?? '') === 'tomoro signature') {
                        $desiredSignature = ['tomoroarenlatte', 'tomoroarenlatte1l', 'cheesecloudchocolate', 'tomorococonutlatte', 'tomorooatlatte', 'seasaltcloudcaramelmacchiato', 'cheesecloudlatte'];
                        $newItems = [];
                        foreach ($desiredSignature as $sigKey) {
                            if (!in_array($sigKey, $newItems, true)) {
                                $newItems[] = $sigKey;
                            }
                        }
                        foreach ($cat['items'] as $orig) {
                            if (!in_array($orig, $newItems, true)) $newItems[] = $orig;
                        }
                        $cat['items'] = $newItems;
                    }
                    if (($cat['id'] ?? '') === 'item-classic-coffee' || strtolower($cat['name'] ?? '') === 'classic coffee') {
                        $desiredClassic = ['cafelatte', 'caffeamericano', 'kopisusuaren', 'brevelatte', 'caffemocca', 'cappuccino'];
                        $newItems = [];
                        foreach ($desiredClassic as $clsKey) {
                            if (!in_array($clsKey, $newItems, true)) {
                                $newItems[] = $clsKey;
                            }
                        }
                        foreach ($cat['items'] as $orig) {
                            if (!in_array($orig, $newItems, true)) $newItems[] = $orig;
                        }
                        $cat['items'] = $newItems;
                    }
                }
                unset($cat);
            }

            if (isset($menuData['items'])) {
                foreach ($menuData['items'] as &$it) {
                    $k = $it['key'] ?? '';
                    // Standard Tomoro Price Corrections across all outlets (Client request: harga asli salah dan lebih mahal)
                    if ($k === 'caffeamericano') {
                        $it['outletPrice'] = 18000;
                        $it['normalPrice'] = 18000;
                        $it['price'] = 14000; // 18k * 0.5 + 5k
                    } elseif ($k === 'cafelatte') {
                        $it['outletPrice'] = 24000;
                        $it['normalPrice'] = 24000;
                        $it['price'] = 17000; // 24k * 0.5 + 5k
                    } elseif ($k === 'cappuccino') {
                        $it['outletPrice'] = 24000;
                        $it['normalPrice'] = 24000;
                        $it['price'] = 17000;
                    } elseif ($k === 'caffemocca') {
                        $it['outletPrice'] = 24000;
                        $it['normalPrice'] = 24000;
                        $it['price'] = 17000;
                    } elseif ($k === 'chocolate') {
                        $it['outletPrice'] = 24000;
                        $it['normalPrice'] = 24000;
                        $it['price'] = 17000;
                    } elseif ($k === 'cheesecloudchocolate') {
                        $it['outletPrice'] = 26000;
                        $it['normalPrice'] = 26000;
                        $it['price'] = 18000; // 26k * 0.5 + 5k
                        if ($isSiwalankerto) {
                            $it['isSoldOut'] = true; // HABIS di Siwalankerto per live app
                        }
                    } elseif ($k === 'tomoroarenlatte') {
                        if ($isSiwalankerto) {
                            $it['outletPrice'] = 24000;
                            $it['normalPrice'] = 24000;
                            $it['price'] = 17000;
                        }
                    }
                }
                unset($it);

                // Add 1L TOMORO Aren Latte if not present
                $has1L = false;
                foreach ($menuData['items'] as $it) {
                    if (($it['key'] ?? '') === 'tomoroarenlatte1l') { $has1L = true; break; }
                }
                if (!$has1L) {
                    $item1L = [
                        'key' => 'tomoroarenlatte1l',
                        'id' => 'tomoroarenlatte1l',
                        'code' => '990001',
                        'name' => '1L TOMORO Aren Latte',
                        'category' => 'Tomoro Signature',
                        'sourceCategory' => 'Tomoro Signature',
                        'img' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?q=80&w=400&auto=format&fit=crop',
                        'desc' => '1 Liter Master Arabica blend dengan paduan gula aren organik alami.',
                        'outletPrice' => 90000,
                        'normalPrice' => 90000,
                        'price' => 50000,
                        'isSoldOut' => false,
                        'notCarried' => false,
                        'isFood' => false,
                        'isSpecial' => true,
                        'options' => ['sizes' => ['1 Liter'], 'temps' => ['Ice'], 'ices' => ['Normal Ice'], 'sugars' => ['Normal Sugar', 'Less Sugar']],
                        'tag' => '',
                        'featured' => true,
                    ];
                    $menuData['items'][] = $item1L;
                }
            }
        }

        $items = $menuData['items'] ?? [];
        $totalCount = count($items);
        $availableCount = 0;
        $soldOutCount = 0;
        foreach ($items as $it) {
            if (!empty($it['isSoldOut'])) {
                $soldOutCount++;
            } else {
                $availableCount++;
            }
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
     * - Kopken: (Harga Asli - 50%) + 4.000, Large: +5.000
     * - Tomoro: (Harga Asli - 50%) + 5.000
     * - Fore: (Harga Asli - 25%) + 2.000, Large: +5.000
     * - Bundling 3: 35.000 (all menu asli 17k & 19k termasuk food, sync real-time stock)
     * - Bundling Large 2: 32.000 (2 cup Large dari menu reg 19k drink only, sync real-time stock)
     */
    protected function applyClientPricingAndBundling(array &$menuData, string $brand): void
    {
        $brand = $this->normalizeBrand($brand);
        $items = &$menuData['items'];

        $stockMap = [];
        $candidates1719 = [];
        $candidates19Drinks = [];

        foreach ($items as &$item) {
            if (!empty($item['isBundle'])) {
                continue;
            }

            $key = $item['key'] ?? '';
            $outletPrice = (int) ($item['outletPrice'] ?? $item['price'] ?? 0);
            $isSoldOut = !empty($item['isSoldOut']) || !empty($item['notCarried']);
            $isFood = !empty($item['isFood']) || (isset($item['category']) && strtolower($item['category']) === 'food');

            $stockMap[$key] = [
                'isSoldOut' => $isSoldOut,
                'outletPrice' => $outletPrice,
                'name' => $item['name'] ?? '',
                'img' => $item['img'] ?? '',
                'category' => $item['category'] ?? '',
                'isFood' => $isFood,
                'options' => $item['options'] ?? [],
            ];

            // Calculate selling price according to client formula
            if ($outletPrice > 0) {
                $calcPrice = $outletPrice;
                $largePrice = null;

                if ($brand === 'kopken') {
                    // RUMUS KOPKEN: HARGA ASLI - 50% + 4.000/cup
                    $calcPrice = (int) round(($outletPrice * 0.5) + 4000);
                    // Catatan: kopken fore upsize +5k
                    $largePrice = $calcPrice + 5000;
                } elseif ($brand === 'tomoro') {
                    // RUMUS TOMORO: HARGA ASLI - 50% + 5.000/cup
                    $calcPrice = (int) round(($outletPrice * 0.5) + 5000);
                    if (!empty($item['largePrice']) || !empty($item['largeOutletPrice'])) {
                        $largeOut = (int) ($item['largeOutletPrice'] ?? ($outletPrice + 5000));
                        $largePrice = (int) round(($largeOut * 0.5) + 5000);
                    }
                } elseif ($brand === 'fore') {
                    // RUMUS FORE: HARGA ASLI - 25% + 2.000/cup
                    $calcPrice = (int) round(($outletPrice * 0.75) + 2000);
                    // Catatan: kopken fore upsize +5k
                    $largePrice = $calcPrice + 5000;
                }

                $item['price'] = $calcPrice;
                $item['normalPrice'] = $outletPrice;
                if ($largePrice !== null) {
                    $item['largePrice'] = $largePrice;
                    $item['largeNormal'] = $item['largeOutletPrice'] ?? ($outletPrice + 6000);
                }
            }

            // Check if eligible for Kopken Bundling 3 (17k & 19k original price, including food)
            if ($brand === 'kopken' && ($outletPrice === 17000 || $outletPrice === 19000)) {
                $candidates1719[] = [
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
            }

            // Check if eligible for Kopken Bundling Large 2 (menu varian Large 25.000 - 27.000 sesuai revisi client v2)
            $largeOut = (int) ($item['largeOutletPrice'] ?? 0);
            if ($largeOut <= 0 && $outletPrice > 0) {
                $largeOut = $outletPrice + 6000;
            }

            // Client revision: Exclude Babyccino as it does not have Large size
            $isNotLargeEligible = (bool) preg_match('/baby|cino/i', ($item['name'] ?? '') . ' ' . $key);

            if ($brand === 'kopken' && !$isFood && !$isNotLargeEligible && ($largeOut >= 25000 && $largeOut <= 27000)) {
                $largeOpt = $item['options'] ?? [];
                $largeOpt['sizes'] = ['Large'];
                $candidates19Drinks[] = [
                    'key' => $key,
                    'name' => $item['name'],
                    'img' => $item['img'],
                    'category' => $item['category'] ?? 'Kopi',
                    'isSoldOut' => $isSoldOut,
                    'outletPrice' => $outletPrice,
                    'largeOutletPrice' => $largeOut,
                    'bundlePrice' => 16000, // 32.000 / 2 = 16.000 per cup (client revision: fix 32.000)
                    'premium' => 0,
                    'options' => $largeOpt,
                ];
            }
        }
        unset($item);

        // If Kopken, add client specific Bundling packages
        if ($brand === 'kopken' && (!empty($candidates1719) || !empty($candidates19Drinks))) {
            $bundling3 = [
                'key' => 'zf-b-3-35k',
                'id' => 'zf-b-3-35k',
                'name' => 'Bundling 3 Cup Reguler (Rp35.000)',
                'category' => 'Promo Bundling',
                'desc' => 'Pilih 3 menu dari semua menu harga asli 17.000 & 19.000 (termasuk menu food)',
                'img' => '/img/bundling-3-cup-reguler.png',
                'price' => 35000,
                'normalPrice' => 54000,
                'isBundle' => true,
                'slots' => 3,
                'group' => 'Bundling 3 (Food & Drinks)',
                'candidates' => $candidates1719,
                'isSoldOut' => false,
            ];

            $bundlingLarge2 = [
                'key' => 'zf-b-large2-32k',
                'id' => 'zf-b-large2-32k',
                'name' => 'Bundling Large 2 (Rp32.000)',
                'category' => 'Promo Bundling',
                'desc' => 'Pilih 2 cup Large dari menu varian Large 25.000–27.000 (Kenangan Mantan, Sparen, dll. Khusus minuman)',
                'img' => '/img/bundling-2-cup-large.png',
                'price' => 32000,
                'normalPrice' => 52000,
                'isBundle' => true,
                'slots' => 2,
                'size' => 'Large',
                'group' => 'Bundling 2 Large',
                'candidates' => $candidates19Drinks,
                'isSoldOut' => false,
            ];

            // Remove existing bundles and replace with client bundles
            $items = array_values(array_filter($items, fn($i) => empty($i['isBundle'])));
            array_unshift($items, $bundling3, $bundlingLarge2);

            $cats = $menuData['categories'] ?? [];
            $bundleCat = [
                'id' => 'bundle-promo-bundling',
                'name' => 'Promo Bundling',
                'kind' => 'bundle',
                'items' => ['zf-b-3-35k', 'zf-b-large2-32k'],
            ];
            $cats = array_values(array_filter($cats, fn($c) => ($c['kind'] ?? '') !== 'bundle'));
            array_unshift($cats, $bundleCat);
            $menuData['categories'] = $cats;
        } else {
            // Live sync candidates for any other bundles
            foreach ($items as &$item) {
                if (!empty($item['isBundle']) && !empty($item['candidates'])) {
                    foreach ($item['candidates'] as &$cand) {
                        $cKey = $cand['key'] ?? '';
                        if (isset($stockMap[$cKey])) {
                            $cand['isSoldOut'] = $stockMap[$cKey]['isSoldOut'];
                        }
                    }
                    unset($cand);
                }
            }
            unset($item);
        }
    }

    /**
     * Get item detail variants and options with real-time stock and client pricing
     */
    public function getItemDetail(string $brand = 'kopken', ?string $outletCode = null, ?string $code = null, ?string $key = null): array
    {
        $brand = $this->normalizeBrand($brand);
        $outletCode = trim((string) $outletCode);
        $code = trim((string) $code);
        $key = trim((string) $key);

        $url = $this->primaryBaseUrl . "/{$brand}/item";
        $params = [
            'outlet' => $outletCode,
            'code' => $code,
            'key' => $key,
        ];
        $res = $this->httpRequest($url, $params);
        if (!$res) {
            $backupUrl = $this->backupBaseUrl . "/{$brand}/item";
            $res = $this->httpRequest($backupUrl, $params);
        }

        if ($res && isset($res['prices']) && is_array($res['prices'])) {
            // Find base item in menu to know outletPrice
            $menu = $this->getMenu($brand, $outletCode);
            $matchedItem = null;
            foreach ($menu['items'] ?? [] as $mi) {
                if (($key !== '' && ($mi['key'] ?? '') === $key) || ($code !== '' && ($mi['code'] ?? '') === $code)) {
                    $matchedItem = $mi;
                    break;
                }
            }

            $basePrice = (int) ($matchedItem['outletPrice'] ?? $matchedItem['price'] ?? 19000);
            $regularCalc = (int) round(($basePrice * 0.5) + 4000);
            $largeCalc = $regularCalc + 5000;

            if ($brand === 'tomoro') {
                $regularCalc = (int) round(($basePrice * 0.5) + 5000);
                $largeCalc = $regularCalc + 5000;
            } elseif ($brand === 'fore') {
                $regularCalc = (int) round(($basePrice * 0.75) + 2000);
                $largeCalc = $regularCalc + 5000;
            }

            // Extract option IDs for specific modifier pricing (Client request v2: Juwara Beans +2k)
            $largeIds = [];
            $juwaraIds = [];
            $oatsideIds = [];

            foreach ($res['groups'] ?? [] as $g) {
                foreach ($g['values'] ?? [] as $v) {
                    $vid = (string) ($v['id'] ?? '');
                    $vtxt = strtolower(trim((string) ($v['text'] ?? '')));
                    if (strpos($vtxt, 'large') !== false) {
                        $largeIds[] = $vid;
                    }
                    if (strpos($vtxt, 'juwara') !== false) {
                        $juwaraIds[] = $vid;
                    }
                    if (strpos($vtxt, 'oatside') !== false || strpos($vtxt, 'oat') !== false) {
                        $oatsideIds[] = $vid;
                    }
                }
            }

            // Adjust variant prices
            foreach ($res['prices'] as $combo => &$p) {
                $comboParts = explode('|', (string) $combo);
                $isLarge = false;
                $isJuwara = false;
                $isOatside = false;

                foreach ($comboParts as $part) {
                    if (in_array($part, $largeIds, true)) $isLarge = true;
                    if (in_array($part, $juwaraIds, true)) $isJuwara = true;
                    if (in_array($part, $oatsideIds, true)) $isOatside = true;
                }

                $finalPrice = $regularCalc;
                if ($isLarge) {
                    $finalPrice += 5000;
                }
                if ($isJuwara) {
                    $finalPrice += 2000; // +2k ka yg ini (client request)
                }
                if ($isOatside) {
                    $finalPrice += 2000; // Client request: Juwara Beans & Oatside ubah jadi 2.000
                }

                $p = $finalPrice;
            }
            unset($p);

            return $res;
        }

        return $res ?: ['isSoldOut' => false, 'groups' => [], 'prices' => []];
    }

    /**
     * Evaluate outlet operating hours based on Asia/Jakarta (WIB) time
     */
    public function evaluateOutletHours(array $outlet): array
    {
        if (isset($outlet['isOpen']) && is_bool($outlet['isOpen']) && !empty($outlet['statusText'])) {
            return $outlet;
        }

        $hours = trim((string) ($outlet['hours'] ?? ''));
        if (empty($hours)) {
            $outlet['isOpen'] = true;
            $outlet['canOrder'] = true;
            $outlet['statusText'] = 'Buka';
            return $outlet;
        }

        // Match time pattern "07:00 - 22:00" or "08.00-21.30"
        if (preg_match('/(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})/', $hours, $m)) {
            $openMinutes = (int) $m[1] * 60 + (int) $m[2];
            $closeMinutes = (int) $m[3] * 60 + (int) $m[4];

            $nowMinutes = (int) date('H') * 60 + (int) date('i');
            $is24Hours = ($openMinutes <= 1 && $closeMinutes >= 23 * 60);

            if ($is24Hours) {
                $isOpen = true;
            } elseif ($openMinutes <= $closeMinutes) {
                $isOpen = ($nowMinutes >= $openMinutes && $nowMinutes <= $closeMinutes);
            } else {
                $isOpen = ($nowMinutes >= $openMinutes || $nowMinutes <= $closeMinutes);
            }

            $outlet['isOpen'] = $isOpen;
            $outlet['canOrder'] = $isOpen;
            $outlet['statusText'] = $isOpen ? 'Buka' : 'Tutup';
        } else {
            $outlet['isOpen'] = true;
            $outlet['canOrder'] = true;
            $outlet['statusText'] = 'Buka';
        }

        return $outlet;
    }

    /**
     * Process order submission and generate formatted WhatsApp checkout link
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

        // Single item fee: +Rp 2.000 if order is 1 pcs
        $singleItemFeeConfig = (int) config('fees.single_item_fee', 2000);
        $hasSingleItemFee = ($totalQty === 1) || !empty($data['hasSingleItemFee']);
        $singleItemFee = $hasSingleItemFee ? $singleItemFeeConfig : 0;

        // Plastic bag fee: +Rp 1.000 if selected
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
        $header = (string) config('wa_format.header', '── .✦ FORMAT ORDER ZONA FITRI ✦.──');
        $footer = (string) config('wa_format.footer', '');

        $waMessage = "{$header}\n";
        $waMessage .= "Kode Pesanan: *{$orderCode}*\n";
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
            if (!empty($item['size'])) $options[] = $item['size'];
            if (!empty($item['temp'])) $options[] = $item['temp'];
            if (!empty($item['ice'])) $options[] = $item['ice'];
            if (!empty($item['sugar'])) $options[] = $item['sugar'];
            $optStr = !empty($options) ? ' (' . implode(', ', $options) . ')' : '';

            $itemQty = (int) ($item['qty'] ?? 1);
            $itemSubtotal = ($item['subtotal'] ?? 0) > 0 ? $item['subtotal'] : (($item['price'] ?? 0) * $itemQty);
            $subtotalFormatted = number_format($itemSubtotal, 0, ',', '.');

            $waMessage .= ($idx + 1) . ". *" . ($item['name'] ?? 'Item') . "*" . $optStr . " — " . $itemQty . "x Rp " . $subtotalFormatted . "\n";
            if (!empty($item['bundleChoices'])) {
                $waMessage .= "   Isi Pilihan: _" . $item['bundleChoices'] . "_\n";
            }
            if (!empty($item['note'])) {
                $waMessage .= "   Catatan: _" . $item['note'] . "_\n";
            }
        }

        $waMessage .= "--------------------------------------\n";
        $waMessage .= "Subtotal Menu: Rp " . number_format($order['subtotal'], 0, ',', '.') . "\n";

        if ($hasSingleItemFee) {
            $waMessage .= "Fee Pesanan 1 pcs: +Rp " . number_format($singleItemFee, 0, ',', '.') . "\n";
        } else {
            $waMessage .= "Fee Pesanan: Rp 0 (Bebas fee order 2+ item) ✨\n";
        }

        if ($hasPlasticBag) {
            $waMessage .= "Kantung Belanja Plastik: +Rp " . number_format($plasticBagFee, 0, ',', '.') . "\n";
        }

        if (!empty($order['notes'])) {
            $waMessage .= "Catatan Khusus: _" . $order['notes'] . "_\n";
        }
        $waMessage .= "--------------------------------------\n";
        $waMessage .= "*TOTAL BAYAR: Rp " . number_format($order['total'], 0, ',', '.') . "*\n\n";
        if (!empty($footer)) {
            $waMessage .= "{$footer}\n\n";
        }
        $waMessage .= "Mohon segera diproses ya min, terima kasih! ☕✨";

        $waUrl = 'https://wa.me/' . $adminPhone . '?text=' . rawurlencode($waMessage);

        return [
            'success' => true,
            'orderCode' => $orderCode,
            'order' => $order,
            'waMessage' => $waMessage,
            'waUrl' => $waUrl,
        ];
    }

    /**
     * Perform HTTP GET request with cURL and stream context fallback
     */
    protected function httpRequest(string $url, array $params = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $timeout = (int) config('api_timeout', 8);

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZonaFitriNative/1.0 (Windows NT 10.0)');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
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
                    'user_agent' => 'ZonaFitriNative/1.0',
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
     * Calculate Haversine distance between two coordinates in kilometers
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
     * Cache helpers
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
     * Default product image based on brand & menu name
     */
    protected function getDefaultItemImage(string $brand, string $name): string
    {
        return 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop';
    }

    /**
     * Built-in fallback outlets for zero downtime
     */
    protected function getFallbackOutlets(string $brand, ?string $query, ?float $lat, ?float $lng): array
    {
        $fallbacks = [
            'kopken' => [
                [
                    'code' => 'LA.CODEFIN',
                    'id' => 101,
                    'name' => 'Codefin Kemang Jakarta Selatan',
                    'address' => 'Jl. Kemang Raya No. 10, RT 09/RW 01, Bangka, Kec. Mampang Prapatan, Jakarta Selatan 12730',
                    'city' => 'Jakarta Selatan',
                    'lat' => -6.2608,
                    'lng' => 106.8156,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '07:00 - 22:00',
                    'category' => 'Shop House',
                    'phone' => '0817 6562 292',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'KK.JKT.RKKSTBNJB',
                    'id' => 3998,
                    'name' => 'Ruko KS Tubun Jakarta Barat',
                    'address' => 'Jl. KS Tubun No. 32 A, Palmerah, Jakarta Barat 11420',
                    'city' => 'Jakarta Barat',
                    'lat' => -6.1888,
                    'lng' => 106.8072,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '00:01 - 23:19',
                    'category' => 'Shop House',
                    'phone' => '0817 6562 292',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'JKT.THAMCIT',
                    'id' => 205,
                    'name' => 'Thamrin City Jakarta Pusat',
                    'address' => 'Thamrin City Lt. Dasar, Jl. Thamrin Boulevard, Tanah Abang, Jakarta Pusat 10230',
                    'city' => 'Jakarta Pusat',
                    'lat' => -6.1952,
                    'lng' => 106.8188,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '08:00 - 21:00',
                    'category' => 'Mall',
                    'phone' => '0817 9875 980',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'KK.JKT.CKJNG',
                    'id' => 3120,
                    'name' => 'Cikini Jakarta Pusat',
                    'address' => 'Jl. Cikini Raya No. 64, Menteng, Jakarta Pusat 10330',
                    'city' => 'Jakarta Pusat',
                    'lat' => -6.1915,
                    'lng' => 106.8405,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '07:00 - 22:00',
                    'category' => 'Shop House',
                    'phone' => '0817 6562 292',
                    'distanceKm' => null,
                ],
            ],
            'tomoro' => [
                [
                    'code' => 'TMR-627779',
                    'name' => 'Cipete Raya Jakarta Selatan',
                    'address' => 'Jl. Cipete Raya No. 14, Cipete Selatan, Cilandak, Jakarta Selatan',
                    'city' => 'Jakarta Selatan',
                    'lat' => -6.27779,
                    'lng' => 106.799476,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '07:00 - 22:00',
                    'category' => 'Shop House',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'TMR-624431',
                    'name' => 'Blok M Plaza Jakarta Selatan',
                    'address' => 'Plaza Blok M Lt. LG, Jl. Bulungan No. 76, Jakarta Selatan',
                    'city' => 'Jakarta Selatan',
                    'lat' => -6.24431,
                    'lng' => 106.79721,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '07:00 - 22:00',
                    'category' => 'Mall',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'TMR-619743',
                    'name' => 'Chubb Square MH Thamrin',
                    'address' => 'Chubb Square Lt. B-1 Unit B3, Jl. MH Thamrin No. 8-9, Jakarta Pusat',
                    'city' => 'Jakarta Pusat',
                    'lat' => -6.19743,
                    'lng' => 106.82112,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '07:00 - 22:00',
                    'category' => 'Office',
                    'distanceKm' => null,
                ],
            ],
            'fore' => [
                [
                    'code' => 'KOKAS',
                    'name' => 'Kota Kasablanka Jakarta Selatan',
                    'address' => 'Mall Kota Kasablanka Lt. UG, Jl. Casablanca Raya No. 88, Tebet, Jakarta Selatan',
                    'city' => 'Jakarta Selatan',
                    'lat' => -6.2241,
                    'lng' => 106.8427,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '08:00 - 22:00',
                    'category' => 'Mall',
                    'distanceKm' => null,
                ],
                [
                    'code' => 'SENAYAN_CITY',
                    'name' => 'Senayan City Jakarta Pusat',
                    'address' => 'Senayan City Lt. LG, Jl. Asia Afrika No. 19, Gelora, Tanah Abang, Jakarta Pusat',
                    'city' => 'Jakarta Pusat',
                    'lat' => -6.2272,
                    'lng' => 106.7984,
                    'isOpen' => true,
                    'canOrder' => true,
                    'statusText' => 'Buka',
                    'hours' => '08:00 - 22:00',
                    'category' => 'Mall',
                    'distanceKm' => null,
                ],
            ],
        ];

        $list = $fallbacks[$brand] ?? $fallbacks['kopken'];

        // Filter by query if supplied
        if ($query !== '' && $query !== null) {
            $qLower = strtolower($query);
            $list = array_values(array_filter($list, function ($it) use ($qLower) {
                return str_contains(strtolower($it['name'] ?? ''), $qLower)
                    || str_contains(strtolower($it['address'] ?? ''), $qLower)
                    || str_contains(strtolower($it['city'] ?? ''), $qLower);
            }));
        }

        // Distance calculate
        if ($lat !== null && $lng !== null) {
            foreach ($list as &$item) {
                if (!empty($item['lat']) && !empty($item['lng'])) {
                    $item['distanceKm'] = $this->calculateDistance($lat, $lng, (float)$item['lat'], (float)$item['lng']);
                }
            }
            unset($item);
            usort($list, fn($a, $b) => ($a['distanceKm'] ?? 999999) <=> ($b['distanceKm'] ?? 999999));
        }

        return $list;
    }

    /**
     * Built-in fallback menu for zero downtime
     */
    protected function getFallbackMenu(string $brand, string $outletCode): array
    {
        $defaultOptions = [
            'sizes' => ['Regular', 'Large'],
            'temps' => ['Ice', 'Hot'],
            'ices' => ['Normal Ice', 'Less Ice', 'No Ice'],
            'sugars' => ['Normal Sugar', 'Less Sugar', 'No Sugar'],
        ];

        $categories = [
            ['id' => 'item-kopi', 'name' => 'Kopi', 'kind' => 'item'],
            ['id' => 'item-non-kopi', 'name' => 'Non-Kopi', 'kind' => 'item'],
            ['id' => 'item-frappe', 'name' => 'Frappe & Blended', 'kind' => 'item'],
            ['id' => 'item-tea', 'name' => 'Tea', 'kind' => 'item'],
        ];

        if ($brand === 'kopken') {
            $items = [
                [
                    'key' => 'kopikenanganmantan',
                    'id' => 'kopikenanganmantan',
                    'name' => 'Kopi Kenangan Mantan',
                    'category' => 'Kopi',
                    'price' => 15000,
                    'normalPrice' => 19000,
                    'largePrice' => 19000,
                    'isSoldOut' => false,
                    'img' => 'https://cdn.kopikenangan.com/public/da484154-47b1-a756-54a4-8b3490fb0eae',
                    'desc' => 'Espresso dengan susu segar dan manis legitnya gula aren asli Indonesia.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'kopisusublackaren',
                    'id' => 'kopisusublackaren',
                    'name' => 'Kopi Susu Black Aren',
                    'category' => 'Kopi',
                    'price' => 16000,
                    'normalPrice' => 21000,
                    'largePrice' => 21000,
                    'isSoldOut' => false,
                    'img' => 'https://cdn.kopikenangan.com/public/2ba99b9b-7912-397a-9fea-a21d4073a197',
                    'desc' => 'Kopi susu khas dengan aroma smoky brown sugar aren pilihan.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'americano',
                    'id' => 'americano',
                    'name' => 'Americano',
                    'category' => 'Kopi',
                    'price' => 14000,
                    'normalPrice' => 17000,
                    'largePrice' => 19000,
                    'isSoldOut' => false,
                    'img' => 'https://cdn.kopikenangan.com/public/36155ddd-784d-a7d9-4d02-13d596417078',
                    'desc' => 'Espresso bold berkualitas yang disajikan dengan air murni segar.',
                    'options' => $defaultOptions,
                    'featured' => false,
                ],
                [
                    'key' => 'taromilk',
                    'id' => 'taromilk',
                    'name' => 'Taro Milk',
                    'category' => 'Non-Kopi',
                    'price' => 15000,
                    'normalPrice' => 19000,
                    'largePrice' => 20000,
                    'isSoldOut' => false,
                    'img' => 'https://cdn.kopikenangan.com/ID/release/pub/kk-api-management/product/d388cac3-16df-4519-9e80-f8ef633048b9?',
                    'desc' => 'Perpaduan lembut susu creamy dengan ekstrak taro wangi.',
                    'options' => $defaultOptions,
                    'featured' => false,
                ],
                [
                    'key' => 'dutchchocolate',
                    'id' => 'dutchchocolate',
                    'name' => 'Dutch Chocolate',
                    'category' => 'Non-Kopi',
                    'price' => 19000,
                    'normalPrice' => 26000,
                    'largePrice' => 25000,
                    'isSoldOut' => false,
                    'img' => 'https://cdn.kopikenangan.com/public/e7dfc639-f112-28b7-4cff-8e1cda6f82b5',
                    'desc' => 'Cokelat Belanda pekat yang rich, creamy, dan tidak terlalu manis.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'butterscotcharenlatte',
                    'id' => 'butterscotcharenlatte',
                    'name' => 'Butterscotch Aren Latte',
                    'category' => 'Kopi',
                    'price' => 16000,
                    'normalPrice' => 20000,
                    'largePrice' => 20000,
                    'isSoldOut' => true,
                    'img' => 'https://cdn.kopikenangan.com/public/a1cb7272-f6e2-bb69-950d-59bc87515fd2',
                    'desc' => 'Aroma butterscotch gurih dan gula aren berpadu nikmat.',
                    'options' => $defaultOptions,
                    'featured' => false,
                ],
            ];
        } elseif ($brand === 'tomoro') {
            $items = [
                [
                    'key' => 'tomoro-aren-latte',
                    'id' => 'tomoro-aren-latte',
                    'name' => 'Tomoro Aren Latte',
                    'category' => 'Kopi',
                    'price' => 18000,
                    'normalPrice' => 22000,
                    'largePrice' => 22000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Master Arabica blend dengan paduan gula aren organik alami.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'tomoro-oat-latte',
                    'id' => 'tomoro-oat-latte',
                    'name' => 'Tomoro Oat Latte',
                    'category' => 'Kopi',
                    'price' => 24000,
                    'normalPrice' => 28000,
                    'largePrice' => 28000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Susu gandum premium dipadukan espresso Arabica seimbang.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'tomoro-cloud-matcha',
                    'id' => 'tomoro-cloud-matcha',
                    'name' => 'Cloud Matcha Frappe',
                    'category' => 'Frappe & Blended',
                    'price' => 22000,
                    'normalPrice' => 26000,
                    'largePrice' => 26000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Matcha asli dengan foam creamy tebal yang menyegarkan.',
                    'options' => $defaultOptions,
                    'featured' => false,
                ],
            ];
        } else {
            $items = [
                [
                    'key' => 'fore-pandan-latte',
                    'id' => 'fore-pandan-latte',
                    'name' => 'Pandan Latte',
                    'category' => 'Kopi',
                    'price' => 26000,
                    'normalPrice' => 32000,
                    'largePrice' => 32000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Kopi susu beraroma daun pandan wangi alami khas Fore Coffee.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'fore-aren-latte',
                    'id' => 'fore-aren-latte',
                    'name' => 'Aren Latte Classic',
                    'category' => 'Kopi',
                    'price' => 23000,
                    'normalPrice' => 29000,
                    'largePrice' => 29000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Espresso Arabica pilihan dipadu susu gurih dan gula aren premium.',
                    'options' => $defaultOptions,
                    'featured' => true,
                ],
                [
                    'key' => 'fore-matcha-chizu',
                    'id' => 'fore-matcha-chizu',
                    'name' => 'Matcha Chizu',
                    'category' => 'Non-Kopi',
                    'price' => 28000,
                    'normalPrice' => 35000,
                    'largePrice' => 35000,
                    'isSoldOut' => false,
                    'img' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?q=80&w=400&auto=format&fit=crop',
                    'desc' => 'Green tea murni dengan topping krim keju gurih creamy.',
                    'options' => $defaultOptions,
                    'featured' => false,
                ],
            ];
        }

        return [
            'success' => true,
            'live' => false,
            'isClosed' => false,
            'brand' => $brand,
            'outletCode' => $outletCode,
            'categories' => $categories,
            'items' => $items,
            'updatedAt' => date('c'),
        ];
    }
}
