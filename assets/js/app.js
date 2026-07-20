/* ================================================================
   KRISHNA TOOLS — front-end engine (Vanilla JS, no build step).
   Core utilities + global search + theme + a declarative calculator
   framework + concrete file/generator engines. Every tool page mounts
   one engine by #toolMount[data-engine].
   ================================================================ */
(function () {
'use strict';
const KT = window.KT || { url: '', lang: 'gu', csrf: '', loggedIn: false };
const GU = KT.lang === 'gu';
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const T = (gu, en) => (GU ? gu : en);

/* ---------- Toast ---------- */
function toast(msg, isErr) {
  const el = $('#ktToast'); if (!el) return alert(msg);
  el.textContent = msg; el.classList.toggle('err', !!isErr); el.classList.add('show');
  clearTimeout(el._t); el._t = setTimeout(() => el.classList.remove('show'), 3000);
}
window.ktToast = toast;

/* ---------- Theme ---------- */
window.ktToggleTheme = function () {
  const root = document.documentElement;
  const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  root.setAttribute('data-theme', next); localStorage.setItem('kt-theme', next);
};

/* ---------- Clipboard / download ---------- */
function copy(text) {
  navigator.clipboard.writeText(text).then(
    () => toast(T('કોપી થયું!', 'Copied!')),
    () => toast(T('કોપી નિષ્ફળ', 'Copy failed'), true));
}
function downloadBlob(blob, name) {
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob); a.download = name;
  document.body.appendChild(a); a.click(); a.remove();
  setTimeout(() => URL.revokeObjectURL(a.href), 4000);
}
function downloadDataUrl(url, name) {
  const a = document.createElement('a'); a.href = url; a.download = name;
  document.body.appendChild(a); a.click(); a.remove();
}
window.ktCopy = copy; window.ktDownloadBlob = downloadBlob;

/* ---------- Lazy CDN loader ---------- */
const CDN = {
  jspdf: 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
  pdflib: 'https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js',
  pdfjs: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
  jszip: 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
  xlsx: 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
  imgcomp: 'https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js',
  cropper: 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js',
  cropperCss: 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css',
  qrcode: 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
  qrscan: 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
  jsbarcode: 'https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js',
  marked: 'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
  heic: 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js',
  exif: 'https://cdn.jsdelivr.net/npm/exifr@7.1.3/dist/full.umd.js',
  chart: 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
};
const _loaded = {};
function loadScript(url) {
  if (_loaded[url]) return _loaded[url];
  _loaded[url] = new Promise((res, rej) => {
    const s = document.createElement('script'); s.src = url; s.async = true;
    s.onload = res; s.onerror = () => rej(new Error('load ' + url));
    document.head.appendChild(s);
  });
  return _loaded[url];
}
function loadCss(url) {
  if (_loaded['css:' + url]) return;
  _loaded['css:' + url] = true;
  const l = document.createElement('link'); l.rel = 'stylesheet'; l.href = url;
  document.head.appendChild(l);
}
async function need(...keys) { for (const k of keys) await loadScript(CDN[k]); }

/* ---------- Usage log (fire and forget) ---------- */
function logUsage(toolId) {
  if (!toolId) return;
  fetch(KT.url + '/api/tool_log.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': KT.csrf },
    body: JSON.stringify({ tool_id: toolId }),
  }).catch(() => {});
}

/* ---------- WhatsApp share ---------- */
function waShareText(text) {
  if (KT.loggedIn) {
    const num = prompt(T('WhatsApp નંબર (91XXXXXXXXXX):', 'WhatsApp number (91XXXXXXXXXX):'));
    if (!num) return;
    fetch(KT.url + '/api/share.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': KT.csrf },
      body: JSON.stringify({ number: num, message: text }),
    }).then(r => r.json()).then(d => toast(d.ok ? T('મોકલ્યું! 🦚', 'Sent! 🦚') : (d.error || 'Failed'), !d.ok))
      .catch(() => window.open('https://wa.me/?text=' + encodeURIComponent(text)));
  } else {
    window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
  }
}
async function waShareFile(blob, filename, caption) {
  if (!KT.loggedIn) { toast(T('ફાઇલ શેર માટે લોગિન કરો', 'Login to share files'), true); return; }
  const num = prompt(T('WhatsApp નંબર (91XXXXXXXXXX):', 'WhatsApp number (91XXXXXXXXXX):'));
  if (!num) return;
  const fd = new FormData();
  fd.append('file', blob, filename); fd.append('number', num); fd.append('message', caption || '');
  toast(T('મોકલી રહ્યા છીએ...', 'Sending...'));
  try {
    const r = await fetch(KT.url + '/api/share.php', { method: 'POST', headers: { 'X-CSRF': KT.csrf }, body: fd });
    const d = await r.json();
    toast(d.ok ? T('મોકલ્યું! 🦚', 'Sent! 🦚') : (d.error || 'Failed'), !d.ok);
  } catch (e) { toast('Failed', true); }
}
window.ktWaShareText = waShareText;

/* ---------- Action bar helper ---------- */
function setActions(buttons) {
  const bar = $('#toolActions'); if (!bar) return;
  bar.innerHTML = '';
  buttons.forEach(b => {
    if (!b) return;
    const btn = document.createElement('button');
    btn.className = 'btn ' + (b.cls || 'btn-ghost') + ' text-sm';
    btn.innerHTML = (b.icon ? `<i data-lucide="${b.icon}"></i>` : '') + b.label;
    btn.onclick = b.onClick; bar.appendChild(btn);
  });
  if (window.lucide) lucide.createIcons();
}

/* ---------- Small DOM builders ---------- */
function el(html) { const d = document.createElement('div'); d.innerHTML = html.trim(); return d.firstElementChild; }
function fileInput(mount, opt) {
  opt = opt || {};
  const accept = opt.accept || 'image/*';
  const wrap = el(`<div>
    <label class="kt-drop block">
      <input type="file" class="hidden" ${opt.multiple ? 'multiple' : ''} accept="${accept}">
      <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto opacity-60"></i>
      <div class="mt-2 font-semibold">${T('ફાઇલ અહીં ડ્રોપ કરો કે ક્લિક કરો', 'Drop file here or click')}</div>
      <div class="text-xs opacity-60 mt-1">${opt.hint || ''}</div>
    </label></div>`);
  mount.appendChild(wrap);
  const input = wrap.querySelector('input');
  const drop = wrap.querySelector('.kt-drop');
  ['dragover', 'dragenter'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.add('drag'); }));
  ['dragleave', 'drop'].forEach(e => drop.addEventListener(e, ev => { ev.preventDefault(); drop.classList.remove('drag'); }));
  drop.addEventListener('drop', ev => { input.files = ev.dataTransfer.files; input.dispatchEvent(new Event('change')); });
  if (window.lucide) lucide.createIcons();
  return input;
}
function checkMax(file, maxMb) {
  if (maxMb && file.size > maxMb * 1024 * 1024) {
    toast(T(`ફાઇલ ${maxMb}MB થી નાની હોવી જોઈએ`, `File must be under ${maxMb}MB`), true);
    return false;
  }
  return true;
}
function fmtBytes(n) {
  if (n < 1024) return n + ' B';
  if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
  return (n / 1048576).toFixed(2) + ' MB';
}

/* ================================================================
   DECLARATIVE CALCULATOR FRAMEWORK
   A calc spec: { fields:[{k,label,type,val,opts,step,unit}], compute(v)=>rows }
   compute returns an array of {label, value, big?} result rows (or a string).
   ================================================================ */
