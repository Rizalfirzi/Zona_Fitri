<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.FIRWYNNE', true);
echo "Drinks at Fairway Nine:\n";
foreach ($m['items'] as $it) {
    if (in_array($it['name'], ['Kopi Kenangan Mantan', 'Americano', 'Spanish Latte', 'Creamy Aren Latte', 'Lemon Black Tea', 'Oreo Shake'])) {
        echo sprintf("%-30s | outletPrice: %-6d | price: %-6d\n", $it['name'], $it['outletPrice'] ?? 0, $it['price'] ?? 0);
    }
}

