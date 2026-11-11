<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.DPNEGORO', true);
echo "Drinks at Diponegoro Surabaya:\n";
foreach ($m['items'] as $it) {
    if (empty($it['isBundle']) && empty($it['isFood']) && ($it['category'] ?? '') !== 'Food') {
        echo sprintf("%-30s | outletPrice: %-6d | normalPrice: %-6d | price: %-6d | isSoldOut: %s\n",
            $it['name'],
            $it['outletPrice'] ?? 0,
            $it['normalPrice'] ?? 0,
            $it['price'] ?? 0,
            json_encode($it['isSoldOut'] ?? false)
        );
    }
}