function renderCalc(mount, spec, ctx) {
  mount.innerHTML = '';
  const form = el('<div class="grid sm:grid-cols-2 gap-4"></div>');
  const state = {};
  (spec.fields || []).forEach(f => {
    state[f.k] = f.val !== undefined ? f.val : (f.type === 'number' ? 0 : '');
    const id = 'f_' + f.k;
    let ctrl;
    if (f.type === 'select') {
      ctrl = `<select class="kt-select" id="${id}">` +
        f.opts.map(o => `<option value="${o.v}" ${o.v == f.val ? 'selected' : ''}>${o.t}</option>`).join('') + '</select>';
    } else if (f.type === 'textarea') {
      ctrl = `<textarea class="kt-input" id="${id}" rows="${f.rows || 4}">${f.val || ''}</textarea>`;
    } else if (f.type === 'date') {
      ctrl = `<input type="date" class="kt-input" id="${id}" value="${f.val || ''}">`;
    } else if (f.type === 'checkbox') {
      ctrl = `<label class="flex items-center gap-2 mt-2"><input type="checkbox" id="${id}" ${f.val ? 'checked' : ''}> <span>${f.cbLabel || ''}</span></label>`;
    } else {
      ctrl = `<input type="${f.type || 'number'}" class="kt-input" id="${id}" value="${f.val ?? ''}" ${f.step ? 'step=' + f.step : ''} ${f.min !== undefined ? 'min=' + f.min : ''} placeholder="${f.ph || ''}">`;
    }
    const col = el(`<div class="${f.full ? 'sm:col-span-2' : ''}">
      ${f.type === 'checkbox' ? '' : `<label class="kt-label" for="${id}">${f.label}${f.unit ? ' <span class="opacity-50">(' + f.unit + ')</span>' : ''}</label>`}
      ${ctrl}</div>`);
    form.appendChild(col);
  });
  mount.appendChild(form);
  const out = el('<div class="mt-5"></div>'); mount.appendChild(out);

  function readState() {
    (spec.fields || []).forEach(f => {
      const node = $('#f_' + f.k);
      if (!node) return;
      if (f.type === 'checkbox') state[f.k] = node.checked;
      else if (f.type === 'number') state[f.k] = parseFloat(node.value) || 0;
      else state[f.k] = node.value;
    });
  }
  function run() {
    readState();
    let rows;
    try { rows = spec.compute(state); }
    catch (e) { out.innerHTML = `<div class="text-red-500">${e.message}</div>`; return; }
    if (typeof rows === 'string') { out.innerHTML = `<div class="kt-card p-4">${rows}</div>`; }
    else if (Array.isArray(rows)) {
      out.innerHTML = `<h3 class="section-title text-lg mb-3">${T('પરિણામ', 'Result')}</h3>
        <div class="grid sm:grid-cols-2 gap-3">` + rows.map(r => `
          <div class="kt-card p-4 ${r.big ? 'sm:col-span-2' : ''}" style="${r.big ? 'background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))' : ''}">
            <div class="text-xs opacity-60">${r.label}</div>
            <div class="font-extrabold ${r.big ? 'text-2xl' : 'text-lg'}" style="color:var(--peacock-teal)">${r.value}</div>
            ${r.note ? `<div class="text-xs opacity-60 mt-1">${r.note}</div>` : ''}
          </div>`).join('') + '</div>';
    }
    const txt = Array.isArray(rows) ? rows.map(r => `${r.label}: ${String(r.value).replace(/<[^>]+>/g, '')}`).join('\n') : String(rows).replace(/<[^>]+>/g, '');
    ctx._resultText = (spec.title || '') + '\n' + txt + '\n\n' + KT.url;
    if (ctx.toolId) logUsage(ctx.toolId);
    if (window.lucide) lucide.createIcons();
  }
  // Auto-run on input + a Calculate button.
  form.addEventListener('input', () => { if (spec.live !== false) run(); });
  setActions([
    { label: T('ગણો', 'Calculate'), cls: 'btn-primary', icon: 'calculator', onClick: run },
    { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => copy(ctx._resultText || '') },
    { label: T('WhatsApp', 'WhatsApp'), cls: 'btn-wa', icon: 'send', onClick: () => waShareText(ctx._resultText || '') },
    { label: T('પ્રિન્ટ', 'Print'), icon: 'printer', onClick: () => window.print() },
  ]);
  if (spec.autorun !== false) run();
}

/* ================================================================
   ENGINE REGISTRY
   ================================================================ */
const Engines = {};
function reg(name, fn) { Engines[name] = fn; }
/* Register a calculator engine from a spec factory (opt-aware). */
function regCalc(name, specFn) { reg(name, (mount, opt, ctx) => renderCalc(mount, specFn(opt), ctx)); }

/* ---------- Codec bitrate table (kbps) for CCTV, by MP & codec ---------- */
const MP_KBPS = { // approximate H.264 CBR kbps at 15fps
  '1': 2048, '2': 4096, '3': 6144, '4': 8192, '5': 10240, '8': 16384, '12': 24576,
};
function cctvBitrate(mp, codec, fps) {
  let k = (MP_KBPS[mp] || 4096) * (fps / 15);
  if (codec === 'h265') k *= 0.5;
  else if (codec === 'h265+') k *= 0.4;
  return k; // kbps
}

