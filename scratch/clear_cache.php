<?php
$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . DIRECTORY_SEPARATOR . 'menu_kopken_*.json');
    foreach ($files as $f) {
        @unlink($f);
        echo "Deleted: " . basename($f) . "\n";
    }
}
echo "Done clearing cache.\n";

