<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

// Call httpRequest directly to see raw categories
$ref = new ReflectionClass($api);
$method = $ref->getMethod('httpRequest');
$method->setAccessible(true);
$prop = $ref->getProperty('primaryBaseUrl');
$prop->setAccessible(true);
$baseUrl = $prop->getValue($api);

$raw = $method->invoke($api, $baseUrl . '/kopken/menu', ['outlet' => 'KK.SBY.MERRNGKTDT']);
echo "Raw categories:\n";
foreach ($raw['categories'] ?? [] as $cat) {
    echo "- " . ($cat['name'] ?? $cat['id']) . " (id: " . ($cat['id'] ?? '') . ")\n";
}

echo "\nAll raw items in raw response:\n";
foreach ($raw['items'] ?? [] as $it) {
    if (isset($it['category']) || isset($it['group']) || isset($it['categoryId'])) {
        echo sprintf("%-40s | cat: %-25s | group: %-20s\n", $it['name'] ?? '', $it['category'] ?? '', $it['group'] ?? ($it['categoryId'] ?? ''));
    }
}

