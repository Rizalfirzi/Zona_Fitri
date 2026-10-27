
import {
  $, $$, api, avatarText, bundleCollage, catIcon, closeSheet, esc, icons, initTheme, loadConfig, logoHtml, mountInfoSheets, openDrawer, openSheet,
  renderDrawer, shopClosedBanner, showDialog, store, toast, toggleTheme, watchNavScroll,
} from './common.js?v=3aeca02b99';
import { buildWaMessage, cartTotals, formatRp, normalizePhone, optionText, orderCode, roundTo, waLink } from './shared/order.js?v=3aeca02b99';

const SLUG_TO_BRAND = { kopikenangan: 'kopken', kopken: 'kopken', tomoro: 'tomoro', fore: 'fore' };

const BRAND_COPY = {
  kopken: { text: 'Kopi susu gula aren favorit, harga lebih hemat. Stok & harga langsung dari outlet pilihanmu.', perks: ['Stok live', 'Varian lengkap', 'Bundling hemat'] },
  tomoro: { text: '100% Arabica, creamy & harum. Pesan lewat admin, kami yang orderkan buat kamu.', perks: ['Harga hemat', 'Order via WA'] },
  fore: { text: 'Kreasi kopi Nusantara dengan harga lebih hemat dari outlet. Stok mengikuti outlet pilihanmu.', perks: ['Stok live', 'Hemat 20%'] },
};

const S = {
  cfg: null,
  brand: null,
  brandId: '',
  outlet: null,
  menu: null,
  byKey: new Map(),
  cart: [],
  mode: 'single', // 'single' (menu satuan) | 'bundle' (promo bundling)
  activeCat: 'all',
  sheet: null,
  bundle: null,
  pickupMode: 'now',
  pendingItem: null,
  outletResults: [],
  kbIndex: -1,
};

const K = {
  cart: () => `jo_cart_${S.brandId}`,
  outlet: () => `jo_outlet_${S.brandId}`,
  customer: 'jo_customer',
};

init().catch((err) => {
  console.error(err);
  $('#menu').innerHTML = `<div class="menu-empty"><b>Halaman belum bisa dimuat</b>${esc(err.message)}</div>`;
});

async function init() {
  initTheme();
  const pathSlug = location.pathname.replace(/^\/|\/$/g, '');
  S.brandId = SLUG_TO_BRAND[pathSlug] || pathSlug || 'kopken';
  S.cfg = await loadConfig();
  const brands = S.cfg?.brands || [];
  S.brand = brands.find((b) => b.id === S.brandId || b.slug === pathSlug);
  if (!S.brand && brands.length) {
    S.brand = brands[0];
    S.brandId = S.brand.id;
  }
  if (!S.brand) {
    $('#menu').innerHTML = '<div class="menu-empty"><b>Brand tidak tersedia</b>Pilih brand lain dari beranda.<br><a class="btn btn-primary" href="/">Ke beranda</a></div>';
    return;
  }
  applyBrand();
  renderDrawer(S.cfg, S.brandId);
  mountInfoSheets(S.cfg, S.brand);
  wireStatic();
  watchNavScroll();
  S.cart = store.get(K.cart(), []) || [];
  S.outlet = store.get(K.outlet(), null);
  restoreCustomer();
  renderOutletCard();
  updateCartBar();
  await loadMenu();
  handleDeepLink();
}

function applyBrand() {
  const b = S.brand;
  document.title = `${b.name} | ${S.cfg.storeName}`;

  // Set brand bar header elements
  const logoEl = $('#brandHeaderLogo');
  if (logoEl) { logoEl.src = b.logo; logoEl.alt = b.name; }
  const nameEl = $('#brandHeaderName');
  if (nameEl) nameEl.textContent = b.name;
  const navBrand = $('#navBrand');
  if (navBrand) navBrand.innerHTML = logoHtml(S.cfg);

  // Optional elements with safe checks
  $('#drawerBtn')?.replaceChildren();
  $('#themeBtn')?.replaceChildren();
  $('#wifiBtn')?.classList.toggle('hidden', !b.wifi);
  const searchInput = $('#menuSearch');
  if (searchInput) searchInput.placeholder = `Cari menu ${b.name}…`;

  $('#cartBarIcon')?.insertAdjacentHTML('afterbegin', icons.bag);
  const chevron = $('#cartBarChevron');
  if (chevron) chevron.innerHTML = icons.chevron;
  const minus = $('#qtyMinus');
  if (minus) minus.innerHTML = icons.minus;
  const plus = $('#qtyPlus');
  if (plus) plus.innerHTML = icons.plus;
  for (const id of ['bundleClose', 'cartClose', 'wifiClose', 'outletClose', 'catClose']) {
    const el = $(`#${id}`);
    if (el) el.innerHTML = icons.close;
  }
  const sentIcon = $('#sentIcon');
  if (sentIcon) sentIcon.innerHTML = icons.check;
  const wifiIcon = $('#wifiIcon');
  if (wifiIcon) wifiIcon.innerHTML = icons.wifi.replace('width="19" height="19"', 'width="26" height="26"');
  const chkBtn = $('#checkoutBtn');
  if (chkBtn) chkBtn.innerHTML = `${icons.wa} Pesan via WhatsApp`;
  const clBanner = $('#closedBanner');
  if (clBanner) clBanner.innerHTML = shopClosedBanner(S.cfg);
  const sFoot = $('#storeFoot');
  if (sFoot) sFoot.innerHTML = `&copy; ${new Date().getFullYear()} ${esc(S.cfg.storeName)} · Jasa titip order, bukan bagian resmi dari ${esc(b.name)}.`;
  if (!S.cfg.pickupTime) $('#pickupWrap')?.classList.add('hidden');
}

