// Shared UI for the home and store pages: config, theme, drawer, sheets, dialogs, CS chat.
import { calcFee, formatRp, waLink } from './shared/order.js?v=3aeca02b99';

export const icons = {
  menu: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
  close: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  sun: '<svg class="theme-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="4.5"/><path d="M12 1.5v2.2M12 20.3v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M1.5 12h2.2M20.3 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"/></svg>',
  moon: '<svg class="theme-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
  home: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z"/></svg>',
  chat: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.6 8.6 0 0 1-3.9-.9L3 20.5l1.6-4.6a8.3 8.3 0 0 1-1.1-4.4A8.5 8.5 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5z"/></svg>',
  help: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  info: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
  wa: '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1s-.5-.1-.7.1-.8 1-1 1.2-.4.2-.7.1a8.2 8.2 0 0 1-2.4-1.5 9 9 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.6.3-.5a.6.6 0 0 0 0-.5l-.9-2.3c-.3-.6-.5-.5-.7-.5h-.6a1.2 1.2 0 0 0-.9.4 3.6 3.6 0 0 0-1.1 2.7 6.3 6.3 0 0 0 1.3 3.3 14.4 14.4 0 0 0 5.5 4.9c2 .8 2.5.7 3 .6a2.9 2.9 0 0 0 1.9-1.3 2.4 2.4 0 0 0 .2-1.3c-.1-.2-.3-.3-.6-.4zM12 21.8a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 1 1 12 21.8zm8.4-18.2A11.8 11.8 0 0 0 1.7 17.8L0 24l6.4-1.7a11.8 11.8 0 0 0 5.6 1.4A11.8 11.8 0 0 0 20.4 3.6z"/></svg>',
  pin: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="7.5"/></svg>',
  search: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>',
  wifi: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>',
  bag: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
  plus: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  minus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  trash: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>',
  edit: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>',
  arrow: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
  chevron: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>',
  chevronUp: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>',
  check: '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
  alert: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  soldout: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="8" y1="11" x2="16" y2="17"/></svg>',
  copy: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
  cup: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h12l-1.3 12.1a2 2 0 0 1-2 1.9H8.3a2 2 0 0 1-2-1.9z"/><path d="M4 8h14"/><path d="M8 8l1-5h4"/></svg>',
  clock: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9.5"/><polyline points="12 7 12 12 15 14"/></svg>',
  star: '<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/></svg>',
  tag: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg>',
  pulse: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 12 7 12 10 5 14 19 17 12 21 12"/></svg>',
  list: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4.5" cy="6" r="1"/><circle cx="4.5" cy="12" r="1"/><circle cx="4.5" cy="18" r="1"/></svg>',
  store: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 4.6 4h14.8L21 9.5"/><path d="M3 9.5a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 12v8h14v-8"/><path d="M10 20v-5h4v5"/></svg>',
  locate: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>',
};

