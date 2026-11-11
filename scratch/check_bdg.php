<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$res = $api->getOutlets('kopken', 'Bandung');
foreach ($res['outlets'] as $o) {
    echo "{$o['name']} ({$o['code']}) - isOpen: " . json_encode($o['isOpen']) . "\n";
}

