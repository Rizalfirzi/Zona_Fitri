<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$ref = new ReflectionClass($api);
$method = $ref->getMethod('httpRequest');
$method->setAccessible(true);
$prop = $ref->getProperty('primaryBaseUrl');
$prop->setAccessible(true);
$baseUrl = $prop->getValue($api);

$raw = $method->invoke($api, $baseUrl . '/kopken/menu', ['outlet' => 'KK.SBY.DPNEGORO']);
foreach ($raw['items'] as $it) {
    if (in_array($it['name'], ['Kopi Kenangan Mantan Frappe', 'Taro Cloud Matcha', 'Thai Tea Loaded', 'Korean Banana Latte'])) {
        echo $it['name'] . ":\n";
        echo "  isSoldOut: " . json_encode($it['isSoldOut'] ?? null) . "\n";
        echo "  notCarried: " . json_encode($it['notCarried'] ?? null) . "\n";
        echo "  available: " . json_encode($it['available'] ?? null) . "\n";
    }
}

