<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$outlets = ['LA.CODEFIN', 'KK.SBY.MERRNGKTDT', 'BDG_ELCENTER', 'CG-001'];
$allFck = [];
foreach ($outlets as $out) {
    $m = $api->getMenu('kopken', $out, true);
    foreach ($m['items'] as $it) {
        $n = $it['name'];
        if (preg_match('/bungeo|spaghetti|burger|gyoza|fried chicken|nugget|judes|fish ball|nasi/i', $n)) {
            $allFck[$it['key']] = $n;
        }
    }
}
print_r($allFck);

