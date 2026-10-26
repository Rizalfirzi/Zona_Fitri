/**
 * ZONA FITRI - Modern Coffee Ordering Application Client
 * Real-time Outlets & Stock Management
 * 100% Native JavaScript (ES6+)
 */

const State = {
  brand: 'kopken',
  outlet: null,
  outlets: [],
  menu: null,
  categories: [],
  activeCategory: 'all',
  searchQuery: '',
  cart: [],
  theme: 'light',
  splashMode: 'malam',
  activeItemForModal: null,
  modalSelectedOptions: {
    size: 'Regular',
    temp: 'Ice',
    ice: 'Normal Ice',
    sugar: 'Normal Sugar',
    note: '',
    qty: 1,
  },
  fees: {
    singleItemFee: 2000,
    plasticBagFee: 1000,
  },
  plasticBagSelected: false,
};

// Brand themes definition
const BrandThemes = {
  kopken: {
    primary: '#791217',
    accent: '#C11F2E',
    light: '#FBEBEE',
    rgb: '121, 18, 23',
    name: 'Kopi Kenangan',
    defaultOutlet: 'LA.CODEFIN',
  },
  tomoro: {
    primary: '#FF8200',
    accent: '#E07000',
    light: '#FFF5EB',
    rgb: '255, 130, 0',
    name: 'Tomoro Coffee',
    defaultOutlet: 'TMR-627779',
  },
  fore: {
    primary: '#006241',
    accent: '#008256',
    light: '#EBF7F2',
    rgb: '0, 98, 65',
    name: 'Fore Coffee',
    defaultOutlet: 'KOKAS',
  },
};

// Format Rupiah Helper
function formatRp(num) {
  return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

// Show Toast Notification
function showToast(msg) {
  const toast = document.getElementById('toastNotice');
  if (!toast) return;
  toast.querySelector('.toast-msg').textContent = msg;
  toast.classList.add('show');
  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => {
    toast.classList.remove('show');
  }, 2800);
}

// Initialize Application
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSplashClock();
  initBrandTabs();
  initModals();
  loadSavedBrandAndOutlet();
  loadCartFromStorage();
  fetchOutlets();
  fetchMenu();
});

// Theme Management
function initTheme() {
  const saved = localStorage.getItem('zf_theme') || 'light';
  applyTheme(saved);

  const btn = document.getElementById('themeToggleBtn');
  if (btn) {
    btn.addEventListener('click', () => {
      const next = State.theme === 'light' ? 'dark' : 'light';
      applyTheme(next);
      localStorage.setItem('zf_theme', next);
    });
  }
}

function applyTheme(theme) {
  State.theme = theme;
  document.documentElement.setAttribute('data-theme', theme);
  const btn = document.getElementById('themeToggleBtn');
  if (btn) {
    btn.textContent = theme === 'dark' ? '☀️' : '🌙';
  }
}

// Real-time Clock (WIB) & Splash Mode
function initSplashClock() {
  function updateClock() {
    const now = new Date();
    // Jakarta / WIB is UTC+7
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const wib = new Date(utc + 3600000 * 7);

    const hours = String(wib.getHours()).padStart(2, '0');
    const mins = String(wib.getMinutes()).padStart(2, '0');
    const secs = String(wib.getSeconds()).padStart(2, '0');

    const clockEl = document.getElementById('spClock');
    if (clockEl) {
      clockEl.textContent = `${hours}:${mins}:${secs} WIB`;
    }

    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const dateStr = `${days[wib.getDay()]}, ${wib.getDate()} ${months[wib.getMonth()]} ${wib.getFullYear()}`;
    const dateEl = document.getElementById('spDate');
    if (dateEl) {
      dateEl.textContent = dateStr;
    }
  }

  updateClock();
  setInterval(updateClock, 1000);

  // Splash mode buttons (Pagi / Senja / Malam)
  const splashCard = document.getElementById('splashCard');
  const segButtons = document.querySelectorAll('.sp-seg button');
  segButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const mode = btn.dataset.mode;
      State.splashMode = mode;
      segButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      if (splashCard) {
        splashCard.className = `splash-card mode-${mode}`;
      }
    });
  });
}

