// Pure helpers shared by the browser and the Node server (and tests).
// No DOM access in this file.

export function formatRp(n) {
  return 'Rp' + Math.round(Number(n) || 0).toLocaleString('id-ID');
}

/** Round `n` to multiple of `step` with support for 'floor', 'ceil', or 'nearest'. */
export function roundTo(n, step = 1, mode = 'nearest') {
  if (!step || step <= 1) {
    if (mode === 'floor') return Math.floor(n);
    if (mode === 'ceil') return Math.ceil(n);
    return Math.round(n);
  }
  if (mode === 'floor') return Math.floor(n / step) * step;
  if (mode === 'ceil') return Math.ceil(n / step) * step;
  return Math.round(n / step) * step;
}

/**
 * Selling price from the outlet price based on brand pricing formula.
 */
export function computePrice(outletPrice, pricing = {}, options = {}) {
  const base = Number(outletPrice) || 0;
  if (base <= 0) return 0;
  const isLarge = Boolean(options.size === 'Large' || options.isLarge);
  const pct = pricing.percent == null ? 100 : Number(pricing.percent);
  const maxDisc = pricing.maxDiscount != null ? Number(pricing.maxDiscount) : Infinity;
  const add = Number(pricing.add) || 0;
  const rawDisc = (base * (100 - pct)) / 100;
  const disc = Math.min(rawDisc, maxDisc);
  const regPrice = Math.round(base - disc + add);
  if (isLarge) {
    const largeAdd = pricing.largeAdd != null ? Number(pricing.largeAdd) : 5000;
    return regPrice + largeAdd;
  }
  return regPrice;
}

/**
 * Returns the effective count of individual items (cups / food / pcs) in a line item.
 * For regular items, this is simply the line quantity.
 * For bundles, this sums user selections (cups) and fixed items (e.g. 2 roti),
 * multiplied by the bundle line quantity.
 */
export function countBundleItems(it) {
  if (!it) return 0;
  const lineQty = Number(it.qty) || 1;
  if (!it.isBundle) return lineQty;

  let count = 0;
  if (Array.isArray(it.selections) && it.selections.length > 0) {
    count += it.selections.length;
  } else if (Number(it.slots) > 0) {
    count += Number(it.slots);
  }

  if (Array.isArray(it.fixedItems) && it.fixedItems.length > 0) {
    for (const f of it.fixedItems) {
      if (typeof f === 'string') {
        const m = f.match(/^\s*(\d+)/);
        count += m ? parseInt(m[1], 10) : 1;
      } else if (typeof f === 'object' && f !== null) {
        count += Number(f.qty) || 1;
      } else {
        count += 1;
      }
    }
  }

  if (count === 0) {
    const nameStr = String(it.name || '');
    const m = nameStr.match(/(\d+)\s*(?:pcs|cup|item|menu)/i)
      || nameStr.match(/large\s*(\d+)/i)
      || nameStr.match(/(\d+)/);
    if (m) count = parseInt(m[1], 10);
  }

  return (count || 1) * lineQty;
}

/**
 * Service fee for a cart.
 * cart items: { qty, isBundle, isSpecial } (isSpecial = frappe / food)
 * rule.mode:
 *   'none'     → 0
 *   'per_item' → perItem × qty of non-bundle items
 *   'tiered'   → 0 when any bundle is present (bundleFree) or total qty >= freeFromQty,
 *                otherwise `single`, or `singleSpecial` for a lone frappe/food item
 */
export function calcFee(cart, rule = {}) {
  if (!Array.isArray(cart) || cart.length === 0) return 0;
  const mode = rule.mode || 'none';
  if (mode === 'none') return 0;
  const nonBundleQty = cart.filter((i) => !i.isBundle).reduce((a, i) => a + (i.qty || 0), 0);
  if (mode === 'per_item') return (Number(rule.perItem) || 0) * nonBundleQty;

  const hasBundle = cart.some((i) => i.isBundle);
  if (hasBundle && rule.bundleFree !== false) return 0;
  const totalQty = cart.reduce((a, i) => a + countBundleItems(i), 0);
  const freeFrom = Number(rule.freeFromQty) || 0;
  if (freeFrom > 0 && totalQty >= freeFrom) return 0;
  if (nonBundleQty === 0) return 0;
  const lone = cart.find((i) => !i.isBundle);
  const each = lone && lone.isSpecial ? Number(rule.singleSpecial) || 0 : Number(rule.single) || 0;
  return each * nonBundleQty;
}