/* ================= CCTV CALCULATORS ================= */
regCalc('cctv-storage', () => ({
  title: T('CCTV સ્ટોરેજ', 'CCTV Storage'),
  fields: [
    { k: 'cams', label: T('કેમેરા સંખ્યા', 'Cameras'), type: 'number', val: 4, min: 1 },
    { k: 'mp', label: T('રિઝોલ્યુશન', 'Resolution'), type: 'select', val: '2', opts: [
      { v: '1', t: '1MP (720p)' }, { v: '2', t: '2MP (1080p)' }, { v: '3', t: '3MP' }, { v: '4', t: '4MP' }, { v: '5', t: '5MP' }, { v: '8', t: '8MP (4K)' }, { v: '12', t: '12MP' }] },
    { k: 'codec', label: T('કોડેક', 'Codec'), type: 'select', val: 'h265', opts: [
      { v: 'h264', t: 'H.264' }, { v: 'h265', t: 'H.265' }, { v: 'h265+', t: 'H.265+' }] },
    { k: 'fps', label: 'FPS', type: 'number', val: 15, min: 1 },
    { k: 'hours', label: T('કલાક/દિવસ', 'Hours/day'), type: 'number', val: 24, min: 1 },
    { k: 'days', label: T('રીટેન્શન દિવસ', 'Retention days'), type: 'number', val: 30, min: 1 },
    { k: 'audio', label: '', type: 'checkbox', cbLabel: T('ઓડિયો ચાલુ', 'Audio on'), val: false },
  ],
  compute: v => {
    let kbps = cctvBitrate(v.mp, v.codec, v.fps);
    if (v.audio) kbps += 64;
    const perCamGB = kbps * 1000 / 8 * 3600 * v.hours * v.days / 1e9;
    const totalGB = perCamGB * v.cams;
    const hdd = [1, 2, 4, 6, 8, 10, 12, 16].find(x => x * 1000 >= totalGB * 1.1) || Math.ceil(totalGB / 1000);
    return [
      { label: T('કુલ સ્ટોરેજ', 'Total storage'), value: (totalGB >= 1000 ? (totalGB / 1000).toFixed(2) + ' TB' : totalGB.toFixed(1) + ' GB'), big: true, note: T('~10% બફર સાથે HDD ભલામણ', 'HDD recommended with ~10% buffer') },
      { label: T('પ્રતિ કેમેરા/દિવસ', 'Per camera/day'), value: (perCamGB / v.days).toFixed(2) + ' GB' },
      { label: T('પ્રતિ કેમેરા બિટરેટ', 'Per-camera bitrate'), value: (kbps / 1000).toFixed(2) + ' Mbps' },
      { label: T('ભલામણ HDD', 'Recommended HDD'), value: hdd + ' TB', big: true },
    ];
  },
}));
regCalc('cctv-reverse', () => ({
  title: T('રિવર્સ સ્ટોરેજ', 'Reverse Storage'),
  fields: [
    { k: 'hdd', label: T('HDD સાઇઝ', 'HDD size'), unit: 'TB', type: 'number', val: 2, min: 0.5, step: '0.5' },
    { k: 'cams', label: T('કેમેરા', 'Cameras'), type: 'number', val: 4, min: 1 },
    { k: 'mp', label: T('રિઝોલ્યુશન', 'Resolution'), type: 'select', val: '2', opts: [
      { v: '1', t: '1MP' }, { v: '2', t: '2MP' }, { v: '4', t: '4MP' }, { v: '5', t: '5MP' }, { v: '8', t: '8MP' }] },
    { k: 'codec', label: T('કોડેક', 'Codec'), type: 'select', val: 'h265', opts: [{ v: 'h264', t: 'H.264' }, { v: 'h265', t: 'H.265' }, { v: 'h265+', t: 'H.265+' }] },
    { k: 'fps', label: 'FPS', type: 'number', val: 15 },
    { k: 'hours', label: T('કલાક/દિવસ', 'Hours/day'), type: 'number', val: 24 },
  ],
  compute: v => {
    const kbps = cctvBitrate(v.mp, v.codec, v.fps);
    const perCamGBday = kbps * 1000 / 8 * 3600 * v.hours / 1e9;
    const totalGBday = perCamGBday * v.cams;
    const days = (v.hdd * 1000 * 0.93) / totalGBday;
    return [
      { label: T('રેકોર્ડિંગ દિવસ', 'Recording days'), value: days.toFixed(1) + ' ' + T('દિવસ', 'days'), big: true },
      { label: T('પ્રતિ દિવસ વપરાશ', 'Usage per day'), value: totalGBday.toFixed(1) + ' GB' },
      { label: T('ઉપયોગી HDD', 'Usable HDD'), value: (v.hdd * 0.93).toFixed(2) + ' TB', note: '1TB≈931GB' },
    ];
  },
}));
regCalc('cctv-bandwidth', () => ({
  title: T('CCTV બેન્ડવિડ્થ', 'CCTV Bandwidth'),
  fields: [
    { k: 'cams', label: T('કેમેરા', 'Cameras'), type: 'number', val: 4 },
    { k: 'mp', label: T('રિઝોલ્યુશન', 'Resolution'), type: 'select', val: '2', opts: [{ v: '1', t: '1MP' }, { v: '2', t: '2MP' }, { v: '4', t: '4MP' }, { v: '8', t: '8MP' }] },
    { k: 'codec', label: T('કોડેક', 'Codec'), type: 'select', val: 'h265', opts: [{ v: 'h264', t: 'H.264' }, { v: 'h265', t: 'H.265' }] },
    { k: 'fps', label: 'FPS', type: 'number', val: 15 },
    { k: 'streams', label: T('એકસાથે સ્ટ્રીમ', 'Concurrent streams'), type: 'number', val: 1 },
  ],
  compute: v => {
    const perCam = cctvBitrate(v.mp, v.codec, v.fps) / 1000; // Mbps
    const total = perCam * v.cams * v.streams;
    return [
      { label: T('જરૂરી અપલોડ', 'Upload needed'), value: total.toFixed(1) + ' Mbps', big: true, note: T('રિમોટ વ્યૂ માટે', 'for remote viewing') },
      { label: T('પ્રતિ કેમેરા', 'Per camera'), value: perCam.toFixed(2) + ' Mbps' },
      { label: T('ભલામણ પ્લાન', 'Recommended plan'), value: Math.ceil(total * 1.3) + ' Mbps+' },
    ];
  },
}));
regCalc('cctv-lens', () => ({
  title: T('લેન્સ કેલ્ક', 'Lens Calc'),
  fields: [
    { k: 'dist', label: T('ટાર્ગેટ અંતર', 'Target distance'), unit: 'm', type: 'number', val: 10 },
    { k: 'width', label: T('દ્રશ્ય પહોળાઈ', 'Scene width'), unit: 'm', type: 'number', val: 4 },
    { k: 'sensor', label: T('સેન્સર', 'Sensor'), type: 'select', val: '4.8', opts: [{ v: '3.6', t: '1/3" (4.8mm)' }, { v: '4.8', t: '1/2.7" (5.4mm)' }, { v: '5.37', t: '1/2.8" (5.37mm)' }] },
  ],
  compute: v => {
    const sw = parseFloat(v.sensor);
    const focal = (sw * v.dist) / v.width;
    const fov = 2 * Math.atan(sw / (2 * focal)) * 180 / Math.PI;
    return [
      { label: T('જરૂરી લેન્સ', 'Required lens'), value: focal.toFixed(1) + ' mm', big: true },
      { label: T('દ્રશ્ય કોણ (FOV)', 'Field of view'), value: fov.toFixed(1) + '°' },
      { label: T('નજીકનું સ્ટાન્ડર્ડ', 'Nearest standard'), value: [2.8, 3.6, 6, 8, 12, 16, 25].reduce((a, b) => Math.abs(b - focal) < Math.abs(a - focal) ? b : a) + ' mm' },
    ];
  },
}));
regCalc('ppm-calc', () => ({
  title: 'PPM',
  fields: [
    { k: 'hres', label: T('હોરિઝોન્ટલ પિક્સેલ', 'Horizontal pixels'), type: 'number', val: 1920 },
    { k: 'width', label: T('દ્રશ્ય પહોળાઈ', 'Scene width'), unit: 'm', type: 'number', val: 4 },
  ],
  compute: v => {
    const ppm = v.hres / v.width;
    const grade = ppm >= 250 ? T('ઓળખ (Identify)', 'Identify') : ppm >= 125 ? T('પરખ (Recognize)', 'Recognize') : ppm >= 25 ? T('શોધ (Detect)', 'Detect') : T('અપૂરતું', 'Insufficient');
    return [
      { label: 'PPM', value: ppm.toFixed(0) + ' px/m', big: true, note: grade },
      { label: 'Detect (25 PPM)', value: (v.hres / 25).toFixed(1) + ' m ' + T('પહોળાઈ', 'width') },
      { label: 'Identify (250 PPM)', value: (v.hres / 250).toFixed(1) + ' m ' + T('પહોળાઈ', 'width') },
    ];
  },
}));
regCalc('poe-budget', () => ({
  title: 'PoE',
  fields: [
    { k: 'budget', label: T('સ્વિચ પાવર બજેટ', 'Switch power budget'), unit: 'W', type: 'number', val: 65 },
    { k: 'cams', label: T('કેમેરા સંખ્યા', 'Cameras'), type: 'number', val: 4 },
    { k: 'watt', label: T('પ્રતિ કેમેરા', 'Per camera'), unit: 'W', type: 'number', val: 6 },
  ],
  compute: v => {
    const used = v.cams * v.watt;
    const head = ((v.budget - used) / v.budget * 100);
    return [
      { label: T('વપરાયેલ પાવર', 'Used power'), value: used.toFixed(0) + ' W', big: true, note: head < 0 ? T('⚠️ બજેટ ઓળંગ્યું!', '⚠️ Over budget!') : T('ઠીક છે', 'OK') },
      { label: T('બાકી હેડરૂમ', 'Headroom'), value: head.toFixed(0) + '%' },
      { label: T('મહત્તમ કેમેરા', 'Max cameras'), value: Math.floor(v.budget / v.watt) },
    ];
  },
}));
regCalc('cable-drop', () => ({
  title: T('કેબલ ડ્રોપ', 'Cable Drop'),
  fields: [
    { k: 'type', label: T('કેબલ પ્રકાર', 'Cable type'), type: 'select', val: 'cat6', opts: [{ v: 'cat6', t: 'Cat6 (data)' }, { v: 'rg59', t: 'RG59 (video)' }, { v: 'power', t: '12V DC power' }] },
    { k: 'len', label: T('લંબાઈ', 'Length'), unit: 'm', type: 'number', val: 50 },
    { k: 'amp', label: T('કરંટ (પાવર માટે)', 'Current (for power)'), unit: 'A', type: 'number', val: 0.5, step: '0.1' },
  ],
  compute: v => {
    if (v.type === 'cat6') return [{ label: T('મહત્તમ અંતર', 'Max distance'), value: '100 m', big: true, note: v.len > 100 ? T('⚠️ ખૂબ લાંબુ, રિપીટર વાપરો', '⚠️ Too long, use a repeater') : T('ઠીક', 'OK') }];
    if (v.type === 'rg59') return [{ label: T('મહત્તમ અંતર', 'Max distance'), value: '300 m', big: true, note: v.len > 300 ? T('⚠️ ખૂબ લાંબુ', '⚠️ Too long') : T('ઠીક', 'OK') }];
    const R = 0.0175 * 2 * v.len / 1.5; // 1.5mm2 copper approx (both directions)
    const drop = v.amp * R;
    return [
      { label: T('વોલ્ટેજ ડ્રોપ', 'Voltage drop'), value: drop.toFixed(2) + ' V', big: true, note: (12 - drop) < 10.8 ? T('⚠️ કેમેરાને પૂરતું વોલ્ટ નહીં', '⚠️ Insufficient voltage') : T('ઠીક', 'OK') },
      { label: T('કેમેરા પર વોલ્ટ', 'Voltage at camera'), value: (12 - drop).toFixed(2) + ' V' },
    ];
  },
}));
regCalc('dvr-planner', () => ({
  title: 'DVR/NVR',
  fields: [
    { k: 'analog', label: T('એનાલોગ કેમેરા', 'Analog cameras'), type: 'number', val: 4 },
    { k: 'ip', label: T('IP કેમેરા', 'IP cameras'), type: 'number', val: 2 },
    { k: 'future', label: T('ભવિષ્ય માટે વધારાના', 'Future spare'), type: 'number', val: 2 },
  ],
  compute: v => {
    const total = v.analog + v.ip + v.future;
    const ch = [4, 8, 16, 32, 64].find(x => x >= total) || 64;
    const type = v.analog > 0 && v.ip > 0 ? T('હાઇબ્રિડ (XVR)', 'Hybrid (XVR)') : v.ip > 0 ? 'NVR' : 'DVR';
    return [
      { label: T('ભલામણ', 'Recommendation'), value: ch + '-CH ' + type, big: true },
      { label: T('કુલ ચેનલ જરૂરી', 'Total channels needed'), value: total },
      { label: T('વધારાની ચેનલ', 'Spare channels'), value: ch - (v.analog + v.ip) },
    ];
  },
}));
regCalc('ip-subnet', () => ({
  title: 'Subnet',
  live: false,
  fields: [
    { k: 'ip', label: 'IP', type: 'text', val: '192.168.1.10', full: true },
    { k: 'cidr', label: 'CIDR /', type: 'number', val: 24, min: 0, max: 32 },
  ],
  compute: v => {
    const parts = v.ip.split('.').map(Number);
    if (parts.length !== 4 || parts.some(p => isNaN(p) || p < 0 || p > 255)) throw new Error(T('અમાન્ય IP', 'Invalid IP'));
    const bits = 0xFFFFFFFF << (32 - v.cidr) >>> 0;
    const ipn = ((parts[0] << 24) | (parts[1] << 16) | (parts[2] << 8) | parts[3]) >>> 0;
    const net = (ipn & bits) >>> 0, bc = (net | (~bits >>> 0)) >>> 0;
    const toIp = n => [(n >>> 24) & 255, (n >>> 16) & 255, (n >>> 8) & 255, n & 255].join('.');
    const hosts = v.cidr >= 31 ? 0 : (Math.pow(2, 32 - v.cidr) - 2);
    return [
      { label: T('નેટવર્ક', 'Network'), value: toIp(net) },
      { label: T('બ્રોડકાસ્ટ', 'Broadcast'), value: toIp(bc) },
      { label: T('સબનેટ માસ્ક', 'Subnet mask'), value: toIp(bits) },
      { label: T('ઉપયોગી હોસ્ટ', 'Usable hosts'), value: hosts.toLocaleString(), big: true },
      { label: T('પ્રથમ હોસ્ટ', 'First host'), value: hosts ? toIp(net + 1) : '-' },
      { label: T('છેલ્લો હોસ્ટ', 'Last host'), value: hosts ? toIp(bc - 1) : '-' },
    ];
  },
}));
regCalc('ip-planner', () => ({
  title: T('IP પ્લાનર', 'IP Planner'), live: false,
  fields: [
    { k: 'start', label: T('શરૂ IP', 'Start IP'), type: 'text', val: '192.168.1.100' },
    { k: 'count', label: T('કેમેરા', 'Cameras'), type: 'number', val: 8 },
    { k: 'gw', label: T('ગેટવે', 'Gateway'), type: 'text', val: '192.168.1.1' },
  ],
  compute: v => {
    const p = v.start.split('.').map(Number);
    let base = (p[0] << 24 | p[1] << 16 | p[2] << 8 | p[3]) >>> 0;
    const toIp = n => [(n >>> 24) & 255, (n >>> 16) & 255, (n >>> 8) & 255, n & 255].join('.');
    let list = '';
    for (let i = 0; i < v.count; i++) list += `Cam ${i + 1}: ${toIp(base + i)}\n`;
    return `<div class="font-mono text-sm whitespace-pre-wrap">${list}</div><div class="mt-2 opacity-70">${T('ગેટવે', 'Gateway')}: ${v.gw} · Mask 255.255.255.0</div>`;
  },
}));
regCalc('time-sync', () => ({
  title: T('ટાઇમ સિંક', 'Time Sync'), live: false,
  fields: [
    { k: 'mode', label: T('મોડ', 'Mode'), type: 'select', val: 'epoch', opts: [{ v: 'epoch', t: 'Epoch → Date' }, { v: 'date', t: 'Date → Epoch' }, { v: 'drift', t: T('ડ્રિફ્ટ ગણો', 'Drift calc') }] },
    { k: 'epoch', label: 'Epoch', type: 'number', val: Math.floor(Date.now() / 1000) },
    { k: 'behind', label: T('પાછળ (સેકન્ડ)', 'Behind (seconds)'), type: 'number', val: 240 },
    { k: 'days', label: T('દિવસ', 'Days'), type: 'number', val: 30 },
  ],
  compute: v => {
    if (v.mode === 'epoch') return [{ label: 'IST', value: new Date(v.epoch * 1000).toLocaleString('en-IN', { timeZone: 'Asia/Kolkata' }), big: true }, { label: 'UTC', value: new Date(v.epoch * 1000).toUTCString() }];
    if (v.mode === 'drift') { const per = v.behind / v.days; return [{ label: T('ડ્રિફ્ટ પ્રતિ દિવસ', 'Drift/day'), value: per.toFixed(1) + ' ' + T('સેકન્ડ', 'sec'), big: true }, { label: T('પ્રતિ મહિનો', 'Per month'), value: (per * 30).toFixed(0) + ' sec' }, { label: T('ભલામણ', 'Advice'), value: 'NTP: time.google.com' }]; }
    return [{ label: 'NTP', value: 'time.google.com, pool.ntp.org, in.pool.ntp.org', big: true }, { label: 'IST offset', value: '+5:30 (UTC+5.5)' }];
  },
}));