// Brand Tabs Switcher
function initBrandTabs() {
  const tabs = document.querySelectorAll('.brand-tab');
  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const b = tab.dataset.brand;
      if (b === State.brand) return;
      switchBrand(b);
    });
  });
}

function switchBrand(brand) {
  State.brand = brand;
  const theme = BrandThemes[brand] || BrandThemes.kopken;

  // Apply CSS Variables
  document.documentElement.style.setProperty('--primary', theme.primary);
  document.documentElement.style.setProperty('--primary-accent', theme.accent);
  document.documentElement.style.setProperty('--primary-light', theme.light);
  document.documentElement.style.setProperty('--primary-rgb', theme.rgb);

  // Update Active Tab UI
  document.querySelectorAll('.brand-tab').forEach((t) => {
    t.classList.toggle('active', t.dataset.brand === brand);
  });

  // Restore saved outlet for this brand or default
  const savedOutlet = localStorage.getItem(`zf_outlet_${brand}`);
  if (savedOutlet) {
    try {
      State.outlet = JSON.parse(savedOutlet);
    } catch {
      State.outlet = null;
    }
  } else {
    State.outlet = null;
  }

  // Update outlet display chip
  updateOutletDisplay();

  // Load cart for this brand
  loadCartFromStorage();

  // Fetch Outlets & Menu
  fetchOutlets();
  fetchMenu();

  showToast(`Beralih ke gerai ${theme.name}`);
}

function loadSavedBrandAndOutlet() {
  const urlParams = new URLSearchParams(window.location.search);
  const brandParam = urlParams.get('brand');
  if (brandParam && BrandThemes[brandParam]) {
    State.brand = brandParam;
  }

  const savedOutlet = localStorage.getItem(`zf_outlet_${State.brand}`);
  if (savedOutlet) {
    try {
      State.outlet = JSON.parse(savedOutlet);
    } catch {
      State.outlet = null;
    }
  }

  // Set active tab in UI
  document.querySelectorAll('.brand-tab').forEach((t) => {
    t.classList.toggle('active', t.dataset.brand === State.brand);
  });

  const theme = BrandThemes[State.brand] || BrandThemes.kopken;
  document.documentElement.style.setProperty('--primary', theme.primary);
  document.documentElement.style.setProperty('--primary-accent', theme.accent);
  document.documentElement.style.setProperty('--primary-light', theme.light);
  document.documentElement.style.setProperty('--primary-rgb', theme.rgb);

  updateOutletDisplay();
}

function updateOutletDisplay() {
  const nameEl = document.getElementById('activeOutletName');
  const cityEl = document.getElementById('activeOutletCity');
  const badgeEl = document.getElementById('activeOutletBadge');

  if (State.outlet) {
    if (nameEl) nameEl.textContent = State.outlet.name;
    if (cityEl) cityEl.textContent = State.outlet.city || State.outlet.category || 'Outlet Resmi';
    if (badgeEl) {
      const isOpen = State.outlet.isOpen !== false;
      badgeEl.className = `badge-outlet-status ${isOpen ? 'open' : 'closed'}`;
      badgeEl.innerHTML = `<span style="font-size:8px;">●</span> ${isOpen ? 'Buka (' + (State.outlet.hours || 'Hari ini') + ')' : 'Tutup'}`;
    }
  } else {
    const brandConf = BrandThemes[State.brand];
    if (nameEl) nameEl.textContent = 'Memuat Outlet Terdekat...';
    if (cityEl) cityEl.textContent = brandConf ? brandConf.name : '';
    if (badgeEl) {
      badgeEl.className = 'badge-outlet-status open';
      badgeEl.textContent = 'Buka';
    }
  }
}

