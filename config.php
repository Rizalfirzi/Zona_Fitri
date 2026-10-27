<?php
/**
 * RID Store (Zona Fitri) - Central Application Configuration (PHP Native)
 * Real-time Coffee Outlets & Stock Management Web Application
 * Exact Replica of ridstore.developerngantuk.my.id Architecture
 * 
 * 100% PHP Native - Zero Database - Zero Laravel Dependency
 */

date_default_timezone_set('Asia/Jakarta');

define('ROOT_PATH', __DIR__);

// Cache directory in system temp directory
$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ridstore_cache';
define('CACHE_PATH', $cacheDir);
if (!is_dir(CACHE_PATH)) {
    @mkdir(CACHE_PATH, 0777, true);
}

$config = [
    'app_name' => 'Zona Fitri',
    'app_domain' => 'zonafitri.id',
    'app_env' => 'production',

    // Primary Upstream Live APIs (Referenced from tixandcoffee.developerngantuk.my.id)
    'api_base_url' => 'https://tixandcoffee.developerngantuk.my.id/api',
    'api_backup_url' => 'https://ridstore.developerngantuk.my.id/api',
    'api_timeout' => 8,

    // Cache TTL Settings (in seconds)
    'cache_ttl' => [
        'outlets' => 1800,  // 30 minutes for outlets list
        'menu' => 60,       // 60 seconds for live stock
    ],

    // Store & Admin Branding
    'store' => [
        'name' => 'Zona Fitri',
        'tagline' => 'Jasa order Kopi Kenangan, Tomoro & Fore',
        'description' => 'Pesan kopi favorit lebih hemat: pilih outlet, pilih menu, lalu kirim pesanan ke WhatsApp admin.',
        'accent' => '#C11F2E',
        'admin_phone' => '628973034455', // Nomor WhatsApp admin Zona Fitri (08973034455)
        'order_prefix' => 'ZF',
        'channel_url' => 'https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F',

        'admins' => [
            [
                'name' => 'Admin Zona Fitri',
                'phone' => '628973034455',
            ],
        ],

        // Operating Status
        'shop' => [
            'open' => true,
            'reason' => '',
            'hours' => '07:00 - 22:00 WIB',
        ],

        'pickup_time' => true,

        'wa_header' => '── .✦ FORMAT ORDER ZONA FITRI✦.──',
        'wa_footer' => "> > Setelah Format ini dikirim, segera melakukan Payment melalui QRIS\n> Proses orderan Prioritas (5-20 Menit)\n> ISI FORMAT ? WAJIB FIX ORDER",

        'home' => [
            'stats' => [
                ['label' => 'Di bawah harga outlet', 'value' => 'Lebih hemat'],
                ['label' => 'Sesuai outlet pilihanmu', 'value' => 'Stok live'],
                ['label' => 'Tanpa install aplikasi', 'value' => 'Via WhatsApp'],
            ],
            'banners' => [
                [
                    'cta' => 'Lihat menu',
                    'href' => '#menu',
                    'text' => 'Kopi Kenangan, Tomoro & Fore — pesan lewat WhatsApp, admin yang orderkan buat kamu.',
                    'theme' => 'forest',
                    'title' => 'Kopi favorit, harga lebih hemat.',
                ],
                [
                    'cta' => 'Order Kopi Kenangan',
                    'href' => '/kopikenangan',
                    'text' => 'Menu yang habis langsung kelihatan, jadi pesananmu nggak batal di tengah jalan.',
                    'theme' => 'cream',
                    'title' => 'Stok sesuai outlet.',
                ],
                [
                    'cta' => 'Lihat paket',
                    'href' => '/kopikenangan',
                    'text' => 'Paket 2–3 cup sudah termasuk fee jasa. Pilih menunya sendiri.',
                    'theme' => 'sage',
                    'title' => 'Bundling lebih hemat.',
                ],
            ],
        ],

        'cs_categories' => [
            'Tanya Menu / Info Promo',
            'Pesanan Belum Diproses',
            'Pesanan Tidak Sesuai / Salah Menu',
            'Kendala Pembayaran',
            'Refund / Pengembalian Dana',
            'Kendala Lainnya',
        ],
    ],

    // Client Pricing Rules & Formulas
    'pricing' => [
        'kopken' => [
            'percent' => 50,
            'add' => 4000,
            'largeAdd' => 5000, // upsize +5k
        ],
        'tomoro' => [
            'percent' => 50,
            'add' => 5000,
        ],
        'fore' => [
            'percent' => 75, // -25%
            'add' => 2000,
            'largeAdd' => 5000, // upsize +5k
        ],
    ],

    // Multi-Brand Coffee Configuration (Kopi Kenangan, Tomoro Coffee, Fore Coffee)
    'brands' => [
        'kopken' => [
            'id' => 'kopken',
            'name' => 'Kopi Kenangan',
            'short' => 'Kenangan',
            'slug' => 'kopikenangan',
            'color' => '#791217',
            'desc' => 'Espresso khas gula aren. Promo hemat setiap hari.',
            'requireOutlet' => true,
            'wifi' => false, // Client request: wifi tdk perlu di tampilkan di web
            'fee' => [
                'mode' => 'none',
                'single' => 0,
                'perItem' => 0,
                'bundleFree' => true,
                'freeFromQty' => 0,
                'singleSpecial' => 0,
            ],
            'defaultOutlet' => 'LA.CODEFIN, JKT.LIPPOKMG, KK.JKT.CKJNG, JKT.THAMCIT, KK.JKT.RKKSTBNJB',
            'logo' => '/img/brands/kopken.png',
        ],
        'tomoro' => [
            'id' => 'tomoro',
            'name' => 'Tomoro Coffee',
            'short' => 'Tomoro',
            'slug' => 'tomoro',
            'color' => '#FF8200',
            'desc' => '100% Master Arabica Beans. Creamy & harum.',
            'requireOutlet' => true,
            'wifi' => false,
            'fee' => [
                'mode' => 'none',
                'single' => 0,
                'perItem' => 0,
                'bundleFree' => true,
                'freeFromQty' => 0,
                'singleSpecial' => 0,
            ],
            'defaultOutlet' => 'TMR-627779',
            'logo' => '/img/brands/tomoro.png',
        ],
        'fore' => [
            'id' => 'fore',
            'name' => 'Fore Coffee',
            'short' => 'Fore',
            'slug' => 'fore',
            'color' => '#006241',
            'desc' => 'Kreasi Nusantara. Premium, harum, dan segar.',
            'requireOutlet' => true,
            'wifi' => false,
            'fee' => [
                'mode' => 'none',
                'single' => 0,
                'perItem' => 0,
                'bundleFree' => true,
                'freeFromQty' => 0,
                'singleSpecial' => 0,
            ],
            'defaultOutlet' => 'KOKAS, Adam Malik',
            'logo' => '/img/brands/fore.png',
        ],
    ],

    // Default Brand
    'default_brand' => 'kopken',
];

function config(string $key, mixed $default = null): mixed
{
    global $config;
    if (isset($config[$key])) {
        return $config[$key];
    }
    $parts = explode('.', $key);
    $curr = $config;
    foreach ($parts as $p) {
        if (!is_array($curr) || !array_key_exists($p, $curr)) {
            return $default;
        }
        $curr = $curr[$p];
    }
    return $curr;
}
