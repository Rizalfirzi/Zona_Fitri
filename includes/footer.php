<?php
/**
 * Footer Template - Zona Fitri
 */
$appName = config('app_name', 'Zona Fitri');
?>

  <!-- Floating Sticky Bottom Cart Bar -->
  <aside class="floating-cart-bar" id="floatingCartBar" aria-label="Keranjang Belanja">
    <div class="cart-bar-left">
      <div class="cart-icon-badge">
        🛍️
        <span class="cart-count-pill" id="cartBarBadge">0</span>
      </div>
      <div class="cart-summary-text">
        <span class="cart-qty-text" id="cartBarQty">0 Pesanan</span>
        <small class="cart-outlet-text" id="cartBarOutlet">Memilih outlet...</small>
      </div>
    </div>

    <div class="cart-bar-right">
      <span class="cart-total-price" id="cartBarTotal">Rp 0</span>
      <button type="button" class="btn-open-cart" onclick="openModal('cartModal')">
        Lihat Keranjang
      </button>
    </div>
  </aside>

  <!-- Toast Notification Container -->
  <div class="toast-notice" id="toastNotice" role="status" aria-live="polite">
    <span class="toast-icon">ℹ️</span>
    <span class="toast-msg">Notifikasi</span>
  </div>

  <!-- Store Footer -->
  <footer class="store-footer">
    <p>&copy; <?= date('Y') ?> <b><?= htmlspecialchars($appName) ?></b> — Real-Time Coffee Ordering</p>
    <p style="margin-top:4px;opacity:0.75;">Zero Database • PHP Native Murni • Terhubung Langsung ke API Gerai Resmi</p>
  </footer>

</div><!-- /.container -->

<!-- Include All Modals -->
<?php require_once __DIR__ . '/modals.php'; ?>

<!-- Client Application Logic -->
<script src="/assets/js/app.js?v=<?= time() ?>"></script>

</body>
</html>