// Fetch Outlets from API
async function fetchOutlets(query = '', lat = null, lng = null) {
  try {
    let url = `/api/outlets.php?brand=${encodeURIComponent(State.brand)}`;
    if (query) url += `&q=${encodeURIComponent(query)}`;
    if (lat && lng) url += `&lat=${lat}&lng=${lng}`;

    const res = await fetch(url);
    const data = await res.json();

    if (data && data.success && Array.isArray(data.outlets)) {
      State.outlets = data.outlets;

      // If no outlet selected yet, select first
      if (!State.outlet && State.outlets.length > 0) {
        State.outlet = State.outlets[0];
        localStorage.setItem(`zf_outlet_${State.brand}`, JSON.stringify(State.outlet));
        updateOutletDisplay();
      }

      renderOutletModalList(State.outlets);
    }
  } catch (err) {
    console.error('Error fetching outlets:', err);
  }
}

// Real-Time GPS Near Me Function
function findNearMe() {
  if (!navigator.geolocation) {
    showToast('Browser tidak mendukung pendeteksi lokasi GPS.');
    return;
  }

  showToast('Mencari outlet terdekat dari lokasimu...');
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      fetchOutlets('', lat, lng).then(() => {
        if (State.outlets && State.outlets.length > 0) {
          const nearest = State.outlets[0];
          selectOutlet(nearest);
          showToast(`Outlet terdekat ditemukan: ${nearest.name} (${nearest.distanceKm} km)`);
        }
      });
    },
    (err) => {
      console.warn(err);
      showToast('Izin GPS ditolak atau tidak tersedia.');
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}

// Select Outlet
function selectOutlet(outlet) {
  State.outlet = outlet;
  localStorage.setItem(`zf_outlet_${State.brand}`, JSON.stringify(outlet));
  updateOutletDisplay();
  closeModal('outletModal');
  fetchMenu();
  showToast(`Outlet diganti ke: ${outlet.name}`);
}

// Render Outlets inside Modal
function renderOutletModalList(list) {
  const container = document.getElementById('outletListContainer');
  if (!container) return;

  if (!list || list.length === 0) {
    container.innerHTML = '<p style="text-align:center;padding:20px;color:var(--text-muted);font-size:0.85rem;">Tidak ada outlet ditemukan.</p>';
    return;
  }

  container.innerHTML = list
    .map((o) => {
      const isSelected = State.outlet && State.outlet.code === o.code;
      const isOpen = o.isOpen !== false;
      const distBadge = o.distanceKm ? `<span class="outlet-dist-pill">📍 ${o.distanceKm} km</span>` : '';

      return `
      <div class="outlet-item-row ${isSelected ? 'selected' : ''}" onclick='window.selectOutletRow(${JSON.stringify(o).replace(/'/g, "&#39;")})'>
        <div class="outlet-row-info">
          <h4>${escapeHtml(o.name)}</h4>
          <p>${escapeHtml(o.address || o.city || '')}</p>
        </div>
        <div class="outlet-row-right">
          ${distBadge}
          <span class="badge-outlet-status ${isOpen ? 'open' : 'closed'}">
            ${isOpen ? 'Buka' : 'Tutup'}
          </span>
        </div>
      </div>
    `;
    })
    .join('');
}

window.selectOutletRow = (o) => selectOutlet(o);

// Fetch Real-time Menu & Stock
async function fetchMenu(forceRefresh = false) {
  const grid = document.getElementById('productGrid');
  if (grid && !forceRefresh) {
    grid.innerHTML = '<div class="skeleton" style="height:220px;border-radius:18px;"></div>'.repeat(4);
  }

  const refreshBtn = document.getElementById('btnRefreshStock');
  if (refreshBtn && forceRefresh) {
    refreshBtn.classList.add('rotating');
  }

  const outletCode = State.outlet ? State.outlet.code : '';
  const url = `/api/menu.php?brand=${encodeURIComponent(State.brand)}&outlet=${encodeURIComponent(outletCode)}${forceRefresh ? '&refresh=1' : ''}`;

  try {
    const res = await fetch(url);
    const data = await res.json();

    if (data && data.success && Array.isArray(data.items)) {
      State.menu = data;
      State.categories = data.categories || [];

      // Update Stock Stats Bar
      const totalEl = document.getElementById('statTotalItems');
      const readyEl = document.getElementById('statReadyItems');
      const soldEl = document.getElementById('statSoldOutItems');

      if (data.stats) {
        if (totalEl) totalEl.textContent = data.stats.total;
        if (readyEl) readyEl.textContent = data.stats.available;
        if (soldEl) soldEl.textContent = data.stats.soldOut;
      }

      renderCategoryPills(State.categories);
      renderProductGrid();

      if (forceRefresh) {
        showToast('🟢 Stok outlet berhasil diperbarui secara live!');
      }
    } else {
      if (grid) grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:30px;color:var(--text-muted);">Menu belum tersedia untuk outlet ini.</p>';
    }
  } catch (err) {
    console.error('Error fetching menu:', err);
    if (grid) grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;padding:30px;color:var(--text-muted);">Gagal memuat menu. Silakan coba kembali.</p>';
  } finally {
    if (refreshBtn) {
      setTimeout(() => refreshBtn.classList.remove('rotating'), 600);
    }
  }
}

