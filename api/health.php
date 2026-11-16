<?php
/**
 * Zona Fitri - Hosting & Database Health Check
 * Access via: https://<your-domain>/api/health.php
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
require_once __DIR__ . '/../includes/SimpleMongo.php';

$mongo = new SimpleMongo();
$api = new CoffeeApi();

$mongoDriverLoaded = extension_loaded('mongodb');
$mongoConnected = $mongo->isConnected();

$kopkenOutlets = $mongo->getOutlets('kopken');
$tomoroOutlets = $mongo->getOutlets('tomoro');

echo json_encode([
    'status' => 'OK',
    'app' => config('store.name', 'Zona Fitri'),
    'environment' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? ROOT_PATH,
    ],
    'database' => [
        'name' => config('mongodb.database', 'zona_fitri'),
        'php_mongodb_extension_loaded' => $mongoDriverLoaded,
        'provider' => $mongo->getProviderName(),
        'status' => $mongoConnected ? 'CONNECTED' : 'STANDBY / MASTER SNAPSHOT',
    ],
    'data_snapshots' => [
        'kopken_outlets' => count($kopkenOutlets),
        'tomoro_outlets' => count($tomoroOutlets),
    ],
    'timestamp' => date('c'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
