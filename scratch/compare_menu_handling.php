<?php
$outlet = 'KK.SBY.THSQRE'; // The Square Surabaya

$sites = [
    'tix' => "https://tixandcoffee.developerngantuk.my.id/api/kopken/menu?outlet=$outlet",
    'rid' => "https://ridstore.developerngantuk.my.id/api/kopken/menu?outlet=$outlet",
    'tyaa' => "https://tyaastore.my.id/api/kopken/menu?outlet=$outlet",
    'twins' => "https://twinsjasdor.my.id/api/kopken/menu?outlet=$outlet",
    'zonafitri' => "https://zonafitrinet.my.id/api/kopken/menu?outlet=$outlet",
];

foreach ($sites as $name => $url) {
    echo "=== $name for $outlet ===\n";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res = curl_exec($ch);
    $data = json_decode($res, true);
    $items = $data['items'] ?? [];
    echo "Items: " . count($items) . "\n";
    $sold = 0; $avail = 0;
    foreach ($items as $it) {
        if (!empty($it['isSoldOut']) || !empty($it['notCarried'])) $sold++;
        else $avail++;
    }
    echo "Avail: $avail, Sold: $sold\n";
    
    // Check specific items: Taro Cloud Latte, Mantan, Dua Shot
    foreach ($items as $it) {
        if (in_array($it['name'], ['Taro Cloud Latte', 'Kopi Kenangan Mantan', 'Dua Shot OG Aren', 'Caramel Latte'])) {
            echo sprintf("  %-25s | price: %-6s | normal: %-6s | sold: %s\n",
                $it['name'],
                $it['price'] ?? '-',
                $it['normalPrice'] ?? '-',
                !empty($it['isSoldOut']) ? 'true' : 'false'
            );
        }
    }
}