// Category icons (one stroke weight, matched by keyword so any brand's categories get one).
const CAT_ICON = {
  bundle: '<path d="M20 12v9H4v-9"/><rect x="2" y="7" width="20" height="5" rx="1"/><path d="M12 21V7"/><path d="M12 7H8a2.5 2.5 0 1 1 0-5c3 0 4 5 4 5z"/><path d="M12 7h4a2.5 2.5 0 1 0 0-5c-3 0-4 5-4 5z"/>',
  sparkle: '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z"/>',
  coffee: '<path d="M17 8h1a4 4 0 0 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4z"/><line x1="7" y1="2" x2="7" y2="4"/><line x1="11" y1="2" x2="11" y2="4"/>',
  glass: '<path d="M6 8h12l-1.4 12.2a2 2 0 0 1-2 1.8H9.4a2 2 0 0 1-2-1.8z"/><path d="M13 8l2-6h3"/><path d="M6.6 13h10.8"/>',
  frappe: '<path d="M7 10h10l-1.2 10.2a2 2 0 0 1-2 1.8h-3.6a2 2 0 0 1-2-1.8z"/><path d="M6 10a6 6 0 0 1 12 0"/><path d="M12 4V2"/>',
  tea: '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.4 5.1-6C9.5 14.5 12 13 13 12"/>',
  food: '<path d="M4 13c0-5 3.6-8 8-8s8 3 8 8"/><path d="M2 13h20l-1.5 4.5A2 2 0 0 1 18.6 19H5.4a2 2 0 0 1-1.9-1.5z"/><path d="M9 9.5c.8-.6 1.8-1 3-1"/>',
  bottle: '<path d="M10 2h4"/><path d="M10 2v3.4a4 4 0 0 1-1 2.6l-.4.5A4 4 0 0 0 7.5 11v9a2 2 0 0 0 2 2h5a2 2 0 0 0 2-2v-9a4 4 0 0 0-1.1-2.5l-.4-.5a4 4 0 0 1-1-2.6V2"/><path d="M7.5 14h9"/>',
  star: '<polygon points="12 2 15.1 8.3 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 8.9 8.3 12 2"/>',
  dot: '<circle cx="12" cy="12" r="4"/>',
};
export function catIcon(name) {
  const n = String(name || '').toLowerCase();
  const k =
    /bundl|paket|promo|combo|hemat/.test(n) ? 'bundle'
    : /custom|spesial|special|baru|new/.test(n) ? 'sparkle'
    : /frappe|blended|shake|cloud/.test(n) ? 'frappe'
    : /tea|matcha|teh/.test(n) ? 'tea'
    : /food|deli|pastry|bakery|roti|snack|pasta|toast|makan/.test(n) ? 'food'
    : /1 ?l\b|liter|everyone|botol/.test(n) ? 'bottle'
    : /non.?(coffee|kopi)|refresh|juice|milk|choco|susu/.test(n) ? 'glass'
    : /coffee|kopi|latte|americano|espresso|signature|twist|favorite|classic|flavored/.test(n) ? 'coffee'
    : /junior|kids|cheese|series/.test(n) ? 'star'
    : 'dot';
  return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${CAT_ICON[k]}</svg>`;
}

export const store = {
  get(key, fallback = null) {
    try {
      const v = localStorage.getItem(key);
      return v == null ? fallback : JSON.parse(v);
    } catch {
      return fallback;
    }
  },
  set(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch {
      /* private mode / storage full */
    }
  },
  remove(key) {
    try {
      localStorage.removeItem(key);
    } catch {
      /* ignore */
    }
  },
};

/** Avatar text for an admin: a trailing number ("Admin 2" → "2"), else the first letter. */
export function avatarText(name) {
  const n = String(name || '').trim();
  const num = /(\d+)$/.exec(n);
  return num ? num[1] : (n[0] || '?').toUpperCase();
}

export function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
}

export async function api(path, opts = {}) {
  const res = await fetch(path, { headers: { accept: 'application/json', ...(opts.body ? { 'content-type': 'application/json' } : {}) }, ...opts });
  let data = null;
  try {
    data = await res.json();
  } catch {
    /* non-JSON */
  }
  if (!res.ok) throw new Error(data?.error || `HTTP ${res.status}`);
  if (data === null) throw new Error(`Respon bukan JSON valid: ${path}`);
  return data;
}

export const FALLBACK_CONFIG = {
  storeName: 'Zona Fitri',
  tagline: 'Jasa order Kopi Kenangan, Tomoro & Fore',
  description: 'Pesan kopi favorit lebih hemat: pilih outlet, pilih menu, lalu kirim pesanan ke WhatsApp admin.',
  accent: '#C11F2E',
  orderPrefix: 'ZF',
  channelUrl: 'https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F',
  admins: [{ name: 'Admin Zona Fitri', phone: '628973034455' }],
  shop: { open: true, reason: '', hours: '07:00 - 22:00 WIB' },
  waHeader: '── .✦ FORMAT ORDER ZONA FITRI✦.──',
  waFooter: "> > Setelah Format ini dikirim, segera melakukan Payment melalui QRIS\n> Proses orderan Prioritas (5-20 Menit)\n> ISI FORMAT ? WAJIB FIX ORDER",
  pickupTime: true,
  home: {
    stats: [
      { value: '50% OFF', label: 'Diskon menu aplikasi' },
      { value: 'Realtime', label: 'Stok & menu live' },
      { value: 'Prioritas', label: 'Proses 5-20 menit' }
    ]
  },
  csCategories: [
    { id: 'order', label: 'Tanya Orderan', hint: 'Kirim kode pesanan' },
    { id: 'menu', label: 'Tanya Menu / Outlet', hint: 'Ketersediaan menu' },
    { id: 'payment', label: 'Pembayaran & QRIS', hint: 'Konfirmasi bukti transfer' },
    { id: 'other', label: 'Lainnya', hint: 'Pertanyaan lain' }
  ],
  pricing: {
    kopken: { largeUpsize: 5000, juwaraBeans: 2000, oatside: 2000, fee: 5000 },
    tomoro: { fee: 5000 },
    fore: { fee: 5000 }
  },
  brands: [
    {
      id: 'kopken',
      name: 'Kopi Kenangan',
      slug: 'kopikenangan',
      short: 'Kopken',
      theme: '#8B1E2B',
      badge: '50% OFF',
      fee: 5000,
      logo: '/img/brand/kopken-logo.png',
      heroBg: '/img/brand/kopken-hero.jpg',
      bgMark: '/img/brand/mark-forest.png',
      allowOrderWithoutOutlet: false
    },
    {
      id: 'tomoro',
      name: 'Tomoro Coffee',
      slug: 'tomoro',
      short: 'Tomoro',
      theme: '#FF6B00',
      badge: '50% OFF',
      fee: 5000,
      logo: '/img/brand/tomoro-logo.png',
      heroBg: '/img/brand/tomoro-hero.jpg',
      bgMark: '/img/brand/mark-cream.png',
      allowOrderWithoutOutlet: false
    },
    {
      id: 'fore',
      name: 'Fore Coffee',
      slug: 'fore',
      short: 'Fore',
      theme: '#1F5E3B',
      badge: '50% OFF',
      fee: 5000,
      logo: '/img/brand/fore-logo.png',
      heroBg: '/img/brand/fore-hero.jpg',
      bgMark: '/img/brand/mark-forest.png',
      allowOrderWithoutOutlet: false
    }
  ]
};

let configPromise;
export async function loadConfig() {
  if (configPromise) return configPromise;
  configPromise = (async () => {
    try {
      const remote = await api('/api/config');
      if (remote && Array.isArray(remote.brands) && remote.brands.length) {
        return { ...FALLBACK_CONFIG, ...remote };
      }
      return FALLBACK_CONFIG;
    } catch (err) {
      console.warn('Gagal memuat /api/config, menggunakan fallback config:', err);
      return FALLBACK_CONFIG;
    }
  })();
  return configPromise;
}

export const $ = (sel, root = document) => root.querySelector(sel);

// Broken product images fall back to the item's initial (CSP forbids inline onerror).
document.addEventListener(
  'error',
  (e) => {
    const img = e.target;
    if (img?.tagName !== 'IMG' || img.dataset.failed) return;
    img.dataset.failed = '1';
    const ph = document.createElement('span');
    ph.className = 'ph';
    ph.textContent = (img.closest('[data-key]')?.querySelector('.row-name, .gcard-name, span')?.textContent || '•').trim().slice(0, 1);
    img.replaceWith(ph);
  },
  true,
);
export const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

// ── Theme ─────────────────────────────────────────────────────────────────
export function initTheme() {
  const saved = store.get('jo_theme');
  const dark = saved ? saved === 'dark' : window.matchMedia?.('(prefers-color-scheme: dark)').matches;
  applyTheme(dark ? 'dark' : 'light');
}
function applyTheme(t) {
  document.documentElement.dataset.theme = t;
  const meta = document.querySelector('meta[name="theme-color"]');
  if (meta) meta.content = t === 'dark' ? '#0F0C0B' : '#FFFFFF';
}
export function toggleTheme() {
  const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  applyTheme(next);
  store.set('jo_theme', next);
}

// ── Sheets & dialogs ──────────────────────────────────────────────────────
// While any sheet (or the drawer) is open we keep exactly one extra history entry,
// so the phone's Back button closes the top layer instead of leaving the page.
// Removing that entry is deferred a tick: closing one sheet and opening another in
// the same click (e.g. cart → "order sent") must reuse the entry, not race it.
const openStack = [];
let armed = false; // our history entry is the current one
let skipPops = 0; // popstates caused by our own history.back()
let backTimer = null;

function arm() {
  clearTimeout(backTimer);
  if (armed) return;
  armed = true;
  history.pushState({ layer: true }, '');
}
function disarmSoon() {
  clearTimeout(backTimer);
  backTimer = setTimeout(() => {
    if (openStack.length || !armed) return;
    armed = false;
    skipPops++;
    history.back();
  }, 0);
}
function pushLayer(id) {
  if (!openStack.includes(id)) openStack.push(id);
  arm();
}
function popLayer(id, fromPop) {
  const i = openStack.lastIndexOf(id);
  if (i < 0) return;
  openStack.splice(i, 1);
  if (!fromPop) disarmSoon();
}
window.addEventListener('popstate', () => {
  if (skipPops > 0) {
    skipPops--;
    return;
  }
  armed = false; // the user's Back consumed our entry
  const top = openStack[openStack.length - 1];
  if (!top) return;
  if (top === 'drawer') closeDrawer(true);
  else closeSheet(top, true);
  if (openStack.length) arm(); // more layers below: Back should close those next
});

export function openSheet(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('active');
  pushLayer(id);
  document.body.classList.add('lock');
}
export function closeSheet(id, fromPop = false) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('active');
  popLayer(id, fromPop);
  if (!openStack.length) document.body.classList.remove('lock');
}
document.addEventListener('click', (e) => {
  const bd = e.target.closest?.('.sheet-backdrop');
  if (bd && e.target === bd) closeSheet(bd.id);
  const closer = e.target.closest?.('[data-close]');
  if (closer) closeSheet(closer.dataset.close);
});
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  const top = openStack[openStack.length - 1];
  if (top === 'drawer') closeDrawer();
  else if (top) closeSheet(top);
});

let toastTimer;
export function toast(msg) {
  let el = $('#toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'toast';
    el.className = 'toast';
    el.setAttribute('role', 'status');
    document.body.appendChild(el);
  }
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
}

/** Centered dialog. kind: 'danger' | 'ok' | 'brand'. Returns a promise resolved on close. */
export function showDialog({ title, html, kind = 'danger', icon = icons.alert, button = 'Mengerti' }) {
  let el = $('#dialogModal');
  if (!el) {
    el = document.createElement('div');
    el.id = 'dialogModal';
    el.className = 'sheet-backdrop dialog-backdrop';
    el.innerHTML = `<div class="dialog" role="alertdialog" aria-modal="true">
      <button class="close-btn" data-close="dialogModal" aria-label="Tutup">${icons.close}</button>
      <div class="dialog-icon"></div><h3 class="dialog-title"></h3><p class="dialog-text"></p>
      <button class="btn btn-primary btn-block" data-close="dialogModal"></button></div>`;
    document.body.appendChild(el);
  }
  $('.dialog-icon', el).className = `dialog-icon ${kind}`;
  $('.dialog-icon', el).innerHTML = icon;
  $('.dialog-title', el).textContent = title;
  $('.dialog-text', el).innerHTML = html;
  $('.btn', el).textContent = button;
  openSheet('dialogModal');
}

// ── Drawer ────────────────────────────────────────────────────────────────
export function openDrawer() {
  $('#drawer')?.classList.add('active');
  $('#drawerOverlay')?.classList.add('active');
  document.body.classList.add('lock');
  pushLayer('drawer');
}
export function closeDrawer(fromPop = false) {
  if (!$('#drawer')?.classList.contains('active')) return;
  $('#drawer').classList.remove('active');
  $('#drawerOverlay')?.classList.remove('active');
  popLayer('drawer', fromPop);
  if (!openStack.length) document.body.classList.remove('lock');
}

export function renderDrawer(cfg, activeBrand = null) {
  const wrap = document.createElement('div');
  wrap.innerHTML = `
    <div class="drawer-overlay" id="drawerOverlay"></div>
    <aside class="drawer" id="drawer" aria-label="Navigasi">
      <div class="drawer-head">
        <a href="/">${logoHtml(cfg)}</a>
        <button class="icon-btn" data-drawer-close aria-label="Tutup menu">${icons.close}</button>
      </div>
      <nav class="drawer-nav">
        <a href="/" class="drawer-link ${activeBrand ? '' : 'active'}">${icons.home}<span>Beranda</span></a>
        <div class="drawer-label eyebrow">Order kopi</div>
        ${cfg.brands
          .map(
            (b) => `<a href="/${b.slug}" class="drawer-link ${activeBrand === b.id ? 'active' : ''}">
              <img class="brand-ico" src="${esc(b.logo)}" alt=""><span>${esc(b.name)}</span></a>`,
          )
          .join('')}
        <div class="drawer-label eyebrow">Bantuan</div>
        <button class="drawer-link" data-action="howto">${icons.help}<span>Cara order</span></button>
        <button class="drawer-link" data-action="about">${icons.info}<span>Tentang kami</span></button>
        <button class="drawer-link" data-action="cs">${icons.chat}<span>Chat admin</span></button>
      </nav>
      <div class="drawer-foot">&copy; ${new Date().getFullYear()} ${esc(cfg.storeName)}</div>
    </aside>`;
  document.body.append(...wrap.children);
  $('#drawerOverlay').addEventListener('click', () => closeDrawer());
  $('[data-drawer-close]').addEventListener('click', () => closeDrawer());
}

/** Logo lockup: logo Zona Fitri resmi. */
export function logoHtml(cfg) {
  const alt = esc(cfg.storeName || 'Zona Fitri');
  return `<span class="logo" role="img" aria-label="${alt}">
    <img src="/img/brand/zonafitri-logo.png" class="logo-rid-img" alt="${alt}" style="height:32px;width:auto;object-fit:contain;display:inline-block;vertical-align:middle;" />
  </span>`;
}

/** Topnav gets a hairline border once the page scrolls. */
export function watchNavScroll() {
  const nav = document.querySelector('.topnav');
  if (!nav) return;
  const on = () => nav.classList.toggle('scrolled', window.scrollY > 8);
  window.addEventListener('scroll', on, { passive: true });
  on();
}

// ── Info sheets (Cara Order, Tentang, Chat Admin) ────────────────────────
export function feeText(rule) {
  if (!rule || rule.mode === 'none') return 'Tanpa fee jasa.';
  if (rule.mode === 'per_item') return `Fee jasa <b>${formatRp(rule.perItem)}</b> per menu (paket bundling sudah termasuk fee).`;
  const lines = [];
  lines.push(`Beli 1 menu: fee <b>${formatRp(rule.single)}</b>`);
  if (Number(rule.singleSpecial) && rule.singleSpecial !== rule.single) lines.push(`Beli 1 Frappe / Food: fee <b>${formatRp(rule.singleSpecial)}</b>`);
  if (Number(rule.freeFromQty) > 0) lines.push(`Beli ${rule.freeFromQty} menu atau lebih${rule.bundleFree !== false ? ', atau paket bundling' : ''}: <b>gratis fee</b>`);
  return lines.join('<br>');
}

export function mountInfoSheets(cfg, brand = null) {
  const brands = brand ? [brand] : cfg.brands;
  const feeHtml = brands.map((b) => `<div class="fee-brand">${esc(b.name)}</div><div>${feeText(b.fee)}</div>`).join('');
  const admins = cfg.admins || [];
  const wrap = document.createElement('div');
  wrap.innerHTML = `
  <div class="sheet-backdrop" id="howtoSheet"><div class="sheet" role="dialog" aria-modal="true" aria-labelledby="howtoTitle">
    <div class="sheet-handle"></div>
    <div class="sheet-head"><div><div class="eyebrow">Panduan</div><h3 class="sheet-title" id="howtoTitle">Cara order di ${esc(cfg.storeName)}</h3></div>
    <button class="close-btn" data-close="howtoSheet" aria-label="Tutup">${icons.close}</button></div>
    <div class="timeline">
      <div class="tl-step"><span class="tl-num">1</span><div><h4>Pilih outlet</h4><p>Pilih outlet tujuan pesanan, atau pakai lokasi kamu. Menu, harga & stok menyesuaikan outlet itu.</p></div></div>
      <div class="tl-step"><span class="tl-num">2</span><div><h4>Pilih menu & varian</h4><p>Atur ukuran, Ice/Hot, gula & es, atau ambil paket bundling yang lebih hemat.</p></div></div>
      <div class="tl-step"><span class="tl-num">3</span><div><h4>Kirim lewat WhatsApp</h4><p>Isi nama & waktu pesanan, tekan <b>Pesan via WhatsApp</b>. Rincian pesanan otomatis terkirim ke admin.</p></div></div>
      <div class="tl-step"><span class="tl-num">4</span><div><h4>Bayar, pesanan diorderkan</h4><p>Admin konfirmasi & kirim info pembayaran. Setelah lunas, pesananmu langsung diorderkan ke outlet.</p></div></div>
    </div>
    <div class="fee-box"><div class="eyebrow">Fee jasa</div>${feeHtml}</div>
  </div></div>

  <div class="sheet-backdrop" id="aboutSheet"><div class="sheet" role="dialog" aria-modal="true" aria-labelledby="aboutTitle">
    <div class="sheet-handle"></div>
    <div class="sheet-head"><div><div class="eyebrow">Tentang kami</div><h3 class="sheet-title" id="aboutTitle">${esc(cfg.storeName)}</h3></div>
    <button class="close-btn" data-close="aboutSheet" aria-label="Tutup">${icons.close}</button></div>
    <div class="about-list">
      <div class="about-item"><span class="ico">${icons.tag}</span><div><h4>Order lebih hemat</h4><p>${esc(cfg.description)}</p></div></div>
      <div class="about-item"><span class="ico">${icons.pulse}</span><div><h4>Stok sesuai outlet</h4><p>Menu & stok diambil dari outlet pilihanmu, jadi menu yang habis langsung kelihatan.</p></div></div>
      <div class="about-item"><span class="ico">${icons.chat}</span><div><h4>Cukup lewat WhatsApp</h4><p>Tanpa install aplikasi. Pilih menu, kirim rincian ke admin, beres.</p></div></div>
    </div>
  </div></div>

  <div class="sheet-backdrop" id="csSheet"><div class="sheet" role="dialog" aria-modal="true" aria-labelledby="csTitle">
    <div class="sheet-handle"></div>
    <div class="sheet-head"><div><div class="eyebrow">Bantuan</div><h3 class="sheet-title" id="csTitle">Chat admin</h3><div class="sheet-sub">Pilih admin & topik, lanjut di WhatsApp.</div></div>
    <button class="close-btn" data-close="csSheet" aria-label="Tutup">${icons.close}</button></div>
    <label class="field-label">Admin</label>
    ${admins.length
      ? `<div class="cs-admins" id="csAdmins">${admins
          .map((a, i) => `<button class="cs-admin ${i === 0 ? 'active' : ''}" data-phone="${esc(a.phone)}" data-name="${esc(a.name)}"><span class="cs-avatar">${esc(avatarText(a.name))}</span>${esc(a.name)}</button>`)
          .join('')}</div>`
      : '<div class="cs-empty" id="csAdmins">Nomor admin belum diatur pemilik toko.</div>'}
    <label class="field-label" for="csCategory">Topik</label>
    <select class="field" id="csCategory">${cfg.csCategories.map((c) => `<option>${esc(c)}</option>`).join('')}</select>
    <label class="field-label" for="csDetail">Detail (opsional)</label>
    <textarea class="field" id="csDetail" rows="3" placeholder="Tulis kode pesanan atau detail kendala…"></textarea>
    <button class="btn btn-primary btn-block" id="csSend" style="margin-top:18px" ${admins.length ? '' : 'disabled'}>${icons.wa} Lanjut ke WhatsApp</button>
  </div></div>`;
  document.body.append(...wrap.children);

  $('#csAdmins').addEventListener('click', (e) => {
    const b = e.target.closest('.cs-admin');
    if (!b) return;
    $$('#csAdmins .cs-admin').forEach((x) => x.classList.toggle('active', x === b));
  });
  $('#csSend').addEventListener('click', () => {
    const a = $('#csAdmins .cs-admin.active');
    if (!a) return;
    const detail = $('#csDetail').value.trim();
    const text = `Halo Admin ${a.dataset.name}, saya butuh bantuan.\n\nTopik: ${$('#csCategory').value}${detail ? `\nDetail: ${detail}` : ''}\n\n(dari ${location.host})`;
    const w = window.open(waLink(a.dataset.phone, text), '_blank');
    if (w) w.opener = null;
  });

  document.addEventListener('click', (e) => {
    const act = e.target.closest('[data-action]')?.dataset.action;
    if (!act) return;
    if (['howto', 'about', 'cs'].includes(act)) {
      closeDrawer();
      openSheet({ howto: 'howtoSheet', about: 'aboutSheet', cs: 'csSheet' }[act]);
    }
  });
}

/**
 * Photo collage for a bundle card: real photos of drinks the customer can pick,
 * preferring different categories so the card shows some variety.
 */
export function bundleCollage(b, { max = 3 } = {}) {
  const withImg = (b.candidates || []).filter((c) => c.img && !c.isSoldOut);
  const picks = [];
  const seenCat = new Set();
  for (const c of withImg) {
    const cat = c.category || '';
    if (picks.length < max && !seenCat.has(cat)) {
      picks.push(c);
      seenCat.add(cat);
    }
  }
  for (const c of withImg) if (picks.length < max && !picks.includes(c)) picks.push(c);
  const n = Math.min(picks.length, b.slots >= 3 ? 3 : 2);
  const k = Math.round((b.fromPrice ?? b.price) / 1000);
  const label = `${b.slots} ${b.size === 'Large' ? 'Large' : 'menu'}${b.fixedItems?.length ? ' + bonus' : ''}`;
  if (!n) {
    return `<div class="bundle-art"><span class="ba-top">${esc(label)}</span><span class="ba-big">${k}<small>rb</small></span></div>`;
  }
  return `<div class="collage c${n}">${picks
    .slice(0, n)
    .map((c) => `<img src="${esc(c.img)}" alt="${esc(c.name)}" loading="lazy" referrerpolicy="no-referrer">`)
    .join('')}<span class="collage-tag"><b>${k}<small>rb</small></b><span>${esc(label)}</span></span></div>`;
}

export function shopClosedBanner(cfg) {
  if (cfg.shop.open) return '';
  return `<div class="closed-banner" role="status">${icons.alert}<div><strong>Toko sedang tutup</strong>${esc(cfg.shop.reason)} Kamu tetap bisa lihat menu.</div></div>`;
}

export { calcFee, formatRp };
