<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#091C45" />
  <meta name="description" content="Order Kopi Kenangan, Tomoro, Fore & Janji Jiwa lebih hemat lewat Zona Fitri. Pilih outlet, pilih menu, kirim pesanan via WhatsApp." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Zona Fitri — Jasa Order Kopi" />
  <meta property="og:description" content="Kopi Kenangan, Tomoro, Fore & Janji Jiwa lebih hemat. Pesan langsung via WhatsApp." />
  <meta property="og:image" content="/img/brand/zonafitri-logo.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <title>Order Kopi | Zona Fitri</title>
  <link rel="icon" href="/img/brand/zonafitri-logo.png" type="image/png" />
  <link rel="apple-touch-icon" href="/img/brand/zonafitri-logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/app.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="/assets/css/store.css?v=<?= time() ?>" />
  <script src="/assets/js/theme-boot.js"></script>
</head>
<body class="store-page">
  <a class="skip-link" href="#menu">Langsung ke menu</a>
  <div class="container">
    <!-- Brand Top Bar (Back button, Logo, Brand Name, 3-dots) Sesuai Gambar 2 -->
    <nav class="store-brand-bar">
      <a href="/" class="brand-back-btn" id="brandBackBtn" aria-label="Kembali ke beranda" title="Kembali ke beranda">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="brand-header-info" id="brandHeaderInfo">
        <div class="brand-header-logo-wrap">
          <img id="brandHeaderLogo" src="" alt="" class="brand-header-logo" />
        </div>
        <div class="brand-header-text">
          <h1 id="brandHeaderName" class="brand-header-name">Kopi Kenangan</h1>
          <span class="brand-header-sub">Pesan Langsung via WhatsApp</span>
        </div>
      </div>
      <button class="icon-btn brand-dots-btn" id="brandBtn" aria-label="Pilih brand lain" title="Ganti brand">•••</button>
    </nav>

    <div id="closedBanner"></div>

    <!-- Outlet Section (2-Column Card) Sesuai Gambar 2 -->
    <div class="outlet-section" id="outletSection">
      <div class="os-col-left">
        <img id="osOutletThumb" src="https://cdn.kopikenangan.com/public/cf8b6835-e066-4a73-7f08-4803716d2d4c" alt="Foto Outlet" class="os-cup-thumb" onerror="this.onerror=null;this.src='/img/outlet_preview_cup.png'" />
      </div>
      <div class="os-col-right">
        <div class="os-header">
          <span class="os-title">📍 PILIH OUTLET</span>
          <button type="button" class="os-near-btn" id="inlineOutletNearMe" title="Cari outlet terdekat dari lokasi saya">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            <span>Terdekat</span>
          </button>
        </div>
        <div class="os-input-wrap" id="outletInputWrap">
          <input type="text" id="inlineOutletInput" placeholder="📍 Ganti outlet (saat ini: Bandung Electronic Center)..." autocomplete="off" enterkeyhint="search" />
          <span class="os-input-chevron">⌵</span>
          <button type="button" class="os-clear hidden" id="inlineOutletClear" aria-label="Bersihkan">✕</button>
        </div>
        <div class="os-chip hidden" id="inlineOutletChip" title="Klik untuk ganti outlet">
          <span class="os-chip-text"><span class="os-chip-ico">🏪</span> Outlet: <b id="inlineOutletChipName"></b></span>
          <button type="button" id="inlineOutletChipRemove" aria-label="Ganti outlet" title="Ganti outlet">⇄ Ganti</button>
        </div>
      </div>
      <div class="os-dropdown hidden" id="inlineOutletDropdown" role="listbox"></div>
    </div>

    <!-- Promo WhatsApp Banner Sesuai Gambar 2 -->
    <a href="https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F" target="_blank" rel="noopener" class="store-promo-banner" aria-label="Ngopi Lebih Mudah di WhatsApp!">
      <img src="/img/banner_wa_promo.png" alt="Ngopi Lebih Mudah di WhatsApp! Pesan cepat, praktis, tanpa ribet." class="spb-banner-img" />
    </a>

    <!-- Menu Toolbar (Search & Category Pills) Sesuai Gambar 2 -->
    <div class="menu-toolbar" id="menuToolbar">
      <!-- Search input dengan icon filter -->
      <div class="menu-search">
        <span class="search-ico">🔍</span>
        <input type="search" id="menuSearch" autocomplete="off" enterkeyhint="search" placeholder="Cari menu Kopi Kenangan…" aria-label="Cari menu" />
        <span class="search-filter-icon" aria-hidden="true" title="Filter menu">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round"><path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/></svg>
        </span>
        <button type="button" class="search-clear hidden" id="menuSearchClear">✕</button>
      </div>

      <!-- Kategori Pills Bar (Semua, Kopi, Non-Kopi, Teh, Frappé) -->
      <div class="cat-bar" id="catBar" role="tablist" aria-label="Kategori menu"></div>

      <div class="outlet-floating-pill hidden" id="outletFloatingPill">
        <span>☕ Outlet: <span id="outletFloatingName"></span></span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
      </div>
    </div>

    <main class="menu" id="menu" aria-live="polite" tabindex="-1"></main>

    <div class="menu-source" id="menuSource"></div>
    <footer class="store-footer-text" style="text-align:center;padding:16px 14px 30px;font-size:11.5px;color:#94A3B8;">&copy; <?= date('Y') ?> Zona Fitri · Jasa titip order kopi</footer>

    <!-- Banner status outlet tutup -->
    <div class="outlet-closed-bar hidden" id="outletClosedBar">
      <span class="oc-closed-dot"></span> Outlet sedang tutup, pemesanan tidak tersedia
    </div>

    <!-- Floating cart bar -->
    <button class="cart-bar" id="cartBar" aria-label="Buka keranjang">
      <span class="cart-bar-left"><span class="cart-bar-icon" id="cartBarIcon"><span class="cart-bar-badge" id="cartBadge">0</span></span>
        <span class="cart-bar-text"><b id="cartBarQty">0 item</b><small id="cartBarOutlet"></small></span></span>
      <span class="cart-bar-right"><span class="price" id="cartBarTotal">Rp0</span><span id="cartBarChevron"></span></span>
    </button>
  </div>

  <!-- Outlet picker sheet -->
  <div class="sheet-backdrop" id="outletSheet">
    <div class="sheet outlet-sheet" role="dialog" aria-modal="true" aria-labelledby="outletSheetTitle">
      <div class="sheet-handle"></div>
      <div class="sheet-head"><div><div class="eyebrow">Outlet pesanan</div><h3 class="sheet-title" id="outletSheetTitle">Pilih outlet</h3></div>
        <button class="close-btn" data-close="outletSheet" aria-label="Tutup" id="outletClose"></button></div>
      <div class="outlet-search">
        <span class="outlet-search-icon" aria-hidden="true"></span>
        <input type="search" id="outletInput" autocomplete="off" enterkeyhint="search" aria-label="Cari outlet" placeholder="Cari nama outlet atau daerah…" />
      </div>
      <button class="geo-btn" id="outletGeo"><span class="geo-ico" id="outletGeoIco"></span><span><b>Pakai lokasi saya</b><small>Tampilkan outlet terdekat</small></span></button>
      <div class="outlet-list" id="outletList" role="listbox" aria-label="Daftar outlet"></div>
    </div>
  </div>

  <!-- Category picker sheet -->
  <div class="sheet-backdrop" id="catSheet">
    <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="catSheetTitle">
      <div class="sheet-handle"></div>
      <div class="sheet-head"><div><div class="eyebrow">Kategori</div><h3 class="sheet-title" id="catSheetTitle">Mau lihat apa?</h3></div>
        <button class="close-btn" data-close="catSheet" aria-label="Tutup" id="catClose"></button></div>
      <div class="cat-list" id="catList"></div>
    </div>
  </div>

  <!-- Product sheet -->
  <div class="sheet-backdrop" id="itemSheet">
    <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="itemName">
      <div class="sheet-handle"></div>
      <div class="item-hero" id="itemHero"></div>
      <div class="item-cat eyebrow" id="itemCat"></div>
      <h3 class="item-name" id="itemName"></h3>
      <p class="item-desc" id="itemDesc"></p>
      <div class="item-price-row"><span class="item-price price" id="itemPrice"></span><span class="item-normal price" id="itemNormal"></span></div>
      <div id="itemOptions"></div>
      <label class="field-label" for="itemNote">Catatan</label>
      <textarea class="field" id="itemNote" rows="2" maxlength="200" placeholder="Contoh: es dipisah, extra shot…"></textarea>
      <div class="qty-row">
        <span class="qty-label">Jumlah</span>
        <div class="qty"><button id="qtyMinus" aria-label="Kurangi"></button><span id="qtyVal" class="num">1</span><button id="qtyPlus" aria-label="Tambah"></button></div>
      </div>
      <div class="sheet-actions">
        <button class="btn btn-soft" data-close="itemSheet">Batal</button>
        <button class="btn btn-primary" id="itemAdd">Tambah</button>
      </div>
    </div>
  </div>

  <!-- Bundle sheet -->
  <div class="sheet-backdrop" id="bundleSheet">
    <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="bundleName">
      <div class="sheet-handle"></div>
      <div class="sheet-head"><div><div class="eyebrow" id="bundleGroup">Promo bundling</div><h3 class="sheet-title" id="bundleName"></h3><div class="sheet-sub" id="bundleDesc"></div></div>
        <button class="close-btn" data-close="bundleSheet" aria-label="Tutup" id="bundleClose"></button></div>
      <div class="bundle-fixed" id="bundleFixed"></div>
      <div class="bundle-slots" id="bundleSlots"></div>
      <h4 class="bundle-grid-title" id="bundleGridTitle"></h4>
      <div class="bundle-grid" id="bundleGrid"></div>
      <div class="bundle-adjust hidden" id="bundleAdjust"></div>
      <label class="field-label" for="bundleNote">Catatan</label>
      <textarea class="field" id="bundleNote" rows="2" maxlength="200" placeholder="Catatan untuk paket ini…"></textarea>
      <div class="bundle-total-bar" id="bundleTotalBar"></div>
      <div class="sheet-actions">
        <button class="btn btn-soft" data-close="bundleSheet">Batal</button>
        <button class="btn btn-primary" id="bundleAdd" disabled>Tambah</button>
      </div>
    </div>
  </div>

  <!-- Cart sheet -->
  <div class="sheet-backdrop" id="cartSheet">
    <div class="sheet" role="dialog" aria-modal="true" aria-labelledby="cartTitle">
      <div class="sheet-handle"></div>
      <div class="sheet-head"><div><div class="eyebrow" id="cartOutlet"></div><h3 class="sheet-title" id="cartTitle">Keranjang</h3></div>
        <button class="close-btn" data-close="cartSheet" aria-label="Tutup" id="cartClose"></button></div>
      <div class="cart-list" id="cartList"></div>

      <div class="cart-form">
        <label class="field-label" for="custName">Nama pemesan</label>
        <input class="field" id="custName" autocomplete="name" maxlength="60" placeholder="Nama kamu" />
        <div class="field-error hidden" id="custNameErr">Isi nama pemesan dulu ya.</div>
        <div id="pickupWrap">
          <label class="field-label">Waktu pesanan</label>
          <div class="seg" id="pickupSeg"><button data-mode="now" class="active">Sekarang</button><button data-mode="custom">Atur jam</button></div>
          <div class="pickup-time hidden" id="pickupTimeWrap"><select class="field" id="pickupTime" aria-label="Waktu pesanan"></select></div>
        </div>
        <div id="adminWrap" class="hidden">
          <label class="field-label">Kirim ke admin</label>
          <div class="cs-admins" id="cartAdmins"></div>
        </div>
        <label class="field-label" for="cartNote">Catatan pesanan</label>
        <textarea class="field" id="cartNote" rows="2" maxlength="500" placeholder="Catatan tambahan untuk admin…"></textarea>
      </div>

      <div class="summary">
        <div class="summary-row" id="sumQtyRow"><span>Total pesanan</span><span id="sumQty">0 item</span></div>
        <div class="summary-total"><span>Total bayar</span><span class="price" id="sumTotal">Rp0</span></div>
      </div>
      <div class="sheet-actions">
        <button class="btn btn-soft" id="cartReset">Kosongkan</button>
        <button class="btn btn-primary" id="checkoutBtn"></button>
      </div>
    </div>
  </div>

  <!-- Order sent -->
  <div class="sheet-backdrop dialog-backdrop" id="sentModal">
    <div class="dialog" role="alertdialog" aria-modal="true" aria-labelledby="sentTitle">
      <div class="dialog-icon ok" id="sentIcon"></div>
      <h3 class="dialog-title" id="sentTitle">Pesanan terkirim ke WhatsApp</h3>
      <p class="dialog-text">Lanjutkan chat dengan admin untuk konfirmasi & pembayaran. Simpan kode pesananmu.</p>
      <div class="order-code-box"><div class="eyebrow">Kode pesanan</div><div class="order-code" id="sentCode"></div><div class="order-code-total price" id="sentTotal"></div></div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <a class="btn btn-primary btn-block" id="sentWaLink" target="_blank" rel="noopener">Buka WhatsApp lagi</a>
        <button class="btn btn-soft btn-block" data-close="sentModal">Selesai</button>
      </div>
    </div>
  </div>

  <!-- Brand switcher modal -->
  <div id="brandModal">
    <div class="brand-modal">
      <div class="brand-modal-head">
        <img src="/img/brand/zonafitri-logo.png" alt="Zona Fitri" style="height:38px;width:auto;object-fit:contain;" />
      </div>
      <div class="brand-modal-grid" id="brandModalGrid"></div>
    </div>
  </div>

  <script type="module" src="/assets/js/store.js?v=<?= time() ?>"></script>
  <script src="/assets/js/nufs.js" defer></script>
</body>
</html>
