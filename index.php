<?php
/**
 * RID Store - Entry Point (PHP Native)
 * Inspired by ridstore.developerngantuk.my.id & Kedai Kyuti
 * 100% PHP Native Murni, Zero Database, Zero Laravel
 */

require_once __DIR__ . '/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$slug = trim($uri, '/');
if (empty($slug) && isset($_GET['brand'])) {
    $slug = trim($_GET['brand']);
}

// Detect if accessing brand store page (/kopikenangan, /tomoro, /fore)
$isStorePage = in_array($slug, ['kopikenangan', 'tomoro', 'fore', 'kopken'], true);

if ($isStorePage) {
    require __DIR__ . '/views/store.php';
} else {
    require __DIR__ . '/views/home.php';
}
