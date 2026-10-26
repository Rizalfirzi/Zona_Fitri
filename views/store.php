<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <meta name="theme-color" content="#F4EFE4" />
  <meta name="description" content="Order Kopi Kenangan, Tomoro & Fore lebih hemat lewat Zona Fitri. Pilih outlet, pilih menu, kirim pesanan via WhatsApp." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Zona Fitri — Jasa Order Kopi" />
  <meta property="og:description" content="Kopi Kenangan, Tomoro & Fore lebih hemat. Pesan langsung via WhatsApp." />
  <meta property="og:image" content="/img/brand/zonafitri-logo.png" />
  <meta name="twitter:card" content="summary_large_image" />
  <title>Order Kopi | Zona Fitri</title>
  <link rel="icon" href="/img/brand/zonafitri-logo.png" type="image/png" />
  <link rel="apple-touch-icon" href="/img/brand/zonafitri-logo.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/app.css" />
  <link rel="stylesheet" href="/assets/css/store.css" />
  <script src="/assets/js/theme-boot.js"></script>
</head>
<body class="store-page">
  <a class="skip-link" href="#menu">Langsung ke menu</a>
  <div class="container">
    <!-- Brand Top Bar (Back button, Logo, Brand Name, 3-dots) -->
    <nav class="store-brand-bar">
      <a href="/" class="brand-back-btn" id="brandBackBtn" aria-label="Kembali ke beranda">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </a>
      <div class="brand-header-info">
        <img id="brandHeaderLogo" src="" alt="" class="brand-header-logo" />
        <div class="brand-header-text">
          <h1 id="brandHeaderName" class="brand-header-name">Kopi Kenangan</h1>
          <span class="brand-header-sub">Pesan langsung via WhatsApp</span>
        </div>
      </div>
      <button class="icon-btn brand-dots-btn" id="brandBtn" aria-label="Pilih brand lain" title="Ganti brand">•••</button>
    </nav>

    <div id="closedBanner"></div>

    <!-- Inline Outlet Picker ala video -->
    <div class="outlet-section" id="outletSection">
      <div class="os-header">
        <div class="os-header-left">
          <span class="os-pin">📍</span>
          <span class="os-title">PILIH OUTLET</span>
        </div>
        <button type="button" class="os-near-btn" id="inlineOutletNearMe" title="Cari outlet terdekat dari lokasi saya">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
          <span>Terdekat</span>
        </button>
      </div>
      <div class="os-input-wrap" id="outletInputWrap">
        <input type="text" id="inlineOutletInput" placeholder="Ketik nama kota atau gerai..." autocomplete="off" enterkeyhint="search" />
        <button type="button" class="os-clear hidden" id="inlineOutletClear" aria-label="Bersihkan">✕</button>
      </div>
      <div class="os-chip hidden" id="inlineOutletChip" title="Klik untuk ganti outlet">
        <span class="os-chip-text">📍 Outlet: <b id="inlineOutletChipName"></b></span>
        <button type="button" id="inlineOutletChipRemove" aria-label="Ganti outlet" title="Ganti outlet">✕ Ganti</button>
      </div>
      <div class="os-dropdown hidden" id="inlineOutletDropdown" role="listbox"></div>
    </div>

    <!-- Mode toggle 2 tab (Menu & Bundling Promo) -->
    <div class="menu-toolbar" id="menuToolbar">
      <div class="mode-seg-3" id="modeSeg" role="tablist" aria-label="Jenis menu">
        <button type="button" class="mode-tab active" data-mode="single">Menu</button>
        <button type="button" class="mode-tab" data-mode="bundle">Bundling Promo</button>
      </div>

      <!-- Menu search & categories (khusus mode Menu) -->
      <div class="mode-single-controls" id="modeSingleControls">
        <div class="menu-search">
          <span class="search-ico">🔍</span>
          <input type="search" id="menuSearch" autocomplete="off" enterkeyhint="search" placeholder="Cari menu…" aria-label="Cari menu" />
          <button type="button" class="search-clear hidden" id="menuSearchClear">✕</button>
        </div>
        <div class="cat-bar" id="catBar" role="tablist" aria-label="Kategori menu"></div>
      </div>

      <div class="outlet-floating-pill hidden" id="outletFloatingPill">
        📍 Outlet: <span id="outletFloatingName"></span>
      </div>
    </div>

    <main class="menu" id="menu" aria-live="polite" tabindex="-1"></main>

    <div class="menu-source" id="menuSource"></div>
    <footer class="store-foot" id="storeFoot"></footer>

    <!-- Banner status outlet tutup ala video -->
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

  <!-- Category picker sheet ("select" kategori) -->
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

  <!-- Brand switcher ala video -->
  <div id="brandModal">
    <div class="brand-modal">
      <div class="brand-modal-head">
        <img src="/img/brand/zonafitri-logo.png" alt="Zona Fitri" style="height:38px;width:auto;object-fit:contain;" />
      </div>
      <div class="brand-modal-grid" id="brandModalGrid"></div>
    </div>
  </div>

  <script type="module" src="/assets/js/store.js"></script>
  <script src="/assets/js/nufs.js" defer></script>
</body>
</html>