/* ================= COMPUTER SHOP CALCULATORS ================= */
regCalc('storage-convert', () => ({
  title: T('સ્ટોરેજ કન્વર્ટ', 'Storage'),
  fields: [
    { k: 'val', label: T('મૂલ્ય', 'Value'), type: 'number', val: 1 },
    { k: 'unit', label: T('યુનિટ', 'Unit'), type: 'select', val: 'GB', opts: ['bit', 'Byte', 'KB', 'MB', 'GB', 'TB', 'PB'].map(u => ({ v: u, t: u })) },
  ],
  compute: v => {
    const decimal = { bit: 0.125, Byte: 1, KB: 1e3, MB: 1e6, GB: 1e9, TB: 1e12, PB: 1e15 };
    const binary = { bit: 0.125, Byte: 1, KB: 1024, MB: 1024 ** 2, GB: 1024 ** 3, TB: 1024 ** 4, PB: 1024 ** 5 };
    const bytesD = v.val * decimal[v.unit];
    return ['KB', 'MB', 'GB', 'TB'].map(u => ({
      label: u, value: `${(bytesD / decimal[u]).toLocaleString(undefined, { maximumFractionDigits: 3 })} <span class="text-xs opacity-60">(×1000)</span> / ${(v.val * decimal[v.unit] / binary[u]).toLocaleString(undefined, { maximumFractionDigits: 3 })} <span class="text-xs opacity-60">(×1024)</span>`,
    }));
  },
}));
regCalc('hdd-capacity', () => ({
  title: T('HDD રિયલ કેપેસિટી', 'HDD Real Capacity'),
  fields: [{ k: 'size', label: T('ડ્રાઇવ સાઇઝ (લેબલ)', 'Drive size (label)'), unit: 'TB', type: 'number', val: 1, step: '0.5' }],
  compute: v => {
    const real = v.size * 1e12 / (1024 ** 3);
    return [
      { label: T('ઉપયોગી જગ્યા', 'Usable space'), value: real.toFixed(0) + ' GB', big: true, note: T('ઉત્પાદક 1TB=1000GB ગણે, OS 1024 ગણે', 'Makers use 1000, OS uses 1024') },
      { label: T('ગુમાવેલ', 'Apparent loss'), value: (v.size * 1000 - real).toFixed(0) + ' GB' },
    ];
  },
}));
regCalc('ram-calc', () => ({
  title: 'RAM', fields: [
    { k: 'slots', label: T('સ્લોટ', 'Slots'), type: 'number', val: 2 },
    { k: 'stick', label: T('પ્રતિ સ્ટિક', 'Per stick'), unit: 'GB', type: 'select', val: '8', opts: [4, 8, 16, 32].map(x => ({ v: x, t: x + ' GB' })) },
    { k: 'ddr', label: 'DDR', type: 'select', val: 'DDR4', opts: ['DDR3', 'DDR4', 'DDR5'].map(x => ({ v: x, t: x })) },
  ],
  compute: v => [
    { label: T('કુલ RAM', 'Total RAM'), value: (v.slots * parseInt(v.stick)) + ' GB', big: true },
    { label: T('ચેનલ', 'Channel'), value: v.slots % 2 === 0 ? T('ડ્યુઅલ-ચેનલ ✅', 'Dual-channel ✅') : T('સિંગલ ⚠️', 'Single ⚠️'), note: v.ddr },
  ],
}));
regCalc('partition-plan', () => ({
  title: T('પાર્ટિશન', 'Partition'), fields: [
    { k: 'size', label: T('ડ્રાઇવ સાઇઝ', 'Drive size'), unit: 'GB', type: 'number', val: 512 },
    { k: 'use', label: T('ઉપયોગ', 'Usage'), type: 'select', val: 'general', opts: [{ v: 'general', t: T('સામાન્ય', 'General') }, { v: 'gaming', t: 'Gaming' }, { v: 'office', t: 'Office' }] },
  ],
  compute: v => {
    const c = v.use === 'gaming' ? 0.4 : 0.25;
    const cg = Math.round(v.size * c);
    return [{ label: 'C: (' + T('સિસ્ટમ', 'System') + ')', value: cg + ' GB', big: true }, { label: 'D: (' + T('ડેટા', 'Data') + ')', value: Math.round((v.size - cg) * 0.6) + ' GB' }, { label: 'E: (' + T('બેકઅપ', 'Backup') + ')', value: Math.round((v.size - cg) * 0.4) + ' GB' }];
  },
}));
regCalc('transfer-time', () => ({
  title: T('ટ્રાન્સફર ટાઇમ', 'Transfer Time'), fields: [
    { k: 'size', label: T('ફાઇલ સાઇઝ', 'File size'), type: 'number', val: 10 },
    { k: 'sunit', label: '', type: 'select', val: 'GB', opts: ['MB', 'GB', 'TB'].map(x => ({ v: x, t: x })) },
    { k: 'speed', label: T('સ્પીડ', 'Speed'), type: 'select', val: '480', opts: [{ v: '60', t: 'USB 2.0 (60 MB/s)' }, { v: '480', t: 'USB 3.0 (480 MB/s)' }, { v: '12.5', t: 'LAN 100Mbps' }, { v: '125', t: 'LAN 1Gbps' }] },
  ],
  compute: v => {
    const mb = v.size * ({ MB: 1, GB: 1024, TB: 1024 * 1024 })[v.sunit];
    const sec = mb / parseFloat(v.speed);
    return [{ label: T('અંદાજિત સમય', 'Estimated time'), value: fmtDuration(sec), big: true }, { label: T('સેકન્ડ', 'Seconds'), value: sec.toFixed(0) }];
  },
}));
regCalc('backup-est', () => ({
  title: T('બેકઅપ', 'Backup'), fields: [
    { k: 'data', label: T('ડેટા સાઇઝ', 'Data size'), unit: 'GB', type: 'number', val: 100 },
    { k: 'copies', label: T('બેકઅપ કોપી', 'Backup copies'), type: 'number', val: 3 },
    { k: 'comp', label: T('કમ્પ્રેશન %', 'Compression %'), type: 'number', val: 30 },
  ],
  compute: v => {
    const per = v.data * (1 - v.comp / 100);
    return [{ label: T('જરૂરી જગ્યા', 'Space needed'), value: (per * v.copies).toFixed(0) + ' GB', big: true }, { label: T('પ્રતિ કોપી', 'Per copy'), value: per.toFixed(0) + ' GB' }];
  },
}));
regCalc('pc-build', () => ({
  title: T('PC બિલ્ડ કોસ્ટ', 'PC Build Cost'), live: false,
  fields: [
    { k: 'parts', label: T('પાર્ટ્સ (નામ,ભાવ પ્રતિ લાઇન)', 'Parts (name,price per line)'), type: 'textarea', rows: 6, full: true, val: 'CPU,8000\nMotherboard,6000\nRAM 16GB,3500\nSSD 512GB,3200\nCabinet+SMPS,2500' },
    { k: 'margin', label: T('માર્જિન %', 'Margin %'), type: 'number', val: 12 },
    { k: 'gst', label: 'GST %', type: 'number', val: 18 },
  ],
  compute: v => {
    let cost = 0;
    v.parts.split('\n').forEach(l => { const p = l.split(','); if (p[1]) cost += parseFloat(p[1]) || 0; });
    const withMargin = cost * (1 + v.margin / 100);
    const gst = withMargin * v.gst / 100;
    return [
      { label: T('પાર્ટ્સ કોસ્ટ', 'Parts cost'), value: '₹' + cost.toFixed(0) },
      { label: T('માર્જિન સાથે', 'With margin'), value: '₹' + withMargin.toFixed(0) },
      { label: 'GST', value: '₹' + gst.toFixed(0) },
      { label: T('કુલ વેચાણ કિંમત', 'Total sale price'), value: '₹' + (withMargin + gst).toFixed(0), big: true },
    ];
  },
}));
regCalc('psu-calc', () => ({
  title: 'PSU', fields: [
    { k: 'cpu', label: 'CPU', unit: 'W', type: 'number', val: 95 },
    { k: 'gpu', label: 'GPU', unit: 'W', type: 'number', val: 150 },
    { k: 'ram', label: T('RAM સ્ટિક', 'RAM sticks'), type: 'number', val: 2 },
    { k: 'hdd', label: T('ડ્રાઇવ', 'Drives'), type: 'number', val: 2 },
    { k: 'fans', label: T('ફેન', 'Fans'), type: 'number', val: 3 },
  ],
  compute: v => {
    const w = v.cpu + v.gpu + v.ram * 5 + v.hdd * 10 + v.fans * 3 + 50;
    const rec = Math.ceil((w * 1.3) / 50) * 50;
    return [{ label: T('કુલ વપરાશ', 'Total draw'), value: w + ' W' }, { label: T('ભલામણ SMPS', 'Recommended SMPS'), value: rec + ' W', big: true, note: T('30% હેડરૂમ સાથે', 'with 30% headroom') }];
  },
}));
regCalc('ups-calc', () => ({
  title: 'UPS', fields: [
    { k: 'va', label: 'UPS VA', type: 'number', val: 600 },
    { k: 'load', label: T('લોડ', 'Load'), unit: 'W', type: 'number', val: 150 },
    { k: 'ah', label: T('બેટરી', 'Battery'), unit: 'Ah', type: 'number', val: 7 },
    { k: 'volt', label: T('બેટરી વોલ્ટ', 'Battery volt'), unit: 'V', type: 'number', val: 12 },
  ],
  compute: v => {
    const wh = v.ah * v.volt * 0.7; // 70% efficiency/DoD
    const mins = (wh / v.load) * 60;
    return [{ label: T('બેકઅપ સમય', 'Backup time'), value: fmtDuration(mins * 60), big: true }, { label: T('બેટરી ક્ષમતા', 'Battery capacity'), value: wh.toFixed(0) + ' Wh' }];
  },
}));
regCalc('ppi-calc', () => ({
  title: 'PPI', fields: [
    { k: 'w', label: T('પહોળાઈ પિક્સેલ', 'Width px'), type: 'number', val: 1920 },
    { k: 'h', label: T('ઊંચાઈ પિક્સેલ', 'Height px'), type: 'number', val: 1080 },
    { k: 'diag', label: T('સ્ક્રીન', 'Screen'), unit: 'inch', type: 'number', val: 24, step: '0.1' },
  ],
  compute: v => {
    const ppi = Math.sqrt(v.w ** 2 + v.h ** 2) / v.diag;
    return [{ label: 'PPI', value: ppi.toFixed(1), big: true }, { label: T('ડોટ પિચ', 'Dot pitch'), value: (25.4 / ppi).toFixed(3) + ' mm' }, { label: T('રિઝોલ્યુશન', 'Resolution'), value: (v.w * v.h / 1e6).toFixed(1) + ' MP' }];
  },
}));
regCalc('aspect-calc', () => ({
  title: T('એસ્પેક્ટ રેશિયો', 'Aspect Ratio'), fields: [
    { k: 'w', label: T('મૂળ પહોળાઈ', 'Original width'), type: 'number', val: 1920 },
    { k: 'h', label: T('મૂળ ઊંચાઈ', 'Original height'), type: 'number', val: 1080 },
    { k: 'nw', label: T('નવી પહોળાઈ', 'New width'), type: 'number', val: 1280 },
  ],
  compute: v => {
    const g = gcd(v.w, v.h);
    return [{ label: T('એસ્પેક્ટ રેશિયો', 'Aspect ratio'), value: `${v.w / g}:${v.h / g}`, big: true }, { label: T('નવી ઊંચાઈ', 'New height'), value: Math.round(v.nw * v.h / v.w) + ' px' }];
  },
}));
regCalc('page-cost', () => ({
  title: T('પેજ કોસ્ટ', 'Page Cost'), fields: [
    { k: 'price', label: T('કારતૂસ ભાવ', 'Cartridge price'), unit: '₹', type: 'number', val: 800 },
    { k: 'yield', label: T('પેજ યીલ્ડ', 'Page yield'), type: 'number', val: 2000 },
  ],
  compute: v => [{ label: T('પ્રતિ પેજ ખર્ચ', 'Cost per page'), value: '₹' + (v.price / v.yield).toFixed(2), big: true }, { label: T('100 પેજ', '100 pages'), value: '₹' + (v.price / v.yield * 100).toFixed(0) }],
}));
regCalc('toner-est', () => ({
  title: T('ટોનર યીલ્ડ', 'Toner Yield'), fields: [
    { k: 'monthly', label: T('માસિક પેજ', 'Monthly pages'), type: 'number', val: 1500 },
    { k: 'yield', label: T('યીલ્ડ', 'Yield'), type: 'number', val: 2000 },
    { k: 'price', label: T('કારતૂસ ભાવ', 'Cartridge price'), unit: '₹', type: 'number', val: 800 },
  ],
  compute: v => {
    const perYear = v.monthly * 12 / v.yield;
    return [{ label: T('વર્ષે કારતૂસ', 'Cartridges/year'), value: perYear.toFixed(1), big: true }, { label: T('વાર્ષિક ખર્ચ', 'Annual cost'), value: '₹' + (perYear * v.price).toFixed(0) }];
  },
}));
regCalc('download-time', () => ({
  title: T('ડાઉનલોડ ટાઇમ', 'Download Time'), fields: [
    { k: 'size', label: T('ફાઇલ સાઇઝ', 'File size'), type: 'number', val: 1 },
    { k: 'sunit', label: '', type: 'select', val: 'GB', opts: ['MB', 'GB'].map(x => ({ v: x, t: x })) },
    { k: 'speed', label: T('સ્પીડ', 'Speed'), unit: 'Mbps', type: 'number', val: 50 },
  ],
  compute: v => {
    const mbits = v.size * ({ MB: 8, GB: 8192 })[v.sunit];
    return [{ label: T('અંદાજિત સમય', 'Estimated time'), value: fmtDuration(mbits / v.speed), big: true }];
  },
}));
regCalc('video-size', () => ({
  title: T('વિડિયો સાઇઝ', 'Video Size'), fields: [
    { k: 'bitrate', label: T('બિટરેટ', 'Bitrate'), unit: 'Mbps', type: 'number', val: 8 },
    { k: 'dur', label: T('સમય', 'Duration'), unit: 'min', type: 'number', val: 60 },
  ],
  compute: v => {
    const gb = v.bitrate * 1e6 / 8 * v.dur * 60 / 1e9;
    return [{ label: T('ફાઇલ સાઇઝ', 'File size'), value: gb >= 1 ? gb.toFixed(2) + ' GB' : (gb * 1000).toFixed(0) + ' MB', big: true }];
  },
}));
regCalc('mbps-convert', () => ({
  title: 'Mbps↔MB/s', fields: [
    { k: 'val', label: T('મૂલ્ય', 'Value'), type: 'number', val: 100 },
    { k: 'from', label: T('થી', 'From'), type: 'select', val: 'mbps', opts: [{ v: 'mbps', t: 'Mbps' }, { v: 'mbs', t: 'MB/s' }] },
  ],
  compute: v => v.from === 'mbps' ? [{ label: 'MB/s', value: (v.val / 8).toFixed(2), big: true }] : [{ label: 'Mbps', value: (v.val * 8).toFixed(2), big: true }],
}));

