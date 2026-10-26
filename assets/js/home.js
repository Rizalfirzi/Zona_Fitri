import {
  $, $$, api, bundleCollage, esc, icons, initTheme, loadConfig, logoHtml, mountInfoSheets, openDrawer, openSheet, renderDrawer, shopClosedBanner, store, toggleTheme, watchNavScroll,
} from './common.js?v=3aeca02b99';
import { cartTotals, formatRp } from './shared/order.js?v=3aeca02b99';

const PERK_ICONS = [icons.tag, icons.pulse, icons.chat];

init().catch((err) => {
  console.error(err);
  $('#prodGrid').innerHTML = `<div class="menu-empty" style="grid-column:1/-1">Halaman belum bisa dimuat: ${esc(err.message)}</div>`;
});

async function init() {
  initTheme();
  const cfg = await loadConfig();
  document.title = `${cfg.storeName} | Jasa Order Kopi`;

  $('#navBrand').innerHTML = logoHtml(cfg);
  $('#themeBtn').innerHTML = icons.sun + icons.moon;
  $('#drawerBtn').innerHTML = icons.menu;
  $('#fabWa').innerHTML = icons.wa;
  $('#ctaBtn').innerHTML = `${icons.wa} Chat admin`;
  $('#closedBanner').innerHTML = shopClosedBanner(cfg);
  $('#themeBtn').addEventListener('click', toggleTheme);
  $('#drawerBtn').addEventListener('click', openDrawer);
  renderDrawer(cfg);
  mountInfoSheets(cfg);
  watchNavScroll();

  renderHero(cfg);
  $('#perks').innerHTML = (cfg.home.stats || [])
    .slice(0, 3)
    .map((s, i) => `<div class="perk"><span class="perk-ico">${PERK_ICONS[i] || icons.star}</span><b>${esc(s.value)}</b><span>${esc(s.label)}</span></div>`)
    .join('');
  renderFooter(cfg);
  renderCartBar(cfg);
  // Coming back from a store page (Back button / bfcache) must show the latest cart.
  window.addEventListener('pageshow', () => renderCartBar(cfg));
  document.addEventListener('visibilitychange', () => document.visibilityState === 'visible' && renderCartBar(cfg));
  await renderFeatured(cfg);
}

// ── Cart bar (all brand carts) ──────────────────────────────────────────
function renderCartBar(cfg) {
  const carts = cfg.brands
    .map((b) => {
      const lines = store.get(`jo_cart_${b.id}`, []) || [];
      const outlet = store.get(`jo_outlet_${b.id}`, null);
      return { b, outlet, ...cartTotals(lines, b.fee) };
    })
    .filter((c) => c.qty > 0);
  const bar = $('#cartBar');
  document.body.classList.toggle('has-cart', carts.length > 0);
  bar.classList.toggle('show', carts.length > 0);
  if (!carts.length) return;
  $('#cartBarIcon').innerHTML = `${icons.bag}<span class="cart-bar-badge" id="cartBadge">0</span>`;
  $('#cartBarChevron').innerHTML = icons.chevron;
  $('#cartsClose').innerHTML = icons.close;
  const qty = carts.reduce((a, c) => a + c.qty, 0);
  const total = carts.reduce((a, c) => a + c.total, 0);
  $('#cartBadge').textContent = qty;
  $('#cartBarQty').textContent = `${qty} item`;
  $('#cartBarTotal').textContent = formatRp(total);
  if (carts.length === 1) {
    const c = carts[0];
    $('#cartBarOutlet').textContent = `${c.b.name}${c.outlet ? ` · ${c.outlet.name}` : ''}`;
    bar.href = `/${c.b.slug}?cart=1`;
    bar.onclick = null;
  } else {
    $('#cartBarOutlet').textContent = `${carts.length} keranjang: ${carts.map((c) => c.b.short).join(', ')}`;
    bar.href = '#';
    bar.onclick = (e) => {
      e.preventDefault();
      openSheet('cartsSheet');
    };
    $('#cartsList').innerHTML = carts
      .map(
        (c) => `<a class="cart-pick" href="/${esc(c.b.slug)}?cart=1"><img src="${esc(c.b.logo)}" alt="">
          <span class="cart-pick-body"><b>${esc(c.b.name)}</b><small>${c.qty} item${c.outlet ? ` · ${esc(c.outlet.name)}` : ''}</small></span>
          <span class="price">${formatRp(c.total)}</span>${icons.chevron}</a>`,
      )
      .join('');
  }
}

// ── Menu favorit (brand tabs + grid) & promo bundling ───────────────────
const menus = {}; // brand id -> Promise<menu | null>

