<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#0a1122" />
  <meta name="description" content="Zona Fitri — Jasa Order Kopi Kenangan, Tomoro Coffee, Fore Coffee & Janji Jiwa. Lebih hemat, pesan langsung via WhatsApp." />
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
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="/assets/css/app.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/home.css?v=<?= time() ?>" />
  <script src="/assets/js/theme-boot.js"></script>
</head>
<body class="home-page">
  <div class="app-container" id="splash">

    <!-- Top Left Leaves & Top Right Dots -->
    <img src="/img/splash_top_decor.png" alt="" class="decor-leaves-top" onerror="this.style.display='none';">
    <div class="decor-dots-top" aria-hidden="true">
      <span></span><span></span><span></span><span></span>
      <span></span><span></span><span></span><span></span>
      <span></span><span></span><span></span><span></span>
    </div>

    <!-- Header Logo & Status Badge -->
    <div class="header">
      <div class="main-logo">
        <img src="/img/brand/zonafitri-logo.png" alt="zona fitri" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
        <h1 style="display:none;">zona<span>fitri</span></h1>
      </div>
      <div class="status-badge" id="spStatus">
        <i class="fa-solid fa-circle-check"></i> <span id="spStatusText">SEDANG BUKA</span>
      </div>
    </div>

    <!-- Hero Text -->
    <div class="hero-text">
      <h2 id="spTitle">Mau Ngopi?</h2>
      <h3 id="spSub">Ingat Fitri</h3>
    </div>

    <!-- Divider -->
    <div class="divider">
      <span>PILIH BRAND KOPI</span>
    </div>

    <!-- Brand Cards List -->
    <div class="brand-list">
      <a href="/kopikenangan" class="brand-card">
        <div class="brand-icon icon-kenangan">
          <img src="/img/brands/kopken_white.png" alt="" onerror="this.onerror=null;this.replaceWith(document.createElement('i')).className='fa-solid fa-heart';">
        </div>
        <div class="brand-name">KOPI KENANGAN</div>
        <div class="brand-arrow">
          <i class="fa-solid fa-chevron-right"></i>
        </div>
      </a>

      <a href="/tomoro" class="brand-card">
        <div class="brand-icon icon-tomoro">
          <img src="/img/brands/tomoro_white.png" alt="" onerror="this.onerror=null;this.replaceWith(document.createElement('i')).className='fa-solid fa-cat';">
        </div>
        <div class="brand-name">TOMORO COFFEE</div>
        <div class="brand-arrow">
          <i class="fa-solid fa-chevron-right"></i>
        </div>
      </a>

      <a href="/fore" class="brand-card">
        <div class="brand-icon icon-fore">
          <img src="/img/brands/fore_white.png" alt="" onerror="this.onerror=null;this.replaceWith(document.createElement('i')).className='fa-brands fa-pagelines';">
        </div>
        <div class="brand-name">FORE COFFEE</div>
        <div class="brand-arrow">
          <i class="fa-solid fa-chevron-right"></i>
        </div>
      </a>

      <a href="/janjiw" class="brand-card">
        <div class="brand-icon icon-jiwa">
          <img src="/img/brands/jiwa_white.png" alt="" onerror="this.onerror=null;this.replaceWith(document.createElement('i')).className='fa-solid fa-mug-hot';">
        </div>
        <div class="brand-name">JANJI JIWA</div>
        <div class="brand-arrow">
          <i class="fa-solid fa-chevron-right"></i>
        </div>
      </a>
    </div>

    <!-- CTA Button (Gabung Saluran WhatsApp) -->
    <div class="cta-container">
      <div class="cta-spark-wrap">
        <svg class="cta-sparks" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5" stroke-linecap="round">
          <line x1="12" y1="2" x2="12" y2="6"></line>
          <line x1="4" y1="12" x2="8" y2="10"></line>
          <line x1="5" y1="19" x2="9" y2="15"></line>
        </svg>
        <a href="https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F" target="_blank" rel="noopener" class="cta-btn" id="spWa">
          <span class="cta-left">
            <i class="fa-brands fa-whatsapp"></i>
            <span class="cta-sep">|</span>
            <span class="cta-text">Gabung Saluran</span>
          </span>
          <i class="fa-solid fa-chevron-right cta-chevron"></i>
        </a>
      </div>
    </div>

    <!-- Footer Promo Section -->
    <div class="footer-promo-row">
      <div class="footer-promo-text">
        <div class="footer-text">
          Ngopi Lebih Seru<br>
          Bersama <span>Zona Fitri</span>
        </div>
        <svg class="underline-svg" viewBox="0 0 200 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 15Q100 -5 195 15" stroke="#9eb1cf" stroke-width="3" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="footer-cup-wrap">
        <img src="/img/coffee_cup_bottom.png" alt="Zona Fitri Coffee" class="footer-cup-img" onerror="this.style.display='none';">
      </div>
    </div>

  </div>

  <!-- Realtime Shop Status & WA Channel Sync Helper -->
  <script src="/assets/js/nufs.js?v=<?= time() ?>" defer></script>
</body>
</html>
