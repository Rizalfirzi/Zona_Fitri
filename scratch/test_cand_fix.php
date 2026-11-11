<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/CoffeeApi.php';

$api = new CoffeeApi();

function testCandidateLogic($brand, $outletCode) {
    global $api;
    $ref = new ReflectionClass($api);
    $method = $ref->getMethod('httpRequest');
    $method->setAccessible(true);
    $prop = $ref->getProperty('primaryBaseUrl');
    $prop->setAccessible(true);
    $baseUrl = $prop->getValue($api);

    $raw = $method->invoke($api, $baseUrl . '/kopken/menu', ['outlet' => $outletCode]);
    $items = $raw['items'] ?? [];

    $candRegular = [];
    $candLarge = [];

    $standardRegularKeys = [
        'kopikenanganmantan', 'spanishlatte', 'americano', 'creamyarenlatte',
        'ogarenspeculooslatte', 'pistachioarenlatte', 'cafemaltlatte',
        'ogthaitea', 'thaitealoaded', 'kenanganmilktea', 'lemonblacktea',
        'milodinosaurus', 'taromilk', 'tarocloudmilktea', 'tarocloudmatcha',
        'tarocloudlatte', 'bananaamericano', 'freshlemonade', 'babyccino',
        'susugrassjelly'
    ];

    foreach ($items as $it) {
        $key = $it['key'] ?? '';
        $name = strtolower($it['name'] ?? '');
        $outletPrice = (int) ($it['outletPrice'] ?? $it['normalPrice'] ?? 0);
        $isFood = !empty($it['isFood']) || (isset($it['category']) && strtolower($it['category']) === 'food');
        $isSoldOut = !empty($it['isSoldOut']) || !empty($it['notCarried']);

        // Filter out FCK items
        if (preg_match('/bungeoppang|spaghetti|burger|gyoza|fried\s*chicken|nugget|mie\s*judes|judes\s*kremes|fish\s*ball|fish\s*cake|boneless|skin|ball|^\s*nasi\b|salt\s*bread\s*original/i', $name)) {
            continue;
        }

        // Regular bundling eligibility (17k - 19k tier, adapting to outlet tiers 17k - 24k)
        $isRegEligible = in_array($key, $standardRegularKeys, true)
            || ($outletPrice >= 17000 && $outletPrice <= 24000 && stripos($name, 'frappe') === false && stripos($name, '1 l') === false);

        if ($isRegEligible) {
            $candRegular[] = ['name' => $it['name'], 'isFood' => $isFood, 'isSoldOut' => $isSoldOut, 'price' => $outletPrice];
        }

        // Large bundling eligibility (25k - 27k tier, adapting to outlet tiers 25k - 30k)
        $largeOut = (int) ($it['largeOutletPrice'] ?? 0);
        if ($largeOut <= 0 && $outletPrice > 0) $largeOut = $outletPrice + 6000;
        $isNotLargeEligible = (bool) preg_match('/baby|cino|black\s*aren|kopsu.*black/i', $name . ' ' . $key);

        $isLargeEligible = !$isFood && !$isNotLargeEligible && (
            in_array($key, ['kopikenanganmantan', 'spanishlatte', 'creamyarenlatte', 'americano', 'ogthaitea', 'kenanganmilktea', 'milodinosaurus', 'lemonblacktea', 'ogarenspeculooslatte', 'pistachioarenlatte', 'cafemaltlatte'], true)
            || ($largeOut >= 25000 && $largeOut <= 30000 && stripos($name, 'frappe') === false && stripos($name, '1 l') === false)
        );

        if ($isLargeEligible) {
            $candLarge[] = ['name' => $it['name'], 'isSoldOut' => $isSoldOut, 'largeOut' => $largeOut];
        }
    }

    echo "Regular Candidates: " . count($candRegular) . " (Avail: " . count(array_filter($candRegular, fn($x) => !$x['isSoldOut'])) . ")\n";
    echo "Large Candidates: " . count($candLarge) . " (Avail: " . count(array_filter($candLarge, fn($x) => !$x['isSoldOut'])) . ")\n";
}

echo "--- Diponegoro Surabaya (KK.SBY.DPNEGORO) ---\n";
testCandidateLogic('kopken', 'KK.SBY.DPNEGORO');

echo "\n--- Fairway Nine (KK.SBY.FIRWYNNE) ---\n";
testCandidateLogic('kopken', 'KK.SBY.FIRWYNNE');

echo "\n--- MERR Rungkut (KK.SBY.MERRNGKTDT) ---\n";
testCandidateLogic('kopken', 'KK.SBY.MERRNGKTDT');