// Prices follow the outlet the customer already picked on that brand's page (pricier
// outlets cost more), so the home page shows the same prices as the store page.
function loadMenu(id) {
  const o = store.get(`jo_outlet_${id}`, null);
  const q = o?.code && !o.manual ? `?outlet=${encodeURIComponent(o.code)}` : '';
  menus[id] ||= api(`/api/${id}/menu${q}`).catch(() => null);
  return menus[id];
}

async function renderFeatured(cfg) {
  let active = cfg.brands[0]?.id;
  const tabs = $('#brandTabs');
  tabs.innerHTML = cfg.brands
    .map((b) => `<button class="brand-tab ${b.id === active ? 'active' : ''}" role="tab" aria-selected="${b.id === active}" data-b="${esc(b.id)}"><img src="${esc(b.logo)}" alt=""><span>${esc(b.short)}</span></button>`)
    .join('');
  tabs.addEventListener('click', (e) => {
    const t = e.target.closest('.brand-tab');
    if (!t || t.dataset.b === active) return;
    active = t.dataset.b;
    $$('.brand-tab').forEach((x) => {
      x.classList.toggle('active', x === t);
      x.setAttribute('aria-selected', String(x === t));
    });
    showGrid(cfg, active);
  });
  cfg.brands.forEach((b) => loadMenu(b.id)); // fetch all brands in parallel
  await showGrid(cfg, active);
  await renderBundles(cfg);
}

async function showGrid(cfg, id) {
  const b = cfg.brands.find((x) => x.id === id);
  const grid = $('#prodGrid');
  $('#seeAllBtn').href = `/${b.slug}`;
  $('#seeAllBtn').innerHTML = `Lihat semua menu ${esc(b.name)} ${icons.arrow}`;
  grid.innerHTML = '<div class="prod skel skel-card"></div>'.repeat(4);
  const menu = await loadMenu(id);
  if ($('.brand-tab.active')?.dataset.b !== id) return; // user switched tabs meanwhile
  if (!menu) {
    grid.innerHTML = `<div class="menu-empty" style="grid-column:1/-1">Menu ${esc(b.name)} belum bisa dimuat. <a class="link-btn" href="/${esc(b.slug)}">Buka halaman menu</a></div>`;
    return;
  }
  const items = menu.items.filter((i) => !i.isSoldOut && !i.isBundle);
  // Admin-featured first, then tagged items, then the rest — photos first.
  const byPhoto = (x, y) => Number(Boolean(y.img)) - Number(Boolean(x.img));
  const picks = [
    ...items.filter((i) => i.featured),
    ...items.filter((i) => !i.featured && i.tag).sort(byPhoto),
    ...items.filter((i) => !i.featured && !i.tag).sort(byPhoto),
  ].slice(0, 8);
  grid.innerHTML = picks.map((i) => prodCard(b, i)).join('') || '<div class="menu-empty" style="grid-column:1/-1">Menu belum tersedia.</div>';
}

async function renderBundles(cfg) {
  const pairs = await Promise.all(cfg.brands.map(async (b) => [b, await loadMenu(b.id)]));
  const cards = [];
  for (const [b, m] of pairs) {
    for (const i of m?.items || []) if (i.isBundle && !i.isSoldOut) cards.push(prodCard(b, i));
  }
  if (!cards.length) return;
  $('#bundleTrack').innerHTML = cards.slice(0, 10).join('');
  const first = pairs.find(([, m]) => m?.items?.some((i) => i.isBundle));
  if (first) $('#bundlesAll').href = `/${first[0].slug}?item=${encodeURIComponent(first[1].items.find((i) => i.isBundle).key)}`;
  $('#bundlesSec').classList.remove('hidden');
}

