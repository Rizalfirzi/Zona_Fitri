# Zona Fitri - Real-Time Coffee Outlets & Stock Web Application ☕✨

Aplikasi Web **PHP Native Murni (Zero Database & Zero Laravel)** untuk pemesanan kopi dan pemantauan ketersediaan stok gerai secara real-time. Terinspirasi dari [ridstore.developerngantuk.my.id](https://ridstore.developerngantuk.my.id) dan berbasis arsitektur native dari project `kedai kyuti`.

Mendukung 3 brand kopi ternama Indonesia:
- **Kopi Kenangan** (Espresso khas Gula Aren)
- **Tomoro Coffee** (100% Master Arabica Beans)
- **Fore Coffee** (Kreasi Nusantara Premium)

---

## 🚀 Fitur Unggulan

1. **100% PHP Native & Zero Database Dependency**
   - Tidak memerlukan PostgreSQL, MySQL, SQLite, ataupun file database lokal.
   - Semua data menu, harga promo, kategori, dan ketersediaan stok gerai diambil langsung secara real-time dari API live upstream.
   - Keranjang pesanan (`cart`) dan preferensi tersimpan di sisi browser (`localStorage`).
   - Format pemesanan langsung terintegrasi otomatis ke WhatsApp admin.

2. **Koneksi Live API Gerai Outlet & Stok Real-Time**
   - **Outlets**: Mengambil daftar outlet resmi secara real-time dengan filter pencarian instan dan perhitungan jarak GPS (Haversine Formula).
   - **Real-Time Stock**: Menampilkan status ketersediaan secara langsung:
     - 🟢 **Stok Ready**: Menu dapat dipesan.
     - 🔴 **Habis di Gerai**: Tombol otomatis dinonaktifkan dengan label jelas agar pesanan tidak dibatalkan di tengah jalan.
   - **Pembaruan Stok Instan (Force Refresh)**: Tombol pembaruan stok live dengan animasi putar (spin) dan notifikasi toast.
   - **Jam Operasional Outlet**: Evaluasi otomatis buka/tutup gerai berdasarkan waktu WIB (Asia/Jakarta).
   - **Fallback Cerdas**: Dilengkapi data cadangan jika remote API sedang offline sehingga web tidak pernah down.

3. **Front-End & UI/UX Interaktif (Mobile-First)**
   - **Splash Header Day/Night**: Mode Pagi ☀️, Senja ⛅, Malam 🌙 lengkap dengan jam WIB real-time.
   - **Multi-Brand Switcher**: Berpindah antar gerai Kopi Kenangan, Tomoro Coffee, dan Fore Coffee dengan tema warna dinamis.
   - **Kustomisasi Minuman**: Bottom sheet modal untuk memilih ukuran cup (*Regular / Large*), suhu (*Ice / Hot*), level es (*Normal / Less / No Ice*), level kemanisan (*Normal / Less / No Sugar*), dan catatan khusus.
   - **Password WiFi Gerai**: Kartu info password WiFi outlet harian dengan tombol salin otomatis 1-klik.
   - **Floating Sticky Cart**: Bar mengambang di bawah layar menampilkan total item, gerai terpilih, dan total bayar.
   - **Pelacakan Pesanan (Order Tracking)**: Pelacak status visual 3 langkah (*Pesanan Diterima ➔ Diproses Outlet ➔ Siap Diambil*).
   - **Tema Gelap & Terang (Dark/Light Mode)**: Tersedia toggle switcher yang tersimpan otomatis di browser.

4. **Siap Deploy ke Vercel, Hosting, & Server Lokal**
   - Dilengkapi `vercel.json` untuk runtime serverless `vercel-php`.
   - File cache diarahkan ke temporary storage (`sys_get_temp_dir()`) yang ramah lingkungan serverless.
   - Dilengkapi `.htaccess` untuk server Apache / XAMPP / Laragon / cPanel.

---

## 📁 Struktur Direktori

```text
e:/swam/zona fitri/
├── api/
│   ├── config.php          # GET: Konfigurasi aplikasi & daftar brand
│   ├── menu.php            # GET: Menu lengkap & stok real-time per outlet
│   ├── order.php           # POST: Validasi pesanan & generate WhatsApp link
│   ├── outlets.php         # GET: Daftar outlet & pencarian jarak GPS terdekat
│   └── track.php           # GET: Pelacakan pesanan berdasarkan kode
├── assets/
│   ├── css/
│   │   └── style.css       # Stylesheet lengkap (Responsive, Theme, Bottom Sheet, Badges)
│   └── js/
│       └── app.js          # Client logic (State, Live Fetch, Cart, Geolocation, Modals)
├── includes/
│   ├── CoffeeApi.php       # Service class PHP untuk request API upstream & cache
│   ├── footer.php          # Template footer, floating cart, & scripts
│   ├── header.php          # Template header & navigation bar
│   └── modals.php          # Bottom sheet modal (Outlet, Kustomisasi, Keranjang, Lacak)
├── .htaccess               # URL rewrite untuk Apache / cPanel / Laragon
├── config.php              # Konfigurasi aplikasi, nomor WhatsApp admin & fee
├── index.php               # Entry point utama aplikasi
├── README.md               # Dokumentasi lengkap
├── run.bat                 # Shortcut 1-klik untuk menjalankan server lokal di Windows
└── vercel.json             # Konfigurasi deployment serverless ke Vercel
```

---

## 💻 Cara Menjalankan Secara Lokal

### Cara 1: Menggunakan PHP Built-in Server (Paling Cepat)
1. Buka folder ini di PowerShell / Terminal.
2. Jalankan perintah:
   ```bash
   php -S localhost:8000
   ```
   Atau cukup klik dua kali file **`run.bat`**.
3. Buka browser dan akses: [http://localhost:8000](http://localhost:8000).

### Cara 2: Menggunakan Laragon / XAMPP
1. Pindahkan atau buat symlink folder `zona fitri` ke folder `htdocs` (XAMPP) atau `www` (Laragon).
2. Akses via browser: `http://localhost/zona-fitri`.

---

## ⚙️ Konfigurasi (`config.php`)

Anda dapat mengubah nama web, nomor WhatsApp admin penerima pesanan, nomor saluran, maupun biaya tambahan pada file [`config.php`](file:///e:/swam/zona%20fitri/config.php):

```php
'store' => [
    'name' => 'Zona Fitri',
    'tagline' => 'Jasa Order Kopi Kenangan, Tomoro & Fore',
    'admin_phone' => '6288298374458', // Nomor WA admin penerima pesanan
    'order_prefix' => 'ZF',
    'channel_url' => 'https://whatsapp.com/channel/0029VbCL8m5L2ATulL6iQJ3d',
],

'fees' => [
    'single_item_fee' => 2000, // Fee pesanan 1 cup (2+ cup gratis)
    'plastic_bag_fee' => 1000, // Kantung belanja plastik
],
```

---

## ☁️ Panduan Deploy ke Vercel

1. Push folder ini ke GitHub / GitLab.
2. Buka dashboard [Vercel](https://vercel.com/) dan import repository.
3. Vercel akan membaca konfigurasi [`vercel.json`](file:///e:/swam/zona%20fitri/vercel.json) secara otomatis.
4. Klik **Deploy** dan aplikasi langsung aktif online!
