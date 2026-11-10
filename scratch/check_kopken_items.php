<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();
$m = $api->getMenu('kopken', 'LA.CODEFIN', true);

echo "Categories:\n";
foreach ($m['categories'] ?? [] as $c) {
    echo "- " . ($c['name'] ?? $c['id']) . " (id: " . ($c['id'] ?? '') . ", count: " . count($c['items'] ?? []) . ")\n";
}

echo "\nItems count: " . count($m['items']) . "\n";
foreach ($m['items'] as $it) {
    echo sprintf("%-45s | %-25s | %-15s | Rp%d\n", $it['name'] ?? '', $it['category'] ?? '', $it['key'] ?? '', $it['price'] ?? 0);
}