/* ================= BUSINESS CALCULATORS ================= */
regCalc('gst-calc', () => ({
  title: 'GST', fields: [
    { k: 'amount', label: T('રકમ', 'Amount'), unit: '₹', type: 'number', val: 1000 },
    { k: 'rate', label: 'GST %', type: 'select', val: '18', opts: [5, 12, 18, 28].map(x => ({ v: x, t: x + '%' })) },
    { k: 'mode', label: T('પ્રકાર', 'Type'), type: 'select', val: 'excl', opts: [{ v: 'excl', t: T('GST વગર (ઉમેરો)', 'Exclusive (add)') }, { v: 'incl', t: T('GST સહિત (કાઢો)', 'Inclusive (extract)') }] },
    { k: 'inter', label: '', type: 'checkbox', cbLabel: T('આંતરરાજ્ય (IGST)', 'Inter-state (IGST)'), val: false },
  ],
  compute: v => {
    const r = parseFloat(v.rate) / 100;
    let base, gst, total;
    if (v.mode === 'excl') { base = v.amount; gst = base * r; total = base + gst; }
    else { total = v.amount; base = total / (1 + r); gst = total - base; }
    const rows = [
      { label: T('મૂળ રકમ', 'Base amount'), value: '₹' + base.toFixed(2) },
      { label: T('કુલ GST', 'Total GST'), value: '₹' + gst.toFixed(2) },
      { label: T('કુલ રકમ', 'Total amount'), value: '₹' + total.toFixed(2), big: true },
    ];
    if (v.inter) rows.push({ label: 'IGST', value: '₹' + gst.toFixed(2) });
    else { rows.push({ label: 'CGST', value: '₹' + (gst / 2).toFixed(2) }); rows.push({ label: 'SGST', value: '₹' + (gst / 2).toFixed(2) }); }
    return rows;
  },
}));
regCalc('emi-calc', () => ({
  title: 'EMI', fields: [
    { k: 'p', label: T('લોન રકમ', 'Loan amount'), unit: '₹', type: 'number', val: 100000 },
    { k: 'r', label: T('વ્યાજ દર (વાર્ષિક)', 'Interest (annual)'), unit: '%', type: 'number', val: 12, step: '0.1' },
    { k: 'n', label: T('મુદત', 'Tenure'), unit: T('મહિના', 'months'), type: 'number', val: 12 },
  ],
  compute: v => {
    const r = v.r / 1200;
    const emi = r === 0 ? v.p / v.n : v.p * r * Math.pow(1 + r, v.n) / (Math.pow(1 + r, v.n) - 1);
    const total = emi * v.n;
    return [
      { label: 'EMI', value: '₹' + emi.toFixed(0), big: true },
      { label: T('કુલ વ્યાજ', 'Total interest'), value: '₹' + (total - v.p).toFixed(0) },
      { label: T('કુલ ચૂકવણી', 'Total payment'), value: '₹' + total.toFixed(0) },
    ];
  },
}));
regCalc('margin-calc', () => ({
  title: T('માર્જિન', 'Margin'), fields: [
    { k: 'cost', label: T('કોસ્ટ', 'Cost'), unit: '₹', type: 'number', val: 100 },
    { k: 'margin', label: T('માર્જિન %', 'Margin %'), type: 'number', val: 20 },
  ],
  compute: v => {
    const mrp = v.cost / (1 - v.margin / 100);
    const markup = (mrp - v.cost) / v.cost * 100;
    return [{ label: 'MRP', value: '₹' + mrp.toFixed(2), big: true }, { label: T('નફો', 'Profit'), value: '₹' + (mrp - v.cost).toFixed(2) }, { label: T('માર્કઅપ', 'Markup'), value: markup.toFixed(1) + '%' }];
  },
}));
regCalc('discount-calc', () => ({
  title: T('ડિસ્કાઉન્ટ', 'Discount'), fields: [
    { k: 'mrp', label: 'MRP', unit: '₹', type: 'number', val: 1000 },
    { k: 'disc', label: T('ડિસ્કાઉન્ટ %', 'Discount %'), type: 'number', val: 10 },
  ],
  compute: v => {
    const save = v.mrp * v.disc / 100;
    return [{ label: T('ચૂકવવાની કિંમત', 'Final price'), value: '₹' + (v.mrp - save).toFixed(2), big: true }, { label: T('બચત', 'You save'), value: '₹' + save.toFixed(2) }];
  },
}));
regCalc('warranty-calc', () => ({
  title: T('વોરંટી', 'Warranty'), live: false, fields: [
    { k: 'date', label: T('ખરીદી તારીખ', 'Purchase date'), type: 'date', val: new Date().toISOString().slice(0, 10) },
    { k: 'months', label: T('વોરંટી મહિના', 'Warranty months'), type: 'number', val: 12 },
  ],
  compute: v => {
    const d = new Date(v.date); d.setMonth(d.getMonth() + v.months);
    const left = Math.ceil((d - new Date()) / 86400000);
    return [{ label: T('એક્સપાયરી', 'Expiry'), value: d.toLocaleDateString('en-IN'), big: true }, { label: T('બાકી દિવસ', 'Days left'), value: left > 0 ? left : T('સમાપ્ત', 'Expired'), note: left <= 30 && left > 0 ? '⚠️' : '' }];
  },
}));
regCalc('amc-calc', () => ({
  title: 'AMC', live: false, fields: [
    { k: 'date', label: T('શરૂ તારીખ', 'Start date'), type: 'date', val: new Date().toISOString().slice(0, 10) },
    { k: 'years', label: T('મુદત વર્ષ', 'Years'), type: 'number', val: 1 },
  ],
  compute: v => {
    const d = new Date(v.date); d.setFullYear(d.getFullYear() + v.years);
    const rem = new Date(d); rem.setDate(rem.getDate() - 30);
    return [{ label: T('રિન્યુઅલ તારીખ', 'Renewal date'), value: d.toLocaleDateString('en-IN'), big: true }, { label: T('રિમાઇન્ડર', 'Reminder'), value: rem.toLocaleDateString('en-IN'), note: T('30 દિવસ પહેલા', '30 days before') }];
  },
}));
regCalc('salary-calc', () => ({
  title: T('સેલરી', 'Salary'), fields: [
    { k: 'ctc', label: T('વાર્ષિક CTC', 'Annual CTC'), unit: '₹', type: 'number', val: 360000 },
  ],
  compute: v => {
    const m = v.ctc / 12;
    const basic = m * 0.5, hra = basic * 0.4, pf = basic * 0.12, esi = m < 21000 ? m * 0.0075 : 0;
    const net = m - pf - esi;
    return [
      { label: T('માસિક ગ્રોસ', 'Monthly gross'), value: '₹' + m.toFixed(0), big: true },
      { label: 'Basic', value: '₹' + basic.toFixed(0) }, { label: 'HRA', value: '₹' + hra.toFixed(0) },
      { label: 'PF (12%)', value: '₹' + pf.toFixed(0) }, { label: 'ESI', value: '₹' + esi.toFixed(0) },
      { label: T('નેટ (ઇન-હેન્ડ)', 'Net (in-hand)'), value: '₹' + net.toFixed(0), big: true },
    ];
  },
}));
regCalc('inventory-calc', () => ({
  title: T('ઇન્વેન્ટરી', 'Inventory'), live: false, fields: [
    { k: 'items', label: T('આઇટમ (નામ,જથ્થો,ભાવ પ્રતિ લાઇન)', 'Items (name,qty,price per line)'), type: 'textarea', rows: 6, full: true, val: 'Mouse,10,250\nKeyboard,5,600\nHDD 1TB,8,3200' },
  ],
  compute: v => {
    let total = 0, out = '<table class="w-full text-sm"><tr class="opacity-60"><th class="text-left">Item</th><th>Qty</th><th>Price</th><th class="text-right">Value</th></tr>';
    v.items.split('\n').forEach(l => { const [n, q, p] = l.split(','); if (!n) return; const val = (parseFloat(q) || 0) * (parseFloat(p) || 0); total += val; out += `<tr><td>${n}</td><td class="text-center">${q}</td><td class="text-center">₹${p}</td><td class="text-right">₹${val.toFixed(0)}</td></tr>`; });
    out += `<tr class="font-bold border-t"><td colspan="3">${T('કુલ મૂલ્ય', 'Total value')}</td><td class="text-right">₹${total.toFixed(0)}</td></tr></table>`;
    return out;
  },
}));

