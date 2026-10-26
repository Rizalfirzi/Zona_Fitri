<?php
/**
 * Header Template - Zona Fitri
 */
$appName = config('app_name', 'Zona Fitri');
$tagline = config('store.tagline', 'Jasa Order Kopi');
$description = config('store.description', 'Pesan kopi favorit lebih hemat: pilih outlet, cek stok real-time, lalu order via WhatsApp.');
$adminPhone = config('store.admin_phone', '628973034455');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="theme-color" content="#791217">
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($appName) ?> — <?= htmlspecialchars($tagline) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
  <meta property="og:type" content="website">

  <title><?= htmlspecialchars($appName) ?> | <?= htmlspecialchars($tagline) ?></title>

  <!-- Google Fonts: Outfit & Caveat -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Core Stylesheet -->
  <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<div class="container">
  <!-- Top Navigation -->
  <header class="topnav" id="mainTopnav">
    <a href="/" class="nav-brand" aria-label="Beranda <?= htmlspecialchars($appName) ?>">
      <div class="brand-badge-logo">☕</div>
      <div class="brand-title-wrap">
        <span class="brand-name"><?= htmlspecialchars($appName) ?></span>
        <span class="brand-tagline">Real-Time Outlet & Stok</span>
      </div>
    </a>

    <div class="topnav-actions">
      <!-- Order Tracking Button -->
      <button type="button" class="icon-btn" onclick="openModal('trackOrderModal')" title="Lacak Pesanan" aria-label="Lacak Pesanan">
        📦
      </button>

      <!-- Theme Switcher -->
      <button type="button" class="icon-btn" id="themeToggleBtn" title="Ganti Tema" aria-label="Ganti Tema">
        🌙
      </button>

      <!-- Direct WA Chat -->
      <a href="https://wa.me/<?= htmlspecialchars($adminPhone) ?>" target="_blank" rel="noopener" class="icon-btn" style="color:#22c55e;" title="Chat Admin WhatsApp" aria-label="Chat Admin WhatsApp">
        💬
      </a>
    </div>
  </header>
