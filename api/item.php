<?php
/**
 * API Endpoint: Item Detail & Real-time Variants/Addons & Prototype Options
 * Supports:
 * - /api/kopken/item?outlet=LA.CODEFIN&code=7833007&key=kopikenanganmantan
 * - /api/menu/options?product=kopken-10019&store=KK.BDG.GRAPOS
 * - /api/menu/options?product=kopken-blueberry-cheesecake-frappe&store=KK.BDG.GRAPOS
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

try {
    $api = new CoffeeApi();

    $brand = $_GET['brand'] ?? '';
    $outletCode = $_GET['outlet'] ?? $_GET['outletCode'] ?? $_GET['store'] ?? '';
    $code = $_GET['code'] ?? '';
    $key = $_GET['key'] ?? '';
    $product = $_GET['product'] ?? '';

    if (!empty($product)) {
        if (preg_match('/^([a-z0-9_-]+)-(.*)$/i', $product, $m)) {
            $prefix = strtolower($m[1]);
            $remainder = $m[2];
            if (in_array($prefix, ['kopken', 'kopi-kenangan', 'kk'], true)) {
                $brand = 'kopken';
                $code = $remainder;
                $key = $remainder;
            } elseif (in_array($prefix, ['fore', 'fore-coffee'], true)) {
                $brand = 'fore';
                $code = $remainder;
                $key = $remainder;
            } elseif (in_array($prefix, ['tomoro', 'tomoro-coffee'], true)) {
                $brand = 'tomoro';
                $code = $remainder;
                $key = $remainder;
            } elseif (in_array($prefix, ['jiwa', 'janji-jiwa', 'janjiw'], true)) {
                $brand = 'jiwa';
                $code = $remainder;
                $key = $remainder;
            } else {
                $code = $remainder;
                $key = $remainder;
            }
        } else {
            $code = $product;
            $key = $product;
        }
    }

    if (empty($brand)) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('#/api/([a-zA-Z0-9_-]+)/item#', $uri, $m)) {
            $brand = $m[1];
        }
    }
    if (empty($brand)) {
        $brand = 'kopken';
    }

    $brand = $api->normalizeBrand($brand);

    if (empty($outletCode)) {
        $rawDef = config("brands.{$brand}.defaultOutlet", config("brands.{$brand}.default_outlet", ''));
        $outletCode = trim(explode(',', (string)$rawDef)[0]);
    }

    $result = $api->getItemDetail($brand, $outletCode, $code, $key);

    // Structure compatible with BOTH Zona_Fitri store.js AND Prototype jasdor
    $response = [
        'success' => true,
        'data' => [
            'productId' => $result['productId'] ?? ($brand . '-' . ($code ?: $key)),
            'name' => $result['name'] ?? '',
            'basePrice' => $result['basePrice'] ?? 0,
            'groups' => $result['groups'] ?? [],
        ],
        'pricing' => $result['pricing'] ?? [
            'id' => $brand,
            'discountPercent' => $brand === 'fore' ? 25 : ($brand === 'tomoro' ? 44 : ($brand === 'jiwa' ? 40 : 38)),
            'serviceFeePerCup' => $brand === 'fore' ? 0 : 2500,
            'productOverrides' => [],
        ],
        'isSoldOut' => $result['isSoldOut'] ?? false,
        'groups' => $result['groups'] ?? [],
        'prices' => $result['prices'] ?? ['default' => $result['basePrice'] ?? 0],
        'desc' => $result['desc'] ?? '',
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Gagal mengambil detail varian item: ' . $e->getMessage(),
    ]);
}