export function cartTotals(cart, rule) {
  const subtotal = cart.reduce((a, i) => a + (i.price || 0) * (i.qty || 0), 0);
  const fee = calcFee(cart, rule);
  const qty = cart.reduce((a, i) => a + countBundleItems(i), 0);
  return { subtotal, fee, total: subtotal + fee, qty };
}

/** Short human-friendly order code, e.g. JO-7K3Q9. Avoids look-alike characters. */
export function orderCode(prefix = 'ZF', rand = Math.random) {
  const abc = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
  let s = '';
  for (let i = 0; i < 5; i++) s += abc[Math.floor(rand() * abc.length)];
  return `${prefix}-${s}`;
}

/**
 * "Ice • Large • Less Sugar" style summary.
 * Accepts the cart's `choices` array ([{ group, value }] or strings) or a legacy
 * { temp, size, ice, sugar } object.
 */
export function optionText(opts) {
  if (!opts) return '';
  if (Array.isArray(opts)) {
    return opts
      .map((c) => (typeof c === 'string' ? c : c && c.value))
      .filter(Boolean)
      .join(' • ');
  }
  const parts = [];
  if (opts.temp) parts.push(opts.temp);
  if (opts.size) parts.push(opts.size);
  if (opts.ice && opts.temp !== 'Hot') parts.push(opts.ice);
  if (opts.sugar) parts.push(opts.sugar);
  return parts.join(' • ');
}

export const DEFAULT_WA_HEADER = '── .✦ FORMAT ORDER ZONA FITRI✦.──';
export const DEFAULT_WA_FOOTER = [
  '> > Setelah Format ini dikirim, segera melakukan Payment melalui QRIS',
  '> Proses orderan Prioritas (5-20 Menit)',
  '> ISI FORMAT ? WAJIB FIX ORDER',
].join('\n');

/**
 * WhatsApp order message, in the shop's "FORMAT ORDER ZONA FITRI" layout.
 * Quantity placed right before price per client request (Page 3 of PDF).
 */
export function buildWaMessage(order) {
  const out = [];
  out.push(order.header || DEFAULT_WA_HEADER);
  out.push('');
  out.push(`Atas Nama : ${order.customerName}`);
  out.push(`Brand/Merchant : ${order.brandName}`);
  out.push(`Nama Outlet : ${order.outlet}`);
  out.push(`Jam/Jadwal : ${order.pickup || 'Sekarang'}`);
  out.push('');
  out.push('Pesanan :');
  order.items.forEach((it, i) => {
    const o = it.isBundle ? '' : optionText(it.choices || it.options);
    out.push(`${i + 1}. ${it.name}${o ? ` (${o})` : ''} — ${it.qty}x ${formatRp(it.price * it.qty)}`);
    if (it.isBundle) {
      for (const f of it.fixedItems || []) out.push(`    • Termasuk: ${f}`);
      for (const sel of it.selections || []) {
        const so = optionText(sel.choices || sel.options);
        out.push(`    • ${sel.name}${so ? ` (${so})` : ''}`);
      }
    }
    if (it.note) out.push(`    Catatan: ${it.note}`);
  });
  out.push('');
  const totalQty = order.qty != null ? order.qty : order.items.reduce((sum, it) => sum + countBundleItems(it), 0);
  out.push(`Total Qty : ${totalQty} item`);
  out.push(`Total : ${formatRp(order.total)}`);
  if (order.note) out.push(`Catatan : ${order.note}`);
  if (order.code) out.push(`Kode Order : ${order.code}`);
  const footer = order.footer == null ? DEFAULT_WA_FOOTER : order.footer;
  if (footer) {
    out.push('');
    out.push(footer);
  }
  return out.join('\n');
}

/** Normalise an Indonesian phone number to wa.me format (62…). Returns '' if invalid. */
export function normalizePhone(raw) {
  let d = String(raw || '').replace(/\D/g, '');
  if (d.startsWith('0')) d = '62' + d.slice(1);
  else if (d.startsWith('8')) d = '62' + d;
  return /^62\d{8,13}$/.test(d) ? d : '';
}

export function waLink(phone, text) {
  return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
}
