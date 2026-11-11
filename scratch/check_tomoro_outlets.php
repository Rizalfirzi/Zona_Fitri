<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$res = $api->getOutlets('tomoro', 'Surabaya');
echo "Tomoro outlets in Surabaya: " . count($res['outlets']) . "\n";
foreach (array_slice($res['outlets'], 0, 5) as $o) {
    echo "{$o['name']} ({$o['code']})\n";
    $m = $api->getMenu('tomoro', $o['code'], true);
    $avail = 0; $sold = 0;
    foreach ($m['items'] as $it) {
        if (!empty($it['isSoldOut'])) $sold++; else $avail++;
    }
    echo "  Live: " . ($m['live'] ? 'yes' : 'no') . " | Avail: $avail | Sold: $sold\n";
}

