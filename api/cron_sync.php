<?php
/**
 * Zona Fitri - Realtime 5-Minute Cache Warmup Endpoint
 * Warms up all 5 coffee brands (kopken, tomoro, fore, jiwa, point) directly via swamptechnology APIs
 * 100% Pure Direct API - Zero Database Overhead
 * 
 * Invocation:
 * 1. Vercel Cron Jobs (configured via vercel.json)
 * 2. External Webhook / Cron Service (e.g. cron-job.org / UptimeRobot)
 *    URL: https://<domain>/api/cron_sync?token=zf_sync_secure
 * 3. CLI: php api/cron_sync.php
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

// Optional simple security token check
$cronSecret = getenv('CRON_SECRET') ?: 'zf_sync_secure';
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$reqToken = $_GET['token'] ?? '';

// Allow if running from CLI, or matching Bearer token, or matching GET token, or Vercel Cron header
$isVercelCron = isset($_SERVER['HTTP_X_VERCEL_CRON']);
$isCli = (php_sapi_name() === 'cli');
$isAuthorized = $isCli || $isVercelCron || ($reqToken === $cronSecret) || (str_contains($authHeader, $cronSecret));

if (!$isAuthorized && !empty($cronSecret) && !empty($_GET)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized cron request. Provide ?token=... or Vercel Cron header.',
    ]);
    exit;
}

$startTime = microtime(true);
$brands = ['kopken', 'tomoro', 'fore', 'jiwa', 'point'];
$defaultOutlets = [
    'kopken' => 'KK.SBY.JMSRI',
    'tomoro' => 'TMR-627779',
    'fore'   => 'KOKAS',
    'jiwa'   => '25',
    'point'  => 'POINT-SBY',
];

$results = [];
$api = new CoffeeApi();

foreach ($brands as $b) {
    try {
        // 1. Force refresh outlets from upstream API
        $outletsRes = $api->getOutlets($b, null);
        $outletCount = count($outletsRes['outlets'] ?? []);

        // 2. Force refresh menu with sample outlet from upstream API
        $outletCode = $defaultOutlets[$b] ?? '';
        $menuRes = $api->getMenu($b, $outletCode, true);
        $menuCount = count($menuRes['items'] ?? []);

        $results[$b] = [
            'status' => 'success',
            'outlets_count' => $outletCount,
            'menu_count' => $menuCount,
            'source' => $outletsRes['source'] ?? 'unknown',
        ];
    } catch (\Throwable $e) {
        $results[$b] = [
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Realtime direct API cache warmup completed successfully.',
    'architecture' => '100% Pure Direct API (Zero Database)',
    'timestamp' => date('c'),
    'duration' => round(microtime(true) - $startTime, 2) . 's',
    'details' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

