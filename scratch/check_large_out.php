<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.DPNEGORO', true);
foreach ($m['items'] as $it) {
    if (in_array($it['name'], ['Kopi Kenangan Mantan', 'Spanish Latte', 'Americano'])) {
        echo $it['name'] . ": outletPrice=" . ($it['outletPrice'] ?? 0) . ", largeOut=" . ($it['largeOutletPrice'] ?? 'none') . "\n";
    }
}