function wireStatic() {
  $('#drawerBtn')?.addEventListener('click', openDrawer);
  $('#themeBtn')?.addEventListener('click', toggleTheme);
  $('#wifiBtn')?.addEventListener('click', openWifi);
  $('#cartBar').addEventListener('click', openCart);
  $('#outletCard')?.addEventListener('click', openOutletSheet);
  // Inline Outlet Picker
  const wrap = $('#outletInputWrap');
  const inlineInput = $('#inlineOutletInput');
  const inlineClear = $('#inlineOutletClear');
  const inlineDropdown = $('#inlineOutletDropdown');
  const chip = $('#inlineOutletChip');
  const chipRemove = $('#inlineOutletChipRemove');
  const osHeader = $('.os-header', $('#outletSection'));

  if (osHeader) {
    osHeader.style.cursor = 'pointer';
    osHeader.addEventListener('click', (e) => {
      if (e.target.closest('#inlineOutletNearMe')) return;
      inlineInput?.focus();
    });
  }

  if (inlineInput) {
    function renderInlineDropdown(isGeo) {
      if (!inlineDropdown) return;
      const q = inlineInput ? inlineInput.value.trim() : '';
      let manualHtml = '';
      if (q) {
        manualHtml = `<div class="od-item od-manual" data-manual="1" style="border-left:3px solid #8F1223;background:#FFF9F9;">
          <div class="od-head">
            <span class="od-name" style="color:#8F1223;">📍 Pakai “${esc(q)}”</span>
            <span class="od-tags"><span class="od-dist" style="background:#FEE2E2;color:#991B1B;font-weight:800;">Manual</span></span>
          </div>
          <div class="od-addr">Outlet tidak ada di daftar? Pakai nama gerai yang kamu ketik ini.</div>
        </div>`;
      }
      if (!S.outletResults.length) {
        if (manualHtml) {
          inlineDropdown.innerHTML = manualHtml;
          inlineDropdown.classList.remove('hidden');
          return;
        }
        inlineDropdown.innerHTML = '<div style="padding:14px;color:#888;font-size:13px;text-align:center;">Tidak ada outlet yang cocok</div>';
        inlineDropdown.classList.remove('hidden');
        return;
      }
      const html = S.outletResults.slice(0, 15).map((o, idx) => {
        const closed = !o.isOpen || !o.canOrder;
        const dist = o.distanceKm != null ? `<span class="od-dist">📍 ${Number(o.distanceKm).toFixed(1)} km</span>` : '';
        return `<div class="od-item" data-idx="${idx}">
          <div class="od-head">
            <span class="od-name">${esc(o.name)}</span>
            <div class="od-tags">
              ${dist}
              ${closed ? '<span class="od-tutup">TUTUP</span>' : ''}
            </div>
          </div>
          <div class="od-type">${esc(o.type || o.category || 'Shop House')}</div>
          <div class="od-addr">${esc(o.address || '')}</div>
          ${closed ? '<div class="od-closed-msg">⛔ Outlet tutup — tidak bisa dipesan</div>' : ''}
        </div>`;
      }).join('');
      inlineDropdown.innerHTML = (isGeo ? '<div class="od-geo-header">⚡ Outlet terdekat dari lokasimu:</div>' : '') + manualHtml + html;
      inlineDropdown.classList.remove('hidden');
    }

    let t;
    inlineInput.addEventListener('input', () => {
      const q = inlineInput.value.trim();
      if (inlineClear) inlineClear.classList.toggle('hidden', !q);
      clearTimeout(t);
      if (!q) {
        if (inlineDropdown) inlineDropdown.classList.add('hidden');
        return;
      }
      t = setTimeout(async () => {
        try {
          const res = await api(`/api/${S.brandId}/outlets?q=${encodeURIComponent(q)}`);
          S.outletResults = res.outlets || [];
          renderInlineDropdown(false);
        } catch (err) {
          if (inlineDropdown) {
            inlineDropdown.innerHTML = `<div style="padding:12px;color:#888;font-size:13px;">Gagal memuat outlet: ${esc(err.message)}</div>`;
            inlineDropdown.classList.remove('hidden');
          }
        }
      }, 250);
    });

    if (inlineClear) {
      inlineClear.addEventListener('click', () => {
        inlineInput.value = '';
        inlineClear.classList.add('hidden');
        if (inlineDropdown) inlineDropdown.classList.add('hidden');
      });
    }

    if (inlineDropdown) {
      inlineDropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.od-item');
        if (!item) return;
        if (item.dataset.manual) {
          const name = inlineInput.value.trim();
          inlineDropdown.classList.add('hidden');
          inlineInput.value = '';
          if (inlineClear) inlineClear.classList.add('hidden');
          selectOutlet({ code: '', name, address: 'Diisi manual', canOrder: true, isOpen: true, statusText: 'Manual', hours: '', manual: true });
          return;
        }
        const o = S.outletResults[Number(item.dataset.idx)];
        if (o) {
          inlineDropdown.classList.add('hidden');
          inlineInput.value = '';
          if (inlineClear) inlineClear.classList.add('hidden');
          selectOutlet(o);
        }
      });
    }

    // Near Me button for inline outlet
    const nearMeBtn = $('#inlineOutletNearMe');
    if (nearMeBtn) {
      nearMeBtn.addEventListener('click', () => {
        if (!navigator.geolocation) {
          toast('Browser tidak mendukung lokasi');
          return;
        }
        nearMeBtn.classList.add('loading');
        const span = nearMeBtn.querySelector('span');
        if (span) span.textContent = 'Mencari...';

        navigator.geolocation.getCurrentPosition(
          async (pos) => {
            nearMeBtn.classList.remove('loading');
            if (span) span.textContent = 'Terdekat';
            const lat = pos.coords.latitude.toFixed(5);
            const lng = pos.coords.longitude.toFixed(5);
            try {
              const res = await api(`/api/${S.brandId}/outlets?lat=${lat}&lng=${lng}`);
              S.outletResults = res.outlets || [];
              if (inlineInput) inlineInput.value = '';
              renderInlineDropdown(true);
            } catch (err) {
              toast('Gagal mencari outlet terdekat: ' + err.message);
            }
          },
          () => {
            nearMeBtn.classList.remove('loading');
            if (span) span.textContent = 'Terdekat';
            toast('Izin lokasi ditolak. Ketik nama outlet saja.');
          },
          { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 }
        );
      });
    }

    // Click outside closes dropdown
    document.addEventListener('click', (e) => {
      if (!e.target.closest('#outletSection')) {
        inlineDropdown?.classList.add('hidden');
      }
    });

    if (chip) {
      chip.addEventListener('click', (e) => {
        if (e.target.closest('#inlineOutletChipRemove')) return;
        inlineInput?.focus();
        if (inlineInput) inlineInput.select();
      });
    }

    if (chipRemove) {
      chipRemove.addEventListener('click', (e) => {
        e.stopPropagation();
        S.outlet = null;
        store.remove(K.outlet());
        renderOutletCard();
        if (inlineInput) {
          inlineInput.value = '';
          inlineInput.focus();
        }
        loadMenu();
        toast('Silakan pilih outlet');
      });
    }
  }

  // Klik floating outlet pill untuk scroll ke outlet search
  const floatPill = $('#outletFloatingPill');
  if (floatPill) {
    floatPill.style.cursor = 'pointer';
    floatPill.addEventListener('click', () => {
      const sec = $('#outletSection');
      if (sec) {
        sec.scrollIntoView({ behavior: 'smooth', block: 'center' });
        inlineInput?.focus();
      }
    });
  }

  // Menu search clear
  const menuSearch = $('#menuSearch');
  const menuSearchClear = $('#menuSearchClear');
  if (menuSearch && menuSearchClear) {
    menuSearch.addEventListener('input', () => {
      menuSearchClear.classList.toggle('hidden', !menuSearch.value);
    });
    menuSearchClear.addEventListener('click', () => {
      menuSearch.value = '';
      menuSearchClear.classList.add('hidden');
      renderMenu();
    });
  }


  // Outlet sheet
  const input = $('#outletInput');
  let t;
  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => searchOutlets(input.value.trim()), 280);
  });
  input.addEventListener('keydown', (e) => {
    const opts = $$('.outlet-opt', $('#outletList'));
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      S.kbIndex = Math.max(0, Math.min(opts.length - 1, S.kbIndex + (e.key === 'ArrowDown' ? 1 : -1)));
      opts.forEach((o, i) => o.classList.toggle('kb', i === S.kbIndex));
      opts[S.kbIndex]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
      e.preventDefault();
      (opts[S.kbIndex] || opts[0])?.click();
    }
  });
  $('#outletGeo')?.addEventListener('click', nearestOutlets);
  $('#outletList')?.addEventListener('click', onOutletClick);

  // Menu search, mode & categories
  $('#menuSearch')?.addEventListener('input', () => renderMenu());
  $('#modeSeg')?.addEventListener('click', (e) => {
    const b = e.target.closest('button[data-mode]');
    if (b && b.dataset.mode !== S.mode) setMode(b.dataset.mode, true);
  });
  $('#catBar')?.addEventListener('click', (e) => {
    const p = e.target.closest('.cat-pill');
    if (p) setCategory(p.dataset.cat);
  });
  $('#catAllBtn')?.addEventListener('click', openCatSheet);
  $('#catList')?.addEventListener('click', (e) => {
    const o = e.target.closest('.cat-opt');
    if (!o) return;
    closeSheet('catSheet');
    if (o.dataset.mode && o.dataset.mode !== S.mode) {
      setMode(o.dataset.mode);
    }
    setCategory(o.dataset.cat);
  });
  $('#menu')?.addEventListener('click', (e) => {
    const el = e.target.closest('[data-key]');
    if (el) openItem(el.dataset.key);
  });

  // Product sheet
  $('#qtyMinus')?.addEventListener('click', () => setQty(S.sheet?.qty - 1));
  $('#qtyPlus')?.addEventListener('click', () => setQty(S.sheet?.qty + 1));
  $('#itemOptions')?.addEventListener('click', (e) => {
    const b = e.target.closest('.opt-pill');
    if (!b || b.disabled) return;
    if (b.dataset.addon) {
      const k = b.dataset.addon;
      if (S.sheet.picked.has(k)) S.sheet.picked.delete(k);
      else S.sheet.picked.add(k);
      renderOptions();
    } else selectOption(b.dataset.g, b.dataset.v);
  });
  $('#itemAdd')?.addEventListener('click', addItemToCart);

  // Bundle sheet
  $('#bundleSlots')?.addEventListener('click', (e) => {
    const s = e.target.closest('.bundle-slot');
    if (s) setBundleSlot(Number(s.dataset.i));
  });
  $('#bundleGrid')?.addEventListener('click', (e) => {
    const c = e.target.closest('.bcand');
    if (c) pickBundleItem(c.dataset.key);
  });
  $('#bundleAdjust')?.addEventListener('click', (e) => {
    const b = e.target.closest('.opt-pill');
    if (b) setBundleChoice(b.dataset.g, b.dataset.v);
    if (e.target.closest('[data-confirm-slot]')) confirmBundleSlot();
  });
  $('#bundleAdd')?.addEventListener('click', addBundleToCart);

  // Cart
  $('#cartList')?.addEventListener('click', onCartClick);
  $('#cartReset')?.addEventListener('click', () => {
    if (!S.cart.length) return;
    S.cart = [];
    saveCart();
    renderCart();
    toast('Keranjang dikosongkan');
  });
  $('#pickupSeg')?.addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    S.pickupMode = b.dataset.mode;
    $$('#pickupSeg button').forEach((x) => x.classList.toggle('active', x === b));
    $('#pickupTimeWrap')?.classList.toggle('hidden', S.pickupMode !== 'custom');
    if (S.pickupMode === 'custom') fillPickupTimes();
  });
  $('#cartAdmins')?.addEventListener('click', (e) => {
    const b = e.target.closest('.cs-admin');
    if (b) $$('#cartAdmins .cs-admin').forEach((x) => x.classList.toggle('active', x === b));
  });
  for (const id of ['custName', 'custPhone']) {
    $(`#${id}`)?.addEventListener('change', saveCustomer);
    $(`#${id}`)?.addEventListener('input', () => setFieldError(id, false));
  }
  $('#checkoutBtn')?.addEventListener('click', checkout);

  // Sticky offsets: menuToolbar height
  const tb = $('#menuToolbar');
  if (tb) {
    const setH = () => {
      document.body.style.setProperty('--toolbar-h', `${tb.offsetHeight}px`);
    };
    new ResizeObserver(setH).observe(tb);
    setH();
  }
}

// ═══════════════ OUTLETS ═══════════════
function openOutletSheet() {
  const input = $('#outletInput');
  openSheet('outletSheet');
  if (!S.outletResults.length) renderOutletHint();
  setTimeout(() => input.focus(), 320);
}

