<?php
/**
 * API Endpoint: Order Tracking
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';

try {
    $code = strtoupper(trim((string)($_GET['code'] ?? '')));

    if (empty($code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Harap masukkan kode pesanan.',
        ]);
        exit;
    }

    // Dynamic tracking statuses
    $statuses = [
        'diterima' => [
            'step' => 1,
            'title' => 'Pesanan Diterima',
            'desc' => 'Admin telah menerima format pesanan dan sedang memverifikasi pembayaran.',
        ],
        'diproses' => [
            'step' => 2,
            'title' => 'Diproses Barista di Outlet',
            'desc' => 'Pesananmu sudah diorderkan dan sedang diracik oleh barista di outlet pilihan.',
        ],
        'siap' => [
            'step' => 3,
            'title' => 'Pesanan Siap Diambil / Diantar',
            'desc' => 'Minuman segar favoritmu sudah siap! Silakan sebutkan nama/kode pesanan di outlet.',
        ],
    ];

    echo json_encode([
        'success' => true,
        'orderCode' => $code,
        'status' => 'diproses',
        'step' => 2,
        'title' => 'Diproses Barista di Outlet',
        'desc' => 'Pesananmu sudah diorderkan dan sedang diracik oleh barista di outlet pilihan.',
        'updatedAt' => date('H:i') . ' WIB',
        'steps' => $statuses,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal melacak pesanan.',
        'error' => $e->getMessage(),
    ]);
}
