<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$m = $api->getMenu('kopken', 'LA.CODEFIN', true);
echo "Items at Codefin Kemang:\n";
$soldNames = [];
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) {
        $soldNames[] = $it['name'];
    }
}
echo "Sold count: " . count($soldNames) . "\n";
print_r($soldNames);

