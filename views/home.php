<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#F4EFE4" />
  <meta name="description" content="Zona Fitri — jasa order Kopi Kenangan, Tomoro Coffee & Fore Coffee. Lebih hemat, pesan langsung via WhatsApp." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Zona Fitri — Jasa Order Kopi" />
  <meta property="og:description" content="Kopi Kenangan, Tomoro & Fore lebih hemat. Pesan langsung via WhatsApp." />
  <meta property="og:image" content="/img/brand/zonafitri-logo.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <title>Zona Fitri | Jasa Order Kopi</title>
  <link rel="icon" href="/img/brand/zonafitri-logo.png" type="image/png" />
  <link rel="apple-touch-icon" href="/img/brand/zonafitri-logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/app.css" />
  <link rel="stylesheet" href="/assets/css/home.css" />
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

    <!-- Splash tampilan pilih brand dengan latar tekstur elegan -->
    <section id="splash" data-mode="malam">
      <div class="sp-bg sp-bg-pagi" style="background-image: url('/img/hero/bg1.jpg');"></div>
      <div class="sp-bg sp-bg-senja" style="background-image: url('/img/hero/bg2.jpg');"></div>
      <div class="sp-bg sp-bg-malam" style="background-image: url('/img/hero/bg1.jpg');"></div>

      <div class="sp-content">
        <div class="sp-header">
          <div class="sp-brand">
            <img src="/img/brand/zonafitri-logo.png" alt="Zona Fitri" style="height:52px;width:auto;object-fit:contain;filter:drop-shadow(0 2px 8px rgba(100,50,20,0.2));" />
          </div>
          <div class="sp-status" id="spStatus"><span class="sp-dot"></span><span id="spStatusText">SEDANG BUKA</span></div>
          <h2 id="spTitle">Pesan Kopi Lebih Hemat</h2>
          <p class="sp-sub" id="spSub">pilih outlet & pesan sekarang</p>
        </div>

        <!-- Direct Brand Picker di Halaman Awal Sesuai Request Client (Page 1) -->
        <div class="sp-brand-picker">
          <span class="sp-picker-label">Pilihan brand langsung di halaman awal saja</span>
          <div class="sp-brand-grid">
            <a href="/kopikenangan" class="sp-brand-card">
              <img src="/img/brands/kopken.png" alt="Kopi Kenangan" />
              <span>KOPI KENANGAN</span>
            </a>
            <a href="/tomoro" class="sp-brand-card">
              <img src="/img/brands/tomoro.png" alt="Tomoro Coffee" />
              <span>TOMORO COFFEE</span>
            </a>
            <a href="/fore" class="sp-brand-card">
              <img src="/img/brands/fore.png" alt="Fore Coffee" />
              <span>FORE COFFEE</span>
            </a>
          </div>
        </div>

        <div class="sp-building-spacer"></div>

        <div class="sp-bottom">
          <div class="sp-cta">
            <a class="btn btn-green" id="spWa" href="https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5a8.5 8.5 0 0 0-7.3 12.8L3.5 20.5l4.3-1.1A8.5 8.5 0 1 0 12 3.5z"/><path d="M9 8.8c.2-.5.4-.5.6-.5l.5.1 1 2.4c.1.2 0 .4-.1.6l-.5.5c.4.8 1 1.5 1.9 2l.6-.5c.2-.2.4-.2.6-.1l2.3 1.1c.2.1.3.3.3.5-.1.9-.6 1.7-1.4 1.9-.7.2-1.5.1-2.7-.4a12.6 12.6 0 0 1-4.5-4c-.6-1-.8-1.9-.6-2.7.1-.5.5-.8 1-1z" fill="currentColor" stroke="none"/></g></svg><span>Gabung Saluran</span></a>
            <button class="btn btn-dark" id="spLihat" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20.5" r="1.5" fill="currentColor"/><circle cx="18" cy="20.5" r="1.5" fill="currentColor"/><path d="M2.5 3.5h3.2l2.4 11.2a1.5 1.5 0 0 0 1.5 1.2h8.5a1.5 1.5 0 0 0 1.5-1.2l1.6-8.2H6.2"/></g></svg><span>Order Sekarang</span></button>
          </div>
          <div class="sp-url"><span>‹</span><span id="spHost">zonafitri.id</span><span>•••</span></div>
        </div>
      </div>
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

  <script type="module" src="/assets/js/home.js"></script>
  <script src="/assets/js/nufs.js" defer></script>
</body>
</html>
