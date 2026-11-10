<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();
$m = $api->getMenu('kopken', 'KK.SBY.MERRNGKTDT', true);

$fckRegex = '/bungeoppang|spaghetti|burger|gyoza|fried\s*chicken|nugget|mie\s*judes|judes\s*kremes|fish\s*ball|fish\s*cake|boneless\s*chicken|chicken\s*skin|chicken\s*ball|^\s*nasi\b|salt\s*bread\s*original/i';

$matched = [];
$keptFood = [];
foreach ($m['items'] as $it) {
    if (($it['category'] ?? '') === 'Food' || !empty($it['isFood']) || stripos($it['name'], 'chicken') !== false) {
        if (preg_match($fckRegex, $it['name']) || preg_match($fckRegex, $it['key'])) {
            $matched[] = $it['name'];
        } else {
            $keptFood[] = $it['name'];
        }
    }
}

echo "MATCHED AS FCK (" . count($matched) . "):\n";
foreach ($matched as $x) echo "- $x\n";

echo "\nKEPT FOOD (" . count($keptFood) . "):\n";
foreach ($keptFood as $x) echo "- $x\n";

