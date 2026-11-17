<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#0F1E36" />
  <meta name="description" content="Zona Fitri — jasa order Kopi Kenangan, Tomoro Coffee, Fore Coffee & Janji Jiwa. Lebih hemat, pesan langsung via WhatsApp." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Zona Fitri — Jasa Order Kopi" />
  <meta property="og:description" content="Kopi Kenangan, Tomoro, Fore & Janji Jiwa lebih hemat. Pesan langsung via WhatsApp." />
  <meta property="og:image" content="/img/brand/zonafitri-logo.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <title>Zona Fitri | Jasa Order Kopi</title>
  <link rel="icon" href="/img/brand/zonafitri-logo.png" type="image/png" />
  <link rel="apple-touch-icon" href="/img/brand/zonafitri-logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/app.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/home.css?v=<?= time() ?>" />
  <script src="/assets/js/theme-boot.js"></script>
</head>
<body class="home-page">
  <a class="skip-link" href="#menu">Langsung ke menu</a>
  <div class="container">
    <nav class="topnav">
      <a href="/" id="navBrand" aria-label="Beranda"></a>
      <div class="topnav-actions">
        <button class="icon-btn" id="themeBtn" aria-label="Ganti tema"></button>
        <button class="icon-btn" id="drawerBtn" aria-label="Buka menu"></button>
      </div>
    </nav>

    <div id="closedBanner"></div>

    <!-- Splash tampilan pilih brand dengan tema Modern Navy Sesuai Desain Baru -->
    <section id="splash" data-mode="malam">
      <!-- Background Ambient Glow & Wave -->
      <div class="sp-bg-glow top-left" aria-hidden="true"></div>
      <div class="sp-bg-wave" aria-hidden="true"></div>

      <!-- Top Section (Logo, Status, Title + Hero Cup) -->
      <div class="sp-hero-top">
        <div class="sp-hero-left">
          <!-- Top Logo Card (White Pill with Outer Glow) -->
          <div class="sp-brand-card-top">
            <img src="/img/brand/zonafitri-logo.png" alt="Zona Fitri" class="sp-brand-img" />
          </div>

          <!-- Status Sedang Buka Pill -->
          <div class="sp-status open" id="spStatus">
            <span class="sp-dot"></span>
            <span id="spStatusText">Sedang Buka</span>
          </div>

          <!-- Hero Title & Tagline with Cyan Curved Underline -->
          <div class="sp-header">
            <h2 id="spTitle" class="sp-main-title">Mau Ngopi?</h2>
            <div class="sp-sub-wrap">
              <span class="sp-sub" id="spSub">Ingat <span class="sp-sub-accent">Fitri<svg class="sp-curved-line" viewBox="0 0 76 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 9C24 2 54 2 74 8" stroke="#38BDF8" stroke-width="3.5" stroke-linecap="round"/></svg></span></span>
            </div>
          </div>
        </div>

        <!-- 3D Coffee Cup on Top-Right -->
        <div class="sp-hero-cup">
          <img src="/img/hero_cup_splash.png" alt="Zona Fitri Coffee Cup" class="sp-cup-img" />
        </div>
      </div>

      <!-- Wave Curve Divider -->
      <div class="sp-wave-divider" aria-hidden="true">
        <svg viewBox="0 0 480 48" preserveAspectRatio="none">
          <path d="M0,0 C160,42 320,42 480,0 L480,48 L0,48 Z" fill="#F8FAFC"/>
        </svg>
      </div>

      <!-- Main Lower Section with Brand Cards -->
      <div class="sp-body">
        <div class="sp-section-divider">
          <span class="sp-divider-line"></span>
          <div class="sp-divider-label">
            <span class="sp-divider-ico">☕</span>
            <span class="sp-picker-label">Pilih Brand Kopi</span>
          </div>
          <span class="sp-divider-line"></span>
        </div>

        <div class="sp-brand-grid">
          <!-- Kopi Kenangan -->
          <a href="/kopikenangan" class="sp-brand-card card-kopken" title="Pesan Kopi Kenangan">
            <div class="sp-card-pastel pastel-pink" aria-hidden="true"></div>
            <div class="brand-icon-box">
              <img src="/img/brands/kopken.png" alt="Kopi Kenangan" />
            </div>
            <div class="brand-info">
              <span class="brand-name">Kopi Kenangan</span>
              <span class="brand-tagline">Untuk kamu, yang selalu spesial</span>
            </div>
            <div class="brand-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>

          <!-- Tomoro Coffee -->
          <a href="/tomoro" class="sp-brand-card card-tomoro" title="Pesan Tomoro Coffee">
            <div class="sp-card-pastel pastel-peach" aria-hidden="true"></div>
            <div class="brand-icon-box">
              <img src="/img/brands/tomoro.png" alt="Tomoro Coffee" />
            </div>
            <div class="brand-info">
              <span class="brand-name">Tomoro Coffee</span>
              <span class="brand-tagline">Good Coffee, Good Mood</span>
            </div>
            <div class="brand-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>

          <!-- Fore Coffee -->
          <a href="/fore" class="sp-brand-card card-fore" title="Pesan Fore Coffee">
            <div class="sp-card-pastel pastel-mint" aria-hidden="true"></div>
            <div class="brand-icon-box">
              <img src="/img/brands/fore.png" alt="Fore Coffee" />
            </div>
            <div class="brand-info">
              <span class="brand-name">Fore Coffee</span>
              <span class="brand-tagline">Live Green, Live Better</span>
            </div>
            <div class="brand-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>

          <!-- Janji Jiwa -->
          <a href="/janjiw" class="sp-brand-card card-jiwa" title="Pesan Janji Jiwa">
            <div class="sp-card-pastel pastel-coral" aria-hidden="true"></div>
            <div class="brand-icon-box">
              <img src="/img/brands/jiwa.png" alt="Janji Jiwa" />
            </div>
            <div class="brand-info">
              <span class="brand-name">Janji Jiwa</span>
              <span class="brand-tagline">Teman Sejati di Setiap Cerita</span>
            </div>
            <div class="brand-arrow">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
          </a>
        </div>

        <!-- Tombol Gabung Saluran (Deep Blue Capsule Button) -->
        <div class="sp-channel-container">
          <a class="sp-channel-btn" id="spWa" href="https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F" target="_blank" rel="noopener">
            <div class="sp-channel-left">
              <svg class="sp-channel-ico" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <span>Gabung Saluran</span>
            </div>
            <svg class="sp-channel-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 18l6-6-6-6"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Bottom Decorative Curve -->
      <div class="sp-footer-decor" aria-hidden="true"></div>
    </section>

    <section class="hero" aria-roledescription="carousel" aria-label="Info utama">
      <div class="hero-track" id="heroTrack"></div>
      <div class="hero-dots" id="heroDots"></div>
    </section>

    <section class="section featured" id="menu" aria-labelledby="menuTitle">
      <div class="eyebrow">Menu favorit</div>
      <h2 class="section-title" id="menuTitle">Mau ngopi apa hari ini?</h2>
      <div class="brand-tabs" id="brandTabs" role="tablist" aria-label="Pilih brand"></div>
      <div class="prod-grid" id="prodGrid" role="tabpanel" aria-live="polite"></div>
      <a class="btn btn-outline btn-block see-all" id="seeAllBtn" href="/kopikenangan">Lihat semua menu</a>
    </section>

    <section class="section bundles-sec hidden" id="bundlesSec" aria-labelledby="bundlesTitle">
      <div class="feat-head"><div><div class="eyebrow">Lebih hemat</div><h2 class="section-title" id="bundlesTitle">Promo bundling</h2></div>
        <a class="link-btn" id="bundlesAll" href="/kopikenangan">Lihat semua</a></div>
      <div class="track" id="bundleTrack"></div>
    </section>

    <section class="perks" id="perks" aria-label="Keunggulan"></section>

    <section class="section" aria-labelledby="howTitle">
      <div class="eyebrow">Cara order</div>
      <h2 class="section-title" id="howTitle">Empat langkah, tanpa install aplikasi</h2>
      <div class="timeline">
        <div class="tl-step"><span class="tl-num">1</span><div><h4>Pilih brand & outlet</h4><p>Pilih outlet tujuan pesanan. Menu, harga & stok mengikuti outlet itu.</p></div></div>
        <div class="tl-step"><span class="tl-num">2</span><div><h4>Pilih menu</h4><p>Atur ukuran, Ice/Hot, gula & es, atau ambil paket bundling yang lebih hemat.</p></div></div>
        <div class="tl-step"><span class="tl-num">3</span><div><h4>Kirim lewat WhatsApp</h4><p>Rincian pesanan otomatis terformat & terkirim ke admin.</p></div></div>
        <div class="tl-step"><span class="tl-num">4</span><div><h4>Bayar, pesanan diorderkan</h4><p>Admin kirim info pembayaran. Setelah lunas, pesananmu langsung diorderkan ke outlet.</p></div></div>
      </div>
    </section>

    <section class="cta" aria-labelledby="ctaTitle">
      <div class="eyebrow">Butuh bantuan?</div>
      <h2 id="ctaTitle">Ada pertanyaan soal pesanan?</h2>
      <p>Admin siap bantu lewat WhatsApp — dari tanya menu sampai cek pesanan.</p>
      <button class="btn btn-primary" data-action="cs" id="ctaBtn">Chat admin</button>
    </section>

    <footer class="foot" id="foot"></footer>
  </div>

  <button class="fab-wa" data-action="cs" aria-label="Chat admin via WhatsApp" id="fabWa"></button>

  <!-- Cart (carts are kept per brand; this shows all of them) -->
  <a class="cart-bar" id="cartBar" href="#" aria-label="Lihat keranjang">
    <span class="cart-bar-left"><span class="cart-bar-icon" id="cartBarIcon"><span class="cart-bar-badge" id="cartBadge">0</span></span>
      <span class="cart-bar-text"><b id="cartBarQty">0 item</b><small id="cartBarOutlet"></small></span></span>
    <span class="cart-bar-right"><span class="price" id="cartBarTotal">Rp0</span><span id="cartBarChevron"></span></span>
  </a>
  <div class="sheet-backdrop" id="cartsSheet">
    <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="cartsTitle">
      <div class="sheet-handle"></div>
      <div class="sheet-head"><div><div class="eyebrow">Keranjang</div><h3 class="sheet-title" id="cartsTitle">Lanjut checkout</h3><div class="sheet-sub">Keranjang dipisah per brand karena outletnya beda.</div></div>
        <button class="close-btn" data-close="cartsSheet" aria-label="Tutup" id="cartsClose"></button></div>
      <div id="cartsList"></div>
    </div>
  </div>

  <!-- Pilih brand ala video (tombol Order Sekarang) -->
  <div id="brandModal">
    <div class="brand-modal">
      <div class="brand-modal-head">
        <img src="/img/brand/zonafitri-logo.png" alt="Zona Fitri" style="height:38px;width:auto;object-fit:contain;" />
      </div>
      <div class="brand-modal-grid" id="brandModalGrid"></div>
    </div>
  </div>

  <script type="module" src="/assets/js/home.js?v=<?= time() ?>"></script>
  <script src="/assets/js/nufs.js" defer></script>
</body>
</html>
