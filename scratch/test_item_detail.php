<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.DPNEGORO', true);
echo "Testing items for Diponegoro Surabaya:\n";
$checked = 0;
foreach ($m['items'] as $it) {
    if (!empty($it['isBundle'])) continue;
    $detail = $api->getItemDetail('kopken', 'KK.SBY.DPNEGORO', $it['code'] ?? null, $it['key'] ?? null);
    $checked++;
    if ($checked <= 10) {
        echo sprintf("%-30s | code: %-15s | key: %-25s | isSoldOut: %s\n", 
            $it['name'], 
            $it['code'] ?? 'NONE', 
            $it['key'] ?? 'NONE', 
            json_encode($detail['isSoldOut'] ?? false)
        );
    }
}

