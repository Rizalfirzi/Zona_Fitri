<?php
/**
 * Zona Fitri - Central Application Configuration (PHP Native)
 * Real-time Multi-Brand Coffee Outlets & Stock Management Web Application
 * 
 * 100% PHP Native - Zero Database - Zero Laravel Dependency
 */

date_default_timezone_set('Asia/Jakarta');

define('ROOT_PATH', __DIR__);

// Cache directory in system temp directory
$cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zonafitri_cache';
define('CACHE_PATH', $cacheDir);
if (!is_dir(CACHE_PATH)) {
    @mkdir(CACHE_PATH, 0777, true);
}

$config = [
    'app_name' => 'Zona Fitri',
    'app_domain' => 'zonafitrinet.my.id',
    'app_env' => 'production',

    // =========================================================================
    // DEDICATED DIRECT BRAND APIS (100% PURE API - ZERO DATABASE)
    // =========================================================================
    'brand_apis' => [
        'kopken' => 'https://kopkenapi.swamptechnology.id',
        'jiwa'   => 'https://jiwaapi.swamptechnology.id',
        'point'  => 'https://pointapi.swamptechnology.id',
        'fore'   => 'https://foreapi.swamptechnology.id',
        'tomoro' => 'https://tomoroapi.swamptechnology.id',
    ],
    'api_timeout' => 8, // 8s timeout for swamptechnology endpoints

    // Cache TTL Settings (in seconds)
    'cache_ttl' => [
        'outlets' => 1800,  // 30 minutes for outlets list
        'menu' => 20,       // 20 seconds for live outlet-specific stock
    ],

    // Store & Admin Branding
    'store' => [
        'name' => 'Zona Fitri',
        'tagline' => 'Jasa order Kopi Kenangan, Tomoro, Fore & Janji Jiwa',
        'description' => 'Pesan kopi favorit lebih hemat: pilih outlet, pilih menu, lalu kirim pesanan ke WhatsApp admin.',
        'accent' => '#1E3A8A',
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
        'wa_footer' => '',

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
                    'text' => 'Kopi Kenangan, Tomoro, Fore & Janji Jiwa — pesan lewat WhatsApp, admin yang orderkan buat kamu.',
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

    // Client Pricing Rules & Formulas (Aligned with Prototype jasdor-four-brand-web.vercel.app)
    'pricing' => [
        'kopken' => [
            'percent' => 50, // Client revision: KOPKEN DISC 50%
            'add' => 4500,    // Client revision: FEE 4.5k/cup
            'largeAdd' => 5000, // Client revision: Large kopken +5k
        ],
        'tomoro' => [
            'percent' => 50, // Client revision: TOMORO DISC 50%
            'add' => 5000,   // Client revision: FEE 5k/cup
            'largeAdd' => 5000, // upsize Large +5k
        ],
        'fore' => [
            'percent' => 25, // Client revision: FORE buat disc 25% NO FEE
            'add' => 0,      // NO FEE
            'largeAdd' => 7000, // upsize Large +7k
        ],
        'jiwa' => [
            'percent' => 40, // 40% discount
            'add' => 2500,   // 2.500 fee per cup
            'largeAdd' => 4000,
        ],
    ],

    // Multi-Brand Coffee Configuration (Kopi Kenangan, Tomoro Coffee, Fore Coffee, Janji Jiwa)
    'brands' => [
        'kopken' => [
            'id' => 'kopken',
            'name' => 'Kopi Kenangan',
            'short' => 'Kenangan',
            'slug' => 'kopikenangan',
            'color' => '#791217',
            'desc' => 'Espresso khas gula aren. Promo hemat setiap hari.',
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
            'defaultOutlet' => 'JKT.STGAMBIR',
            'default_outlet' => 'JKT.STGAMBIR',
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
            'defaultOutlet' => 'JKT0004',
            'default_outlet' => 'JKT0004',
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
            'defaultOutlet' => '415',
            'default_outlet' => '415',
            'logo' => '/img/brands/fore.png',
        ],
        'jiwa' => [
            'id' => 'jiwa',
            'name' => 'Janji Jiwa',
            'short' => 'Janji Jiwa',
            'slug' => 'janjiw',
            'color' => '#C2410C',
            'desc' => 'Kopi Dari Hati. Promo hemat resmi.',
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
            'defaultOutlet' => '2248',
            'default_outlet' => '2248',
            'logo' => '/img/brands/jiwa.png',
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
