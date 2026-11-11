<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$testCode = config("brands.fore.defaultOutlet");
echo "fore defaultOutlet: $testCode\n";
$m = $api->getMenu('fore', $testCode, true);
echo "Count items: " . count($m['items'] ?? []) . "\n";
$avail = 0; $sold = 0;
foreach ($m['items'] as $it) {
    if (!empty($it['isSoldOut'])) $sold++; else $avail++;
}
echo "Avail: $avail, Sold: $sold\n";

