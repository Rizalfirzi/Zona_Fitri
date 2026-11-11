<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';
$api = new CoffeeApi();

$outlets = [
    'LA.CODEFIN' => 'Codefin Kemang',
    'KK.SBY.FIRWYNNE' => 'Fairway Nine',
    'KK.SBY.DPNEGORO' => 'Diponegoro Surabaya',
    'KK.SBY.MERRNGKTDT' => 'MERR Rungkut DT',
];

foreach ($outlets as $code => $name) {
    echo "=== Outlet: $name ($code) ===\n";
    $m = $api->getMenu('kopken', $code, true);
    foreach ($m['items'] as $it) {
        if (!empty($it['isBundle'])) {
            echo "Bundle: {$it['name']}\n";
            $cands = $it['candidates'] ?? [];
            $availCount = 0;
            $soldCount = 0;
            $soldNames = [];
            foreach ($cands as $c) {
                if (!empty($c['isSoldOut'])) {
                    $soldCount++;
                    $soldNames[] = $c['name'];
                } else {
                    $availCount++;
                }
            }
            echo "  Candidates: Avail $availCount, Sold $soldCount\n";
            if ($soldCount > 0) {
                echo "  Sold out candidates: " . implode(', ', $soldNames) . "\n";
            }
        }
    }
    echo "\n";
}

