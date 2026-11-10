<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();

// Delete cache to ensure fresh run
$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';
if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . DIRECTORY_SEPARATOR . 'menu_kopken_*.json') as $f) {
        @unlink($f);
    }
}

$m = $api->getMenu('kopken', 'KK.SBY.MERRNGKTDT', true);

echo "Outlet: MERR Rungkut Surabaya (DT)\n";
echo "Total categories: " . count($m['categories']) . "\n";
foreach ($m['categories'] as $c) {
    echo "- " . $c['name'] . " (" . count($c['items']) . " items)\n";
}

$foundRemoved = [];
foreach ($m['items'] as $it) {
    $n = $it['name'];
    if (preg_match('/bungeo|spaghetti|burger|gyoza|fried chicken|nugget|judes|fish ball|nasi|salt bread original/i', $n)) {
        $foundRemoved[] = $n;
    }
}

if (!empty($foundRemoved)) {
    echo "\nWARNING! Found removed items: " . implode(', ', $foundRemoved) . "\n";
} else {
    echo "\nSUCCESS! All specified FCK & Salt Bread Original items are completely eliminated.\n";
}

echo "\nFood category items remaining:\n";
$foodCat = null;
foreach ($m['categories'] as $c) {
    if (strtolower($c['name']) === 'food' || $c['id'] === 'item-food') {
        $foodCat = $c;
        break;
    }
}

if ($foodCat) {
    $itemMap = [];
    foreach ($m['items'] as $it) $itemMap[$it['key']] = $it;
    foreach ($foodCat['items'] as $k) {
        $it = $itemMap[$k] ?? null;
        if ($it) {
            echo "  * " . $it['name'] . " (" . $k . ")\n";
        }
    }
}

