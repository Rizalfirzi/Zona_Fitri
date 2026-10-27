<?php
/**
 * Zona Fitri - Entry Point (PHP Native)
 * Inspired by ridstore.developerngantuk.my.id & Kedai Kyuti
 * 100% PHP Native Murni, Zero Database, Zero Laravel
 * Supports built-in server directly (php -S localhost:8000) and serverless environments
 */

require_once __DIR__ . '/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$cleanUri = '/' . trim((string)$uri, '/');

// 1. Static asset fallback protection (if requested through index.php)
if (preg_match('#\.(png|webp|jpe?g|svg|ico|gif|css|js|map|woff2?|ttf|eot)$#i', $uri)) {
    if (preg_match('#^/img/#', $uri)) {
        header("Location: https://ridstore.developerngantuk.my.id{$uri}", true, 302);
        exit;
    }
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Asset not found: ' . htmlspecialchars($uri);
    exit;
}

// 2. Direct API Routing (flawless even when running without router.php)
if ($cleanUri === '/api/config') {
    require __DIR__ . '/api/config.php';
    exit;
}

if (preg_match('#^/api/([a-zA-Z0-9_-]+)/outlets$#', $cleanUri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/outlets.php';
    exit;
}

if (preg_match('#^/api/([a-zA-Z0-9_-]+)/menu$#', $cleanUri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/menu.php';
    exit;
}

if (preg_match('#^/api/([a-zA-Z0-9_-]+)/item$#', $cleanUri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/item.php';
    exit;
}

if ($cleanUri === '/api/orders' || $cleanUri === '/api/order') {
    require __DIR__ . '/api/order.php';
    exit;
}

if ($cleanUri === '/api/track') {
    require __DIR__ . '/api/track.php';
    exit;
}

// 3. Brand Store Pages (/kopikenangan, /tomoro, /fore)
$slug = trim($cleanUri, '/');
if (empty($slug) && isset($_GET['brand'])) {
    $slug = trim($_GET['brand']);
}

$isStorePage = in_array($slug, ['kopikenangan', 'tomoro', 'fore', 'kopken'], true);

if ($isStorePage) {
    require __DIR__ . '/views/store.php';
} else {
    require __DIR__ . '/views/home.php';
}
