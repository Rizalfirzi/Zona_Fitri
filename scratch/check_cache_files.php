<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

// Check cache dir for cached json files
$cacheDir = defined('CACHE_PATH') ? CACHE_PATH : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';
echo "Cache dir: $cacheDir\n";
if (is_dir($cacheDir)) {
    $files = scandir($cacheDir);
    foreach ($files as $f) {
        if (str_ends_with($f, '.json') || str_contains($f, 'kopken')) {
            echo "Cache file: $f\n";
            $content = file_get_contents($cacheDir . DIRECTORY_SEPARATOR . $f);
            if (stripos($content, 'gyoza') !== false || stripos($content, 'nugget') !== false || stripos($content, 'fried') !== false) {
                echo "-> FOUND in $f!\n";
            }
        }
    }
}

