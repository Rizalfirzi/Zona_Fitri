<?php
require_once __DIR__ . '/../config.php';
$cacheDir = defined('CACHE_PATH') ? CACHE_PATH : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.json');
    foreach ($files as $f) {
        @unlink($f);
    }
    echo "Deleted " . count($files) . " cache files in $cacheDir\n";
} else {
    echo "Cache dir does not exist: $cacheDir\n";
}