function renderOutletHint() {
  S.outletResults = [];
  $('#outletList').innerHTML = `<div class="outlet-empty">Ketik nama outlet atau daerah,<br>atau pakai lokasi kamu untuk outlet terdekat.</div>`;
}

let searchSeq = 0;
async function searchOutlets(q, coords) {
  const seq = ++searchSeq;
  const box = $('#outletList');
  if (!q && !coords) return renderOutletHint();
  box.innerHTML = Array.from({ length: 4 }, () => '<div class="skel" style="height:62px;margin:12px 0"></div>').join('');
  const params = new URLSearchParams();
  if (q) params.set('q', q);
  if (coords) {
    params.set('lat', coords.lat);
    params.set('lng', coords.lng);
  }
  try {
    const { outlets } = await api(`/api/${S.brandId}/outlets?${params}`);
    if (seq !== searchSeq) return;
    S.outletResults = outlets;
    renderOutletList(q);
  } catch (err) {
    if (seq !== searchSeq) return;
    S.outletResults = [];
    renderOutletList(q, err.message);
  }
}

function fmtDist(km) {
  if (km == null) return '';
  return km < 1 ? `<b>${Math.round(km * 1000)}</b><small>meter</small>` : `<b>${km < 10 ? km.toFixed(1) : Math.round(km)}</b><small>km</small>`;
}

function statusPill(o) {
  const cls = o.manual ? 'pill-warn' : o.canOrder ? 'pill-open' : o.statusText === 'Pesan di Outlet' ? 'pill-warn' : 'pill-close';
  return `<span class="pill ${cls}">${esc(o.manual ? 'Diisi manual' : o.statusText)}</span>`;
}

function renderOutletList(q, error) {
  const box = $('#outletList');
  S.kbIndex = -1;
  const hasDist = S.outletResults.some((o) => o.distanceKm != null);
  const rows = S.outletResults.map(
    (o, i) => `<button class="outlet-opt ${S.outlet?.code && S.outlet.code === o.code ? 'current' : ''}" role="option" data-i="${i}">
      ${hasDist ? `<span class="oo-dist">${fmtDist(o.distanceKm)}</span>` : ''}
      <span class="oo-body"><span class="oo-top"><span class="oo-name">${esc(o.name)}</span>${statusPill(o)}</span>
        <span class="oo-addr">${esc(o.address)}</span>${o.hours ? `<span class="oo-hours">Buka ${esc(o.hours)}</span>` : ''}</span></button>`,
  );
  // Brands without a reliable outlet source let the customer type the outlet name.
  if (q) {
    rows.push(`<button class="outlet-opt outlet-manual" data-manual="1"><span class="oo-body"><span class="oo-name">Pakai “${esc(q)}”</span>
      <span class="oo-addr">Outlet tidak ada di daftar? Pakai nama yang kamu ketik.</span></span></button>`);
  }
  if (S.brandId === 'tomoro' && S.outletResults.length) rows.push('<div class="outlet-note">Data outlet: daftar admin & © OpenStreetMap contributors</div>');
  box.innerHTML = rows.length ? rows.join('') : `<div class="outlet-empty">${error ? esc(error) : 'Outlet tidak ditemukan.<br>Coba kata kunci lain atau pakai lokasi kamu.'}</div>`;
}

function onOutletClick(e) {
  const opt = e.target.closest('.outlet-opt');
  if (!opt) return;
  if (opt.dataset.manual) {
    const name = $('#outletInput').value.trim();
    selectOutlet({ code: '', name, address: 'Diisi manual', canOrder: true, isOpen: true, statusText: 'Manual', hours: '', manual: true });
  } else {
    selectOutlet(S.outletResults[Number(opt.dataset.i)]);
  }
}

function nearestOutlets() {
  if (!navigator.geolocation) return toast('Browser tidak mendukung lokasi');
  const btn = $('#outletGeo');
  btn.classList.add('loading');
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      btn.classList.remove('loading');
      $('#outletInput').value = '';
      searchOutlets('', { lat: pos.coords.latitude.toFixed(5), lng: pos.coords.longitude.toFixed(5) });
    },
    () => {
      btn.classList.remove('loading');
      toast('Izin lokasi ditolak. Ketik nama outlet saja.');
    },
    { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
  );
}

async function selectOutlet(o) {
  if (!o) return;
  S.outlet = {
    code: o.code, name: o.name, address: o.address, isOpen: o.isOpen, canOrder: o.canOrder,
    statusText: o.statusText, hours: o.hours, manual: Boolean(o.manual),
  };
  store.set(K.outlet(), S.outlet);
  closeSheet('outletSheet');
  renderOutletCard();
  if (!o.canOrder) {
    showDialog({
      title: o.statusText === 'Pesan di Outlet' ? 'Outlet hanya terima pesanan di tempat' : 'Outlet sedang tutup',
      html: `<b>${esc(o.name)}</b> belum bisa menerima pesanan saat ini${o.hours ? ` (jam buka ${esc(o.hours)})` : ''}. Pilih outlet lain ya.`,
      icon: icons.store,
    });
  } else {
    toast(`Outlet dipilih: ${o.name}`);
  }
  await loadMenu();
  if (S.pendingItem) {
    const k = S.pendingItem;
    S.pendingItem = null;
    openItem(k);
  }
}

function renderOutletCard() {
  const o = S.outlet;
  const chip = $('#inlineOutletChip');
  const chipName = $('#inlineOutletChipName');
  const wrap = $('#outletInputWrap');
  const inlineInput = $('#inlineOutletInput');
  const floatPill = $('#outletFloatingPill');
  const floatName = $('#outletFloatingName');
  const closedBar = $('#outletClosedBar');

  // Kotak search outlet SELALU terlihat agar pelanggan tidak bingung mencari outlet
  if (wrap) wrap.classList.remove('hidden');

  if (o && o.name) {
    if (chip) chip.classList.remove('hidden');
    if (chipName) chipName.textContent = o.name;
    if (inlineInput) {
      inlineInput.placeholder = `Ganti outlet (saat ini: ${o.name})…`;
    }
    if (floatPill) {
      floatPill.classList.remove('hidden');
      if (floatName) floatName.textContent = o.name;
    }
    if (closedBar) {
      closedBar.classList.toggle('hidden', o.isOpen !== false && o.canOrder !== false);
    }
  } else {
    if (chip) chip.classList.add('hidden');
    if (inlineInput) {
      inlineInput.placeholder = 'Ketik nama kota atau gerai...';
    }
    if (floatPill) floatPill.classList.add('hidden');
    if (closedBar) closedBar.classList.add('hidden');
  }

  const card = $('#outletCard');
  if (card) {
    card.classList.toggle('empty', !o);
    if (!o) {
      card.innerHTML = `<span class="oc-ico">${icons.store}</span>
        <span class="oc-body"><span class="oc-label">📍 Pilih outlet</span><span class="oc-name">Ketik nama kota atau gerai…</span>
        <span class="oc-meta">Harga & stok mengikuti outlet</span></span><span class="oc-change">Cari ${icons.chevron}</span>`;
    } else {
      card.innerHTML = `<span class="oc-ico">${icons.store}</span>
        <span class="oc-body"><span class="oc-label">📍 Pilih outlet</span><span class="oc-name">${esc(o.name)}</span>
        <span class="oc-meta">${statusPill(o)}${o.hours ? `<span>${esc(o.hours)}</span>` : ''}</span></span>
        <span class="oc-change">Ganti ${icons.chevron}</span>`;
    }
  }

  document.dispatchEvent(new CustomEvent('outlet-changed'));
}

// ═══════════════ MENU ═══════════════
async function loadMenu() {
  const menuEl = $('#menu');
  if (!menuEl) return;

  // Pas di awal / belum pilih outlet: list menu dikosongkan dengan petunjuk ramah
  if (!S.outlet) {
    menuEl.innerHTML = '<div class="menu-empty" style="grid-column: 1 / -1; width: 100%;"><span style="font-size:32px;display:block;margin-bottom:8px;">📍</span><b>Pilih outlet terlebih dahulu</b>Ketik nama kota atau gerai pada kotak pencarian di atas, atau klik tombol <b>Terdekat</b> untuk melihat menu dan harga outlet.</div>';
    const catBar = $('#catBar');
    if (catBar) catBar.innerHTML = '';
    const menuSource = $('#menuSource');
    if (menuSource) menuSource.textContent = '';
    return;
  }

  if (!S.menu) menuEl.innerHTML = Array.from({ length: 6 }, () => '<div class="skel skel-row"></div>').join('');
  else menuEl.style.opacity = '.5';
  const code = S.outlet && !S.outlet.manual ? S.outlet.code : '';
  try {
    S.menu = await api(`/api/${S.brandId}/menu${code ? `?outlet=${encodeURIComponent(code)}` : ''}`);
    S.byKey = new Map(S.menu.items.map((i) => [i.key, i]));
    const hasBundles = S.menu.categories.some((c) => c.kind === 'bundle');
    const modeSeg = $('#modeSeg');
    if (modeSeg) modeSeg.classList.toggle('hidden', !hasBundles);
    if (!hasBundles) S.mode = 'single';
    renderCats();
    renderMenu();
    syncCart();
    const menuSource = $('#menuSource');
    if (menuSource) {
      menuSource.textContent = S.menu.live
        ? `Stok & harga live dari outlet ${S.outlet?.name || ''}.`
        : S.menu.preview && S.brand.requireOutlet
          ? 'Pilih outlet untuk melihat stok & harga outlet.'
          : 'Stok diperbarui manual oleh admin.';
    }
  } catch (err) {
    menuEl.innerHTML = `<div class="menu-empty"><b>Menu belum bisa dimuat</b>${esc(err.message)}<br><button class="btn btn-primary" id="retryMenu">Coba lagi</button></div>`;
    $('#retryMenu')?.addEventListener('click', loadMenu);
  } finally {
    menuEl.style.opacity = '';
  }
}

