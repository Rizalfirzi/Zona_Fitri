/* RID Store — lapisan gaya video (splash 3D, brand modal 2x2, kartu WiFi). */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  function api(path) {
    return fetch(path, { headers: { accept: 'application/json' } }).then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    });
  }
  function getJSON(key, fb) {
    try {
      var v = localStorage.getItem(key);
      return v == null ? fb : JSON.parse(v);
    } catch (e) { return fb; }
  }

  /* ── Splash (beranda) ── */
  var splash = $('#splash');
  if (splash) {
    // Mode siang/senja/malam
    var seg = $('#spSeg');
    if (seg) {
      var saved = null;
      try { saved = localStorage.getItem('jo_daymode'); } catch (e) {}
      if (saved && $('button[data-m="' + saved + '"]', seg)) {
        splash.dataset.mode = saved;
        $$('button', seg).forEach(function (b) { b.classList.toggle('active', b.dataset.m === saved); });
      }
      seg.addEventListener('click', function (e) {
        var b = e.target.closest('button[data-m]');
        if (!b) return;
        splash.dataset.mode = b.dataset.m;
        $$('button', seg).forEach(function (x) { x.classList.toggle('active', x === b); });
        try { localStorage.setItem('jo_daymode', b.dataset.m); } catch (err) {}
      });
    }

    // Jam WIB live & Tanggal
    var clockEl = $('#spClock'), dateEl = $('#spDate');
    if (clockEl || dateEl) {
      function tick() {
        try {
          var now = new Date();
          var t = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', hour12: false }).format(now);
          if (clockEl) clockEl.textContent = t.replace('.', ':') + ' WIB';
          if (dateEl) dateEl.textContent = new Intl.DateTimeFormat('id-ID', { timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long' }).format(now);
        } catch (e) {}
      }
      tick();
      setInterval(tick, 1000);
    }

    var hostEl = $('#spHost');
    if (hostEl) hostEl.textContent = location.host.replace(/^www\./, '');

    // Status toko + link saluran WA
    api('/api/config').then(function (cfg) {
      var open = !!(cfg.shop && cfg.shop.open);
      var st = $('#spStatus');
      if (st) {
        st.classList.toggle('open', open);
        var stTxt = $('#spStatusText');
        if (stTxt) stTxt.textContent = open ? 'SEDANG BUKA' : 'SEDANG TUTUP';
      }
      var spTitle = $('#spTitle');
      if (spTitle) spTitle.textContent = open ? 'Toko Sedang Buka' : 'Toko Sedang Tutup';
      var spSub = $('#spSub');
      if (spSub) spSub.textContent = open ? 'pilih outlet & pesan sekarang' : (cfg.shop && cfg.shop.reason ? cfg.shop.reason : 'open besok lgi');
      var spWa = $('#spWa');
      if (spWa) {
        var channelLink = (cfg.settings && cfg.settings.channelUrl) || cfg.channelUrl || 'https://whatsapp.com/channel/0029VbAzAXsI1rcbcchmmj2F';
        spWa.href = channelLink;
      }
    }).catch(function () {});
  }

  /* ── Brand modal 2x2 (beranda: tombol Lihat-lihat; toko: tombol •••) ── */
  var brandModal = $('#brandModal');
  var brandBtn = document.getElementById('brandBtn') || document.getElementById('spLihat');
  if (brandModal && brandBtn) {
    var slug = location.pathname.replace(/^\/|\/$/g, '');
    brandModal.addEventListener('click', function (e) {
      if (e.target === brandModal) brandModal.classList.remove('show');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') brandModal.classList.remove('show');
    });
    brandBtn.addEventListener('click', function () {
      var grid = $('#brandModalGrid');
      if (grid && !grid.children.length) {
        var items = [
          {
            slug: 'kopikenangan',
            name: 'KOPI KENANGAN',
            icon: '<svg width="34" height="34" viewBox="0 0 40 40"><path d="M20 34C10 26 4 20 4 13.5 4 8 8 4 13 4c3.5 0 6 2 7 4 1-2 3.5-4 7-4 5 0 9 4 9 9.5C36 20 30 26 20 34Z" fill="none" stroke="#C11F2E" stroke-width="3"/><path d="M11 11l10 14M15 9l10 14" stroke="#C11F2E" stroke-width="1.8" stroke-linecap="round"/></svg>'
          },
          {
            slug: 'fore',
            name: 'FORE COFFEE',
            icon: '<svg width="34" height="34" viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="14" stroke="#006241" stroke-width="2.5"/><path d="M20 10c-5 6-4 12 0 18 4-6 5-12 0-18z" fill="#006241"/></svg>'
          },
          {
            slug: 'tomoro',
            name: 'TOMORO COFFEE',
            icon: '<img src="/img/brands/tomoro.png" style="width:38px;height:38px;object-fit:contain;" alt="Tomoro">'
          }
        ];
        grid.innerHTML = items.map(function (b) {
          return '<a class="brand-tile' + (b.slug === slug ? ' active' : '') + '" href="' + (b.isCgv ? 'javascript:void(0)' : '/' + b.slug) + '"' + (b.isCgv ? ' id="cgvModalBtn"' : '') + '>' +
            b.icon + '<small>' + b.name + '</small></a>';
        }).join('');
        var cgvBtn = $('#cgvModalBtn');
        if (cgvBtn) {
          cgvBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Pemesanan CGV Cinemas segera hadir di Zona Fitri!');
          });
        }
      }
      brandModal.classList.add('show');
    });
  }

  /* ── Kartu WiFi (halaman toko) ── */
  var wifiCard = $('#wifiCard');
  if (wifiCard) {
    var MAP = { kopikenangan: 'kopken', tomoro: 'tomoro', fore: 'fore' };
    var brandSlug = location.pathname.replace(/^\/|\/$/g, '');
    var brandId = MAP[brandSlug] || 'kopken';
    var passEl = $('#wifiCardPass'), dateEl2 = $('#wifiCardDate');
    function loadWifi() {
      var outlet = getJSON('jo_outlet_' + brandId, null);
      if (!outlet || !outlet.code || outlet.manual) {
        passEl.textContent = 'Pilih outlet dulu';
        dateEl2.textContent = 'Password WiFi hari ini';
        return;
      }
      api('/api/' + brandId + '/wifi?outlet=' + encodeURIComponent(outlet.code)).then(function (d) {
        if (d && d.wifi && d.wifi.password) {
          passEl.textContent = d.wifi.password;
          dateEl2.textContent = 'Password WiFi — ' + new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        } else {
          passEl.textContent = 'Tanya kasir';
          dateEl2.textContent = 'Belum tersedia hari ini';
        }
      }).catch(function () {
        passEl.textContent = 'Tanya kasir';
        dateEl2.textContent = 'Belum tersedia';
      });
    }
    api('/api/config').then(function (cfg) {
      var b = (cfg.brands || []).filter(function (x) { return x.id === brandId; })[0];
      if (b && b.wifi) {
        wifiCard.classList.remove('hidden');
        loadWifi();
      }
    }).catch(function () {});

    var copyBtn = $('#wifiCardCopy');
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        var t = passEl.textContent;
        if (!t || /Pilih|Tanya|Belum/.test(t)) return;
        if (navigator.clipboard) navigator.clipboard.writeText(t).catch(function () {});
        this.textContent = 'Disalin ✓';
        var btn = this;
        setTimeout(function () { btn.textContent = 'Salin ▼'; }, 1600);
      });
    }

    // Refresh wifi saat outlet berubah di storage
    window.addEventListener('storage', function(e) {
      if (e.key === 'jo_outlet_' + brandId) loadWifi();
    });
    document.addEventListener('outlet-changed', loadWifi);
  }

  /* ── Label kategori ala video (tampilan) ── */
  var CAT_LABEL = { Coffee: 'Kopi', 'Non Coffee': 'Non-Kopi' };
  function renameCats() {
    $$('#catBar .cat-pill, #catList .cat-opt .n').forEach(function (el) {
      var t = el.textContent.trim();
      if (CAT_LABEL[t]) el.textContent = CAT_LABEL[t];
    });
  }
  if ($('#catBar') || $('#catList')) {
    renameCats();
    var catObs = new MutationObserver(renameCats);
    if ($('#catBar')) catObs.observe($('#catBar'), { childList: true, subtree: true });
    if ($('#catList')) catObs.observe($('#catList'), { childList: true, subtree: true });
  }
})();
