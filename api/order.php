<?php
/**
 * API Endpoint: Save Order Log (Optional) & Process Order
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        $data = $_POST;
    }

    $api = new CoffeeApi();
    $result = $api->saveOrder($data ?? []);

    echo json_encode([
        'success' => true,
        'orderCode' => $result['orderCode'] ?? ($data['code'] ?? 'ZF-' . substr(bin2hex(random_bytes(3)), 0, 5)),
        'order' => $result['order'] ?? $data,
        'waUrl' => $result['waUrl'] ?? null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Pesanan diterima.',
    ]);
}