// Render Categories Bar
function renderCategoryPills(categories) {
  const container = document.getElementById('categoryScroll');
  if (!container) return;

  let html = `<button class="cat-pill ${State.activeCategory === 'all' ? 'active' : ''}" onclick="window.filterCategory('all')">✨ Semua Menu</button>`;

  categories.forEach((cat) => {
    const id = cat.id || cat.name;
    const isActive = State.activeCategory === id;
    html += `<button class="cat-pill ${isActive ? 'active' : ''}" onclick="window.filterCategory('${escapeHtml(id)}')">${escapeHtml(cat.name)}</button>`;
  });

  container.innerHTML = html;
}

window.filterCategory = (catId) => {
  State.activeCategory = catId;
  document.querySelectorAll('.cat-pill').forEach((btn) => {
    btn.classList.toggle('active', btn.getAttribute('onclick')?.includes(`'${catId}'`));
  });
  renderProductGrid();
};

// Render Products with Real-time Stock Badges
function renderProductGrid() {
  const grid = document.getElementById('productGrid');
  if (!grid || !State.menu || !State.menu.items) return;

  let items = State.menu.items;

  // Search filter
  if (State.searchQuery) {
    const q = State.searchQuery.toLowerCase();
    items = items.filter((it) => (it.name || '').toLowerCase().includes(q) || (it.category || '').toLowerCase().includes(q));
  }

  // Category filter
  if (State.activeCategory !== 'all') {
    items = items.filter((it) => {
      const c = (it.category || '').toLowerCase();
      const matchCat = State.categories.find((x) => x.id === State.activeCategory);
      if (matchCat) {
        return c.includes(matchCat.name.toLowerCase()) || (it.group || '').toLowerCase().includes(matchCat.name.toLowerCase());
      }
      return true;
    });
  }

  if (items.length === 0) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px 20px;color:var(--text-muted);">Tidak ada menu yang sesuai kriteria pencarian.</div>';
    return;
  }

  grid.innerHTML = items
    .map((item) => {
      const isSoldOut = Boolean(item.isSoldOut || item.notCarried);
      const isBundle = Boolean(item.isBundle);
      const normalPrice = item.normalPrice || item.outletPrice || item.price;
      const activePrice = item.price;

      const hasDiscount = normalPrice && normalPrice > activePrice;

      return `
      <div class="product-card ${isSoldOut ? 'soldout' : ''}" data-key="${escapeHtml(item.key || item.id)}">
        <div class="prod-img-wrap">
          <img src="${escapeHtml(item.img)}" alt="${escapeHtml(item.name)}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop'" />
          
          <!-- Live Stock Indicator Badge -->
          <span class="stock-badge-tag ${isSoldOut ? 'soldout' : 'ready'}">
            <span>●</span> ${isSoldOut ? 'Habis di Gerai' : 'Stok Ready'}
          </span>

          ${isBundle ? '<span class="bundle-badge-tag">Promo Paket</span>' : ''}
        </div>
        
        <div class="prod-body">
          <span class="prod-category">${escapeHtml(item.category || 'Menu')}</span>
          <h3 class="prod-name" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</h3>
          <p class="prod-desc">${escapeHtml(item.desc || '')}</p>

          <div class="prod-footer">
            <div class="price-wrap">
              ${hasDiscount ? `<span class="price-normal">${formatRp(normalPrice)}</span>` : ''}
              <span class="price-active">${formatRp(activePrice)}</span>
            </div>

            <button type="button" class="btn-add-item" ${isSoldOut ? 'disabled' : ''} onclick='window.openCustomModal(${JSON.stringify(item).replace(/'/g, "&#39;")})'>
              ${isSoldOut ? 'Habis' : '+ Pesan'}
            </button>
          </div>
        </div>
      </div>
    `;
    })
    .join('');
}

