<?php
/**
 * Modals & Bottom Sheets Template - Zona Fitri
 */
?>

<!-- ==========================================================================
     1. OUTLET PICKER MODAL (Bottom Sheet)
     ========================================================================== -->
<div class="modal-backdrop" id="outletModal" role="dialog" aria-modal="true" aria-labelledby="outletModalTitle">
  <div class="bottom-sheet">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-head">
      <div class="sheet-head-title">
        <h3 id="outletModalTitle">Pilih Outlet Tujuan</h3>
        <p>Menu, stok real-time, dan harga mengikuti outlet ini</p>
      </div>
      <button type="button" class="btn-sheet-close" onclick="closeModal('outletModal')" aria-label="Tutup">✕</button>
    </div>

    <div class="sheet-body">
      <!-- Search Input -->
      <input type="search" class="outlet-search-input" id="outletSearchInput" placeholder="Cari nama kota, mall, atau jalan..." autocomplete="off">
      
      <!-- Quick GPS Button -->
      <button type="button" class="btn-near-gps" id="btnFindNearMe" style="width:100%;justify-content:center;padding:9px;margin-bottom:14px;border-radius:12px;">
        <span>📍</span> Gunakan Lokasi GPS Saya (Urutkan Terdekat)
      </button>

      <!-- Outlet List Container -->
      <div class="outlet-list-items" id="outletListContainer">
        <!-- Rendered dynamically by app.js -->
        <p style="text-align:center;padding:20px;color:var(--text-muted);font-size:0.85rem;">Memuat daftar outlet...</p>
      </div>
    </div>
  </div>
</div>

<!-- ==========================================================================
     2. DRINK CUSTOMIZATION MODAL (Bottom Sheet)
     ========================================================================== -->
<div class="modal-backdrop" id="customItemModal" role="dialog" aria-modal="true" aria-labelledby="modalItemName">
  <div class="bottom-sheet">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-head">
      <div class="sheet-head-title">
        <h3>Kustomisasi Minuman</h3>
        <p>Atur ukuran, suhu, takaran es & gula sesuai selera</p>
      </div>
      <button type="button" class="btn-sheet-close" onclick="closeModal('customItemModal')" aria-label="Tutup">✕</button>
    </div>

    <div class="sheet-body">
      <!-- Item Preview -->
      <div class="custom-item-preview">
        <img id="modalItemImg" class="custom-item-img" src="" alt="Item">
        <div class="custom-item-info">
          <h4 id="modalItemName">Nama Menu</h4>
          <span class="custom-item-price" id="modalItemPrice">Rp 0</span>
        </div>
      </div>

      <!-- Size Selection -->
      <div class="option-group">
        <div class="option-group-title">
          <span>Pilih Ukuran Cup</span>
          <small style="color:var(--primary);font-weight:700;">Wajib</small>
        </div>
        <div class="option-chips" id="modalSizeChips"></div>
      </div>

      <!-- Temperature Selection -->
      <div class="option-group">
        <div class="option-group-title">
          <span>Suhu Minuman</span>
          <small style="color:var(--primary);font-weight:700;">Wajib</small>
        </div>
        <div class="option-chips" id="modalTempChips"></div>
      </div>

      <!-- Ice Level Selection -->
      <div class="option-group">
        <div class="option-group-title">
          <span>Level Es (Ice Level)</span>
        </div>
        <div class="option-chips" id="modalIceChips"></div>
      </div>

      <!-- Sugar Level Selection -->
      <div class="option-group">
        <div class="option-group-title">
          <span>Tingkat Kemanisan (Sugar Level)</span>
        </div>
        <div class="option-chips" id="modalSugarChips"></div>
      </div>

      <!-- Special Notes -->
      <div class="option-group">
        <div class="option-group-title">
          <span>Catatan Khusus (Opsional)</span>
        </div>
        <textarea class="notes-input" id="modalItemNotes" rows="2" placeholder="Contoh: sedotan dipisah, jangan terlalu pahit..."></textarea>
      </div>
    </div>

    <div class="sheet-foot">
      <!-- Quantity Stepper -->
      <div class="qty-stepper">
        <button type="button" class="qty-btn" onclick="changeModalQty(-1)" aria-label="Kurangi">-</button>
        <span class="qty-display" id="modalQtyDisplay">1</span>
        <button type="button" class="qty-btn" onclick="changeModalQty(1)" aria-label="Tambah">+</button>
      </div>

      <!-- Add to Cart Action -->
      <button type="button" class="btn-primary-action" id="modalBtnAddSubtotal" onclick="confirmAddToCart()">
        Tambah ke Keranjang
      </button>
    </div>
  </div>
</div>

<!-- ==========================================================================
     3. SHOPPING CART & CHECKOUT MODAL (Bottom Sheet)
     ========================================================================== -->
