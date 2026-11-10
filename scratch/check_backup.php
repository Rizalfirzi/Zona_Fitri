<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$ref = new ReflectionClass($api);
$method = $ref->getMethod('httpRequest');
$method->setAccessible(true);
$prop = $ref->getProperty('backupBaseUrl');
$prop->setAccessible(true);
$baseUrl = $prop->getValue($api);

$raw = $method->invoke($api, $baseUrl . '/kopken/menu', ['outlet' => 'KK.SBY.MERRNGKTDT']);
$names = array_column($raw['items'] ?? [], 'name');
echo "Count: " . count($names) . "\n";
foreach ($names as $n) {
    if (stripos($n, 'gyoza') !== false || stripos($n, 'nugget') !== false || stripos($n, 'fried') !== false || stripos($n, 'bungeo') !== false) {
        echo "- $n\n";
    }
}