const modeOf = (cat) => (cat.kind === 'bundle' ? 'bundle' : 'single');
const visibleCats = () => (S.menu?.categories || []).filter((c) => modeOf(c) === S.mode);

function setMode(mode, scroll) {
  S.mode = mode;
  S.activeCat = 'all';
  $$('#modeSeg button').forEach((b) => b.classList.toggle('active', b.dataset.mode === mode));
  const singleControls = $('#modeSingleControls');
  if (singleControls) singleControls.classList.toggle('hidden', mode !== 'single');

  renderCats();
  renderMenu();
  if (scroll) {
    const bar = $('#menuToolbar');
    if (bar) {
      const navH = $('.store-brand-bar')?.offsetHeight || 0;
      const top = bar.getBoundingClientRect().top + window.scrollY - navH;
      if (window.scrollY > top) window.scrollTo({ top, behavior: 'smooth' });
    }
  }
}

function renderCats() {
  const catBar = $('#catBar');
  if (!catBar) return;
  if (!S.outlet || !S.menu) {
    catBar.innerHTML = '';
    return;
  }
  const cats = visibleCats();
  if (!cats.length) {
    catBar.innerHTML = '';
    return;
  }
  if (!S.activeCat) S.activeCat = 'all';
  if (S.activeCat !== 'all' && !cats.some((c) => c.id === S.activeCat)) {
    S.activeCat = 'all';
  }
  const allPill = `<button class="cat-pill ${S.activeCat === 'all' ? 'active' : ''}" role="tab" data-cat="all"><span>Semua</span></button>`;
  const pills = cats.map(
    (c) => `<button class="cat-pill ${S.activeCat === c.id ? 'active' : ''}" role="tab" data-cat="${esc(c.id)}"><span>${esc(c.name)}</span></button>`
  );
  catBar.innerHTML = [allPill, ...pills].join('');
}

function openCatSheet() {
  if (!S.menu) return;
  const groups = [
    ['single', 'Menu satuan'],
    ['bundle', 'Promo bundling'],
  ];
  const active = S.activeCat || 'all';
  $('#catList').innerHTML = groups
    .map(([mode, label]) => {
      const cats = S.menu.categories.filter((c) => modeOf(c) === mode);
      if (!cats.length) return '';
      const allBtn = `<button class="cat-opt ${active === 'all' && S.mode === mode ? 'active' : ''}" data-cat="all" data-mode="${mode}"><span class="ico">✨</span><span class="n">Semua Menu</span><small>${cats.reduce((sum, c) => sum + c.items.length, 0)} menu</small></button>`;
      return `<div class="cat-group-label eyebrow">${label}</div>${allBtn}${cats
        .map((c) => `<button class="cat-opt ${c.id === active ? 'active' : ''}" data-cat="${esc(c.id)}" data-mode="${mode}"><span class="ico">${catIcon(c.name)}</span><span class="n">${esc(c.name)}</span><small>${c.items.length} menu</small></button>`)
        .join('')}`;
    })
    .join('');
  openSheet('catSheet');
}

function setCategory(catId) {
  S.activeCat = catId || 'all';
  setActivePill(S.activeCat);
  renderMenu();
  const bar = $('#menuToolbar');
  if (bar) {
    const navH = $('.store-brand-bar')?.offsetHeight || 0;
    const top = bar.getBoundingClientRect().top + window.scrollY - navH;
    if (window.scrollY > top) window.scrollTo({ top, behavior: 'smooth' });
  }
}

function renderMenu() {
  const menuEl = $('#menu');
  if (!menuEl) return;
  if (!S.outlet || !S.menu) {
    menuEl.innerHTML = '';
    return;
  }
  const q = $('#menuSearch')?.value?.trim()?.toLowerCase() || '';
  // While searching, look through everything (both modes) so nothing is hidden.
  let cats = q ? S.menu.categories : visibleCats();
  if (!q && S.activeCat && S.activeCat !== 'all') {
    cats = cats.filter((c) => c.id === S.activeCat);
  }
  const html = [];
  for (const cat of cats) {
    const items = cat.items.map((k) => S.byKey.get(k)).filter((i) => i && (!q || i.name.toLowerCase().includes(q)));
    if (!items.length) continue;
    const isGrid = cat.kind === 'bundle' || cat.kind === 'custom';
    html.push(`<section class="menu-section" id="sec-${esc(cat.id)}" data-cat="${esc(cat.id)}" aria-labelledby="h-${esc(cat.id)}">
      <div class="section-head"><h2 class="section-title" id="h-${esc(cat.id)}">${esc(cat.name)}</h2><span class="section-count">${items.length} menu</span></div>
      ${isGrid ? `<div class="grid">${items.map((it, i) => gridCard(it, i)).join('')}</div>` : items.map(rowCard).join('')}</section>`);
  }
  menuEl.innerHTML = html.length
    ? html.join('')
    : `<div class="menu-empty"><b>Menu tidak ditemukan</b>${q ? `Tidak ada menu dengan kata “${esc(q)}”.` : 'Belum ada menu di kategori ini.'}</div>`;
}

function setActivePill(cat) {
  const pills = $$('.cat-pill');
  pills.forEach((p) => p.classList.toggle('active', p.dataset.cat === cat));
  const act = pills.find((p) => p.dataset.cat === cat);
  if (act) {
    const bar = $('#catBar');
    if (bar) bar.scrollTo({ left: act.offsetLeft - bar.offsetLeft - 12, behavior: 'smooth' });
  }
}

function thumb(item, cls = 'thumb') {
  const ph = `<img class="ph-logo" src="${esc(S.brand.logo)}" alt="">`;
  return `<div class="${cls}">${item.img ? `<img src="${esc(item.img)}" alt="${esc(item.name)}" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='${esc(S.brand.logo)}';this.classList.add('ph-logo');">` : ph}</div>`;
}

function priceHtml(item) {
  const save = item.normalPrice && item.normalPrice > item.price ? Math.round((1 - item.price / item.normalPrice) * 100) : 0;
  const badge = item.discountLabel ? `Diskon ${item.discountLabel.replace(/^-/, '')}` : save >= 5 ? `-${save}%` : '';
  return `<span class="price">${formatRp(item.price)}</span>${item.normalPrice ? `<span class="price-normal">${formatRp(item.normalPrice)}</span>` : ''}${badge ? `<span class="save-badge ${item.discountLabel ? 'disc' : ''}">${esc(badge)}</span>` : ''}`;
}

function rowCard(item) {
  const save = item.normalPrice && item.normalPrice > item.price ? Math.round((1 - item.price / item.normalPrice) * 100) : 0;
  const badge = item.discountLabel ? `Diskon ${item.discountLabel.replace(/^-/, '')}` : save >= 5 ? `-${save}%` : '';
  const tagHtml = item.isSoldOut
    ? '<span class="badge badge-sold">Tidak tersedia</span>'
    : (item.tag ? `<span class="badge">${esc(item.tag)}</span>` : '<span class="badge badge-new">NEW</span>');

  return `<button class="row ${item.isSoldOut ? 'sold' : ''}" data-key="${esc(item.key)}" aria-label="${esc(item.name)}, ${formatRp(item.price)}${item.isSoldOut ? ', tidak tersedia' : ''}">
    ${thumb(item)}
    <div class="row-tags">${tagHtml}</div>
    <div class="row-body">
      <div class="row-name">${esc(item.name)}</div>
      ${item.desc ? `<div class="row-desc">${esc(item.desc)}</div>` : ''}
      <div class="row-price">
        ${item.normalPrice && item.normalPrice > item.price ? `<span class="price-normal">${formatRp(item.normalPrice)}</span>` : ''}
        <div class="row-price-bottom">
          <span class="price">${formatRp(item.price)}</span>
          ${badge && !item.isSoldOut ? `<span class="save-badge">${esc(badge)}</span>` : ''}
        </div>
      </div>
    </div>
    ${item.isSoldOut ? '' : `<span class="add-btn" aria-hidden="true">${icons.plus}</span>`}
  </button>`;
}

