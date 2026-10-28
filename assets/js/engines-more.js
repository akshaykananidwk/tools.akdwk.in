/* ================================================================
   KRISHNA TOOLS — data-driven engines for the industry tool packs
   (includes/tools_more/*.php). Each tool is pure data; these four
   engines turn that data into a working tool:
     formula  — calculator: inputs → computed outputs (JS expressions)
     textgen  — wishes / captions / messages from templates
     card     — greeting card / poster / banner drawn on a canvas (PNG)
     doc      — printable bill / estimate / letter with optional items
   ================================================================ */
(function () {
'use strict';
const X = window.KTX;
if (!X) return;
const { $, T, GU, KT, toast, copy, downloadBlob, waShareText, waShareFile, setActions, el, reg, renderCalc, logUsage } = X;

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const L = (o, k) => (GU ? (o[k + '_gu'] ?? o.gu ?? o[k] ?? o.en) : (o[k + '_en'] ?? o.en ?? o[k] ?? o.gu)) || '';
const fill = (tpl, v) => String(tpl).replace(/\{(\w+)\}/g, (_, k) => (v[k] ?? '') === '' ? '' : String(v[k]));

/* ---------- formula helpers (available inside every expression) ---------- */
const H = {
  round: (x, n = 0) => { const p = Math.pow(10, n); return Math.round((+x + Number.EPSILON) * p) / p; },
  ceil: Math.ceil, floor: Math.floor, min: Math.min, max: Math.max, abs: Math.abs, pow: Math.pow,
  sqrt: Math.sqrt, log: Math.log, log10: Math.log10, exp: Math.exp, PI: Math.PI,
  sin: Math.sin, cos: Math.cos, tan: Math.tan, atan: Math.atan,
  rad: d => d * Math.PI / 180, deg: r => r * 180 / Math.PI,
  // EMI / annuity payment for principal p, annual rate r%, n months
  emi: (p, r, n) => { const i = r / 1200; return i ? p * i * Math.pow(1 + i, n) / (Math.pow(1 + i, n) - 1) : p / n; },
  // future value of a monthly SIP
  sip: (m, r, n) => { const i = r / 1200; return i ? m * ((Math.pow(1 + i, n) - 1) / i) * (1 + i) : m * n; },
  // days between two yyyy-mm-dd strings (b - a)
  days: (a, b) => { const d1 = new Date(a), d2 = new Date(b); return (isNaN(d1) || isNaN(d2)) ? NaN : Math.round((d2 - d1) / 864e5); },
  addDays: (a, n) => { const d = new Date(a); if (isNaN(d)) return '—'; d.setDate(d.getDate() + Math.round(n)); return d.toLocaleDateString(GU ? 'gu-IN' : 'en-IN', { day: 'numeric', month: 'long', year: 'numeric' }); },
  today: () => new Date().toISOString().slice(0, 10),
  inr: x => '₹' + (+x).toLocaleString('en-IN', { maximumFractionDigits: 2 }),
  fix: (x, n = 2) => (+x).toFixed(n),
  pick: (cond, a, b) => (cond ? a : b),
  T: (gu, en) => (GU ? gu : en),
};
const _fcache = {};
function compile(expr) {
  if (!_fcache[expr]) {
    // eslint-disable-next-line no-new-func
    _fcache[expr] = new Function('v', 'H', 'with (H) { return (' + expr + '); }');
  }
  return _fcache[expr];
}
function fmtOut(val, o) {
  if (val === undefined || val === null || (typeof val === 'number' && !isFinite(val))) return '—';
  if (typeof val !== 'number') return esc(val) + (o.u ? ' ' + esc(o.u) : '');
  const d = o.d ?? 2;
  let s;
  switch (o.fmt) {
    case 'inr': s = '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: d }); break;
    case 'int': s = Math.round(val).toLocaleString('en-IN'); break;
    case 'pct': s = val.toFixed(d) + '%'; break;
    default: s = val.toLocaleString('en-IN', { maximumFractionDigits: d });
  }
  return s + (o.u ? ' ' + esc(GU && o.u_gu ? o.u_gu : o.u) : '');
}
function toFields(inputs) {
  return (inputs || []).map(i => {
    const f = { k: i.k, label: esc(L(i, 'label')), val: i.v, unit: i.u ? esc(GU && i.u_gu ? i.u_gu : i.u) : '', step: 'any', full: !!i.full };
    if (i.opts) { f.type = 'select'; f.opts = i.opts.map(o => ({ v: o[0], t: esc(GU ? (o[2] || o[1]) : o[1]) })); }
    else f.type = i.t || 'number';
    if (f.type === 'date' && (f.val === 'today' || !f.val)) f.val = H.today();
    return f;
  });
}
function normalise(inputs, v) {
  const out = Object.assign({}, v);
  (inputs || []).forEach(i => { if (i.opts && out[i.k] !== '' && !isNaN(+out[i.k])) out[i.k] = +out[i.k]; });
  return out;
}

/* ================= formula ================= */
reg('formula', function (mount, opt, ctx) {
  renderCalc(mount, {
    title: document.querySelector('h1') ? document.querySelector('h1').textContent.trim() : '',
    fields: toFields(opt.in),
    compute: raw => {
      const v = normalise(opt.in, raw);
      return (opt.out || []).map(o => {
        let val;
        try { val = compile(o.f)(v, H); } catch (e) { val = '—'; }
        return { label: esc(L(o, 'label')), value: fmtOut(val, o), big: !!o.big, note: o.note_en ? esc(L(o, 'note')) : '' };
      });
    },
  }, ctx);
});

/* ================= textgen ================= */
reg('textgen', function (mount, opt, ctx) {
  mount.innerHTML = '';
  const fields = toFields(opt.in);
  const hasGu = (opt.tpl_gu || []).length, hasEn = (opt.tpl_en || []).length;
  const form = el('<div class="grid sm:grid-cols-2 gap-4"></div>');
  fields.forEach(f => {
    const id = 'f_' + f.k;
    const ctrl = f.type === 'select'
      ? `<select class="kt-select" id="${id}">${f.opts.map(o => `<option value="${esc(o.v)}" ${o.v == f.val ? 'selected' : ''}>${o.t}</option>`).join('')}</select>`
      : f.type === 'textarea' ? `<textarea class="kt-input" id="${id}" rows="3">${esc(f.val || '')}</textarea>`
      : `<input class="kt-input" id="${id}" type="${f.type === 'number' ? 'number' : (f.type || 'text')}" value="${esc(f.val ?? '')}">`;
    form.appendChild(el(`<div><label class="kt-label" for="${id}">${f.label}</label>${ctrl}</div>`));
  });
  if (hasGu && hasEn) {
    form.appendChild(el(`<div><label class="kt-label" for="f__lang">${T('ભાષા', 'Language')}</label>
      <select class="kt-select" id="f__lang"><option value="gu" ${GU ? 'selected' : ''}>ગુજરાતી</option><option value="en" ${GU ? '' : 'selected'}>English</option></select></div>`));
  }
  mount.appendChild(form);
  const out = el('<div class="mt-5 grid gap-3"></div>'); mount.appendChild(out);
  let all = '', lastLog = 0;
  const used = () => { if (ctx.toolId && Date.now() - lastLog > 30000) { lastLog = Date.now(); logUsage(ctx.toolId); } };
  function run() {
    const v = {};
    fields.forEach(f => { const n = $('#f_' + f.k); v[f.k] = n ? n.value.trim() : ''; });
    const lang = $('#f__lang') ? $('#f__lang').value : (hasGu ? 'gu' : 'en');
    const list = (lang === 'gu' ? opt.tpl_gu : opt.tpl_en) || [];
    const texts = list.map(t => fill(t, v).replace(/[ \t]+\n/g, '\n').replace(/ {2,}/g, ' ').trim());
    all = texts.join('\n\n');
    out.innerHTML = `<h3 class="section-title text-lg">${T('તૈયાર લખાણ', 'Ready-to-use text')} <span class="text-sm opacity-60">(${texts.length})</span></h3>` +
      texts.map((t, i) => `<div class="kt-card p-4"><div style="white-space:pre-wrap">${esc(t)}</div>
        <div class="flex gap-2 mt-3"><button type="button" class="btn btn-ghost !py-1 text-sm" data-c="${i}">${T('કોપી', 'Copy')}</button>
        <button type="button" class="btn btn-wa !py-1 text-sm" data-w="${i}">WhatsApp</button></div></div>`).join('');
    out.querySelectorAll('[data-c]').forEach(b => b.onclick = () => { copy(texts[+b.dataset.c]); used(); });
    out.querySelectorAll('[data-w]').forEach(b => b.onclick = () => { waShareText(texts[+b.dataset.w]); used(); });
  }
  form.addEventListener('input', run); form.addEventListener('change', run);
  setActions([
    { label: T('બનાવો', 'Generate'), cls: 'btn-primary', icon: 'sparkles', onClick: () => { run(); used(); } },
    { label: T('બધું કોપી', 'Copy all'), icon: 'copy', onClick: () => { copy(all); used(); } },
  ]);
  run();
});

/* ================= card ================= */
const SIZES = { square: [1080, 1080], story: [1080, 1920], wide: [1280, 720], a4: [1240, 1754] };
function wrapLines(c, text, maxW) {
  const out = [];
  String(text).split('\n').forEach(par => {
    let line = '';
    par.split(' ').forEach(w => {
      const test = line ? line + ' ' + w : w;
      if (c.measureText(test).width > maxW && line) { out.push(line); line = w; } else line = test;
    });
    out.push(line);
  });
  return out;
}
reg('card', function (mount, opt, ctx) {
  mount.innerHTML = '';
  const cfg = opt.card || {};
  const fields = toFields(opt.in);
  const form = el('<div class="grid sm:grid-cols-2 gap-4"></div>');
  fields.forEach(f => {
    const id = 'f_' + f.k;
    const ctrl = f.type === 'select'
      ? `<select class="kt-select" id="${id}">${f.opts.map(o => `<option value="${esc(o.v)}" ${o.v == f.val ? 'selected' : ''}>${o.t}</option>`).join('')}</select>`
      : f.type === 'textarea' ? `<textarea class="kt-input" id="${id}" rows="3">${esc(f.val || '')}</textarea>`
      : `<input class="kt-input" id="${id}" type="${f.type === 'number' ? 'number' : (f.type === 'date' ? 'date' : 'text')}" value="${esc(f.val ?? '')}">`;
    form.appendChild(el(`<div><label class="kt-label" for="${id}">${f.label}</label>${ctrl}</div>`));
  });
  const bg = cfg.bg || ['#1E3A8A', '#0E7490'];
  form.appendChild(el(`<div><label class="kt-label" for="f__size">${T('સાઇઝ', 'Size')}</label><select class="kt-select" id="f__size">
    <option value="square">${T('ચોરસ (Instagram/WhatsApp)', 'Square (Instagram/WhatsApp)')}</option>
    <option value="story">${T('સ્ટોરી / સ્ટેટસ', 'Story / Status')}</option>
    <option value="wide">${T('પહોળું (YouTube/Facebook)', 'Wide (YouTube/Facebook)')}</option>
    <option value="a4">A4 ${T('પોસ્ટર', 'poster')}</option></select></div>`));
  form.appendChild(el(`<div class="flex gap-3 items-end"><div><label class="kt-label" for="f__c1">${T('રંગ 1', 'Colour 1')}</label><input type="color" id="f__c1" value="${bg[0]}" class="h-10 w-16"></div>
    <div><label class="kt-label" for="f__c2">${T('રંગ 2', 'Colour 2')}</label><input type="color" id="f__c2" value="${bg[1] || bg[0]}" class="h-10 w-16"></div></div>`));
  if (cfg.size) $('#f__size', form).value = cfg.size;
  mount.appendChild(form);
  const canvas = document.createElement('canvas');
  const wrap = el('<div class="mt-5 text-center"></div>');
  canvas.style.cssText = 'max-width:100%;max-height:520px;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,.18)';
  wrap.appendChild(canvas); mount.appendChild(wrap);
  const fontFam = '"Baloo Bhai 2", "Noto Sans Gujarati", Inter, sans-serif';
  function draw() {
    const v = {};
    fields.forEach(f => { const n = $('#f_' + f.k); v[f.k] = n ? n.value.trim() : ''; });
    const [W, Hh] = SIZES[$('#f__size').value] || SIZES.square;
    canvas.width = W; canvas.height = Hh;
    const c = canvas.getContext('2d');
    const g = c.createLinearGradient(0, 0, W, Hh);
    g.addColorStop(0, $('#f__c1').value); g.addColorStop(1, $('#f__c2').value);
    c.fillStyle = g; c.fillRect(0, 0, W, Hh);
    // soft decorative circles
    c.globalAlpha = 0.12; c.fillStyle = '#fff';
    [[0.12, 0.1, 0.22], [0.9, 0.18, 0.16], [0.82, 0.92, 0.26], [0.1, 0.85, 0.14]].forEach(([x, y, r]) => { c.beginPath(); c.arc(W * x, Hh * y, Math.min(W, Hh) * r, 0, 7); c.fill(); });
    c.globalAlpha = 1;
    // inner border
    c.strokeStyle = 'rgba(255,255,255,.55)'; c.lineWidth = Math.max(4, W / 270);
    c.strokeRect(W * 0.04, W * 0.04, W - W * 0.08, Hh - W * 0.08);
    const fg = cfg.fg || '#FFFFFF';
    c.fillStyle = fg; c.textAlign = 'center'; c.textBaseline = 'middle';
    const lines = (cfg.lines || []).map(l => ({ text: fill(GU ? (l.gu ?? l.t) : (l.en ?? l.t), v).trim(), s: l.s || 1, b: l.b !== false }))
      .filter(l => l.text);
    const emoji = cfg.emoji || '';
    const base = Math.min(W, Hh) / 14;
    const maxW = W * 0.82;
    // measure total height
    const blocks = [];
    if (emoji) blocks.push({ text: emoji, size: base * 2.4, lines: [emoji], gap: base * 0.5, emoji: true });
    lines.forEach(l => {
      const size = base * l.s;
      c.font = `${l.b ? 800 : 500} ${size}px ${fontFam}`;
      blocks.push({ size, b: l.b, lines: wrapLines(c, l.text, maxW), gap: size * 0.45 });
    });
    const total = blocks.reduce((s, b) => s + b.lines.length * b.size * 1.2 + b.gap, 0);
    let y = (Hh - total) / 2;
    blocks.forEach(b => {
      c.font = b.emoji ? `${b.size}px sans-serif` : `${b.b ? 800 : 500} ${b.size}px ${fontFam}`;
      c.shadowColor = 'rgba(0,0,0,.25)'; c.shadowBlur = b.size / 8;
      b.lines.forEach(t => { y += b.size * 0.6; c.fillText(t, W / 2, y); y += b.size * 0.6; });
      c.shadowBlur = 0; y += b.gap;
    });
    if (cfg.footer !== false) {
      c.font = `500 ${base * 0.42}px ${fontFam}`; c.globalAlpha = 0.75;
      c.fillText(fill(GU ? (cfg.footer_gu || '') : (cfg.footer_en || ''), v) || 'tools.akdwk.in', W / 2, Hh - W * 0.075);
      c.globalAlpha = 1;
    }
  }
  const redraw = () => (document.fonts && document.fonts.ready ? document.fonts.ready.then(draw) : draw());
  form.addEventListener('input', redraw); form.addEventListener('change', redraw);
  const blob = () => new Promise(r => canvas.toBlob(r, 'image/png'));
  const fname = () => (opt.slug || 'card') + '.png';
  setActions([
    { label: T('ડાઉનલોડ PNG', 'Download PNG'), cls: 'btn-cta', icon: 'download', onClick: async () => { downloadBlob(await blob(), fname()); if (ctx.toolId) logUsage(ctx.toolId); } },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: async () => waShareFile(await blob(), fname(), '') },
  ]);
  redraw();
});

/* ================= doc (bill / estimate / letter) ================= */
function bizLoad() { try { return JSON.parse(localStorage.getItem('kt-biz') || '{}'); } catch (e) { return {}; } }
function bizSave(b) { try { localStorage.setItem('kt-biz', JSON.stringify(b)); } catch (e) {} }
reg('doc', function (mount, opt, ctx) {
  mount.innerHTML = '';
  const d = opt.doc || {};
  const biz = bizLoad();
  const bizFields = d.nobiz ? [] : [
    { k: '_bn', label: T('ધંધા / સંસ્થાનું નામ', 'Business / organisation name'), val: biz._bn || '' },
    { k: '_ba', label: T('સરનામું', 'Address'), val: biz._ba || '' },
    { k: '_bp', label: T('ફોન', 'Phone'), val: biz._bp || '' },
    { k: '_bg', label: T('GSTIN (વૈકલ્પિક)', 'GSTIN (optional)'), val: biz._bg || '' },
  ];
  const meta = toFields(opt.in);
  const form = el('<div class="grid sm:grid-cols-2 gap-4"></div>');
  const ctrl = f => {
    const id = 'f_' + f.k;
    if (f.type === 'select') return `<select class="kt-select" id="${id}">${f.opts.map(o => `<option value="${esc(o.v)}" ${o.v == f.val ? 'selected' : ''}>${o.t}</option>`).join('')}</select>`;
    if (f.type === 'textarea') return `<textarea class="kt-input" id="${id}" rows="3">${esc(f.val || '')}</textarea>`;
    return `<input class="kt-input" id="${id}" type="${f.type === 'date' ? 'date' : (f.type === 'number' ? 'number' : 'text')}" value="${esc(f.val ?? '')}" ${f.type === 'number' ? 'step="any"' : ''}>`;
  };
  [...bizFields, ...meta].forEach(f => form.appendChild(el(`<div class="${f.full || f.type === 'textarea' ? 'sm:col-span-2' : ''}"><label class="kt-label" for="f_${f.k}">${f.label}</label>${ctrl(f)}</div>`)));
  mount.appendChild(form);

  let items = [];
  const itemsBox = el('<div class="mt-5"></div>');
  if (d.items) {
    const ic = d.items;
    items = (ic.rows || [['', 1, 0]]).map(r => ({ d: GU && r[3] ? r[3] : r[0], q: r[1], r: r[2] }));
    mount.appendChild(itemsBox);
  }
  const prev = el('<div class="mt-5 kt-card p-0 overflow-auto"></div>'); mount.appendChild(prev);

  function renderItems() {
    if (!d.items) return;
    const ic = d.items;
    itemsBox.innerHTML = `<div class="kt-label">${esc(T(ic.title_gu || 'વિગત', ic.title_en || 'Items'))}</div>` +
      items.map((it, i) => `<div class="grid grid-cols-12 gap-2 mb-2">
        <input class="kt-input col-span-6" data-i="${i}" data-f="d" value="${esc(it.d)}" placeholder="${esc(T(ic.desc_gu || 'વિગત', ic.desc_en || 'Description'))}">
        <input class="kt-input col-span-2" data-i="${i}" data-f="q" type="number" step="any" value="${it.q}" placeholder="${esc(T(ic.qty_gu || 'જથ્થો', ic.qty_en || 'Qty'))}">
        <input class="kt-input col-span-3" data-i="${i}" data-f="r" type="number" step="any" value="${it.r}" placeholder="${esc(T(ic.rate_gu || 'ભાવ', ic.rate_en || 'Rate'))}">
        <button type="button" class="btn btn-ghost col-span-1 !px-0" data-del="${i}" aria-label="Remove">✕</button></div>`).join('') +
      `<button type="button" class="btn btn-ghost text-sm" id="addRow">+ ${T('લાઇન ઉમેરો', 'Add line')}</button>`;
    itemsBox.querySelectorAll('input').forEach(n => n.oninput = () => { const it = items[+n.dataset.i]; it[n.dataset.f] = n.dataset.f === 'd' ? n.value : (parseFloat(n.value) || 0); render(); });
    itemsBox.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { items.splice(+b.dataset.del, 1); renderItems(); render(); });
    $('#addRow', itemsBox).onclick = () => { items.push({ d: '', q: 1, r: 0 }); renderItems(); render(); };
  }

  let html = '', plain = '';
  function render() {
    const v = {};
    [...bizFields, ...meta].forEach(f => { const n = $('#f_' + f.k); v[f.k] = n ? n.value.trim() : ''; });
    if (bizFields.length) bizSave({ _bn: v._bn, _ba: v._ba, _bp: v._bp, _bg: v._bg });
    const title = esc(T(d.title_gu || '', d.title_en || ''));
    const metaRows = meta.filter(f => !f.hide && v[f.k] !== '' && f.type !== 'textarea' && !(d.tax_key && f.k === d.tax_key) && !(d.disc_key && f.k === d.disc_key))
      .map(f => `<tr><td style="color:#555;padding:2px 12px 2px 0">${f.label}</td><td style="padding:2px 0"><b>${esc(v[f.k])}</b></td></tr>`).join('');
    let table = '', totals = '', total = 0;
    if (d.items) {
      const sub = items.reduce((s, it) => s + (+it.q || 0) * (+it.r || 0), 0);
      const disc = d.disc_key ? sub * (parseFloat(v[d.disc_key]) || 0) / 100 : 0;
      const taxp = d.tax_key ? (parseFloat(v[d.tax_key]) || 0) : 0;
      const tax = (sub - disc) * taxp / 100;
      total = sub - disc + tax;
      const m = x => '₹' + x.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      const ic = d.items;
      table = `<table style="width:100%;border-collapse:collapse;margin-top:14px;font-size:14px">
        <tr style="background:#1E3A8A;color:#fff"><th style="padding:6px;text-align:left">#</th><th style="padding:6px;text-align:left">${esc(T(ic.desc_gu || 'વિગત', ic.desc_en || 'Description'))}</th><th style="padding:6px;text-align:right">${esc(T(ic.qty_gu || 'જથ્થો', ic.qty_en || 'Qty'))}</th><th style="padding:6px;text-align:right">${esc(T(ic.rate_gu || 'ભાવ', ic.rate_en || 'Rate'))}</th><th style="padding:6px;text-align:right">${T('રકમ', 'Amount')}</th></tr>` +
        items.filter(it => it.d || it.r).map((it, i) => `<tr style="border-bottom:1px solid #ddd"><td style="padding:6px">${i + 1}</td><td style="padding:6px">${esc(it.d)}</td><td style="padding:6px;text-align:right">${it.q}</td><td style="padding:6px;text-align:right">${m(+it.r || 0)}</td><td style="padding:6px;text-align:right">${m((+it.q || 0) * (+it.r || 0))}</td></tr>`).join('') + '</table>';
      totals = `<table style="margin-left:auto;margin-top:10px;font-size:14px">
        <tr><td style="padding:2px 16px">${T('પેટા સરવાળો', 'Subtotal')}</td><td style="text-align:right">${m(sub)}</td></tr>
        ${disc ? `<tr><td style="padding:2px 16px">${T('ડિસ્કાઉન્ટ', 'Discount')} (${esc(v[d.disc_key])}%)</td><td style="text-align:right">−${m(disc)}</td></tr>` : ''}
        ${tax ? `<tr><td style="padding:2px 16px">${esc(T(d.tax_gu || 'ટેક્સ', d.tax_en || 'Tax'))} (${taxp}%)</td><td style="text-align:right">${m(tax)}</td></tr>` : ''}
        <tr style="font-size:17px"><td style="padding:6px 16px"><b>${T('કુલ', 'Total')}</b></td><td style="text-align:right"><b>${m(total)}</b></td></tr></table>`;
    }
    const body = (GU ? d.body_gu : d.body_en) || d.body_en || '';
    const bodyHtml = body ? `<div style="white-space:pre-wrap;margin-top:16px;line-height:1.7;font-size:15px">${esc(fill(body, v))}</div>` : '';
    const notes = meta.filter(f => f.type === 'textarea' && v[f.k]).map(f => `<div style="margin-top:12px;font-size:13px"><b>${f.label}:</b> <span style="white-space:pre-wrap">${esc(v[f.k])}</span></div>`).join('');
    const foot = (GU ? d.foot_gu : d.foot_en) || '';
    html = `<div style="font-family:'Hind Vadodara','Noto Sans Gujarati',Arial,sans-serif;color:#111;background:#fff;padding:28px;max-width:800px;margin:auto">
      ${bizFields.length ? `<div style="display:flex;justify-content:space-between;gap:16px;border-bottom:3px solid #1E3A8A;padding-bottom:10px">
        <div><div style="font-size:22px;font-weight:800;color:#1E3A8A">${esc(v._bn || T('તમારા ધંધાનું નામ', 'Your business name'))}</div>
        <div style="font-size:13px;color:#444">${esc(v._ba)}${v._bp ? ' · 📞 ' + esc(v._bp) : ''}${v._bg ? '<br>GSTIN: ' + esc(v._bg) : ''}</div></div>
        <div style="text-align:right;font-size:20px;font-weight:800;color:#0E7490">${title}</div></div>`
        : `<div style="text-align:center;font-size:22px;font-weight:800;color:#1E3A8A;border-bottom:3px solid #1E3A8A;padding-bottom:8px">${title}</div>`}
      ${metaRows ? `<table style="margin-top:12px;font-size:14px">${metaRows}</table>` : ''}
      ${bodyHtml}${table}${totals}${notes}
      ${foot ? `<div style="margin-top:20px;font-size:12px;color:#555;white-space:pre-wrap">${esc(fill(foot, v))}</div>` : ''}
      ${d.sign !== false ? `<div style="margin-top:40px;text-align:right;font-size:13px">______________________<br>${T('સહી', 'Signature')}</div>` : ''}
    </div>`;
    prev.innerHTML = html;
    const tmp = document.createElement('div'); tmp.innerHTML = html; plain = tmp.innerText;
  }
  form.addEventListener('input', render); form.addEventListener('change', render);
  function printIt() {
    const w = window.open('', '_blank');
    if (!w) { toast(T('Pop-up બ્લોક છે. મંજૂરી આપો.', 'Pop-up blocked. Please allow pop-ups.'), true); return; }
    w.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${esc(T(d.title_gu || '', d.title_en || ''))}</title>
      <link href="https://fonts.googleapis.com/css2?family=Hind+Vadodara:wght@400;700&display=swap" rel="stylesheet">
      <style>body{margin:0}@page{margin:12mm}</style></head><body>${html}<script>setTimeout(function(){print()},600)<\/script></body></html>`);
    w.document.close();
    if (ctx.toolId) logUsage(ctx.toolId);
  }
  setActions([
    { label: T('પ્રિન્ટ / PDF', 'Print / PDF'), cls: 'btn-cta', icon: 'printer', onClick: printIt },
    { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => copy(plain) },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(plain) },
  ]);
  renderItems(); render();
});
})();
