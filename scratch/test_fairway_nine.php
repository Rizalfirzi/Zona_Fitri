<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'KK.SBY.FIRWYNNE', true);
echo "Fairway Nine:\n";
echo "Live: " . ($m['live'] ? 'yes' : 'no') . "\n";
echo "Total items: " . count($m['items']) . "\n";
$avail = 0; $sold = 0;
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) $sold++; else $avail++;
}
echo "Avail: $avail, Sold: $sold\n";
echo "\nSold out items:\n";
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) echo "- {$it['name']}\n";
}

