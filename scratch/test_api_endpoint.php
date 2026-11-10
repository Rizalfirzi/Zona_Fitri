<?php
$_GET['brand'] = 'kopken';
$_GET['outlet'] = 'KK.SBY.MERRNGKTDT';
$_GET['refresh'] = '1';

ob_start();
require __DIR__ . '/../api/menu.php';
$output = ob_get_clean();

$data = json_decode($output, true);
if (!$data || empty($data['items'])) {
    echo "ERROR fetching menu: " . substr($output, 0, 200) . "\n";
    exit(1);
}

echo "Successfully loaded menu for KK.SBY.MERRNGKTDT:\n";
echo "- Total items: " . count($data['items']) . "\n";
echo "- Total categories: " . count($data['categories']) . "\n";

$badItems = [];
foreach ($data['items'] as $it) {
    $n = $it['name'];
    if (preg_match('/bungeo|spaghetti|burger|gyoza|fried chicken|nugget|judes|fish ball|fish cake|boneless chicken|chicken skin|chicken ball|^\s*nasi\b|salt bread original/i', $n)) {
        $badItems[] = $n;
    }
}

if (!empty($badItems)) {
    echo "FAIL: Found removed items: " . implode(', ', $badItems) . "\n";
    exit(1);
}

echo "ALL TARGET ITEMS ARE REMOVED!\n";