/* ================= DATE / TIME / MATH ================= */
regCalc('days-calc', () => ({
  title: T('દિવસ ગણો', 'Days'), live: false, fields: [
    { k: 'd1', label: T('તારીખ 1', 'Date 1'), type: 'date', val: new Date().toISOString().slice(0, 10) },
    { k: 'd2', label: T('તારીખ 2', 'Date 2'), type: 'date', val: new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10) },
  ],
  compute: v => {
    const a = new Date(v.d1), b = new Date(v.d2);
    const days = Math.abs(Math.round((b - a) / 86400000));
    let working = 0; const d = new Date(Math.min(a, b));
    for (let i = 0; i < days; i++) { if (d.getDay() !== 0) working++; d.setDate(d.getDate() + 1); }
    return [{ label: T('કુલ દિવસ', 'Total days'), value: days, big: true }, { label: T('અઠવાડિયા', 'Weeks'), value: (days / 7).toFixed(1) }, { label: T('મહિના', 'Months'), value: (days / 30.44).toFixed(1) }, { label: T('વર્કિંગ ડે (રવિ બાદ)', 'Working days (excl. Sun)'), value: working }];
  },
}));
regCalc('age-calc', () => ({
  title: T('ઉંમર', 'Age'), live: false, fields: [
    { k: 'dob', label: T('જન્મ તારીખ', 'Date of birth'), type: 'date', val: '2000-01-01' },
  ],
  compute: v => {
    const dob = new Date(v.dob), now = new Date();
    let y = now.getFullYear() - dob.getFullYear(), m = now.getMonth() - dob.getMonth(), d = now.getDate() - dob.getDate();
    if (d < 0) { m--; d += new Date(now.getFullYear(), now.getMonth(), 0).getDate(); }
    if (m < 0) { y--; m += 12; }
    const next = new Date(now.getFullYear(), dob.getMonth(), dob.getDate());
    if (next < now) next.setFullYear(now.getFullYear() + 1);
    return [{ label: T('ઉંમર', 'Age'), value: `${y} ${T('વર્ષ', 'yr')} ${m} ${T('મહિના', 'mo')} ${d} ${T('દિવસ', 'd')}`, big: true }, { label: T('આગામી જન્મદિવસ', 'Next birthday'), value: Math.ceil((next - now) / 86400000) + ' ' + T('દિવસમાં', 'days') }, { label: T('કુલ દિવસ જીવ્યા', 'Total days lived'), value: Math.floor((now - dob) / 86400000).toLocaleString() }];
  },
}));
regCalc('date-math', () => ({
  title: T('ડેટ ગણિત', 'Date Math'), live: false, fields: [
    { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) },
    { k: 'op', label: '', type: 'select', val: 'add', opts: [{ v: 'add', t: T('ઉમેરો', 'Add') }, { v: 'sub', t: T('બાદ કરો', 'Subtract') }] },
    { k: 'days', label: T('દિવસ', 'Days'), type: 'number', val: 30 },
    { k: 'months', label: T('મહિના', 'Months'), type: 'number', val: 0 },
  ],
  compute: v => {
    const d = new Date(v.date); const s = v.op === 'sub' ? -1 : 1;
    d.setMonth(d.getMonth() + s * v.months); d.setDate(d.getDate() + s * v.days);
    return [{ label: T('પરિણામ તારીખ', 'Result date'), value: d.toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }), big: true }];
  },
}));
regCalc('working-days', () => ({
  title: T('વર્કિંગ ડે', 'Working Days'), live: false, fields: [
    { k: 'd1', label: T('શરૂ', 'From'), type: 'date', val: new Date().toISOString().slice(0, 10) },
    { k: 'd2', label: T('અંત', 'To'), type: 'date', val: new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10) },
    { k: 'sat', label: '', type: 'checkbox', cbLabel: T('શનિવાર પણ રજા', 'Saturday off too'), val: false },
  ],
  compute: v => {
    const a = new Date(v.d1), b = new Date(v.d2); let wd = 0; const d = new Date(a);
    while (d <= b) { const day = d.getDay(); if (day !== 0 && !(v.sat && day === 6)) wd++; d.setDate(d.getDate() + 1); }
    return [{ label: T('કામકાજના દિવસ', 'Working days'), value: wd, big: true }];
  },
}));
regCalc('epoch-convert', () => ({
  title: 'Epoch', live: false, fields: [
    { k: 'epoch', label: 'Unix timestamp', type: 'number', val: Math.floor(Date.now() / 1000) },
  ],
  compute: v => {
    const ms = String(v.epoch).length > 11 ? v.epoch : v.epoch * 1000;
    const d = new Date(ms);
    return [{ label: T('સ્થાનિક (IST)', 'Local (IST)'), value: d.toLocaleString('en-IN', { timeZone: 'Asia/Kolkata' }), big: true }, { label: 'UTC', value: d.toUTCString() }, { label: 'ISO', value: d.toISOString() }];
  },
}));
regCalc('tz-convert', () => ({
  title: T('ટાઇમ ઝોન', 'Time Zone'), live: false, fields: [
    { k: 'time', label: T('IST સમય', 'IST time'), type: 'text', val: '12:00' },
    { k: 'zone', label: T('ઝોન', 'Zone'), type: 'select', val: 'America/New_York', opts: ['America/New_York', 'Europe/London', 'Asia/Dubai', 'Asia/Singapore', 'Australia/Sydney', 'America/Los_Angeles'].map(z => ({ v: z, t: z })) },
  ],
  compute: v => {
    const [h, m] = v.time.split(':').map(Number);
    const now = new Date(); now.setHours(h, m || 0, 0, 0);
    // Treat input as IST wall clock.
    const istStr = now.toLocaleString('en-US', { timeZone: 'Asia/Kolkata', hour: '2-digit', minute: '2-digit' });
    const other = now.toLocaleString('en-US', { timeZone: v.zone, hour: '2-digit', minute: '2-digit', weekday: 'short' });
    return [{ label: 'IST', value: istStr, big: true }, { label: v.zone, value: other, big: true }];
  },
}));
regCalc('percent-calc', () => ({
  title: T('ટકાવારી', 'Percentage'), fields: [
    { k: 'mode', label: T('પ્રકાર', 'Type'), type: 'select', val: 'of', opts: [{ v: 'of', t: 'X% of Y' }, { v: 'isof', t: 'X is what % of Y' }, { v: 'change', t: T('ફેરફાર %', '% change') }] },
    { k: 'x', label: 'X', type: 'number', val: 10 },
    { k: 'y', label: 'Y', type: 'number', val: 200 },
  ],
  compute: v => {
    if (v.mode === 'of') return [{ label: `${v.x}% of ${v.y}`, value: (v.x * v.y / 100).toFixed(2), big: true }];
    if (v.mode === 'isof') return [{ label: `${v.x} / ${v.y}`, value: (v.x / v.y * 100).toFixed(2) + '%', big: true }];
    return [{ label: T('ફેરફાર', 'Change'), value: ((v.y - v.x) / v.x * 100).toFixed(2) + '%', big: true, note: v.y >= v.x ? T('વધારો', 'increase') : T('ઘટાડો', 'decrease') }];
  },
}));
regCalc('unit-convert', () => ({
  title: T('યુનિટ', 'Unit'), fields: [
    { k: 'cat', label: T('શ્રેણી', 'Category'), type: 'select', val: 'length', opts: [{ v: 'length', t: T('લંબાઈ', 'Length') }, { v: 'weight', t: T('વજન', 'Weight') }, { v: 'temp', t: T('તાપમાન', 'Temp') }, { v: 'area', t: T('વિસ્તાર', 'Area') }, { v: 'volume', t: T('વોલ્યુમ', 'Volume') }] },
    { k: 'val', label: T('મૂલ્ય', 'Value'), type: 'number', val: 1 },
    { k: 'from', label: T('થી', 'From'), type: 'select', val: 'm', opts: [{ v: 'm', t: 'meter' }] },
  ],
  compute: v => {
    const U = {
      length: { m: 1, km: 1000, cm: 0.01, mm: 0.001, mile: 1609.34, foot: 0.3048, inch: 0.0254, yard: 0.9144 },
      weight: { kg: 1, g: 0.001, mg: 1e-6, ton: 1000, lb: 0.453592, oz: 0.0283495 },
      area: { 'm²': 1, 'km²': 1e6, 'ft²': 0.092903, acre: 4046.86, hectare: 10000 },
      volume: { L: 1, mL: 0.001, 'm³': 1000, gallon: 3.78541 },
    };
    if (v.cat === 'temp') {
      return [{ label: '°C', value: v.val }, { label: '°F', value: (v.val * 9 / 5 + 32).toFixed(2), big: true }, { label: 'K', value: (v.val + 273.15).toFixed(2) }];
    }
    const map = U[v.cat]; const keys = Object.keys(map);
    const base = v.val * (map[v.from] || map[keys[0]]);
    return keys.map(k => ({ label: k, value: (base / map[k]).toLocaleString(undefined, { maximumFractionDigits: 4 }), big: k === v.from }));
  },
}));

