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

$raw = $method->invoke($api, $baseUrl . '/kopken/menu', ['outlet' => 'KK.SBY.MERRNGKTDT']);
$foodItems = [];
foreach ($raw['items'] ?? [] as $it) {
    if (($it['category'] ?? '') === 'Food' || str_contains(strtolower($it['name']), 'chicken') || str_contains(strtolower($it['name']), 'spaghetti') || str_contains(strtolower($it['name']), 'bungeo') || str_contains(strtolower($it['name']), 'mie') || str_contains(strtolower($it['name']), 'gyoza') || str_contains(strtolower($it['name']), 'nugget') || str_contains(strtolower($it['name']), 'nasi')) {
        $foodItems[] = $it['name'];
    }
}
echo json_encode($foodItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