function gridCard(item, i) {
  const art = item.isBundle && !item.img
    ? bundleCollage(item)
    : item.img
      ? `<img src="${esc(item.img)}" alt="${esc(item.name)}" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='${esc(S.brand.logo)}';this.classList.add('ph-logo');">`
      : `<img class="ph-logo" src="${esc(S.brand.logo)}" alt="">`;
  const meta = item.isBundle ? item.desc || item.badge || `${item.slots} menu ${item.size}` : item.desc;
  const tagHtml = item.isSoldOut
    ? '<span class="badge badge-sold">Tidak tersedia</span>'
    : (item.tag ? `<span class="badge">${esc(item.tag)}</span>` : item.isBundle && item.badge ? `<span class="badge">${esc(item.badge)}</span>` : '');

  return `<button class="gcard ${item.isBundle ? 'bundle-gcard' : ''} ${item.isSoldOut ? 'sold' : ''}" data-key="${esc(item.key)}">
    <div class="gcard-art">${art}${tagHtml}</div>
    <div class="gcard-body"><div class="gcard-name">${esc(item.name)}</div>${meta ? `<div class="gcard-meta">${esc(meta)}</div>` : ''}
      <div class="gcard-foot">${item.isBundle && (item.priceVaries || item.fromPrice > item.price)
        ? `<span class="bundle-from">${item.priceVaries ? '<small>mulai</small> ' : ''}<span class="price">${formatRp(item.fromPrice || item.price)}</span></span>`
        : `<span class="price">${formatRp(item.isBundle ? item.fromPrice ?? item.price : item.price)}</span>`}${item.isSoldOut ? '' : `<span class="add-btn" aria-hidden="true">${icons.plus}</span>`}</div></div>
  </button>`;
}

function handleDeepLink() {
  const p = new URLSearchParams(location.search);
  const key = p.get('item');
  const item = key && S.byKey.get(key);
  if (item) {
    if (item.isBundle && S.mode !== 'bundle') setMode('bundle');
    if (S.brand.requireOutlet && !S.outlet) {
      S.pendingItem = key;
      openOutletSheet();
    } else openItem(key);
  } else if (p.has('outlet') && !S.outlet) openOutletSheet();
  else if (p.has('cart') && S.cart.length) openCart();
}

// ═══════════════ PRODUCT SHEET ═══════════════
function needOutlet(itemKey) {
  if (!S.brand.requireOutlet || S.outlet) return false;
  S.pendingItem = itemKey;
  const card = $('#outletCard');
  card.classList.remove('attention');
  void card.offsetWidth;
  card.classList.add('attention');
  toast(`Pilih outlet ${S.brand.name} dulu ya`);
  openOutletSheet();
  return true;
}

function openItem(key, editIndex = null) {
  const item = S.byKey.get(key);
  if (!item) return;
  if (editIndex == null && needOutlet(key)) return;
  if (item.isSoldOut && editIndex == null) {
    showDialog(
      item.notCarried
        ? { title: 'Tidak tersedia di outlet ini', html: `<b>${esc(item.name)}</b> tidak dijual di <b>${esc(S.outlet?.name || 'outlet ini')}</b>. Coba ganti outlet lain ya.`, icon: icons.store }
        : { title: 'Stok habis', html: `Maaf, <b>${esc(item.name)}</b> sedang habis di outlet ini. Coba menu lain atau ganti outlet.`, icon: icons.soldout },
    );
    return;
  }
  if (item.isBundle) return openBundle(item, editIndex);

  const line = editIndex != null ? S.cart[editIndex] : null;
  S.sheet = {
    item, groups: staticGroups(item), prices: null, normals: null, sel: {}, qty: line?.qty || 1, editIndex, loading: false,
    addons: extrasGroup(item), comboAddons: null, picked: new Set(),
  };
  for (const g of S.sheet.groups) S.sheet.sel[g.id] = g.default;
  if (line) restoreSelection(line.choices);

  $('#itemHero').classList.toggle('show', Boolean(item.img));
  $('#itemHero').innerHTML = item.img ? `<img src="${esc(item.img)}" alt="${esc(item.name)}" referrerpolicy="no-referrer">` : '';
  $('#itemCat').textContent = item.category || '';
  $('#itemName').textContent = item.name;
  $('#itemDesc').textContent = item.desc || '';
  $('#itemNormal').textContent = item.normalPrice ? formatRp(item.normalPrice) : '';
  $('#itemNote').value = line?.note || '';
  $('#qtyVal').textContent = S.sheet.qty;
  renderOptions();
  openSheet('itemSheet');

  // Exact variants and stock straight from the outlet (Kopi Kenangan official API).
  if (item.code && S.outlet?.code && !S.outlet.manual && S.brandId === 'kopken') loadItemDetail(item, line);
}

/** Shop-made add-ons ("Extra es batu", …) for drinks, as a multi-select group. */
function extrasGroup(item, extras = S.menu?.extras || []) {
  if (item.isFood || !extras.length) return [];
  return [{ id: 'extras', name: 'Tambahan', values: extras.map((x) => ({ id: x.id, text: x.text, price: x.price })) }];
}

function staticGroups(item) {
  const o = item.options || {};
  const groups = [];
  if (o.sizes?.length) {
    groups.push({ id: 'size', key: 'size', name: 'Ukuran', values: o.sizes.map((v) => ({ id: v, text: v })), default: o.sizes[0], impact: true });
  }
  if (o.temps?.length) groups.push({ id: 'temp', key: 'temp', name: 'Suhu', values: o.temps.map((v) => ({ id: v, text: v })), default: o.temps[0] });
  if (o.ices?.length) groups.push({ id: 'ice', key: 'ice', name: 'Level es', values: o.ices.map((v) => ({ id: v, text: v })), default: o.ices[0], hideWhen: { temp: 'Hot' } });
  if (o.sugars?.length) groups.push({ id: 'sugar', key: 'sugar', name: 'Level gula', values: o.sugars.map((v) => ({ id: v, text: v })), default: o.sugars[0] });
  return groups;
}

async function loadItemDetail(item, line) {
  const sheet = S.sheet;
  sheet.loading = true;
  renderOptions();
  try {
    const d = await api(`/api/kopken/item?outlet=${encodeURIComponent(S.outlet.code)}&code=${encodeURIComponent(item.code)}&key=${encodeURIComponent(item.key)}`);
    if (S.sheet !== sheet) return;
    if (d.isSoldOut) {
      closeSheet('itemSheet');
      item.isSoldOut = true;
      renderMenu();
      showDialog({ title: 'Stok habis', html: `Maaf, <b>${esc(item.name)}</b> sedang habis di outlet ini.`, icon: icons.soldout });
      return;
    }
    if (d.groups?.length && d.prices && Object.keys(d.prices).length) {
      sheet.groups = d.groups;
      sheet.prices = d.prices;
      sheet.normals = d.normals || null;
      sheet.sel = {};
      for (const g of d.groups) sheet.sel[g.id] = g.default;
      sheet.addons = [...(d.addons || []), ...extrasGroup(item, d.extras || S.menu?.extras || [])];
      sheet.comboAddons = d.comboAddons && Object.keys(d.comboAddons).length ? d.comboAddons : null;
      sheet.picked = new Set();
      if (line) restoreSelection(line.choices);
      ensureValidCombo();
    }
    if (d.desc && !item.desc) $('#itemDesc').textContent = d.desc;
  } catch {
    /* keep the static options */
  } finally {
    if (S.sheet === sheet) {
      sheet.loading = false;
      renderOptions();
    }
  }
}

function restoreSelection(choices = []) {
  for (const c of choices || []) {
    const g = S.sheet.groups.find((x) => x.name === c.group);
    const v = g?.values.find((x) => x.text === c.value);
    if (g && v) S.sheet.sel[g.id] = v.id;
    const a = S.sheet.addons.find((x) => x.name === c.group);
    const av = a?.values.find((x) => `+${x.text}` === c.value);
    if (a && av) S.sheet.picked.add(`${a.id}:${av.id}`);
  }
}

/** Add-on values the current variant allows (official add-ons depend on the combination). */
function availableAddons() {
  const sh = S.sheet;
  const allowed = sh.comboAddons && sh.prices ? new Set(sh.comboAddons[comboKey(sh.sel)] || []) : null;
  return sh.addons
    .map((a) => ({ ...a, values: a.id === 'extras' || !allowed ? a.values : a.values.filter((v) => allowed.has(v.id)) }))
    .filter((a) => a.values.length);
}

function addonTotal() {
  let t = 0;
  for (const a of availableAddons()) for (const v of a.values) if (S.sheet.picked.has(`${a.id}:${v.id}`)) t += v.price;
  return t;
}

const impactGroups = () => S.sheet.groups.filter((g) => g.impact && S.sheet.prices);
const comboKey = (sel) => impactGroups().map((g) => sel[g.id]).join('|');

/** Price of the chosen variant, without add-ons. */
function isLargeSelected(sel = S.sheet?.sel) {
  if (!sel || !S.sheet?.groups) return false;
  for (const g of S.sheet.groups) {
    const valId = sel[g.id];
    if (valId == null) continue;
    const v = g.values?.find((x) => String(x.id) === String(valId));
    const txt = (v?.text || String(valId)).toLowerCase();
    if (txt.includes('large') || txt === 'l') return true;
  }
  return false;
}

/** Price of the chosen variant, without add-ons. */
function unitPrice(sel = S.sheet.sel) {
  const { item, prices } = S.sheet;
  const isLarge = isLargeSelected(sel);
  if (prices) {
    const p = prices[comboKey(sel)];
    if (p != null) {
      if (isLarge && item.price && p <= item.price) {
        return (item.largePrice || (item.price + 5000));
      }
      return p;
    }
  }
  if (isLarge) {
    if (item.largePrice) return item.largePrice;
    return (item.price || 0) + 5000;
  }
  return item.price;
}