/* Helpers used by calcs */
function gcd(a, b) { return b ? gcd(b, a % b) : a; }
function fmtDuration(sec) {
  if (sec < 60) return sec.toFixed(1) + ' ' + T('સેકન્ડ', 'sec');
  if (sec < 3600) return (sec / 60).toFixed(1) + ' ' + T('મિનિટ', 'min');
  if (sec < 86400) return (sec / 3600).toFixed(1) + ' ' + T('કલાક', 'hr');
  return (sec / 86400).toFixed(1) + ' ' + T('દિવસ', 'days');
}

/* Dynamic-field wiring for unit-convert (from list depends on category) — patch after render */
document.addEventListener('input', e => {
  if (e.target && e.target.id === 'f_cat') {
    const fromSel = $('#f_from');
    if (fromSel) {
      const cat = e.target.value;
      const opts = { length: ['m', 'km', 'cm', 'mm', 'mile', 'foot', 'inch', 'yard'], weight: ['kg', 'g', 'mg', 'ton', 'lb', 'oz'], area: ['m²', 'km²', 'ft²', 'acre', 'hectare'], volume: ['L', 'mL', 'm³', 'gallon'], temp: ['°C'] }[cat] || ['m'];
      fromSel.innerHTML = opts.map(o => `<option value="${o}">${o}</option>`).join('');
    }
  }
});

