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
foreach ($raw['items'] ?? [] as $it) {
    echo $it['name'] . " (" . ($it['category'] ?? '') . ")\n";
}

