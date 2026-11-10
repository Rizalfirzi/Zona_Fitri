<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();
foreach (['kopken', 'tomoro', 'fore', 'jiwa'] as $brand) {
    $m = $api->getMenu($brand, null, true);
    foreach ($m['items'] as $it) {
        $itName = strtolower(trim((string)($it['name'] ?? '')));
        $itKey = strtolower(trim((string)($it['key'] ?? '')));
        if (preg_match('/\bnasi\b/i', $itName)) {
            echo "Match nasi: $brand - {$it['name']} ($itKey)\n";
        }
    }
}

