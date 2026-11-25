<?php
/**
 * Zona Fitri - Hosting & Direct API Health Check
 * Access via: https://<your-domain>/api/health.php
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();

echo json_encode([
    'status' => 'OK',
    'app' => config('store.name', 'Zona Fitri'),
    'architecture' => '100% Pure Direct API Native (Zero Database)',
    'environment' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? ROOT_PATH,
    ],
    'brand_apis' => config('brand_apis', []),
    'timestamp' => date('c'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


