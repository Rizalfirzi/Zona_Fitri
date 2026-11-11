<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.DPNEGORO', true);
echo "Sold out items at Diponegoro Surabaya:\n";
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) {
        echo "- {$it['name']} ({$it['category']})\n";
    }
}

