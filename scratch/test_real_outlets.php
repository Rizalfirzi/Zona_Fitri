<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

echo "Testing Outlets in Surabaya & Jakarta for Kopi Kenangan:\n";
$outlets = [
    'LA.CODEFIN' => 'Codefin Kemang',
    'KK.SBY.MERRNGKTDT' => 'MERR Rungkut Surabaya (DT)',
    'BDG_ELCENTER' => 'Bandung Electronic Center',
    'CG-001' => 'Cilandak Town Square',
    'KK.SBY.DIPONEGORO' => 'Diponegoro Surabaya',
    'KK.SBY.TUNJUNGAN' => 'Tunjungan Plaza Surabaya',
];

foreach ($outlets as $code => $name) {
    try {
        $m = $api->getMenu('kopken', $code, true);
        $avail = 0; $sold = 0;
        foreach ($m['items'] as $it) {
            if (!empty($it['isSoldOut'])) $sold++; else $avail++;
        }
        echo sprintf("%-30s | Live: %-5s | Avail: %-3d | Sold: %-3d\n", $name, $m['live'] ? 'yes' : 'no', $avail, $sold);
    } catch (Throwable $e) {
        echo "$name: ERROR " . $e->getMessage() . "\n";
    }
}

