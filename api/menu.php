<?php
/**
 * API Endpoint: Menu & Real-time Stock per Outlet
 * Supports:
 * - /api/kopken/menu?outlet=LA.CODEFIN
 * - /api/tomoro/menu?outlet=TMR-627779
 * - /api/fore/menu?outlet=KOKAS
 * - /api/menu.php?brand=kopken&outlet=LA.CODEFIN
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

try {
    $api = new CoffeeApi();

    $brand = $_GET['brand'] ?? '';
    if (empty($brand)) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('#/api/([a-zA-Z0-9_-]+)/menu#', $uri, $m)) {
            $brand = $m[1];
        }
    }
    if (empty($brand)) {
        $brand = config('default_brand', 'kopken');
    }

    $outletCode = $_GET['outlet'] ?? $_GET['outletCode'] ?? '';
    if (empty($outletCode)) {
        $outletCode = config("brands.{$brand}.default_outlet", 'LA.CODEFIN');
    }

    $forceRefresh = isset($_GET['refresh']) && in_array(strtolower((string)$_GET['refresh']), ['1', 'true', 'yes'], true);

    $result = $api->getMenu($brand, $outletCode, $forceRefresh);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil menu & ketersediaan stok: ' . $e->getMessage(),
    ]);
}