// Search Menu Handler
const searchInput = document.getElementById('menuSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', (e) => {
    State.searchQuery = e.target.value.trim();
    renderProductGrid();
  });
}

// Outlet Search Input Handler
const outletSearchInput = document.getElementById('outletSearchInput');
if (outletSearchInput) {
  let debounceTimer;
  outletSearchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      fetchOutlets(e.target.value.trim());
    }, 300);
  });
}

// Drink Customization Modal
window.openCustomModal = (item) => {
  State.activeItemForModal = item;
  State.modalSelectedOptions = {
    size: 'Regular',
    temp: 'Ice',
    ice: 'Normal Ice',
    sugar: 'Normal Sugar',
    note: '',
    qty: 1,
  };

  const nameEl = document.getElementById('modalItemName');
  const imgEl = document.getElementById('modalItemImg');
  const priceEl = document.getElementById('modalItemPrice');
  const qtyEl = document.getElementById('modalQtyDisplay');
  const noteEl = document.getElementById('modalItemNotes');

  if (nameEl) nameEl.textContent = item.name;
  if (imgEl) imgEl.src = item.img || 'https://images.unsplash.com/photo-1541167760496-1628856ab772?q=80&w=400&auto=format&fit=crop';
  if (qtyEl) qtyEl.textContent = '1';
  if (noteEl) noteEl.value = '';

  // Render option chips
  renderOptionChips('modalSizeChips', item.options?.sizes || ['Regular', 'Large'], 'size', updateModalPrice);
  renderOptionChips('modalTempChips', item.options?.temps || ['Ice', 'Hot'], 'temp', updateModalPrice);
  renderOptionChips('modalIceChips', item.options?.ices || ['Normal Ice', 'Less Ice', 'No Ice'], 'ice');
  renderOptionChips('modalSugarChips', item.options?.sugars || ['Normal Sugar', 'Less Sugar', 'No Sugar'], 'sugar');

  updateModalPrice();
  openModal('customItemModal');
};

function renderOptionChips(containerId, options, fieldKey, onChangeCallback) {
  const container = document.getElementById(containerId);
  if (!container) return;

  if (!options || options.length === 0) {
    container.parentElement.style.display = 'none';
    return;
  }
  container.parentElement.style.display = 'block';

  container.innerHTML = options
    .map((opt) => {
      const isActive = State.modalSelectedOptions[fieldKey] === opt;
      return `
      <button type="button" class="option-chip-btn ${isActive ? 'active' : ''}" onclick="window.selectOptionChip('${containerId}', '${fieldKey}', '${escapeHtml(opt)}')">
        ${escapeHtml(opt)}
      </button>
    `;
    })
    .join('');
}

window.selectOptionChip = (containerId, fieldKey, val) => {
  State.modalSelectedOptions[fieldKey] = val;
  const container = document.getElementById(containerId);
  if (container) {
    container.querySelectorAll('.option-chip-btn').forEach((btn) => {
      btn.classList.toggle('active', btn.textContent.trim() === val);
    });
  }
  updateModalPrice();
};

function updateModalPrice() {
  const item = State.activeItemForModal;
  if (!item) return;

  let unitPrice = item.price;
  if (State.modalSelectedOptions.size === 'Large' && item.largePrice) {
    unitPrice = item.largePrice;
  }

  const subtotal = unitPrice * State.modalSelectedOptions.qty;
  const priceEl = document.getElementById('modalItemPrice');
  const actionBtnEl = document.getElementById('modalBtnAddSubtotal');

  if (priceEl) priceEl.textContent = formatRp(unitPrice);
  if (actionBtnEl) actionBtnEl.textContent = `Tambah ke Keranjang • ${formatRp(subtotal)}`;
}