function renderHero(cfg) {
  const slides = cfg.home.banners || [];
  if (!slides.length) {
    $('.hero').remove();
    return;
  }
  const logos = cfg.brands.map((b) => `<img src="${esc(b.logo)}" alt="">`).join('');
  const themeOf = (s) => (['forest', 'sage', 'cream'].includes(s.theme) ? s.theme : 'forest');
  $('#heroTrack').innerHTML = slides
    .map((s, i) => {
      const t = themeOf(s);
      return `<div class="hero-slide t-${t}" ${i ? 'aria-hidden="true"' : ''}>
        <img class="hero-mark" src="/img/brand/mark-${t === 'cream' ? 'forest' : 'cream'}.png" alt="">
        ${i === 0 ? `<div class="hero-brands" aria-label="Kopi Kenangan, Tomoro, Fore">${logos}</div>` : ''}
        <div class="hero-eyebrow">${esc(i === 0 ? 'Jasa order kopi' : cfg.storeName)}</div>
        <h1 class="hero-title">${esc(s.title)}</h1>
        <div class="hero-line"></div>
        <p class="hero-text">${esc(s.text)}</p>
        <div class="hero-actions">
          ${s.cta ? `<a class="hero-cta" href="${esc(safeHref(s.href))}" ${i ? 'tabindex="-1"' : ''}>${esc(s.cta)} ${icons.arrow}</a>` : ''}
          ${i === 0 ? `<button class="hero-link" data-action="howto">Cara order</button>` : ''}
        </div></div>`;
    })
    .join('');
  const hero = $('.hero');
  if (slides.length < 2) return;
  $('#heroDots').innerHTML = slides.map((_, i) => `<button class="hero-dot ${i ? '' : 'active'}" aria-label="Slide ${i + 1}" data-i="${i}"></button>`).join('');
  let idx = 0;
  const go = (i) => {
    idx = (i + slides.length) % slides.length;
    $('#heroTrack').style.transform = `translateX(-${idx * 100}%)`;
    $$('.hero-dot').forEach((d, j) => d.classList.toggle('active', j === idx));
    $$('.hero-slide').forEach((el, j) => {
      el.toggleAttribute('aria-hidden', j !== idx);
      el.querySelectorAll('a,button').forEach((a) => (a.tabIndex = j === idx ? 0 : -1));
    });
    hero.dataset.themeNow = themeOf(slides[idx]);
  };
  hero.dataset.themeNow = themeOf(slides[0]);
  const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  let timer = reduce ? null : setInterval(() => go(idx + 1), 6000);
  const restart = () => {
    clearInterval(timer);
    if (!reduce) timer = setInterval(() => go(idx + 1), 6000);
  };
  $('#heroDots').addEventListener('click', (e) => {
    const d = e.target.closest('.hero-dot');
    if (!d) return;
    go(Number(d.dataset.i));
    restart();
  });
  let x0 = null;
  const tr = $('#heroTrack');
  tr.addEventListener('touchstart', (e) => (x0 = e.touches[0].clientX), { passive: true });
  tr.addEventListener('touchend', (e) => {
    if (x0 == null) return;
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 40) {
      go(idx + (dx < 0 ? 1 : -1));
      restart();
    }
    x0 = null;
  });
}

function renderFooter(cfg) {
  $('#foot').innerHTML = `
    <img class="foot-logo" src="/img/brand/logo-cream.png" alt="${esc(cfg.storeName)}">
    <p class="foot-tag">${esc(cfg.tagline)}. ${esc(cfg.description)}</p>
    <nav class="foot-links" aria-label="Tautan">
      ${cfg.brands.map((b) => `<a href="/${esc(b.slug)}">${esc(b.name)}</a>`).join('')}
      <button data-action="howto">Cara order</button><button data-action="about">Tentang kami</button><button data-action="cs">Chat admin</button>
    </nav>
    <div class="foot-legal">&copy; ${new Date().getFullYear()} ${esc(cfg.storeName)}. Layanan jasa titip order — bukan bagian resmi dari ${cfg.brands.map((b) => esc(b.name)).join(', ')}. Semua merek milik pemiliknya masing-masing.</div>`;
}

function safeHref(h) {
  const s = String(h || '#');
  return /^(\/|#|https:\/\/)/.test(s) ? s : '#';
}

function prodCard(b, i) {
  const href = `/${b.slug}?item=${encodeURIComponent(i.key)}`;
  const img = i.isBundle
    ? bundleCollage(i)
    : i.img
      ? `<img src="${esc(i.img)}" alt="${esc(i.name)}" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='${esc(b.logo)}';this.classList.add('ph-logo');">`
      : `<img class="ph-logo" src="${esc(b.logo)}" alt="">`;
  const save = i.normalPrice && i.normalPrice > i.price ? Math.round((1 - i.price / i.normalPrice) * 100) : 0;
  const badge = i.tag || (i.isBundle ? 'Bundling' : i.discountLabel ? `Diskon ${i.discountLabel.replace(/^-/, '')}` : save >= 5 ? `Hemat ${save}%` : '');
  return `<a class="prod" href="${esc(href)}">
    <div class="prod-img">${img}${badge ? `<span class="badge">${esc(badge)}</span>` : ''}<span class="prod-add" aria-hidden="true">${icons.plus}</span></div>
    <div class="prod-body"><span class="prod-name">${esc(i.name)}</span><span class="prod-cat">${esc(i.isBundle ? i.group : i.category)}</span>
      <span class="prod-price">${i.isBundle && i.priceVaries ? '<small>mulai</small> ' : ''}<span class="price">${formatRp(i.isBundle ? i.fromPrice ?? i.price : i.price)}</span>${i.normalPrice ? `<s>${formatRp(i.normalPrice)}</s>` : ''}</span></div>
  </a>`;
}