/** Pre-discount price of the chosen variant (for the strike-through), or null. */
function normalPrice(sel = S.sheet.sel) {
  const { item, normals, prices } = S.sheet;
  if (prices) return normals ? normals[comboKey(sel)] ?? null : null;
  if (sel.size === 'Large' && item.largePrice) return item.largeNormal || null;
  return item.normalPrice || null;
}

function ensureValidCombo(changedGroup) {
  const { prices, sel } = S.sheet;
  if (!prices || prices[comboKey(sel)] != null) return;
  // Pick the available combination closest to the current selection, keeping the
  // value the customer just chose.
  const ids = impactGroups().map((g) => g.id);
  let best = null;
  let bestScore = -1;
  for (const k of Object.keys(prices)) {
    const vals = k.split('|');
    if (changedGroup && vals[ids.indexOf(changedGroup)] !== sel[changedGroup]) continue;
    const score = vals.reduce((a, v, i) => a + (sel[ids[i]] === v ? 1 : 0), 0);
    if (score > bestScore) [best, bestScore] = [vals, score];
  }
  if (best) ids.forEach((id, i) => (sel[id] = best[i]));
}

function selectOption(gid, vid) {
  S.sheet.sel[gid] = vid;
  ensureValidCombo(gid);
  renderOptions();
}

function isHidden(g, sel) {
  return Object.entries(g.hideWhen || {}).some(([gid, vid]) => sel[gid] === vid);
}

const GROUP_LABELS = { Temperature: 'Suhu', Size: 'Ukuran', Beans: 'Biji kopi', 'Milk Option': 'Susu', 'Sugar Level': 'Level gula', 'Ice Level': 'Level es' };

function renderOptions() {
  const sh = S.sheet;
  const base = unitPrice();
  const html = sh.groups
    .filter((g) => !isHidden(g, sh.sel))
    .map((g) => {
      const pills = g.values.map((v) => {
        let extra = '';
        let disabled = false;
        if (g.impact) {
          const p = unitPrice({ ...sh.sel, [g.id]: v.id });
          if (sh.prices && p == null) {
            // Only reachable through other options; still selectable (auto-adjusts).
            disabled = !Object.keys(sh.prices).some((k) => k.split('|')[impactGroups().findIndex((x) => x.id === g.id)] === v.id);
          } else if (p != null && base != null && p !== base && sh.sel[g.id] !== v.id) {
            extra = `<small>${p > base ? '+' : '−'}${formatRp(Math.abs(p - base))}</small>`;
          }
        }
        return `<button class="opt-pill ${sh.sel[g.id] === v.id ? 'active' : ''}" data-g="${esc(g.id)}" data-v="${esc(v.id)}" aria-pressed="${sh.sel[g.id] === v.id}" ${disabled ? 'disabled' : ''}>${esc(v.text)}${extra}</button>`;
      });
      return `<div class="opt-group" role="group" aria-label="${esc(GROUP_LABELS[g.name] || g.name)}"><div class="opt-title">${esc(GROUP_LABELS[g.name] || g.name)}</div><div class="opt-choices">${pills.join('')}</div></div>`;
    })
    .join('');
  const addonHtml = availableAddons()
    .map(
      (a) => `<div class="opt-group" role="group" aria-label="${esc(a.name)}"><div class="opt-title">${esc(a.name)} <span class="opt-hint">opsional, boleh lebih dari satu</span></div><div class="opt-choices">${a.values
        .map((v) => {
          const on = sh.picked.has(`${a.id}:${v.id}`);
          return `<button class="opt-pill addon ${on ? 'active' : ''}" data-addon="${esc(`${a.id}:${v.id}`)}" aria-pressed="${on}">${esc(v.text)}<small>+${formatRp(v.price)}</small></button>`;
        })
        .join('')}</div></div>`,
    )
    .join('');
  $('#itemOptions').innerHTML = html + addonHtml + (sh.loading ? `<div class="opt-loading"><span class="spinner"></span> Cek varian & stok di outlet…</div>` : '');
  const extra = addonTotal();
  const price = (base ?? sh.item.price) + extra;
  const normal = normalPrice();
  $('#itemPrice').textContent = formatRp(price);
  $('#itemNormal').textContent = normal && normal > (base ?? 0) ? formatRp(normal + extra) : '';
  $('#itemAdd').textContent = `${sh.editIndex != null ? 'Simpan' : 'Tambah'} · ${formatRp(price * sh.qty)}`;
  $('#itemAdd').disabled = base == null;
}

function setQty(n) {
  S.sheet.qty = Math.max(1, Math.min(20, n));
  $('#qtyVal').textContent = S.sheet.qty;
  renderOptions();
}

function currentChoices(groups, sel) {
  return groups
    .filter((g) => !isHidden(g, sel))
    .map((g) => {
      const v = g.values.find((x) => String(x.id) === String(sel[g.id]));
      return { group: g.name, value: v ? v.text : (sel[g.id] || '') };
    });
}

function pickedChoices() {
  const out = [];
  for (const a of availableAddons()) for (const v of a.values) if (S.sheet.picked.has(`${a.id}:${v.id}`)) out.push({ group: a.name, value: `+${v.text}` });
  return out;
}

function addItemToCart() {
  const sh = S.sheet;
  let variant = unitPrice();
  if (variant == null) return;
  const isLarge = isLargeSelected(sh.sel);
  if (isLarge && sh.item.price && variant <= sh.item.price) {
    variant = sh.item.largePrice || (sh.item.price + 5000);
  }
  const price = variant + addonTotal();
  const line = {
    uid: sh.editIndex != null ? S.cart[sh.editIndex].uid : uid(),
    key: sh.item.key,
    name: sh.item.name,
    img: sh.item.img,
    qty: sh.qty,
    price,
    basePrice: sh.item.price,
    isBundle: false,
    isSpecial: sh.item.isSpecial,
    choices: [...currentChoices(sh.groups, sh.sel), ...pickedChoices()],
    note: $('#itemNote').value.trim(),
  };
  if (sh.editIndex != null) S.cart[sh.editIndex] = line;
  else {
    const same = S.cart.find((l) => !l.isBundle && l.key === line.key && optionText(l.choices) === optionText(line.choices) && l.note === line.note);
    if (same) same.qty = Math.min(50, same.qty + line.qty);
    else S.cart.push(line);
  }
  saveCart(true);
  closeSheet('itemSheet');
  if (sh.editIndex != null) renderCart();
  toast(sh.editIndex != null ? 'Pesanan diperbarui' : `${line.name} masuk keranjang`);
}

// ═══════════════ BUNDLE SHEET ═══════════════
function openBundle(bundle, editIndex = null) {
  const line = editIndex != null ? S.cart[editIndex] : null;
  S.bundle = {
    bundle,
    editIndex,
    active: 0,
    slots: Array.from({ length: bundle.slots }, (_, i) => (line?.selections?.[i] ? { ...line.selections[i] } : null)),
    adjusting: null,
  };
  $('#bundleGroup').textContent = bundle.group || 'Promo bundling';
  $('#bundleName').textContent = bundle.name;
  $('#bundleDesc').textContent = bundle.desc || '';
  if ($('#bundlePrice')) $('#bundlePrice').textContent = '';
  $('#bundleFixed').textContent = bundle.fixedItems?.length ? `Termasuk: ${bundle.fixedItems.join(', ')}` : '';
  $('#bundleNote').value = line?.note || '';
  const firstEmpty = S.bundle.slots.findIndex((s) => !s);
  S.bundle.active = firstEmpty === -1 ? 0 : firstEmpty;
  renderBundle();
  openSheet('bundleSheet');
}

function getCandidatePrice(b, c) {
  if (!b || !c) return 0;
  if (c.bundlePrice != null) return Number(c.bundlePrice);
  if (b.price && b.slots) {
    return Math.round(b.price / b.slots);
  }
  if (b.formula) {
    const outPrice = b.size === 'Large'
      ? Number(c.largeOutletPrice || (c.outletPrice ? c.outletPrice + 6000 : 0) || c.largePrice || c.price || 0)
      : Number(c.outletPrice || c.price || 0);
    const pct = b.formula.percent == null ? 50 : Number(b.formula.percent);
    const add = Number(b.formula.add) || 5000;
    const step = Number(b.formula.round) || 1000;
    const mode = b.formula.roundMode || 'floor';
    return roundTo((outPrice * pct) / 100 + add, step, mode);
  }
  return 0;
}

/** Bundle price at this outlet: exact bundle price + optional add-on beans */
function bundlePrice(B = S.bundle, slots = B?.slots || []) {
  if (!B || !B.bundle) return 0;
  const b = B.bundle;
  const filledCount = slots.filter(Boolean).length;
  let extras = 0;
  for (let i = 0; i < b.slots; i++) {
    const s = slots[i];
    if (s && s.choices) {
      for (const ch of s.choices) {
        const v = String(ch.value || ch).toLowerCase();
        const g = String(ch.group || '').toLowerCase();
        if ((g.includes('bean') || g.includes('biji')) && !v.includes('blend') && !v.includes('default')) {
          extras += 3000;
        }
      }
    }
  }

  if (b.price && !b.formula) {
    if (filledCount === b.slots) return b.price + extras;
    const basePortion = Math.round(b.price / b.slots);
    return (basePortion * filledCount) + extras;
  }

  let totalPrice = 0;
  for (let i = 0; i < b.slots; i++) {
    const s = slots[i];
    if (s) {
      const c = b.candidates?.find((x) => x.key === s.key);
      totalPrice += getCandidatePrice(b, c);
    }
  }
  return totalPrice + extras;
}