/* Export internals for the file/generator engine module (loaded below). */
window.KTX = { $, $$, T, GU, KT, toast, copy, downloadBlob, downloadDataUrl, loadScript, loadCss, need, CDN, logUsage, waShareText, waShareFile, setActions, el, fileInput, checkMax, fmtBytes, reg, Engines, regCalc, renderCalc, fmtDuration, gcd };

/* ================================================================
   MOUNT: pick engine, or graceful fallback.
   ================================================================ */
function mountTool() {
  const mount = document.getElementById('toolMount');
  if (!mount) return;
  const name = mount.dataset.engine;
  const opt = JSON.parse(mount.dataset.opt || '{}');
  const ctx = {
    toolId: parseInt(mount.dataset.toolid) || 0,
    slug: mount.dataset.slug,
    allowed: mount.dataset.allowed === '1',
    watermark: mount.dataset.watermark === '1',
    maxMb: parseInt(mount.dataset.maxmb) || 5,
    _resultText: '',
  };
  // The heavier file/generator engines live in engines.js; wait for it if needed.
  const run = () => {
    const fn = window.KTX.Engines[name];
    if (fn) { try { fn(mount, opt, ctx); } catch (e) { console.error(e); mount.innerHTML = `<div class="text-red-500">${e.message}</div>`; } }
    else {
      mount.innerHTML = `<div class="text-center py-6">
        <div class="text-4xl mb-2">🦚</div>
        <p class="font-semibold">${T('આ ટૂલ ટૂંક સમયમાં આવી રહ્યું છે.', 'This tool is coming soon.')}</p>
        <p class="opacity-60 text-sm mt-1">${T('નીચે સંબંધિત ટૂલ્સ અજમાવો.', 'Try the related tools below.')}</p></div>`;
    }
  };
  if (window.KTX.Engines[name]) run();
  else loadScript(KT.url + '/assets/js/engines.js').then(run).catch(run);
}

/* ================================================================
   GLOBAL SEARCH (fetches the tool index once)
   ================================================================ */
let toolIndex = null;
async function ensureIndex() {
  if (toolIndex) return toolIndex;
  try { toolIndex = await (await fetch(KT.url + '/api/tools_index.php')).json(); }
  catch (e) { toolIndex = []; }
  return toolIndex;
}
function wireSearch() {
  const inp = $('#globalSearch'), box = $('#searchResults');
  if (!inp) return;
  let tmr;
  inp.addEventListener('input', () => {
    clearTimeout(tmr);
    tmr = setTimeout(async () => {
      const term = inp.value.trim().toLowerCase();
      if (!term) { box.classList.add('hidden'); return; }
      const idx = await ensureIndex();
      const hits = idx.filter(t => (t.name_gu + ' ' + t.name_en + ' ' + t.slug).toLowerCase().includes(term)).slice(0, 8);
      box.innerHTML = hits.length ? hits.map(h => `<a href="${KT.url}/tool/${h.slug}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-[color:var(--gold)]/10">
        <i data-lucide="${h.icon}" class="w-4 h-4 opacity-70"></i><span class="text-sm">${GU ? h.name_gu : h.name_en}</span></a>`).join('')
        : `<div class="p-2 text-sm opacity-60">${T('કંઈ મળ્યું નહીં', 'No results')}</div>`;
      box.classList.remove('hidden'); if (window.lucide) lucide.createIcons();
    }, 150);
  });
  document.addEventListener('click', e => { if (!box.contains(e.target) && e.target !== inp) box.classList.add('hidden'); });
}

/* ================================================================
   Festival particles (Janmashtami)
   ================================================================ */
function festivalParticles() {
  if (new Date().getMonth() !== 7) return; // August only
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div'); p.className = 'gold-particle';
    p.style.left = Math.random() * 100 + 'vw';
    p.style.animationDuration = (4 + Math.random() * 4) + 's';
    p.style.animationDelay = (Math.random() * 5) + 's';
    document.body.appendChild(p);
  }
}

/* ---------- Boot ---------- */
document.addEventListener('DOMContentLoaded', () => {
  mountTool();
  wireSearch();
  festivalParticles();
});
})();
