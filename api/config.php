<?php
/**
 * API Endpoint: Configuration & Brands Metadata
 * Matches exactly the schema expected by RID Store frontend
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';

try {
    $brands = array_values(config('brands', []));

    $response = [
        'storeName' => config('store.name', 'Zona Fitri'),
        'tagline' => config('store.tagline', 'Jasa order Kopi Kenangan, Tomoro & Fore'),
        'description' => config('store.description', 'Pesan kopi favorit lebih hemat: pilih outlet, pilih menu, lalu kirim pesanan ke WhatsApp admin.'),
        'accent' => config('store.accent', '#C11F2E'),
        'orderPrefix' => config('store.order_prefix', 'ZF'),
        'channelUrl' => config('store.channel_url', 'https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F'),
        'admins' => config('store.admins', [
            ['name' => 'Admin Zona Fitri', 'phone' => '628973034455']
        ]),
        'shop' => config('store.shop', [
            'open' => true,
            'reason' => '',
            'hours' => '07:00 - 22:00 WIB'
        ]),
        'waHeader' => config('store.wa_header', '── .✦ FORMAT ORDER ZONA FITRI✦.──'),
        'waFooter' => config('store.wa_footer', "> > Setelah Format ini dikirim, segera melakukan Payment melalui QRIS\n> Proses orderan Prioritas (5-20 Menit)\n> ISI FORMAT ? WAJIB FIX ORDER"),
        'pickupTime' => (bool) config('store.pickup_time', true),
        'home' => config('store.home', []),
        'csCategories' => config('store.cs_categories', []),
        'pricing' => config('pricing', []),
        'brands' => $brands,
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error: ' . $e->getMessage(),
    ]);
}
