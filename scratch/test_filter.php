<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();
$m = $api->getMenu('kopken', 'KK.SBY.MERRNGKTDT', true);

$removedKeys = [];
$filteredItems = [];
foreach ($m['items'] as $it) {
    $itCat = strtolower(trim((string)($it['category'] ?? '')));
    $itName = strtolower(trim((string)($it['name'] ?? '')));
    $itKey = strtolower(trim((string)($it['key'] ?? '')));

    $isFck = str_contains($itCat, 'fried chicken')
        || str_contains($itCat, 'fck')
        || str_contains($itName, 'bungeoppang')
        || str_contains($itName, 'spaghetti')
        || str_contains($itName, 'burger')
        || str_contains($itName, 'gyoza')
        || str_contains($itName, 'fried chicken')
        || str_contains($itName, 'nugget')
        || str_contains($itName, 'mie judes')
        || str_contains($itName, 'judes kremes')
        || str_contains($itName, 'fish ball')
        || preg_match('/\bnasi\b/i', $itName)
        || $itKey === 'nasi'
        || str_contains($itName, 'salt bread original')
        || $itKey === 'saltbreadoriginal';

    if ($isFck) {
        $removedKeys[$it['key']] = $it['name'];
    } else {
        $filteredItems[] = $it;
    }
}

echo "REMOVED ITEMS (" . count($removedKeys) . "):\n";
foreach ($removedKeys as $k => $n) {
    echo "- $n ($k)\n";
}

echo "\nREMAINING FOOD ITEMS:\n";
foreach ($filteredItems as $it) {
    if (($it['category'] ?? '') === 'Food') {
        echo "- " . $it['name'] . " (" . $it['key'] . ")\n";
    }
}

