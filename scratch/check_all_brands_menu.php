<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();

foreach (['kopken', 'tomoro', 'fore', 'jiwa'] as $brand) {
    echo "=== BRAND: $brand ===\n";
    try {
        $m = $api->getMenu($brand, null, true);
        echo "Success: " . ($m['success'] ? 'true' : 'false') . "\n";
        echo "Live: " . ($m['live'] ? 'true' : 'false') . "\n";
        echo "Outlet: " . ($m['outletCode'] ?? '-') . "\n";
        echo "Total items: " . count($m['items'] ?? []) . "\n";
        $soldOut = 0;
        $avail = 0;
        foreach ($m['items'] as $it) {
            if (!empty($it['isSoldOut'])) $soldOut++;
            else $avail++;
        }
        echo "Available: $avail, Sold out: $soldOut\n";
        if ($avail === 0) {
            echo "ALERT: ALL ITEMS SOLD OUT FOR $brand!\n";
        }
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

