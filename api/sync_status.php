<?php
/**
 * Realtime Sync & MongoDB Atlas Database Status Endpoint for Zona Fitri
 * Modelled after Kedai Kyuti architecture for zero-delay, zero-limit Vercel performance.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, s-maxage=60, max-age=30');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/SimpleMongo.php';

$mongo = new SimpleMongo();
$status = [
    'success' => true,
    'app' => 'Zona Fitri',
    'database' => 'zona_fitri',
    'provider' => $mongo->getProviderName(),
    'connected' => $mongo->isConnected(),
    'timestamp' => date('c'),
];

// Check master files
$dataDir = __DIR__ . '/../data';
$brands = ['kopken', 'tomoro', 'fore', 'jiwa'];
$status['master_files'] = [];

foreach ($brands as $b) {
    $menuFile = "{$dataDir}/menu_master_{$b}.json";
    $outletsFile = "{$dataDir}/outlets_master_{$b}.json";
    $status['master_files'][$b] = [
        'menu_exists' => file_exists($menuFile),
        'menu_last_modified' => file_exists($menuFile) ? date('c', filemtime($menuFile)) : null,
        'outlets_exists' => file_exists($outletsFile),
        'outlets_last_modified' => file_exists($outletsFile) ? date('c', filemtime($outletsFile)) : null,
    ];
}

$status['sources'] = [
    'https://twinsjasdor.my.id',
    'https://tixandcoffee.developerngantuk.my.id',
    'https://ridstore.developerngantuk.my.id',
    'https://tyaastore.my.id'
];

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
