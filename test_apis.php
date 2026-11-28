<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/CoffeeApi.php';

$api = new CoffeeApi();

$testOutlets = [
    'kopken' => 'KK.SBY.MERRNGKTDT',
    'jiwa'   => '2248',
    'fore'   => 'KOKAS',
    'tomoro' => 'TMR-627779'
];

foreach ($testOutlets as $b => $code) {
    echo "=== Testing {$b} ===\n";
    $t0 = microtime(true);
    $outlets = $api->getOutlets($b);
    $tOutlets = round((microtime(true) - $t0) * 1000, 1);
    echo "Outlets: " . count($outlets['outlets'] ?? []) . " (source: " . ($outlets['source'] ?? 'unknown') . ", time: {$tOutlets}ms)\n";

    $t1 = microtime(true);
    $menu = $api->getMenu($b, $code, true);
    $tMenu = round((microtime(true) - $t1) * 1000, 1);
    echo "Menu for {$code}: " . count($menu['items'] ?? []) . " items (live: " . (!empty($menu['live']) ? 'YES' : 'NO') . ", time: {$tMenu}ms)\n";
    if (!empty($menu['items'])) {
        $first = $menu['items'][0];
        echo "Sample item: {$first['name']} -> Rp " . number_format($first['price'], 0, ',', '.') . " (soldOut: " . ($first['isSoldOut'] ? 'YES' : 'NO') . ")\n";
    }
    echo "\n";
}
