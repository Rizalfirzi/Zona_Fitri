<?php
/**
 * API Endpoint: Item Detail & Real-time Variants/Addons
 * Supports: /api/kopken/item?outlet=LA.CODEFIN&code=7833007&key=kopikenanganmantan
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

    $brand = $_GET['brand'] ?? 'kopken';
    $outletCode = $_GET['outlet'] ?? $_GET['outletCode'] ?? '';
    $code = $_GET['code'] ?? '';
    $key = $_GET['key'] ?? '';

    $result = $api->getItemDetail($brand, $outletCode, $code, $key);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil detail varian item: ' . $e->getMessage(),
    ]);
}
