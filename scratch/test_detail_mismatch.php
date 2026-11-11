<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();
$m = $api->getMenu('kopken', 'KK.SBY.FIRWYNNE', true);

echo "Testing getItemDetail for Fairway Nine:\n";
$soldDetails = [];
foreach ($m['items'] as $it) {
    if (!empty($it['isBundle'])) continue;
    if (!empty($it['isSoldOut'])) continue; // skip already sold out
    $d = $api->getItemDetail('kopken', 'KK.SBY.FIRWYNNE', $it['code'] ?? '', $it['key'] ?? '');
    if (!empty($d['isSoldOut'])) {
        $soldDetails[] = $it['name'];
    }
}

if (!empty($soldDetails)) {
    echo "Items marked available in menu but sold out in detail (" . count($soldDetails) . "):\n";
    foreach ($soldDetails as $n) echo "- $n\n";
} else {
    echo "All available items in menu are also available in detail!\n";
}

