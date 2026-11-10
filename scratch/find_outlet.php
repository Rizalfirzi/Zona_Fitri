<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();
$outlets = $api->getOutlets('kopken', 'MERR Rungkut');
print_r($outlets);