// Customization Qty Stepper
window.changeModalQty = (delta) => {
  let next = State.modalSelectedOptions.qty + delta;
  if (next < 1) next = 1;
  State.modalSelectedOptions.qty = next;
  const qtyEl = document.getElementById('modalQtyDisplay');
  if (qtyEl) qtyEl.textContent = next;
  updateModalPrice();
};

// Add to Cart
window.confirmAddToCart = () => {
  const item = State.activeItemForModal;
  if (!item) return;

  let unitPrice = item.price;
  if (State.modalSelectedOptions.size === 'Large' && item.largePrice) {
    unitPrice = item.largePrice;
  }

  const noteEl = document.getElementById('modalItemNotes');
  const note = noteEl ? noteEl.value.trim() : '';

  const cartItem = {
    cartId: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
    id: item.id || item.key,
    name: item.name,
    size: State.modalSelectedOptions.size,
    temp: State.modalSelectedOptions.temp,
    ice: State.modalSelectedOptions.ice,
    sugar: State.modalSelectedOptions.sugar,
    note: note,
    qty: State.modalSelectedOptions.qty,
    price: unitPrice,
    subtotal: unitPrice * State.modalSelectedOptions.qty,
  };

  State.cart.push(cartItem);
  saveCartToStorage();
  updateCartUI();
  closeModal('customItemModal');
  showToast(`✅ "${item.name}" ditambahkan ke keranjang!`);
};

// Cart Management & LocalStorage
function loadCartFromStorage() {
  const saved = localStorage.getItem(`zf_cart_${State.brand}`);
  if (saved) {
    try {
      State.cart = JSON.parse(saved);
    } catch {
      State.cart = [];
    }
  } else {
    State.cart = [];
  }
  updateCartUI();
}

function saveCartToStorage() {
  localStorage.setItem(`zf_cart_${State.brand}`, JSON.stringify(State.cart));
}

function updateCartUI() {
  const totalQty = State.cart.reduce((sum, it) => sum + it.qty, 0);
  const subtotal = State.cart.reduce((sum, it) => sum + it.subtotal, 0);

  const cartBar = document.getElementById('floatingCartBar');
  const badgeEl = document.getElementById('cartBarBadge');
  const qtyEl = document.getElementById('cartBarQty');
  const totalEl = document.getElementById('cartBarTotal');
  const outletEl = document.getElementById('cartBarOutlet');

  if (totalQty > 0) {
    if (cartBar) cartBar.classList.add('active');
    if (badgeEl) badgeEl.textContent = totalQty;
    if (qtyEl) qtyEl.textContent = `${totalQty} Pesanan`;
    if (totalEl) totalEl.textContent = formatRp(subtotal);
    if (outletEl) outletEl.textContent = State.outlet ? State.outlet.name : 'Outlet Pilihan';
  } else {
    if (cartBar) cartBar.classList.remove('active');
  }

  renderCartModalItems();
}

function renderCartModalItems() {
  const container = document.getElementById('cartItemsList');
  if (!container) return;

  if (State.cart.length === 0) {
    container.innerHTML = '<p style="text-align:center;padding:30px;color:var(--text-muted);font-size:0.85rem;">Keranjang pesananmu masih kosong.</p>';
    updateCartBillSummary(0, 0);
    return;
  }

  const totalQty = State.cart.reduce((sum, it) => sum + it.qty, 0);
  const subtotal = State.cart.reduce((sum, it) => sum + it.subtotal, 0);

  container.innerHTML = State.cart
    .map((item, idx) => {
      const opts = [item.size, item.temp, item.ice, item.sugar].filter(Boolean).join(' • ');

      return `
      <div class="cart-item-card">
        <div class="cart-item-left">
          <h5>${escapeHtml(item.name)}</h5>
          <p class="cart-item-opts">${escapeHtml(opts)}</p>
          ${item.note ? `<p style="font-size:0.7rem;color:#f59e0b;">Catatan: ${escapeHtml(item.note)}</p>` : ''}
          <span class="cart-item-unit-price">${formatRp(item.price)} x ${item.qty}</span>
        </div>
        <div class="cart-item-right">
          <span class="cart-item-subtotal">${formatRp(item.subtotal)}</span>
          <div style="display:flex;align-items:center;gap:6px;">
            <button type="button" class="icon-btn" style="width:26px;height:26px;font-size:12px;" onclick="window.updateCartItemQty(${idx}, -1)">-</button>
            <span style="font-size:0.85rem;font-weight:800;">${item.qty}</span>
            <button type="button" class="icon-btn" style="width:26px;height:26px;font-size:12px;" onclick="window.updateCartItemQty(${idx}, 1)">+</button>
          </div>
        </div>
      </div>
    `;
    })
    .join('');

  updateCartBillSummary(subtotal, totalQty);
}

