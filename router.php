<?php
/**
 * PHP Built-in Server Router for Local Development
 * Usage: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

// 1. Serve static files directly if existing
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// 1b. Fallback proxy for /img/menu/ images to upstream CDN if not downloaded locally
if (preg_match('#^/img/menu/#', $uri)) {
    header("Location: https://ridstore.developerngantuk.my.id{$uri}", true, 302);
    exit;
}

// 1c. If a static asset is missing, return 404 instead of falling through to index.php (HTML)
if (preg_match('#\.(png|webp|jpe?g|svg|ico|gif|css|js|map|woff2?|ttf|eot)$#i', $uri)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Asset not found: ' . htmlspecialchars($uri);
    exit;
}

// 2. Handle /api/config
if ($uri === '/api/config') {
    require __DIR__ . '/api/config.php';
    exit;
}

// 3. Handle /api/:brand/outlets
if (preg_match('#^/api/([a-zA-Z0-9_-]+)/outlets$#', $uri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/outlets.php';
    exit;
}

// 4. Handle /api/:brand/menu
if (preg_match('#^/api/([a-zA-Z0-9_-]+)/menu$#', $uri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/menu.php';
    exit;
}

// 4b. Handle /api/:brand/item
if (preg_match('#^/api/([a-zA-Z0-9_-]+)/item$#', $uri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/api/item.php';
    exit;
}

// 5. Handle /api/orders
if ($uri === '/api/orders' || $uri === '/api/order') {
    require __DIR__ . '/api/order.php';
    exit;
}

// 6. Handle /api/track
if ($uri === '/api/track') {
    require __DIR__ . '/api/track.php';
    exit;
}

// 7. Handle Brand Store Pages: /kopikenangan, /tomoro, /fore
if (preg_match('#^/(kopikenangan|tomoro|fore)$#', $uri, $m)) {
    $_GET['brand'] = $m[1];
    require __DIR__ . '/index.php';
    exit;
}

// 8. Default to index.php
require __DIR__ . '/index.php';