<div class="modal-backdrop" id="cartModal" role="dialog" aria-modal="true" aria-labelledby="cartModalTitle">
  <div class="bottom-sheet">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-head">
      <div class="sheet-head-title">
        <h3 id="cartModalTitle">Keranjang Pesanan</h3>
        <p>Cek kembali pesanan sebelum dikirim ke WhatsApp</p>
      </div>
      <button type="button" class="btn-sheet-close" onclick="closeModal('cartModal')" aria-label="Tutup">✕</button>
    </div>

    <div class="sheet-body">
      <!-- Cart Items List -->
      <div class="cart-items-list" id="cartItemsList">
        <!-- Rendered by app.js -->
      </div>

      <!-- Plastic Bag Fee Option -->
      <label class="checkbox-pill-wrap" for="plasticBagCheckbox">
        <div>
          <b style="font-size:0.85rem;color:var(--text-main);">Kantung Belanja Plastik (+Rp 1.000)</b>
          <p style="font-size:0.72rem;color:var(--text-muted);">Membantu mempermudah membawa minuman pesananmu</p>
        </div>
        <input type="checkbox" id="plasticBagCheckbox" style="width:18px;height:18px;accent-color:var(--primary);cursor:pointer;">
      </label>

      <!-- Customer Order Information Form -->
      <div class="cart-order-form">
        <div class="form-group">
          <label for="custNameInput">Nama Pemesan <span style="color:#ef4444;">*</span></label>
          <input type="text" class="form-input" id="custNameInput" placeholder="Contoh: Fitri / Budi" required autocomplete="name">
        </div>

        <div class="form-group">
          <label for="custPhoneInput">Nomor WhatsApp (Opsional)</label>
          <input type="tel" class="form-input" id="custPhoneInput" placeholder="Contoh: 081234567890" autocomplete="tel">
        </div>

        <div class="form-group">
          <label for="pickupTimeSelect">Waktu Pengambilan / Pickup</label>
          <select class="form-input" id="pickupTimeSelect">
            <option value="Sekarang">Sekarang (Langsung Dipesankan)</option>
            <option value="15 Menit Lagi">15 Menit Lagi</option>
            <option value="30 Menit Lagi">30 Menit Lagi</option>
            <option value="1 Jam Lagi">1 Jam Lagi</option>
          </select>
        </div>

        <div class="form-group">
          <label for="orderNotesInput">Catatan Tambahan untuk Admin</label>
          <input type="text" class="form-input" id="orderNotesInput" placeholder="Contoh: Tolong siapkan kantung terpisah">
        </div>
      </div>

      <!-- Bill Breakdown -->
      <div class="cart-bill-summary">
        <div class="bill-row">
          <span>Subtotal Menu</span>
          <span id="billSubtotal">Rp 0</span>
        </div>
        <div class="bill-row">
          <span>Fee Pesanan Satuan (1 pcs)</span>
          <span id="billSingleItemFee">Gratis (2+ pcs)</span>
        </div>
        <div class="bill-row">
          <span>Kantung Belanja</span>
          <span id="billPlasticFee">Rp 0</span>
        </div>
        <div class="bill-row total-row">
          <span>Total Pembayaran</span>
          <b id="billTotal">Rp 0</b>
        </div>
      </div>
    </div>

    <div class="sheet-foot">
      <button type="button" class="btn-primary-action" onclick="proceedCheckout()" style="background-color:#22c55e;">
        <span style="font-size:1.1rem;">💬</span> Pesan via WhatsApp
      </button>
    </div>
  </div>
</div>

<!-- ==========================================================================
     4. ORDER TRACKING MODAL (Bottom Sheet)
     ========================================================================== -->
<div class="modal-backdrop" id="trackOrderModal" role="dialog" aria-modal="true" aria-labelledby="trackModalTitle">
  <div class="bottom-sheet">
    <div class="sheet-handle-bar"></div>
    <div class="sheet-head">
      <div class="sheet-head-title">
        <h3 id="trackModalTitle">Lacak Status Pesanan</h3>
        <p>Pantau proses pesanan kopi favoritmu secara real-time</p>
      </div>
      <button type="button" class="btn-sheet-close" onclick="closeModal('trackOrderModal')" aria-label="Tutup">✕</button>
    </div>

    <div class="sheet-body">
      <div style="display:flex;gap:8px;margin-bottom:14px;">
        <input type="text" class="form-input" id="trackCodeInput" placeholder="Masukkan kode pesanan (misal: ZF-A1B2C3)" style="text-transform:uppercase;">
        <button type="button" class="btn-primary-action" onclick="trackOrder()" style="flex-grow:0;white-space:nowrap;padding:10px 16px;">
          Lacak
        </button>
      </div>

      <div id="trackResultBox" style="display:none;"></div>
    </div>
  </div>
</div>
