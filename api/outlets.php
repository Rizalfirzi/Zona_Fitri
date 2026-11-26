<?php
/**
 * API Endpoint: Outlets List & Live Distance
 * Supports:
 * - /api/kopken/outlets
 * - /api/tomoro/outlets
 * - /api/fore/outlets
 * - /api/outlets.php?brand=kopken
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
if (!empty($_GET['q']) || !empty($_GET['lat'])) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
} else {
    // Vercel Edge CDN Cache: cache outlet di Edge selama 1 menit, stale-while-revalidate 10 menit
    header('Cache-Control: public, s-maxage=60, max-age=30, stale-while-revalidate=600');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

try {
    $api = new CoffeeApi();

    $brand = $_GET['brand'] ?? '';
    if (empty($brand)) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('#/api/([a-zA-Z0-9_-]+)/outlets#', $uri, $m)) {
            $brand = $m[1];
        }
    }
    if (empty($brand)) {
        $brand = config('default_brand', 'kopken');
    }

    $query = isset($_GET['q']) ? trim((string)$_GET['q']) : (isset($_GET['search']) ? trim((string)$_GET['search']) : null);
    $lat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
    $lng = isset($_GET['lng']) && is_numeric($_GET['lng']) ? (float)$_GET['lng'] : null;

    $result = $api->getOutlets($brand, $query, $lat, $lng);
    $result['data'] = $result['outlets'] ?? [];
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Gagal mengambil data outlet: ' . $e->getMessage(),
    ]);
}