window.updateCartItemQty = (idx, delta) => {
  if (!State.cart[idx]) return;
  State.cart[idx].qty += delta;
  if (State.cart[idx].qty <= 0) {
    State.cart.splice(idx, 1);
  } else {
    State.cart[idx].subtotal = State.cart[idx].qty * State.cart[idx].price;
  }
  saveCartToStorage();
  updateCartUI();
};

function updateCartBillSummary(subtotal, totalQty) {
  const singleItemFee = totalQty === 1 ? State.fees.singleItemFee : 0;
  const plasticBagFee = State.plasticBagSelected ? State.fees.plasticBagFee : 0;
  const total = subtotal + singleItemFee + plasticBagFee;

  const subtotalEl = document.getElementById('billSubtotal');
  const singleFeeEl = document.getElementById('billSingleItemFee');
  const plasticFeeEl = document.getElementById('billPlasticFee');
  const totalEl = document.getElementById('billTotal');

  if (subtotalEl) subtotalEl.textContent = formatRp(subtotal);
  if (singleFeeEl) singleFeeEl.textContent = singleItemFee > 0 ? `+${formatRp(singleItemFee)}` : 'Gratis (2+ pcs)';
  if (plasticFeeEl) plasticFeeEl.textContent = plasticBagFee > 0 ? `+${formatRp(plasticBagFee)}` : 'Rp 0';
  if (totalEl) totalEl.textContent = formatRp(total);
}

// Plastic Bag Toggle
const plasticCheckbox = document.getElementById('plasticBagCheckbox');
if (plasticCheckbox) {
  plasticCheckbox.addEventListener('change', (e) => {
    State.plasticBagSelected = e.target.checked;
    const subtotal = State.cart.reduce((sum, it) => sum + it.subtotal, 0);
    const totalQty = State.cart.reduce((sum, it) => sum + it.qty, 0);
    updateCartBillSummary(subtotal, totalQty);
  });
}

// Checkout to WhatsApp
window.proceedCheckout = async () => {
  if (State.cart.length === 0) {
    showToast('Keranjang pesanan masih kosong.');
    return;
  }

  const nameInput = document.getElementById('custNameInput');
  const phoneInput = document.getElementById('custPhoneInput');
  const pickupSelect = document.getElementById('pickupTimeSelect');
  const notesInput = document.getElementById('orderNotesInput');

  const customerName = nameInput ? nameInput.value.trim() : '';
  if (!customerName) {
    showToast('Harap masukkan nama pemesan.');
    if (nameInput) nameInput.focus();
    return;
  }

  const customerPhone = phoneInput ? phoneInput.value.trim() : '';
  const pickupTime = pickupSelect ? pickupSelect.value : 'Sekarang';
  const notes = notesInput ? notesInput.value.trim() : '';

  const subtotal = State.cart.reduce((sum, it) => sum + it.subtotal, 0);
  const totalQty = State.cart.reduce((sum, it) => sum + it.qty, 0);

  const payload = {
    brand: State.brand,
    outletCode: State.outlet ? State.outlet.code : '',
    outletName: State.outlet ? State.outlet.name : 'Outlet',
    customerName: customerName,
    customerPhone: customerPhone,
    pickupTime: pickupTime,
    notes: notes,
    hasSingleItemFee: totalQty === 1,
    plasticBag: State.plasticBagSelected,
    subtotal: subtotal,
    items: State.cart,
  };

  showToast('Memformat pesanan untuk WhatsApp...');

  try {
    const res = await fetch('/api/order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (data && data.success && data.waUrl) {
      // Save last order code to local storage for tracking
      localStorage.setItem('zf_last_order', data.orderCode);

      // Clear cart
      State.cart = [];
      saveCartToStorage();
      updateCartUI();
      closeModal('cartModal');

      // Redirect directly to WhatsApp
      window.location.href = data.waUrl;
    } else {
      showToast(data.message || 'Gagal memproses pesanan.');
    }
  } catch (err) {
    console.error('Checkout error:', err);
    showToast('Terjadi kesalahan jaringan.');
  }
};

