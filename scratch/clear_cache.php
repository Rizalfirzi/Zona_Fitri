<?php
require_once __DIR__ . '/../config.php';
echo "Cache dir: " . CACHE_PATH . "\n";
$files = glob(CACHE_PATH . DIRECTORY_SEPARATOR . '*.json');
echo "Found " . count($files) . " cached files\n";
foreach ($files as $f) {
    @unlink($f);
}
echo "Successfully cleared all cache files.\n";