function renderBundle() {
  const B = S.bundle;
  const b = B.bundle;
  const filled = B.slots.filter(Boolean).length;
  const totalPrice = bundlePrice();

  if ($('#bundlePrice')) $('#bundlePrice').textContent = '';

  $('#bundleSlots').innerHTML = B.slots
    .map((s, i) => {
      const c = s ? b.candidates?.find((x) => x.key === s.key) : null;
      const candP = c ? getCandidatePrice(b, c) : null;
      let extra = 0;
      if (s?.choices) {
        for (const ch of s.choices) {
          const v = String(ch.value || ch).toLowerCase();
          const g = String(ch.group || '').toLowerCase();
          if ((g.includes('bean') || g.includes('biji')) && !v.includes('blend') && !v.includes('default')) {
            extra += 3000;
          }
        }
      }
      const itemPrice = candP != null ? candP + extra : null;
      return `<button class="bundle-slot ${i === B.active ? 'active' : ''} ${s ? 'filled' : ''}" data-i="${i}">
        <span class="slot-num">${i + 1}</span>
        <span class="slot-txt">
          <span class="slot-name">
            ${esc(s ? s.name : `Menu ${i + 1}`)}
            ${itemPrice ? `<b class="slot-price">(${formatRp(itemPrice)})</b>` : ''}
          </span>
          <span class="slot-opts">${esc(s ? optionText(s.choices) : 'Belum dipilih')}</span>
        </span>
      </button>`;
    })
    .join('');

  $('#bundleGridTitle').textContent = `Pilih menu ke-${B.active + 1}${b.size === 'Large' ? ' (ukuran Large)' : ''}`;

  const picked = B.slots[B.active]?.key;
  $('#bundleGrid').innerHTML = (b.candidates || [])
    .map((c) => {
      const p = getCandidatePrice(b, c);
      const isPicked = picked === c.key;
      const priceHtml = c.isSoldOut
        ? '<span class="bcand-sold">Tidak tersedia</span>'
        : p != null ? `<span class="bcand-price">${formatRp(p)}</span>` : '';

      return `<button class="bcand ${c.isSoldOut ? 'sold' : ''} ${isPicked ? 'picked' : ''}" data-key="${esc(c.key)}" ${c.isSoldOut ? 'disabled' : ''}>
        ${c.img ? `<img src="${esc(c.img)}" alt="" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='${esc(S.brand.logo)}';this.classList.add('ph-logo');">` : `<img class="ph-logo" src="${esc(S.brand.logo)}" alt="">`}
        <span class="bcand-name">${esc(c.name)}</span>
        ${priceHtml}
      </button>`;
    })
    .join('');

  const adj = $('#bundleAdjust');
  if (B.adjusting) {
    const a = B.adjusting;
    const groups = a.groups.filter((g) => !isHidden(g, a.sel));
    adj.innerHTML = `<h4>${esc(a.name)}</h4>${groups
      .map(
        (g) => `<div class="opt-group"><div class="opt-title">${esc(GROUP_LABELS[g.name] || g.name)}</div><div class="opt-choices">${g.values
          .map((v) => `<button class="opt-pill ${a.sel[g.id] === v.id ? 'active' : ''}" data-g="${esc(g.id)}" data-v="${esc(v.id)}">${esc(v.text)}</button>`)
          .join('')}</div></div>`,
      )
      .join('')}<button class="btn btn-primary btn-block btn-sm" style="margin-top:14px;min-height:44px" data-confirm-slot>Pakai pilihan ini</button>`;
    adj.classList.remove('hidden');
  } else {
    adj.classList.add('hidden');
  }

  const totalBar = $('#bundleTotalBar');
  if (totalBar) {
    totalBar.innerHTML = `
      <span class="btb-label">Total (${filled}/${b.slots} menu)</span>
      <b class="btb-price">${filled === b.slots ? formatRp(totalPrice) : (filled > 0 ? `Sementara ${formatRp(totalPrice)}` : 'Rp0')}</b>
    `;
  }

  const addBtn = $('#bundleAdd');
  if (addBtn) {
    addBtn.disabled = filled < b.slots;
    addBtn.textContent = filled < b.slots
      ? `Pilih ${b.slots - filled} menu lagi`
      : `${B.editIndex != null ? 'Simpan' : 'Tambah'} · Total ${formatRp(totalPrice)}`;
  }
}

function setBundleSlot(i) {
  S.bundle.active = i;
  S.bundle.adjusting = null;
  renderBundle();
}

function pickBundleItem(key) {
  const c = S.bundle.bundle.candidates.find((x) => x.key === key);
  if (!c || c.isSoldOut) return;
  const groups = staticGroups({ options: { ...c.options, sizes: [] } });
  const sel = {};
  for (const g of groups) sel[g.id] = g.default;
  S.bundle.adjusting = { key, name: c.name, groups, sel };
  if (!groups.length) return confirmBundleSlot();
  renderBundle();
  $('#bundleAdjust').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function setBundleChoice(gid, vid) {
  S.bundle.adjusting.sel[gid] = vid;
  renderBundle();
}

function confirmBundleSlot() {
  const B = S.bundle;
  const a = B.adjusting;
  const choices = currentChoices(a.groups, a.sel);
  if (B.bundle.size && B.bundle.size !== 'Regular') choices.splice(1, 0, { group: 'Size', value: B.bundle.size });
  B.slots[B.active] = { key: a.key, name: a.name, choices };
  B.adjusting = null;
  const next = B.slots.findIndex((s) => !s);
  if (next !== -1) B.active = next;
  renderBundle();
}

function addBundleToCart() {
  const B = S.bundle;
  if (B.slots.some((s) => !s)) return;
  const line = {
    uid: B.editIndex != null ? S.cart[B.editIndex].uid : uid(),
    key: B.bundle.key,
    name: B.bundle.name,
    img: '',
    qty: B.editIndex != null ? S.cart[B.editIndex].qty : 1,
    price: bundlePrice(),
    basePrice: bundlePrice(),
    isBundle: true,
    isSpecial: false,
    selections: B.slots,
    fixedItems: B.bundle.fixedItems || [],
    note: $('#bundleNote').value.trim(),
  };
  if (B.editIndex != null) S.cart[B.editIndex] = line;
  else S.cart.push(line);
  saveCart(true);
  closeSheet('bundleSheet');
  if (B.editIndex != null) renderCart();
  toast(`${line.name} masuk keranjang`);
}

// ═══════════════ CART ═══════════════
function uid() {
  return Math.random().toString(36).slice(2, 10);
}

function saveCart(bump = false) {
  store.set(K.cart(), S.cart);
  updateCartBar(bump);
}

/** After the outlet (and so the menu) changes, refresh prices and flag sold-out lines. */
function syncCart() {
  let changed = false;
  for (const l of S.cart) {
    const item = S.byKey.get(l.key);
    let sold = !item || item.isSoldOut;
    if (l.isBundle && item) {
      sold = sold || (l.selections || []).some((s) => item.candidates.find((c) => c.key === s.key)?.isSoldOut !== false);
      const price = bundlePrice({ bundle: item }, l.selections || []);
      if (!sold && price !== l.price) {
        l.price = price;
        l.basePrice = price;
        changed = true;
      }
    } else if (item && item.price !== l.basePrice) {
      l.price = Math.max(0, item.price + (l.price - l.basePrice));
      l.basePrice = item.price;
      changed = true;
    }
    if (Boolean(l.soldOut) !== Boolean(sold)) {
      l.soldOut = sold;
      changed = true;
    }
  }
  if (changed) saveCart();
}

function updateCartBar(bump = false) {
  const { qty, total } = cartTotals(S.cart, S.brand.fee);
  const bar = $('#cartBar');
  bar.classList.toggle('show', qty > 0);
  $('#cartBadge').textContent = qty;
  $('#cartBarQty').textContent = `${qty} item`;
  $('#cartBarOutlet').textContent = S.outlet ? `Outlet ${S.outlet.name}` : 'Lihat keranjang';
  $('#cartBarTotal').textContent = formatRp(total);
  if (bump) {
    bar.classList.remove('bump');
    void bar.offsetWidth;
    bar.classList.add('bump');
  }
}

function openCart() {
  renderCart();
  const admins = S.cfg.admins;
  $('#adminWrap').classList.toggle('hidden', admins.length < 2);
  const lastAdmin = store.get('jo_admin', 0);
  $('#cartAdmins').innerHTML = admins
    .map((a, i) => `<button class="cs-admin ${i === (lastAdmin < admins.length ? lastAdmin : 0) ? 'active' : ''}" data-i="${i}"><span class="cs-avatar">${esc(avatarText(a.name))}</span>${esc(a.name)}</button>`)
    .join('');
  openSheet('cartSheet');
}

function renderCart() {
  const o = S.outlet;
  $('#cartOutlet').textContent = o ? `${S.brand.name} · ${o.name}` : S.brand.name;
  const list = $('#cartList');
  if (!S.cart.length) {
    list.innerHTML = `<div class="cart-empty"><b>Keranjang masih kosong</b>Pilih menu favoritmu dulu ya.</div>`;
  } else {
    list.innerHTML = S.cart
      .map((l, i) => {
        const opts = l.isBundle
          ? [...(l.fixedItems || []).map((f) => `+ ${esc(f)}`), ...(l.selections || []).map((s, j) => `${j + 1}. ${esc(s.name)}${s.choices?.length ? ` (${esc(optionText(s.choices))})` : ''}`)].join('<br>')
          : esc(optionText(l.choices));
        const item = S.byKey.get(l.key);
        return `<div class="cart-item ${l.soldOut ? 'sold' : ''}" data-i="${i}">
          ${l.isBundle ? `<div class="thumb"><span class="ph">${esc(String(Math.round(l.price / 1000)))}rb</span></div>` : thumb({ name: l.name, img: l.img || item?.img })}
          <div class="ci-body"><div class="ci-name">${esc(l.name)}</div>
            ${opts ? `<div class="ci-opts">${opts}</div>` : ''}
            ${l.note ? `<div class="ci-opts">Catatan: ${esc(l.note)}</div>` : ''}
            ${l.soldOut ? '<div class="ci-warn">Habis di outlet ini. Hapus dulu ya.</div>' : ''}
            <div class="ci-foot"><span class="price">${formatRp(l.price * l.qty)}</span>
              <div class="ci-actions">
                <button class="mini" data-act="edit" aria-label="Ubah ${esc(l.name)}">${icons.edit}</button>
                <button class="mini" data-act="dec" aria-label="Kurangi">${l.qty > 1 ? icons.minus : icons.trash}</button>
                <span class="q num">${l.qty}</span>
                <button class="mini" data-act="inc" aria-label="Tambah">${icons.plus}</button>
              </div></div></div></div>`;
      })
      .join('');
  }
  const t = cartTotals(S.cart, S.brand.fee);
  if ($('#sumSubtotal')) $('#sumSubtotal').textContent = formatRp(t.subtotal);
  if ($('#sumFee')) $('#sumFee').textContent = '';
  if ($('#sumTotal')) $('#sumTotal').textContent = formatRp(t.total);
  if ($('#sumNote')) $('#sumNote').textContent = '';
  $('#checkoutBtn').disabled = !S.cart.length;
  updateCartBar();
}

function onCartClick(e) {
  const btn = e.target.closest('[data-act]');
  if (!btn) return;
  const i = Number(btn.closest('.cart-item').dataset.i);
  const l = S.cart[i];
  if (!l) return;
  if (btn.dataset.act === 'inc') l.qty = Math.min(50, l.qty + 1);
  else if (btn.dataset.act === 'dec') {
    if (l.qty > 1) l.qty -= 1;
    else S.cart.splice(i, 1);
  } else if (btn.dataset.act === 'edit') {
    if (!S.byKey.has(l.key)) return toast('Menu ini tidak tersedia di outlet ini');
    return openItem(l.key, i);
  }
  saveCart();
  renderCart();
}

function fillPickupTimes() {
  const sel = $('#pickupTime');
  const now = new Date(Date.now() + 7 * 3600 * 1000); // WIB
  let m = now.getUTCHours() * 60 + now.getUTCMinutes() + 20;
  m = Math.ceil(m / 15) * 15;
  const end = 23 * 60 + 45;
  const opts = [];
  for (; m <= end; m += 15) {
    const t = `${String(Math.floor(m / 60)).padStart(2, '0')}.${String(m % 60).padStart(2, '0')}`;
    opts.push(`<option value="${t} WIB">${t} WIB</option>`);
  }
  sel.innerHTML = opts.length ? opts.join('') : '<option value="Besok">Besok (chat admin)</option>';
}

function restoreCustomer() {
  const c = store.get(K.customer, {}) || {};
  const nameEl = $('#custName');
  if (nameEl) nameEl.value = c.name || '';
}

function saveCustomer() {
  const name = $('#custName')?.value?.trim() || '';
  store.set(K.customer, { name });
}

function setFieldError(id, on) {
  $(`#${id}`)?.classList?.toggle('invalid', on);
  $(`#${id}Err`)?.classList?.toggle('hidden', !on);
}

function checkout() {
  if (!S.cart.length) return;
  if (!S.cfg.shop.open) {
    return showDialog({ title: 'Toko sedang tutup', html: esc(S.cfg.shop.reason || 'Belum bisa menerima pesanan saat ini.') });
  }
  if (S.brand.requireOutlet && !S.outlet) {
    closeSheet('cartSheet');
    return needOutlet(null);
  }
  if (S.outlet && !S.outlet.canOrder) {
    return showDialog({ title: 'Outlet sedang tutup', html: `<b>${esc(S.outlet.name)}</b> belum bisa menerima pesanan. Ganti outlet dulu ya.`, icon: icons.store });
  }
  const sold = S.cart.filter((l) => l.soldOut);
  if (sold.length) {
    return showDialog({ title: 'Ada menu yang habis', html: `Hapus dulu menu yang habis: <b>${esc(sold.map((l) => l.name).join(', '))}</b>`, icon: icons.soldout });
  }
  const name = $('#custName')?.value?.trim() || '';
  if (!name) {
    setFieldError('custName', true);
    $('#custName')?.focus();
    return;
  }
  const admins = S.cfg.admins;
  if (!admins.length) return showDialog({ title: 'Admin belum diatur', html: 'Nomor WhatsApp admin belum diisi. Hubungi pemilik toko.' });
  const adminIdx = Number($('#cartAdmins .cs-admin.active')?.dataset.i || 0);
  const admin = admins[adminIdx] || admins[0];
  store.set('jo_admin', adminIdx);
  saveCustomer();

  const totals = cartTotals(S.cart, S.brand.fee);
  const pickup = S.cfg.pickupTime ? (S.pickupMode === 'custom' ? $('#pickupTime')?.value : 'Sekarang') : '';
  const order = {
    storeName: S.cfg.storeName,
    code: orderCode(S.cfg.orderPrefix || 'ZF'),
    brand: S.brandId,
    brandName: S.brand.name,
    outlet: S.outlet ? `${S.outlet.name}${S.outlet.manual ? ' (manual)' : ''}` : '-',
    customerName: name,
    customerPhone: '',
    pickup,
    items: S.cart,
    ...totals,
    note: $('#cartNote')?.value?.trim() || '',
    header: S.cfg.waHeader,
    footer: S.cfg.waFooter,
  };
  const link = waLink(admin.phone, buildWaMessage(order));
  // Open WhatsApp synchronously inside the click so popup blockers allow it.
  const win = window.open(link, '_blank');
  if (win) win.opener = null;
  else location.href = link;

  const payload = {
    code: order.code, brand: S.brandId, outlet: { code: S.outlet?.code || '', name: S.outlet?.name || '' },
    customerName: name, customerPhone: phoneRaw, pickup, note: order.note, admin: admin.name,
    items: S.cart.map((l) => ({ name: l.name, qty: l.qty, price: l.price, choices: l.choices, note: l.note, isBundle: l.isBundle, isSpecial: l.isSpecial, selections: l.selections })),
  };
  fetch('/api/orders', { method: 'POST', headers: { 'content-type': 'application/json' }, body: JSON.stringify(payload), keepalive: true }).catch(() => {});

  store.set('jo_last_order', { code: order.code, link, total: totals.total, at: Date.now() });
  S.cart = [];
  saveCart();
  $('#cartNote').value = '';
  closeSheet('cartSheet');
  $('#sentCode').textContent = order.code;
  $('#sentTotal').textContent = `Total ${formatRp(totals.total)}`;
  $('#sentWaLink').href = link;
  $('#sentWaLink').innerHTML = `${icons.wa} Buka WhatsApp lagi`;
  openSheet('sentModal');
}

// ═══════════════ WI-FI ═══════════════
async function openWifi() {
  if (!S.outlet || S.outlet.manual) {
    return showDialog({ title: 'Pilih outlet dulu', html: 'Pilih outlet Kopi Kenangan dulu untuk lihat password Wi-Fi hari ini.', kind: 'brand', icon: icons.wifi, button: 'Oke' });
  }
  $('#wifiTitle').textContent = S.outlet.name;
  $('#wifiDate').innerHTML = `${icons.clock} ${new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}`;
  $('#wifiBody').innerHTML = '<div style="padding:18px"><span class="spinner"></span></div>';
  openSheet('wifiModal');
  try {
    const { wifi } = await api(`/api/kopken/wifi?outlet=${encodeURIComponent(S.outlet.code)}`);
    if (!wifi) throw new Error('unavailable');
    $('#wifiBody').innerHTML = `
      <div class="wifi-field"><div><small>Nama jaringan</small><strong>${esc(wifi.ssid)}</strong></div></div>
      <div class="wifi-field pass"><div><small>Password hari ini</small><strong>${esc(wifi.password)}</strong></div>
        <button class="copy-btn" id="wifiCopy">${icons.copy} Salin</button></div>`;
    $('#wifiCopy').addEventListener('click', () => {
      navigator.clipboard?.writeText(wifi.password).then(() => toast('Password disalin'), () => toast('Gagal menyalin'));
    });
  } catch {
    $('#wifiBody').innerHTML = `<p class="dialog-text" style="margin:0">Password Wi-Fi outlet ini belum tersedia. Tanyakan ke kasir atau cek struk pembelian ya.</p>`;
  }
}
