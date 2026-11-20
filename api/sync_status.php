<?php
/**
 * Direct Multi-Brand Coffee APIs Status Endpoint for Zona Fitri
 * Fully decentralized across 4 dedicated Nuxt Serverless APIs on swamptechnology.id:
 * - https://kopkenapi.swamptechnology.id
 * - https://jiwaapi.swamptechnology.id
 * - https://foreapi.swamptechnology.id
 * - https://tomoroapi.swamptechnology.id
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, s-maxage=60, max-age=30');

require_once __DIR__ . '/../config.php';

$dataDir = __DIR__ . '/../data';
$brands = ['kopken', 'tomoro', 'fore', 'jiwa'];
$masterFiles = [];

foreach ($brands as $b) {
    $menuFile = "{$dataDir}/menu_master_{$b}.json";
    $outletsFile = "{$dataDir}/outlets_master_{$b}.json";
    $outletsCount = 0;
    if (file_exists($outletsFile)) {
        $arr = json_decode((string)file_get_contents($outletsFile), true);
        if (is_array($arr)) $outletsCount = count($arr);
    }
    $masterFiles[$b] = [
        'outlets_count' => $outletsCount,
        'menu_exists' => file_exists($menuFile),
        'menu_last_modified' => file_exists($menuFile) ? date('c', filemtime($menuFile)) : null,
        'outlets_exists' => file_exists($outletsFile),
        'outlets_last_modified' => file_exists($outletsFile) ? date('c', filemtime($outletsFile)) : null,
    ];
}

$status = [
    'success' => true,
    'app' => 'Zona Fitri',
    'architecture' => 'Direct Brand APIs (Nuxt Serverless)',
    'apis' => config('brand_apis', []),
    'master_files' => $masterFiles,
    'total_outlets' => array_sum(array_column($masterFiles, 'outlets_count')),
    'timestamp' => date('c'),
];

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