// Order Tracking Handler
window.trackOrder = async () => {
  const codeInput = document.getElementById('trackCodeInput');
  const code = codeInput ? codeInput.value.trim() : '';

  if (!code) {
    showToast('Masukkan kode pesanan terlebih dahulu.');
    return;
  }

  try {
    const res = await fetch(`/api/track.php?code=${encodeURIComponent(code)}`);
    const data = await res.json();

    const resultBox = document.getElementById('trackResultBox');
    if (!resultBox) return;

    if (data && data.success) {
      resultBox.style.display = 'block';
      resultBox.innerHTML = `
        <div style="background-color:var(--bg-subtle);border-radius:14px;padding:14px;border:1px solid var(--border-color);">
          <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <b style="font-size:0.95rem;color:var(--text-main);">${escapeHtml(data.orderCode)}</b>
            <span class="badge-outlet-status open">Status: ${escapeHtml(data.title)}</span>
          </div>
          <p style="font-size:0.75rem;color:var(--text-muted);margin-bottom:12px;">${escapeHtml(data.desc)}</p>

          <div class="track-stepper">
            <div class="track-step-item ${data.step >= 1 ? 'active' : ''}">
              <div class="track-step-dot">1</div>
              <h5>Pesanan Diterima</h5>
              <p>Format order telah diterima admin.</p>
            </div>
            <div class="track-step-item ${data.step >= 2 ? 'active' : ''}">
              <div class="track-step-dot">2</div>
              <h5>Diproses Outlet</h5>
              <p>Barista sedang meracik pesanan.</p>
            </div>
            <div class="track-step-item ${data.step >= 3 ? 'active' : ''}">
              <div class="track-step-dot">3</div>
              <h5>Siap Diambil / Diantar</h5>
              <p>Pesanan siap dinikmati.</p>
            </div>
          </div>
        </div>
      `;
    } else {
      resultBox.style.display = 'block';
      resultBox.innerHTML = `<p style="color:#ef4444;font-size:0.8rem;text-align:center;">${escapeHtml(data.message || 'Pesanan tidak ditemukan.')}</p>`;
    }
  } catch (err) {
    console.error('Track error:', err);
  }
};

// Copy WiFi Password
window.copyWifiPassword = () => {
  const passEl = document.getElementById('wifiPassText');
  const text = passEl ? passEl.textContent.trim() : 'kopikenangan';
  navigator.clipboard.writeText(text).then(() => {
    showToast(`Password WiFi "${text}" berhasil disalin! 📋`);
  });
};

// Modal Management
function initModals() {
  document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        backdrop.classList.remove('active');
      }
    });
  });
}

window.openModal = (id) => {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('active');
};

window.closeModal = (id) => {
  const modal = document.getElementById(id);
  if (modal) modal.classList.remove('active');
};

// Global Event Listeners
const btnRefreshStock = document.getElementById('btnRefreshStock');
if (btnRefreshStock) {
  btnRefreshStock.addEventListener('click', () => fetchMenu(true));
}

const btnFindNearMe = document.getElementById('btnFindNearMe');
if (btnFindNearMe) {
  btnFindNearMe.addEventListener('click', findNearMe);
}

// Utility: HTML Escape
function escapeHtml(str) {
  return String(str || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
