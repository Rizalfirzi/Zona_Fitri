<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$testCode = config("brands.kopken.defaultOutlet");
$m = $api->getMenu('kopken', $testCode, false);
echo "Live: " . ($m['live'] ? 'true' : 'false') . "\n";
echo "OutletCode: " . ($m['outletCode'] ?? '-') . "\n";
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) {
        echo "Sold out: {$it['name']} (isSoldOut=" . json_encode($it['isSoldOut']) . ", notCarried=" . json_encode($it['notCarried'] ?? null) . ")\n";
    } else {
        echo "AVAILABLE: {$it['name']}\n";
    }
}

