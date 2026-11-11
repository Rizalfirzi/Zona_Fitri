<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$res = $api->getOutlets('kopken', 'Fairway Nine');
print_r($res);

