<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();
$m = $api->getMenu('kopken', 'KK.SBY.MERRNGKTDT', true);

echo "Categories:\n";
foreach ($m['categories'] ?? [] as $c) {
    echo "- " . ($c['name'] ?? $c['id']) . " (id: " . ($c['id'] ?? '') . ", count: " . count($c['items'] ?? []) . ")\n";
}

echo "\nFood/Other Items:\n";
foreach ($m['items'] as $it) {
    if (($it['category'] ?? '') === 'Food' || !empty($it['isFood']) || stripos($it['name'], 'chicken') !== false || stripos($it['name'], 'bungeo') !== false || stripos($it['name'], 'spaghetti') !== false) {
        echo sprintf("%-45s | %-25s | %-15s | Rp%d\n", $it['name'] ?? '', $it['category'] ?? '', $it['key'] ?? '', $it['price'] ?? 0);
    }
}

