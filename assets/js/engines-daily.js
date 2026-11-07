/* ================================================================
   KRISHNA TOOLS — "daily" engine: interactive everyday tools
   (includes/tools_more/*.php entries with type => 'app').
   One engine, many kinds: opt.kind picks the tool, opt.cfg holds
   per-tool settings. Everything runs in the browser.
   ================================================================ */
(function () {
'use strict';
const X = window.KTX;
if (!X) return;
const { $, T, KT, toast, copy, downloadBlob, waShareText, waShareFile, setActions, printHtml, el, reg, logUsage, need, loadScript, fileInput } = X;

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const store = {
  get(k, d) { try { const v = localStorage.getItem('kt-d-' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
  set(k, v) { try { localStorage.setItem('kt-d-' + k, JSON.stringify(v)); } catch (e) {} },
};
const rnd = n => { const a = new Uint32Array(1); crypto.getRandomValues(a); return a[0] % n; };   // 0..n-1, fair
const shuffle = arr => { const a = arr.slice(); for (let i = a.length - 1; i > 0; i--) { const j = rnd(i + 1); [a[i], a[j]] = [a[j], a[i]]; } return a; };
const txtBlob = t => new Blob([t], { type: 'text/plain;charset=utf-8' });
const inr = x => '₹' + (+x || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
let used = false;
const use = ctx => { if (!used && ctx.toolId) { used = true; logUsage(ctx.toolId); } };

function inWords(num) {
  num = Math.floor(Math.abs(+num || 0));
  if (!num) return 'Zero';
  const a = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
  const two = n => n < 20 ? a[n] : b[Math.floor(n / 10)] + (n % 10 ? ' ' + a[n % 10] : '');
  const three = n => (n >= 100 ? a[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' : '') : '') + (n % 100 ? two(n % 100) : '');
  const out = [];
  const cr = Math.floor(num / 1e7); num %= 1e7; if (cr) out.push((cr >= 1000 ? inWords(cr) : three(cr)) + ' Crore');
  const lk = Math.floor(num / 1e5); num %= 1e5; if (lk) out.push(two(lk) + ' Lakh');
  const th = Math.floor(num / 1000); num %= 1000; if (th) out.push(two(th) + ' Thousand');
  if (num) out.push(three(num));
  return out.join(' ');
}
const rupeesWords = n => 'Rupees ' + inWords(n) + (Math.round((n - Math.floor(n)) * 100) ? ' and ' + inWords(Math.round((n - Math.floor(n)) * 100)) + ' Paise' : '') + ' Only';

/* Render an HTML snippet to an A4 PDF (keeps ₹ and Indian scripts intact). */
async function htmlToPdf(html, name, ctx) {
  try {
    await need('jspdf');
    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js');
    const host = document.createElement('div');
    host.style.cssText = 'position:fixed;left:-10000px;top:0;width:794px;background:#fff';
    host.innerHTML = html; document.body.appendChild(host);
    const pages = host.querySelectorAll('.kt-page');
    const { jsPDF } = window.jspdf; const pdf = new jsPDF({ unit: 'mm', format: 'a4' });
    const targets = pages.length ? Array.from(pages) : [host];
    for (let i = 0; i < targets.length; i++) {
      const canvas = await window.html2canvas(targets[i], { scale: 2, backgroundColor: '#ffffff' });
      const pw = 210, ph = 297, pagePx = Math.floor(canvas.width * ph / pw);
      for (let y = 0, k = 0; y < canvas.height; y += pagePx, k++) {
        const part = document.createElement('canvas'); part.width = canvas.width; part.height = Math.min(pagePx, canvas.height - y);
        part.getContext('2d').drawImage(canvas, 0, y, canvas.width, part.height, 0, 0, canvas.width, part.height);
        if (i || k) pdf.addPage();
        pdf.addImage(part.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, pw, part.height * pw / canvas.width);
      }
    }
    host.remove();
    downloadBlob(pdf.output('blob'), name);
    use(ctx);
  } catch (e) {
    console.error(e);
    toast('Could not create the PDF. Check your internet connection and try again, or use Print.', true);
  }
}

/* A QR code drawn by qrcodejs, returned as a canvas. */
async function qrCanvas(text, size) {
  await need('qrcode');
  const holder = document.createElement('div');
  new window.QRCode(holder, { text, width: size || 300, height: size || 300, correctLevel: window.QRCode.CorrectLevel.M });
  const c = holder.querySelector('canvas');
  if (c) return c;
  // Very old browsers: the library falls back to <img>.
  const img = holder.querySelector('img');
  await new Promise(r => { if (img.complete) r(); else img.onload = r; });
  const cv = document.createElement('canvas'); cv.width = cv.height = size || 300; cv.getContext('2d').drawImage(img, 0, 0); return cv;
}
const canvasBlob = c => new Promise(r => c.toBlob(r, 'image/png'));

/* Text output box with Copy / Download / WhatsApp actions. */
function textActions(getText, name) {
  setActions([
    { label: 'Copy', cls: 'btn-cta', icon: 'copy', onClick: () => { const t = getText(); if (t) copy(t); else toast('Nothing to copy yet', true); } },
    { label: 'Download .txt', icon: 'download', onClick: () => downloadBlob(txtBlob(getText()), (name || 'text') + '.txt') },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(getText()) },
  ]);
}

const K = {};   // kind → function(mount, opt, ctx)

/* ======================= TEXT OPERATIONS ======================= */
const SAMPLE = {
  reverse: 'Krishna Tools makes daily work easy',
  clean: 'This text was copied\nfrom a PDF   and has\n\n\nbroken lines   and    extra   spaces.',
  sort: 'Rahul\nAmit\nPriya\nNeha\nKaran\nDivya',
  prefix: '9876543210\n9123456780\n9988776655',
  number: 'Milk\nBread\nEggs\nRice',
  repeat: 'Happy Birthday 🎉',
  extract: 'Contact rahul@example.com or call +91 98765 43210. Visit https://tools.akdwk.in #free #tools. Office: 079-1234567, priya.shah@mail.in',
  wa: 'Shop open today\nFlat 20% off on all items',
  emoji: 'Happy Diwali 🪔✨ to all of you 🎉🙏 Stay happy 😊',
  freq: 'Free online tools help you save time. Our free tools work on mobile and computer. Use free tools daily.',
};
K.textop = function (mount, opt, ctx) {
  const op = (opt.cfg || {}).op || 'clean';
  const optsHtml = {
    reverse: `<select class="kt-select" id="o1"><option value="c">Reverse all letters</option><option value="w">Reverse word order</option><option value="e">Reverse each word</option><option value="l">Reverse line order</option></select>`,
    clean: `<div class="grid sm:grid-cols-2 gap-1 text-sm">${[['lb', 'Remove line breaks (join lines)', 1], ['sp', 'Remove extra spaces', 1], ['el', 'Remove empty lines', 1], ['tr', 'Trim each line', 1], ['tb', 'Replace tabs with spaces', 1], ['pa', 'Keep paragraphs (double line breaks)', 0]].map(([k, l, c]) => `<label class="flex gap-2 items-center"><input type="checkbox" id="c_${k}" ${c ? 'checked' : ''}> ${l}</label>`).join('')}</div>`,
    sort: `<div class="grid sm:grid-cols-3 gap-2"><select class="kt-select" id="o1"><option value="az">A → Z</option><option value="za">Z → A</option><option value="len">Shortest first</option><option value="lenr">Longest first</option><option value="num">Numbers: small → big</option><option value="numr">Numbers: big → small</option><option value="rev">Reverse order</option><option value="shuf">Shuffle randomly</option></select><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_u"> Remove duplicates</label><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_e" checked> Remove empty lines</label></div>`,
    prefix: `<div class="grid sm:grid-cols-3 gap-2"><input class="kt-input" id="o1" placeholder="Prefix (start)" value="+91 "><input class="kt-input" id="o2" placeholder="Suffix (end)" value=""><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_e" checked> Skip empty lines</label></div>`,
    number: `<div class="grid sm:grid-cols-3 gap-2"><select class="kt-select" id="o1"><option value="1. ">1. 2. 3.</option><option value="1) ">1) 2) 3)</option><option value="(1) ">(1) (2) (3)</option><option value="1 - ">1 - 2 - 3</option><option value="#1 ">#1 #2 #3</option></select><input class="kt-input" id="o2" type="number" value="1" title="Start from"><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_e" checked> Skip empty lines</label></div>`,
    repeat: `<div class="grid sm:grid-cols-2 gap-2"><input class="kt-input" id="o1" type="number" min="1" max="10000" value="10" title="How many times"><select class="kt-select" id="o2"><option value="\n">Each on a new line</option><option value=" ">Separated by space</option><option value="">No space</option><option value=", ">Separated by comma</option></select></div>`,
    blank: `<div class="grid sm:grid-cols-2 gap-2"><input class="kt-input" id="o1" type="number" min="1" max="2000" value="1" title="How many blank characters"><select class="kt-select" id="o2"><option value="⠀">Braille blank (best for WhatsApp)</option><option value="ㅤ">Hangul filler (names, bios)</option><option value="​">Zero-width space</option><option value=" ">Em space</option></select></div>`,
    extract: `<div class="grid sm:grid-cols-2 gap-2"><select class="kt-select" id="o1"><option value="email">Email addresses</option><option value="phone">Phone / mobile numbers</option><option value="url">Links (URLs)</option><option value="tag">Hashtags</option><option value="num">All numbers</option></select><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_u" checked> Remove duplicates</label></div>`,
    wa: `<select class="kt-select" id="o1"><option value="*">Bold  *text*</option><option value="_">Italic  _text_</option><option value="~">Strikethrough  ~text~</option><option value="\`\`\`">Monospace  \`\`\`text\`\`\`</option><option value="\`">Inline code  \`text\`</option><option value="- ">Bulleted list</option><option value="1. ">Numbered list</option><option value="> ">Quote</option></select>`,
    emoji: '',
    freq: `<div class="grid sm:grid-cols-2 gap-2"><label class="flex gap-2 items-center text-sm"><input type="checkbox" id="c_s" checked> Ignore common words (the, and, is…)</label><input class="kt-input" id="o1" type="number" value="20" min="5" max="200" title="Show top"></div>`,
  }[op];
  const isFreq = op === 'freq', isBlank = op === 'blank';
  mount.appendChild(el(`<div class="space-y-3">
    ${isBlank ? '' : `<div><label class="kt-label" for="tIn">${op === 'repeat' ? 'Text to repeat' : 'Your text'}</label><textarea class="kt-input" id="tIn" rows="6">${esc(SAMPLE[op] || '')}</textarea></div>`}
    ${optsHtml ? `<div><div class="kt-label">Options</div>${optsHtml}</div>` : ''}
    <div class="kt-result"><label class="kt-label" for="tOut">Result <span id="tInfo" class="opacity-60 text-xs"></span></label>
      ${isFreq ? '<div id="tTable" class="overflow-auto"></div>' : ''}
      <textarea class="kt-input" id="tOut" rows="6" readonly ${isFreq ? 'style="display:none"' : ''}></textarea></div></div>`));
  const val = id => { const n = $('#' + id, mount); return n ? (n.type === 'checkbox' ? n.checked : n.value) : ''; };
  const lines = t => t.split(/\r?\n/);
  const STOP = new Set('a an the and or but is are was were be been to of in on at for with by from as it this that these those i you he she we they my your our their me us them not no so if then than too very can will just do does did have has had am its into about up out over more most such only own same also'.split(' '));
  function run() {
    const t = isBlank ? '' : val('tIn');
    let out = '', info = '';
    switch (op) {
      case 'reverse': {
        const m = val('o1');
        out = m === 'c' ? Array.from(t).reverse().join('') : m === 'w' ? lines(t).map(l => l.split(/(\s+)/).reverse().join('')).join('\n')
          : m === 'e' ? lines(t).map(l => l.split(/(\s+)/).map(w => Array.from(w).reverse().join('')).join('')).join('\n') : lines(t).reverse().join('\n');
        break; }
      case 'clean': {
        let s = t;
        if (val('c_tb')) s = s.replace(/\t/g, ' ');
        if (val('c_tr')) s = lines(s).map(l => l.trim()).join('\n');
        if (val('c_lb')) s = val('c_pa') ? s.split(/\n\s*\n/).map(p => p.replace(/\s*\n\s*/g, ' ')).join('\n\n') : s.replace(/\s*\n\s*/g, ' ');
        if (val('c_el')) s = s.replace(/\n\s*\n+/g, val('c_pa') ? '\n\n' : '\n');
        if (val('c_sp')) s = s.replace(/[  ]{2,}/g, ' ');
        out = s.trim(); info = `(${t.length} → ${out.length} characters)`;
        break; }
      case 'sort': {
        let a = lines(t);
        if (val('c_e')) a = a.filter(x => x.trim() !== '');
        if (val('c_u')) a = [...new Set(a)];
        const n = x => parseFloat(String(x).replace(/[^0-9.\-]/g, '')) || 0;
        const m = val('o1');
        const cmp = (x, y) => x.localeCompare(y, 'en', { sensitivity: 'base', numeric: true });
        a = m === 'az' ? a.sort(cmp) : m === 'za' ? a.sort(cmp).reverse() : m === 'len' ? a.sort((x, y) => x.length - y.length) : m === 'lenr' ? a.sort((x, y) => y.length - x.length)
          : m === 'num' ? a.sort((x, y) => n(x) - n(y)) : m === 'numr' ? a.sort((x, y) => n(y) - n(x)) : m === 'rev' ? a.reverse() : shuffle(a);
        out = a.join('\n'); info = `(${a.length} lines)`;
        break; }
      case 'prefix': out = lines(t).map(l => (val('c_e') && !l.trim()) ? l : val('o1') + l + val('o2')).join('\n'); break;
      case 'number': {
        let i = parseInt(val('o2')) || 1; const f = val('o1');
        out = lines(t).map(l => (val('c_e') && !l.trim()) ? l : f.replace('1', i++) + l).join('\n'); break; }
      case 'repeat': {
        const n = Math.min(10000, Math.max(1, parseInt(val('o1')) || 1));
        out = Array(n).fill(t).join(val('o2')); info = `(${n} times, ${out.length.toLocaleString('en-IN')} characters)`; break; }
      case 'blank': out = val('o2').repeat(Math.min(2000, Math.max(1, parseInt(val('o1')) || 1))); info = '(invisible – tap Copy and paste it)'; break;
      case 'extract': {
        const re = { email: /[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/g, phone: /(?:\+?\d{1,3}[\s\-]?)?(?:\(?\d{2,5}\)?[\s\-]?)?\d{3,5}[\s\-]?\d{3,5}/g, url: /\bhttps?:\/\/[^\s<>"')]+|\bwww\.[^\s<>"')]+/g, tag: /#[\p{L}\p{N}_]+/gu, num: /-?\d+(?:[.,]\d+)*/g }[val('o1')];
        let a = (t.match(re) || []).map(x => x.trim());
        if (val('o1') === 'phone') a = a.filter(x => x.replace(/\D/g, '').length >= 8);
        if (val('c_u')) a = [...new Set(a)];
        out = a.join('\n'); info = `(${a.length} found)`; break; }
      case 'wa': {
        const s = val('o1'); let i = 1;
        out = lines(t).map(l => {
          if (!l.trim()) return l;
          if (s === '- ' || s === '> ') return s + l;
          if (s === '1. ') return (i++) + '. ' + l;
          const m = /^(\s*)(.*?)(\s*)$/.exec(l); return m[1] + s + m[2] + s + m[3];
        }).join('\n'); break; }
      case 'emoji': out = t.replace(/[\p{Extended_Pictographic}\u{1F1E6}-\u{1F1FF}\u{1F3FB}-\u{1F3FF}️‍⃣]/gu, '').replace(/[ \t]{2,}/g, ' ').replace(/ +\n/g, '\n').trim(); break;
      case 'freq': {
        const words = (t.toLowerCase().match(/[\p{L}\p{N}']+/gu) || []);
        const total = words.length, cnt = {};
        words.forEach(w => { if (val('c_s') && STOP.has(w)) return; cnt[w] = (cnt[w] || 0) + 1; });
        const top = Object.entries(cnt).sort((a, b) => b[1] - a[1]).slice(0, parseInt(val('o1')) || 20);
        $('#tTable', mount).innerHTML = top.length ? `<table class="w-full text-sm"><tr class="text-left opacity-60"><th class="py-1">#</th><th>Word</th><th class="text-right">Count</th><th class="text-right">Density</th></tr>${top.map(([w, c], i) => `<tr class="border-t" style="border-color:var(--border)"><td class="py-1">${i + 1}</td><td><b>${esc(w)}</b></td><td class="text-right">${c}</td><td class="text-right">${(c / total * 100).toFixed(2)}%</td></tr>`).join('')}</table>` : '<p class="opacity-60">Type some text above.</p>';
        out = top.map(([w, c]) => `${w}: ${c} (${(c / total * 100).toFixed(2)}%)`).join('\n'); info = `(${total} words, ${Object.keys(cnt).length} different)`;
        break; }
    }
    $('#tOut', mount).value = out;
    $('#tInfo', mount).textContent = info;
  }
  mount.addEventListener('input', e => { if (e.target.id !== 'tOut') { run(); use(ctx); } });
  mount.addEventListener('change', run);
  run();
  textActions(() => $('#tOut', mount).value, opt.slug);
};

/* ======================= FANCY TEXT ======================= */
function mathMap(upper, lower, digit, exc) {
  return ch => {
    const c = ch.codePointAt(0);
    if (exc && exc[ch]) return exc[ch];
    if (c >= 65 && c <= 90 && upper) return String.fromCodePoint(upper + c - 65);
    if (c >= 97 && c <= 122 && lower) return String.fromCodePoint(lower + c - 97);
    if (c >= 48 && c <= 57 && digit) return String.fromCodePoint(digit + c - 48);
    return ch;
  };
}
const charMap = (from, to) => { const f = Array.from(from), t = Array.from(to); const m = {}; f.forEach((c, i) => { m[c] = t[i]; }); return ch => m[ch] || ch; };
const FANCY = [
  ['Bold', mathMap(0x1D400, 0x1D41A, 0x1D7CE)],
  ['Italic', mathMap(0x1D434, 0x1D44E, 0, { h: 'ℎ' })],
  ['Bold Italic', mathMap(0x1D468, 0x1D482)],
  ['Script', mathMap(0x1D49C, 0x1D4B6, 0, { B: 'ℬ', E: 'ℰ', F: 'ℱ', H: 'ℋ', I: 'ℐ', L: 'ℒ', M: 'ℳ', R: 'ℛ', e: 'ℯ', g: 'ℊ', o: 'ℴ' })],
  ['Bold Script', mathMap(0x1D4D0, 0x1D4EA)],
  ['Fraktur', mathMap(0x1D504, 0x1D51E, 0, { C: 'ℭ', H: 'ℌ', I: 'ℑ', R: 'ℜ', Z: 'ℨ' })],
  ['Bold Fraktur', mathMap(0x1D56C, 0x1D586)],
  ['Double Struck', mathMap(0x1D538, 0x1D552, 0x1D7D8, { C: 'ℂ', H: 'ℍ', N: 'ℕ', P: 'ℙ', Q: 'ℚ', R: 'ℝ', Z: 'ℤ' })],
  ['Sans', mathMap(0x1D5A0, 0x1D5BA, 0x1D7E2)],
  ['Sans Bold', mathMap(0x1D5D4, 0x1D5EE, 0x1D7EC)],
  ['Sans Italic', mathMap(0x1D608, 0x1D622)],
  ['Sans Bold Italic', mathMap(0x1D63C, 0x1D656)],
  ['Monospace', mathMap(0x1D670, 0x1D68A, 0x1D7F6)],
  ['Circled', mathMap(0x24B6, 0x24D0, 0, { 0: '⓪', 1: '①', 2: '②', 3: '③', 4: '④', 5: '⑤', 6: '⑥', 7: '⑦', 8: '⑧', 9: '⑨' })],
  ['Dark Circles', ch => { const c = ch.toUpperCase().codePointAt(0); return c >= 65 && c <= 90 ? String.fromCodePoint(0x1F150 + c - 65) : ch; }],
  ['Squares', ch => { const c = ch.toUpperCase().codePointAt(0); return c >= 65 && c <= 90 ? String.fromCodePoint(0x1F130 + c - 65) : ch; }],
  ['Dark Squares', ch => { const c = ch.toUpperCase().codePointAt(0); return c >= 65 && c <= 90 ? String.fromCodePoint(0x1F170 + c - 65) : ch; }],
  ['Wide', mathMap(0xFF21, 0xFF41, 0xFF10, { ' ': '　' })],
  ['Small Caps', charMap('abcdefghijklmnopqrstuvwxyz', 'ᴀʙᴄᴅᴇꜰɢʜɪᴊᴋʟᴍɴᴏᴘǫʀꜱᴛᴜᴠᴡxʏᴢ')],
  ['Superscript', charMap('abcdefghijklmnoprstuvwxyzABDEGHIJKLMNOPRTUVW0123456789', 'ᵃᵇᶜᵈᵉᶠᵍʰⁱʲᵏˡᵐⁿᵒᵖʳˢᵗᵘᵛʷˣʸᶻᴬᴮᴰᴱᴳᴴᴵᴶᴷᴸᴹᴺᴼᴾᴿᵀᵁⱽᵂ⁰¹²³⁴⁵⁶⁷⁸⁹')],
  ['Bubble Greek', charMap('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'αв¢∂єƒgнιנкℓмησρqяѕтυνωχуzαв¢∂єƒgнιנкℓмησρqяѕтυνωχуz')],
];
const WRAP = [
  ['Strikethrough', s => Array.from(s).map(c => c + '̶').join('')],
  ['Underline', s => Array.from(s).map(c => c + '̲').join('')],
  ['Slashed', s => Array.from(s).map(c => c + '̸').join('')],
  ['Upside Down', s => Array.from(s).reverse().map(charMap('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.,!?\'"()[]{}<>_&', 'ɐqɔpǝɟƃɥᴉɾʞlɯuodbɹsʇnʌʍxʎz∀ᗺƆᗡƎℲ⅁HIſꓘ˥WNOԀΌᴚS⊥∩ΛMX⅄Z0ƖᄅƐㄣϛ9ㄥ86˙\'¡¿,„)(][}{><‾⅋')).join('')],
  ['Spaced', s => Array.from(s).join(' ')],
  ['Hearts', s => '♥ ' + s + ' ♥'],
  ['Stars', s => '★彡 ' + s + ' 彡★'],
  ['Royal', s => '꧁༒☬ ' + s + ' ☬༒꧂'],
  ['Gamer', s => '亗 ' + s + ' 亗'],
  ['Sparkle', s => '✨ ' + s + ' ✨'],
  ['Brackets', s => '【 ' + s + ' 】'],
  ['Waves', s => '•°•.° ' + s + ' °.•°•'],
  ['Crown', s => '♛ ' + s + ' ♛'],
];
K.fancy = function (mount, opt, ctx) {
  mount.appendChild(el(`<div><label class="kt-label" for="fIn">Your text or name</label>
    <input class="kt-input text-lg" id="fIn" value="Krishna Tools" maxlength="200">
    <div id="fList" class="mt-4 grid gap-2 kt-result"></div></div>`));
  const list = $('#fList', mount);
  function run() {
    const s = $('#fIn', mount).value || 'Krishna Tools';
    const rows = FANCY.map(([n, f]) => [n, Array.from(s).map(f).join('')])
      .concat(WRAP.map(([n, f]) => [n, f(n === 'Hearts' || n === 'Stars' || n === 'Royal' || n === 'Gamer' || n === 'Crown' ? Array.from(s).map(FANCY[3][1]).join('') : s)]));
    list.innerHTML = rows.map(([n, v], i) => `<div class="kt-card p-3 flex items-center gap-3"><div class="flex-1 min-w-0"><div class="text-xs opacity-60">${n}</div><div class="text-lg break-words" data-v="${i}">${esc(v)}</div></div><button class="btn btn-ghost text-sm shrink-0" data-c="${i}">Copy</button></div>`).join('');
    list.querySelectorAll('[data-c]').forEach(b => b.onclick = () => { copy(rows[+b.dataset.c][1]); use(ctx); });
  }
  $('#fIn', mount).oninput = run; run();
  setActions([]);
};

/* ======================= OCR ======================= */
K.ocr = function (mount, opt, ctx) {
  const top = el(`<div class="grid sm:grid-cols-2 gap-3 mb-3"><div><label class="kt-label" for="oLang">Language of the text</label>
    <select class="kt-select" id="oLang"><option value="eng">English</option><option value="guj">Gujarati</option><option value="hin">Hindi</option><option value="eng+guj">English + Gujarati</option><option value="eng+hin">English + Hindi</option><option value="mar">Marathi</option></select></div>
    <div class="text-xs opacity-70 self-end">Tip: crop to the text, keep the photo straight and well lit. Handwriting is not read reliably.</div></div>`);
  mount.appendChild(top);
  const input = fileInput(mount, { accept: 'image/*', hint: 'JPG, PNG, WEBP or a screenshot' });
  const box = el(`<div class="kt-result mt-4"><div id="oStat" class="text-sm opacity-70"></div>
    <img id="oPrev" class="max-h-56 rounded-lg mt-2 hidden" alt="Selected image">
    <label class="kt-label mt-3" for="oOut">Extracted text</label><textarea class="kt-input" id="oOut" rows="10" placeholder="The text from your image will appear here"></textarea></div>`);
  mount.appendChild(box);
  let busy = false;
  input.addEventListener('change', async () => {
    const f = input.files && input.files[0]; if (!f || busy) return;
    busy = true;
    const prev = $('#oPrev', box); prev.src = URL.createObjectURL(f); prev.classList.remove('hidden');
    const stat = $('#oStat', box);
    try {
      stat.textContent = 'Loading the OCR engine (first time takes a little longer)…';
      await loadScript('https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js');
      const res = await window.Tesseract.recognize(f, $('#oLang', mount).value, {
        logger: m => { if (m.status) stat.textContent = m.status.replace(/^\w/, c => c.toUpperCase()) + (m.progress ? ' ' + Math.round(m.progress * 100) + '%' : '') + '…'; },
      });
      const text = (res.data.text || '').trim();
      $('#oOut', box).value = text;
      stat.textContent = text ? `✔ Done — ${text.split(/\s+/).length} words found (confidence ${Math.round(res.data.confidence)}%)` : 'No text found. Try a clearer or larger image, or choose the right language.';
      use(ctx);
    } catch (e) { console.error(e); stat.textContent = 'Could not read the image. Check your internet connection (the engine downloads once) and try again.'; }
    busy = false;
  });
  textActions(() => $('#oOut', box).value, 'image-text');
};

/* ======================= SPEECH TO TEXT ======================= */
K.stt = function (mount, opt, ctx) {
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  mount.appendChild(el(`<div class="space-y-3">
    <div class="grid sm:grid-cols-2 gap-3"><div><label class="kt-label" for="sLang">Language</label><select class="kt-select" id="sLang">
      <option value="gu-IN">Gujarati (ગુજરાતી)</option><option value="hi-IN">Hindi (हिंदी)</option><option value="en-IN" selected>English (India)</option><option value="en-US">English (US)</option><option value="mr-IN">Marathi</option><option value="bn-IN">Bengali</option><option value="ta-IN">Tamil</option><option value="te-IN">Telugu</option><option value="kn-IN">Kannada</option><option value="ml-IN">Malayalam</option><option value="pa-IN">Punjabi</option><option value="ur-IN">Urdu</option></select></div>
      <div class="flex items-end gap-2"><button class="btn btn-cta flex-1" id="sGo"><i data-lucide="mic"></i> Start speaking</button></div></div>
    <div id="sStat" class="text-sm opacity-70">${SR ? 'Tap Start and allow the microphone.' : '⚠️ Your browser does not support voice typing. Please open this page in Google Chrome or Microsoft Edge.'}</div>
    <div class="kt-result"><textarea class="kt-input text-lg" id="sOut" rows="10" placeholder="Your words will appear here…"></textarea>
    <div id="sLive" class="text-sm opacity-60 mt-1"></div></div></div>`));
  if (window.lucide) lucide.createIcons();
  let rec = null, on = false;
  const btn = $('#sGo', mount), out = $('#sOut', mount);
  function stop() { on = false; if (rec) try { rec.stop(); } catch (e) {} btn.innerHTML = '<i data-lucide="mic"></i> Start speaking'; btn.classList.add('btn-cta'); if (window.lucide) lucide.createIcons(); $('#sStat', mount).textContent = 'Stopped. Tap Start to continue.'; }
  btn.onclick = () => {
    if (!SR) { toast('Voice typing works in Chrome or Edge', true); return; }
    if (on) { stop(); return; }
    rec = new SR(); rec.lang = $('#sLang', mount).value; rec.continuous = true; rec.interimResults = true;
    rec.onresult = e => {
      let fin = '', mid = '';
      for (let i = e.resultIndex; i < e.results.length; i++) { const r = e.results[i]; if (r.isFinal) fin += r[0].transcript; else mid += r[0].transcript; }
      if (fin) { out.value = (out.value && !/\s$/.test(out.value) ? out.value + ' ' : out.value) + fin.trim(); use(ctx); }
      $('#sLive', mount).textContent = mid;
    };
    rec.onerror = e => { $('#sStat', mount).textContent = e.error === 'not-allowed' ? 'Microphone permission was blocked. Allow it from the address bar and try again.' : 'Error: ' + e.error; };
    rec.onend = () => { if (on) try { rec.start(); } catch (e) { stop(); } };   // keep listening until the user stops
    try { rec.start(); on = true; btn.innerHTML = '<i data-lucide="square"></i> Stop'; btn.classList.remove('btn-cta'); if (window.lucide) lucide.createIcons(); $('#sStat', mount).textContent = '🎙️ Listening… speak now'; }
    catch (e) { toast('Could not start the microphone', true); }
  };
  $('#sLang', mount).onchange = () => { if (on) { stop(); btn.click(); } };
  textActions(() => out.value, 'voice-typing');
};

/* ======================= TEXT TO SPEECH ======================= */
K.tts = function (mount, opt, ctx) {
  const ok = 'speechSynthesis' in window;
  mount.appendChild(el(`<div class="space-y-3">
    <textarea class="kt-input" id="vIn" rows="7">Welcome to Krishna Tools. Paste any text here and press Play to listen.</textarea>
    <div class="grid sm:grid-cols-3 gap-3"><div class="sm:col-span-3"><label class="kt-label" for="vVoice">Voice</label><select class="kt-select" id="vVoice"></select></div>
      <div><label class="kt-label" for="vRate">Speed <span id="vRateV">1</span>×</label><input type="range" id="vRate" min="0.5" max="2" step="0.1" value="1" class="w-full"></div>
      <div><label class="kt-label" for="vPitch">Pitch <span id="vPitchV">1</span></label><input type="range" id="vPitch" min="0.5" max="2" step="0.1" value="1" class="w-full"></div></div>
    <div id="vStat" class="text-sm opacity-70">${ok ? '' : '⚠️ Your browser does not support text to speech.'}</div></div>`));
  const sel = $('#vVoice', mount);
  let voices = [];
  function load() {
    voices = speechSynthesis.getVoices().slice().sort((a, b) => (/IN$/.test(b.lang) - /IN$/.test(a.lang)) || a.lang.localeCompare(b.lang));
    sel.innerHTML = voices.map((v, i) => `<option value="${i}">${esc(v.name)} (${esc(v.lang)})</option>`).join('') || '<option>Default voice</option>';
    const pref = voices.findIndex(v => v.lang === 'en-IN'); if (pref > -1) sel.value = pref;
    if (!voices.some(v => /^gu/.test(v.lang))) $('#vStat', mount).textContent = 'No Gujarati voice is installed on this device. Hindi and English voices work on most phones; Gujarati may be available in Chrome on Android (Settings → Text-to-speech).';
  }
  if (ok) { load(); speechSynthesis.onvoiceschanged = load; }
  ['vRate', 'vPitch'].forEach(id => $('#' + id, mount).oninput = e => { $('#' + id + 'V', mount).textContent = e.target.value; });
  function play() {
    if (!ok) return;
    speechSynthesis.cancel();
    const text = $('#vIn', mount).value.trim(); if (!text) { toast('Type some text first', true); return; }
    // Long text is split into sentences (some browsers stop after ~15 seconds of one utterance).
    const parts = text.match(/[^.!?।\n]+[.!?।]?/g) || [text];
    parts.forEach(p => { const u = new SpeechSynthesisUtterance(p.trim()); const v = voices[+sel.value]; if (v) { u.voice = v; u.lang = v.lang; } u.rate = +$('#vRate', mount).value; u.pitch = +$('#vPitch', mount).value; speechSynthesis.speak(u); });
    use(ctx);
  }
  setActions([
    { label: 'Play', cls: 'btn-cta', icon: 'play', onClick: play },
    { label: 'Pause / Resume', icon: 'pause', onClick: () => { if (speechSynthesis.paused) speechSynthesis.resume(); else speechSynthesis.pause(); } },
    { label: 'Stop', icon: 'square', onClick: () => speechSynthesis.cancel() },
  ]);
};

/* ======================= NOTEPAD ======================= */
K.notepad = function (mount, opt, ctx) {
  let notes = store.get('notes', null) || { a: { t: 'My note', x: '' } };
  let cur = store.get('note-cur', Object.keys(notes)[0]); if (!notes[cur]) cur = Object.keys(notes)[0];
  mount.appendChild(el(`<div class="space-y-2">
    <div class="flex gap-2 flex-wrap"><select class="kt-select flex-1" id="nSel"></select><input class="kt-input flex-1" id="nTitle" placeholder="Note title">
      <button class="btn btn-ghost text-sm" id="nNew">+ New</button><button class="btn btn-ghost text-sm" id="nDel">Delete</button></div>
    <textarea class="kt-input" id="nTxt" rows="16" placeholder="Start typing… it saves automatically in this browser." style="font-size:16px;line-height:1.6"></textarea>
    <div class="text-xs opacity-60 flex justify-between"><span id="nCount"></span><span id="nSaved"></span></div></div>`));
  const sel = $('#nSel', mount), txt = $('#nTxt', mount), title = $('#nTitle', mount);
  function fill() {
    sel.innerHTML = Object.entries(notes).map(([k, n]) => `<option value="${k}" ${k === cur ? 'selected' : ''}>${esc(n.t || 'Untitled')}</option>`).join('');
    txt.value = notes[cur].x; title.value = notes[cur].t; count();
  }
  function count() { const v = txt.value; $('#nCount', mount).textContent = `${(v.match(/\S+/g) || []).length} words · ${v.length} characters`; }
  function save() { notes[cur].x = txt.value; notes[cur].t = title.value; store.set('notes', notes); store.set('note-cur', cur); $('#nSaved', mount).textContent = '✔ Saved ' + new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' }); }
  txt.oninput = () => { save(); count(); use(ctx); };
  title.oninput = () => { save(); sel.options[sel.selectedIndex].textContent = title.value || 'Untitled'; };
  sel.onchange = () => { cur = sel.value; store.set('note-cur', cur); fill(); };
  $('#nNew', mount).onclick = () => { cur = 'n' + Date.now(); notes[cur] = { t: 'Note ' + (Object.keys(notes).length + 1), x: '' }; save(); fill(); txt.focus(); };
  $('#nDel', mount).onclick = () => { if (!confirm('Delete this note?')) return; delete notes[cur]; if (!Object.keys(notes).length) notes = { a: { t: 'My note', x: '' } }; cur = Object.keys(notes)[0]; store.set('notes', notes); fill(); };
  fill();
  setActions([
    { label: 'Download .txt', cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(txtBlob(txt.value), (title.value || 'note').replace(/[^\w\- ]+/g, '') + '.txt') },
    { label: 'Copy', icon: 'copy', onClick: () => copy(txt.value) },
    { label: 'Print', icon: 'printer', onClick: () => printHtml(`<h2 style="font-family:Arial">${esc(title.value)}</h2><pre style="white-space:pre-wrap;font-family:Arial;font-size:14px">${esc(txt.value)}</pre>`, title.value) },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(txt.value) },
  ]);
};

/* ======================= TYPING TEST ======================= */
const PASSAGES = [
  'Typing is a skill that improves with daily practice. Sit straight, keep your fingers on the home row and look at the screen instead of the keyboard. Speed will come naturally once your accuracy is good, so focus on typing every word correctly before trying to go faster.',
  'India is a land of many languages, festivals and traditions. From the deserts of Rajasthan to the backwaters of Kerala, every state has its own food, music and culture. Millions of people travel across the country every year to experience this amazing variety.',
  'A small business owner has to manage many things at the same time. Keeping accounts, talking to customers, ordering stock and paying bills on time are all part of the daily routine. Simple digital tools can save hours of work and reduce costly mistakes.',
  'The computer has changed the way we learn, work and communicate. Today students can attend online classes, farmers can check crop prices and shopkeepers can accept digital payments from anywhere. Learning basic computer skills is now as important as reading and writing.',
  'Good health depends on regular exercise, a balanced diet and enough sleep. Drinking plenty of water, walking for at least thirty minutes and eating fresh fruits and vegetables can keep many diseases away. Small healthy habits followed every day make a big difference over time.',
];
K.typing = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="space-y-3">
    <div class="flex flex-wrap gap-2 items-center"><select class="kt-select w-auto" id="tyT"><option value="60">1 minute</option><option value="120">2 minutes</option><option value="300">5 minutes</option></select>
      <button class="btn btn-ghost text-sm" id="tyNew">↻ New passage</button>
      <div class="ml-auto flex gap-4 text-sm"><span>⏱ <b id="tyTime">1:00</b></span><span>WPM <b id="tyW">0</b></span><span>Accuracy <b id="tyA">100</b>%</span></div></div>
    <div id="tyText" class="kt-card p-4 text-lg leading-relaxed select-none" style="font-family:ui-monospace,Menlo,monospace"></div>
    <textarea class="kt-input text-lg" id="tyIn" rows="4" placeholder="Start typing here – the timer starts with your first key" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
    <div id="tyRes" class="kt-result"></div></div>`));
  let text = '', start = 0, timer = 0, limit = 60, done = false, pi = rnd(PASSAGES.length);
  const box = $('#tyText', mount), inp = $('#tyIn', mount);
  function reset(newText) {
    if (newText) pi = (pi + 1) % PASSAGES.length;
    text = PASSAGES[pi] + ' ' + PASSAGES[(pi + 1) % PASSAGES.length];
    clearInterval(timer); start = 0; done = false; limit = +$('#tyT', mount).value;
    inp.value = ''; inp.disabled = false; $('#tyRes', mount).innerHTML = ''; paint(); stats();
  }
  function paint() {
    const v = inp.value;
    box.innerHTML = Array.from(text).map((c, i) => {
      const st = i < v.length ? (v[i] === c ? 'color:#059669' : 'color:#fff;background:#DC2626;border-radius:2px') : (i === v.length ? 'border-bottom:2px solid #1E3A8A' : 'opacity:.55');
      return `<span style="${st}">${esc(c)}</span>`;
    }).join('');
  }
  function stats() {
    const v = inp.value, el2 = start ? (Date.now() - start) / 1000 : 0;
    let ok = 0; for (let i = 0; i < v.length; i++) if (v[i] === text[i]) ok++;
    const wpm = el2 ? Math.round(ok / 5 / (el2 / 60)) : 0, acc = v.length ? Math.round(ok / v.length * 100) : 100;
    const left = Math.max(0, limit - el2);
    $('#tyTime', mount).textContent = Math.floor(left / 60) + ':' + String(Math.floor(left % 60)).padStart(2, '0');
    $('#tyW', mount).textContent = wpm; $('#tyA', mount).textContent = acc;
    return { wpm, acc, ok, typed: v.length, errors: v.length - ok, el: el2 };
  }
  function finish() {
    done = true; clearInterval(timer); inp.disabled = true;
    const s = stats();
    const level = s.wpm >= 60 ? 'Excellent – professional speed 🏆' : s.wpm >= 40 ? 'Very good – above average 👍' : s.wpm >= 30 ? 'Good – meets most data entry tests ✅' : 'Keep practising – aim for 30+ WPM 💪';
    $('#tyRes', mount).innerHTML = `<div class="kt-card p-4" style="background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))"><div class="text-2xl font-extrabold" style="color:var(--peacock-teal)">${s.wpm} WPM · ${s.acc}% accuracy</div><div class="text-sm mt-1">${level}</div><div class="text-xs opacity-70 mt-1">${s.typed} characters typed, ${s.errors} mistakes, in ${Math.round(s.el)} seconds. SSC CGL needs 35 WPM in English; CHSL data entry needs about 8,000 key depressions per hour.</div></div>`;
    ctx._resultText = `My typing speed: ${s.wpm} WPM with ${s.acc}% accuracy on Krishna Tools\n${KT.url}/tool/${opt.slug}`;
    use(ctx);
  }
  inp.addEventListener('input', () => {
    if (done) return;
    if (!start) { start = Date.now(); timer = setInterval(() => { stats(); if ((Date.now() - start) / 1000 >= limit) finish(); }, 250); }
    paint(); stats();
    if (inp.value.length >= text.length) finish();
  });
  inp.addEventListener('paste', e => e.preventDefault());
  $('#tyT', mount).onchange = () => reset(false);
  $('#tyNew', mount).onclick = () => reset(true);
  reset(false);
  setActions([
    { label: 'Restart', cls: 'btn-cta', icon: 'rotate-ccw', onClick: () => reset(false) },
    { label: 'Share score', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(ctx._resultText || 'Test your typing speed: ' + KT.url + '/tool/' + opt.slug) },
  ]);
};

/* ======================= WHATSAPP LINK ======================= */
K.walink = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="grid sm:grid-cols-3 gap-3">
    <div><label class="kt-label" for="wC">Country code</label><input class="kt-input" id="wC" value="91" inputmode="numeric"></div>
    <div class="sm:col-span-2"><label class="kt-label" for="wN">Mobile number</label><input class="kt-input text-lg" id="wN" placeholder="98765 43210" inputmode="tel"></div>
    <div class="sm:col-span-3"><label class="kt-label" for="wM">Message (optional)</label><textarea class="kt-input" id="wM" rows="3" placeholder="Hello, I want to know about…"></textarea></div>
    <div class="sm:col-span-3 kt-result"><div class="kt-card p-3 break-all text-sm" id="wL"></div><div id="wQ" class="mt-3 flex justify-center"></div></div></div>`));
  const link = () => {
    let n = $('#wN', mount).value.replace(/\D/g, ''); const c = $('#wC', mount).value.replace(/\D/g, '');
    if (n.length > 10 && c && n.startsWith(c)) n = n.slice(c.length);
    n = n.replace(/^0+/, '');
    const m = $('#wM', mount).value.trim();
    return n ? `https://wa.me/${c}${n}${m ? '?text=' + encodeURIComponent(m) : ''}` : '';
  };
  const upd = () => { const l = link(); $('#wL', mount).textContent = l || 'Enter a number to make the link'; };
  mount.addEventListener('input', upd); upd();
  setActions([
    { label: 'Open WhatsApp chat', cls: 'btn-wa', icon: 'message-circle', onClick: () => { const l = link(); if (!l) return toast('Enter the mobile number', true); window.open(l, '_blank'); use(ctx); } },
    { label: 'Copy link', icon: 'copy', onClick: () => { const l = link(); if (l) copy(l); } },
    { label: 'Show QR code', icon: 'qr-code', onClick: async () => { const l = link(); if (!l) return toast('Enter the mobile number', true); const c = await qrCanvas(l, 240); const q = $('#wQ', mount); q.innerHTML = ''; q.appendChild(c); use(ctx); } },
    { label: 'Download QR', icon: 'download', onClick: async () => { const l = link(); if (!l) return; downloadBlob(await canvasBlob(await qrCanvas(l, 600)), 'whatsapp-qr.png'); } },
  ]);
};

/* ======================= UPI QR ======================= */
K.upiqr = function (mount, opt, ctx) {
  const saved = store.get('upi', {});
  mount.appendChild(el(`<div class="grid sm:grid-cols-2 gap-3">
    <div><label class="kt-label" for="uPa">UPI ID</label><input class="kt-input" id="uPa" placeholder="yourname@okaxis" value="${esc(saved.pa || '')}"></div>
    <div><label class="kt-label" for="uPn">Name (shop / person)</label><input class="kt-input" id="uPn" placeholder="Krishna Traders" value="${esc(saved.pn || '')}"></div>
    <div><label class="kt-label" for="uAm">Amount (optional)</label><input class="kt-input" id="uAm" type="number" step="0.01" min="0" placeholder="Leave empty for any amount"></div>
    <div><label class="kt-label" for="uTn">Note (optional)</label><input class="kt-input" id="uTn" placeholder="Bill no. 101"></div>
    <div class="sm:col-span-2 kt-result flex flex-col items-center gap-2"><canvas id="uCv" width="600" height="840" style="width:300px;max-width:100%;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.12)"></canvas>
    <div class="text-xs opacity-60 text-center">Scan once with your own UPI app to check the name before printing.</div></div></div>`));
  const cv = $('#uCv', mount);
  const v = id => $('#' + id, mount).value.trim();
  const upi = () => {
    const pa = v('uPa'); if (!/^[\w.\-]{2,}@[a-zA-Z][\w.\-]*$/.test(pa)) return '';
    const q = new URLSearchParams({ pa, pn: v('uPn') || pa }); const am = parseFloat(v('uAm'));
    if (am > 0) q.set('am', am.toFixed(2)); q.set('cu', 'INR'); if (v('uTn')) q.set('tn', v('uTn'));
    return 'upi://pay?' + q.toString().replace(/\+/g, '%20');
  };
  let t = 0;
  async function draw() {
    store.set('upi', { pa: v('uPa'), pn: v('uPn') });
    const g = cv.getContext('2d'), W = 600, Hh = 840;
    g.fillStyle = '#fff'; g.fillRect(0, 0, W, Hh);
    g.fillStyle = '#1E3A8A'; g.fillRect(0, 0, W, 130);
    g.fillStyle = '#fff'; g.textAlign = 'center'; g.font = 'bold 40px Arial'; g.fillText((v('uPn') || 'Your Shop Name').slice(0, 24), W / 2, 70);
    g.font = '24px Arial'; g.fillText('Scan & Pay with any UPI app', W / 2, 110);
    const u = upi();
    if (u) { const q = await qrCanvas(u, 420); g.drawImage(q, 90, 165, 420, 420); }
    else { g.fillStyle = '#F1F5F9'; g.fillRect(90, 165, 420, 420); g.fillStyle = '#64748B'; g.font = '26px Arial'; g.fillText('Enter a valid UPI ID', W / 2, 380); }
    g.fillStyle = '#111'; g.font = 'bold 30px Arial'; g.fillText(v('uPa') || 'name@bank', W / 2, 640);
    const am = parseFloat(v('uAm')); if (am > 0) { g.fillStyle = '#059669'; g.font = 'bold 44px Arial'; g.fillText(inr(am), W / 2, 700); }
    g.fillStyle = '#475569'; g.font = '22px Arial'; g.fillText('Google Pay • PhonePe • Paytm • BHIM • Any bank app', W / 2, 770);
    g.fillStyle = '#E2E8F0'; g.fillRect(0, 800, W, 40); g.fillStyle = '#64748B'; g.font = '18px Arial'; g.fillText('Made free at tools.akdwk.in', W / 2, 827);
  }
  mount.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { draw(); use(ctx); }, 250); });
  draw();
  setActions([
    { label: 'Download PNG', cls: 'btn-cta', icon: 'download', onClick: async () => { if (!upi()) return toast('Enter a valid UPI ID', true); await draw(); downloadBlob(await canvasBlob(cv), 'upi-qr.png'); use(ctx); } },
    { label: 'Print', icon: 'printer', onClick: async () => { if (!upi()) return toast('Enter a valid UPI ID', true); await draw(); printHtml(`<div style="text-align:center"><img src="${cv.toDataURL()}" style="width:90mm"></div>`, 'UPI QR'); } },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: async () => { if (!upi()) return toast('Enter a valid UPI ID', true); await draw(); waShareFile(await canvasBlob(cv), 'upi-qr.png', 'Pay using this UPI QR code'); } },
    { label: 'Copy UPI link', icon: 'link', onClick: () => { const u = upi(); if (u) copy(u); else toast('Enter a valid UPI ID', true); } },
  ]);
};

/* ======================= vCard QR ======================= */
K.vcardqr = function (mount, opt, ctx) {
  const F = [['fn', 'Full name', 'Akshay Patel'], ['org', 'Business / company', 'AK Computer'], ['title', 'Designation', 'Owner'], ['tel', 'Mobile', '+91 98765 43210'], ['tel2', 'Other phone (optional)', ''], ['email', 'Email', ''], ['url', 'Website', ''], ['adr', 'Address', 'Dwarka, Gujarat']];
  const saved = store.get('vcard', {});
  mount.appendChild(el(`<div class="grid sm:grid-cols-2 gap-3">${F.map(([k, l, d]) => `<div><label class="kt-label" for="v_${k}">${l}</label><input class="kt-input" id="v_${k}" value="${esc(saved[k] ?? d)}"></div>`).join('')}
    <div class="sm:col-span-2 kt-result flex justify-center" id="vQ"></div></div>`));
  const g = k => $('#v_' + k, mount).value.trim();
  const vcf = () => {
    const e = s => s.replace(/([,;\\])/g, '\\$1');
    const parts = g('fn').split(/\s+/);
    return ['BEGIN:VCARD', 'VERSION:3.0', `N:${e(parts.slice(1).join(' '))};${e(parts[0] || '')};;;`, `FN:${e(g('fn'))}`, g('org') && `ORG:${e(g('org'))}`, g('title') && `TITLE:${e(g('title'))}`,
      g('tel') && `TEL;TYPE=CELL:${g('tel')}`, g('tel2') && `TEL;TYPE=WORK:${g('tel2')}`, g('email') && `EMAIL:${g('email')}`, g('url') && `URL:${g('url')}`, g('adr') && `ADR;TYPE=WORK:;;${e(g('adr'))};;;;`, 'END:VCARD'].filter(Boolean).join('\n');
  };
  let t = 0;
  async function draw() { const s = {}; F.forEach(([k]) => { s[k] = g(k); }); store.set('vcard', s); const q = $('#vQ', mount); const c = await qrCanvas(vcf(), 260); q.innerHTML = ''; q.appendChild(c); }
  mount.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { draw(); use(ctx); }, 300); });
  draw();
  setActions([
    { label: 'Download QR (PNG)', cls: 'btn-cta', icon: 'download', onClick: async () => { downloadBlob(await canvasBlob(await qrCanvas(vcf(), 800)), 'contact-qr.png'); use(ctx); } },
    { label: 'Download contact (.vcf)', icon: 'contact', onClick: () => downloadBlob(new Blob([vcf()], { type: 'text/vcard' }), (g('fn') || 'contact').replace(/\W+/g, '-') + '.vcf') },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: async () => waShareFile(await canvasBlob(await qrCanvas(vcf(), 800)), 'contact-qr.png', 'Scan to save my contact') },
  ]);
};

/* ======================= RANDOM NUMBER ======================= */
K.random = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="grid sm:grid-cols-4 gap-3">
    <div><label class="kt-label" for="rA">From</label><input class="kt-input" id="rA" type="number" value="1"></div>
    <div><label class="kt-label" for="rB">To</label><input class="kt-input" id="rB" type="number" value="100"></div>
    <div><label class="kt-label" for="rN">How many</label><input class="kt-input" id="rN" type="number" value="1" min="1" max="1000"></div>
    <div class="flex flex-col justify-end gap-1 text-sm"><label class="flex gap-2"><input type="checkbox" id="rU" checked> No repeats</label><label class="flex gap-2"><input type="checkbox" id="rS"> Sort</label></div>
    <div class="sm:col-span-4 kt-result text-center"><div id="rOut" class="text-5xl font-extrabold py-6 break-words" style="color:var(--peacock-teal)">?</div><div id="rHist" class="text-xs opacity-60"></div></div></div>`));
  const hist = [];
  function gen() {
    let a = Math.ceil(+$('#rA', mount).value), b = Math.floor(+$('#rB', mount).value); if (a > b) [a, b] = [b, a];
    const n = Math.min(1000, Math.max(1, parseInt($('#rN', mount).value) || 1)), range = b - a + 1;
    if ($('#rU', mount).checked && n > range) return toast(`Only ${range} different numbers exist in this range`, true);
    let out = [];
    if ($('#rU', mount).checked) { const s = new Set(); while (s.size < n) s.add(a + rnd(range)); out = [...s]; } else for (let i = 0; i < n; i++) out.push(a + rnd(range));
    if ($('#rS', mount).checked) out.sort((x, y) => x - y);
    const o = $('#rOut', mount); o.style.fontSize = n > 20 ? '1.25rem' : (n > 3 ? '2rem' : ''); o.textContent = out.join(', ');
    hist.unshift(out.join(', ')); $('#rHist', mount).textContent = 'Previous: ' + hist.slice(1, 6).join(' | ');
    ctx._resultText = 'Random number(s): ' + out.join(', '); use(ctx);
  }
  setActions([{ label: 'Generate', cls: 'btn-cta', icon: 'dices', onClick: gen }, { label: 'Copy', icon: 'copy', onClick: () => copy($('#rOut', mount).textContent) }]);
};

/* ======================= COIN FLIP ======================= */
K.coin = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="text-center py-4">
    <style>.kt-coin{width:170px;height:170px;margin:0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:800;color:#7c5a00;background:radial-gradient(circle at 35% 30%,#fff6c2,#f5c518 45%,#b8860b);box-shadow:0 8px 24px rgba(0,0,0,.25),inset 0 0 0 8px rgba(255,255,255,.25);cursor:pointer;user-select:none}.kt-coin.spin{animation:ktflip 1s ease-out}@keyframes ktflip{0%{transform:rotateY(0) translateY(0)}50%{transform:rotateY(900deg) translateY(-60px)}100%{transform:rotateY(1800deg) translateY(0)}}</style>
    <div class="kt-coin" id="cC">TAP</div><div id="cR" class="text-3xl font-extrabold mt-5" style="color:var(--peacock-teal)">Tap the coin to toss</div>
    <div class="mt-3 text-sm opacity-70">Heads: <b id="cH">0</b> · Tails: <b id="cT">0</b></div></div>`));
  let h = 0, tl = 0, busy = false;
  function flip() {
    if (busy) return; busy = true;
    const c = $('#cC', mount); c.classList.remove('spin'); void c.offsetWidth; c.classList.add('spin'); c.textContent = '';
    $('#cR', mount).textContent = '…';
    setTimeout(() => { const head = rnd(2) === 0; if (head) h++; else tl++; c.textContent = head ? 'HEADS' : 'TAILS'; $('#cR', mount).textContent = head ? '🪙 Heads!' : '🪙 Tails!'; $('#cH', mount).textContent = h; $('#cT', mount).textContent = tl; busy = false; ctx._resultText = 'Coin toss: ' + (head ? 'Heads' : 'Tails'); use(ctx); }, 1000);
  }
  $('#cC', mount).onclick = flip;
  setActions([{ label: 'Toss coin', cls: 'btn-cta', icon: 'refresh-cw', onClick: flip }, { label: 'Reset count', icon: 'rotate-ccw', onClick: () => { h = tl = 0; $('#cH', mount).textContent = 0; $('#cT', mount).textContent = 0; } }]);
};

/* ======================= DICE ======================= */
K.dice = function (mount, opt, ctx) {
  const FACES = ['⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];
  mount.appendChild(el(`<div class="text-center"><div class="flex justify-center items-center gap-2"><label class="kt-label m-0" for="dN">Number of dice</label><select class="kt-select w-auto" id="dN">${[1, 2, 3, 4, 5, 6].map(n => `<option ${n === 1 ? 'selected' : ''}>${n}</option>`).join('')}</select></div>
    <div id="dBox" class="flex flex-wrap justify-center gap-2 my-6 cursor-pointer select-none" style="font-size:96px;line-height:1;color:#1E3A8A">⚀</div>
    <div id="dTot" class="text-2xl font-extrabold" style="color:var(--peacock-teal)">Tap to roll</div></div>`));
  let busy = false;
  function roll() {
    if (busy) return; busy = true;
    const n = +$('#dN', mount).value, box = $('#dBox', mount); let k = 0;
    const iv = setInterval(() => {
      const vals = Array.from({ length: n }, () => rnd(6));
      box.innerHTML = vals.map(x => `<span>${FACES[x]}</span>`).join('');
      if (++k >= 10) { clearInterval(iv); const tot = vals.reduce((s, x) => s + x + 1, 0); $('#dTot', mount).textContent = n > 1 ? `${vals.map(x => x + 1).join(' + ')} = ${tot}` : `You rolled ${tot}`; ctx._resultText = 'Dice: ' + vals.map(x => x + 1).join(', '); busy = false; use(ctx); }
    }, 70);
  }
  $('#dBox', mount).onclick = roll;
  setActions([{ label: 'Roll', cls: 'btn-cta', icon: 'dice-5', onClick: roll }]);
};

/* ======================= SPIN THE WHEEL ======================= */
K.wheel = function (mount, opt, ctx) {
  const COLORS = ['#1E3A8A', '#059669', '#EA580C', '#DB2777', '#7C3AED', '#0891B2', '#CA8A04', '#DC2626', '#4F46E5', '#0F766E'];
  mount.appendChild(el(`<div class="grid md:grid-cols-[1fr,240px] gap-4 items-start">
    <div class="relative mx-auto" style="max-width:420px;width:100%"><canvas id="wC" width="840" height="840" style="width:100%;cursor:pointer"></canvas>
      <div style="position:absolute;top:50%;right:-6px;transform:translateY(-50%);width:0;height:0;border-top:16px solid transparent;border-bottom:16px solid transparent;border-right:30px solid #111"></div></div>
    <div><label class="kt-label" for="wN">Names / options (one per line)</label><textarea class="kt-input" id="wN" rows="10">${esc(store.get('wheel', 'Rahul\nPriya\nAmit\nNeha\nKaran\nDivya'))}</textarea></div>
    <div class="md:col-span-2 kt-result text-center"><div id="wW" class="text-3xl font-extrabold" style="color:var(--peacock-teal)"></div></div></div>`));
  const cv = $('#wC', mount), g = cv.getContext('2d');
  let ang = 0, spinning = false;
  const names = () => $('#wN', mount).value.split('\n').map(s => s.trim()).filter(Boolean).slice(0, 100);
  function draw() {
    const n = names(), R = 410, C = 420; g.clearRect(0, 0, 840, 840);
    if (!n.length) return;
    const step = 2 * Math.PI / n.length;
    n.forEach((nm, i) => {
      g.beginPath(); g.moveTo(C, C); g.arc(C, C, R, ang + i * step, ang + (i + 1) * step); g.closePath();
      g.fillStyle = COLORS[i % COLORS.length]; g.fill(); g.strokeStyle = '#fff'; g.lineWidth = 3; g.stroke();
      g.save(); g.translate(C, C); g.rotate(ang + (i + 0.5) * step); g.textAlign = 'right'; g.fillStyle = '#fff';
      g.font = `bold ${Math.max(18, Math.min(40, 400 / n.length + 14))}px Arial`; g.fillText(nm.slice(0, 18), R - 24, 12); g.restore();
    });
    g.beginPath(); g.arc(C, C, 46, 0, 2 * Math.PI); g.fillStyle = '#fff'; g.fill(); g.fillStyle = '#1E3A8A'; g.font = 'bold 26px Arial'; g.textAlign = 'center'; g.fillText('SPIN', C, C + 9);
  }
  function spin() {
    const n = names(); if (n.length < 2) return toast('Add at least 2 names', true);
    if (spinning) return; spinning = true; $('#wW', mount).textContent = '';
    const start = ang, total = 2 * Math.PI * (6 + Math.random() * 4) + rnd(360) * Math.PI / 180, dur = 5000, t0 = performance.now();
    (function frame(now) {
      const p = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - p, 4);
      ang = start + total * e; draw();
      if (p < 1) return requestAnimationFrame(frame);
      spinning = false;
      const step = 2 * Math.PI / n.length, a = ((-ang) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);   // pointer at angle 0 (right side)
      const win = n[Math.floor(a / step) % n.length];
      $('#wW', mount).textContent = '🎉 ' + win;
      ctx._resultText = 'Spin the wheel winner: ' + win; ctx._winner = win; use(ctx);
    })(t0);
  }
  $('#wN', mount).oninput = () => { store.set('wheel', $('#wN', mount).value); draw(); };
  cv.onclick = spin; draw();
  setActions([
    { label: 'Spin', cls: 'btn-cta', icon: 'loader-pinwheel', onClick: spin },
    { label: 'Remove winner', icon: 'user-minus', onClick: () => { if (!ctx._winner) return; const a = $('#wN', mount).value.split('\n'); const i = a.findIndex(x => x.trim() === ctx._winner); if (i > -1) a.splice(i, 1); $('#wN', mount).value = a.join('\n'); store.set('wheel', a.join('\n')); ctx._winner = ''; draw(); } },
    { label: 'Shuffle', icon: 'shuffle', onClick: () => { $('#wN', mount).value = shuffle(names()).join('\n'); draw(); } },
  ]);
};

/* ======================= TEAMS ======================= */
K.teams = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="grid sm:grid-cols-3 gap-3">
    <div class="sm:col-span-3"><label class="kt-label" for="tmN">Names (one per line)</label><textarea class="kt-input" id="tmN" rows="8">Rahul\nPriya\nAmit\nNeha\nKaran\nDivya\nRohan\nSneha\nVikas\nPooja</textarea></div>
    <div><label class="kt-label" for="tmM">Split by</label><select class="kt-select" id="tmM"><option value="t">Number of teams</option><option value="p">People per team</option></select></div>
    <div><label class="kt-label" for="tmK">How many</label><input class="kt-input" id="tmK" type="number" value="2" min="1"></div>
    <div class="sm:col-span-3 kt-result grid sm:grid-cols-2 gap-3" id="tmOut"></div></div>`));
  let txt = '';
  function make() {
    const n = shuffle($('#tmN', mount).value.split('\n').map(s => s.trim()).filter(Boolean)); if (!n.length) return toast('Add some names', true);
    const k = Math.max(1, parseInt($('#tmK', mount).value) || 1);
    const teams = $('#tmM', mount).value === 't' ? Math.min(k, n.length) : Math.ceil(n.length / k);
    const out = Array.from({ length: teams }, () => []); n.forEach((x, i) => out[i % teams].push(x));
    $('#tmOut', mount).innerHTML = out.map((t, i) => `<div class="kt-card p-3"><div class="font-bold mb-1" style="color:var(--peacock-teal)">Team ${i + 1} (${t.length})</div>${t.map(esc).join('<br>')}</div>`).join('');
    txt = out.map((t, i) => `Team ${i + 1}: ${t.join(', ')}`).join('\n'); use(ctx);
  }
  setActions([{ label: 'Make teams', cls: 'btn-cta', icon: 'shuffle', onClick: make }, { label: 'Copy', icon: 'copy', onClick: () => txt && copy(txt) }, { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => txt && waShareText(txt) }]);
  make();
};

/* ======================= SOUND ======================= */
let actx = null;
function beep(times, freq) {
  try {
    actx = actx || new (window.AudioContext || window.webkitAudioContext)();
    for (let i = 0; i < (times || 3); i++) {
      const o = actx.createOscillator(), g = actx.createGain(), t = actx.currentTime + i * 0.35;
      o.type = 'square'; o.frequency.value = freq || 880; o.connect(g); g.connect(actx.destination);
      g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(0.3, t + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + 0.28);
      o.start(t); o.stop(t + 0.3);
    }
  } catch (e) {}
  if (navigator.vibrate) navigator.vibrate([300, 150, 300]);
}
const mmss = s => { s = Math.max(0, Math.round(s)); return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); };

/* ======================= POMODORO ======================= */
K.pomodoro = function (mount, opt, ctx) {
  mount.appendChild(el(`<div class="text-center space-y-4">
    <div class="grid grid-cols-4 gap-2 text-left">${[['pw', 'Focus (min)', 25], ['ps', 'Short break', 5], ['pl', 'Long break', 15], ['pr', 'Rounds', 4]].map(([k, l, v]) => `<div><label class="kt-label text-xs" for="${k}">${l}</label><input class="kt-input" id="${k}" type="number" min="1" value="${v}"></div>`).join('')}</div>
    <div id="pPh" class="text-lg font-bold" style="color:var(--saffron)">Focus</div>
    <div id="pT" class="font-extrabold" style="font-size:5rem;line-height:1;color:var(--krishna-blue)">25:00</div>
    <div id="pR" class="text-sm opacity-70">Round 1</div></div>`));
  let phase = 'w', round = 1, left = 0, iv = 0, running = false;
  const mins = k => Math.max(1, +$('#' + k, mount).value || 1) * 60;
  const label = { w: '🎯 Focus', s: '☕ Short break', l: '🌴 Long break' };
  function show() { $('#pT', mount).textContent = mmss(left); $('#pPh', mount).textContent = label[phase]; $('#pR', mount).textContent = `Round ${round} of ${$('#pr', mount).value}`; document.title = mmss(left) + ' ' + label[phase]; }
  function setPhase(p) { phase = p; left = mins(p === 'w' ? 'pw' : p === 's' ? 'ps' : 'pl'); show(); }
  function next() {
    beep(3, phase === 'w' ? 660 : 990);
    if (phase === 'w') { if (round >= (+$('#pr', mount).value || 4)) setPhase('l'); else setPhase('s'); }
    else { if (phase === 'l') round = 1; else round++; setPhase('w'); }
    toast(label[phase] + ' started');
  }
  function tick() { left--; if (left <= 0) next(); show(); }
  function toggle() { if (running) { clearInterval(iv); running = false; } else { iv = setInterval(tick, 1000); running = true; use(ctx); } }
  setPhase('w');
  mount.addEventListener('change', () => { if (!running) setPhase(phase); });
  setActions([
    { label: 'Start / Pause', cls: 'btn-cta', icon: 'play', onClick: toggle },
    { label: 'Skip', icon: 'skip-forward', onClick: next },
    { label: 'Reset', icon: 'rotate-ccw', onClick: () => { clearInterval(iv); running = false; round = 1; setPhase('w'); } },
  ]);
};

/* ======================= ALARM ======================= */
K.alarm = function (mount, opt, ctx) {
  let alarms = store.get('alarms', []);
  const now = new Date(Date.now() + 5 * 60000);
  mount.appendChild(el(`<div class="space-y-4">
    <div id="aNow" class="text-center font-extrabold" style="font-size:3.5rem;color:var(--krishna-blue)"></div>
    <div class="grid sm:grid-cols-3 gap-2"><input class="kt-input" id="aT" type="time" value="${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}"><input class="kt-input" id="aL" placeholder="Label (e.g. Medicine)"><button class="btn btn-cta" id="aAdd">+ Set alarm</button></div>
    <div id="aList" class="space-y-2"></div>
    <div id="aRing" class="hidden kt-card p-5 text-center" style="background:#FEF3C7"><div class="text-2xl font-extrabold">⏰ <span id="aRL"></span></div><button class="btn btn-cta mt-3" id="aStop">Stop alarm</button></div>
    <p class="text-xs opacity-60">Keep this tab open and the volume up. Phones may pause background tabs to save battery — for important alarms also use your phone's clock app.</p></div>`));
  let ringIv = 0;
  function list() {
    $('#aList', mount).innerHTML = alarms.length ? alarms.map((a, i) => `<div class="kt-card p-3 flex items-center gap-3"><b class="text-xl">${esc(a.t)}</b><span class="flex-1 opacity-70">${esc(a.l || '')}</span><label class="text-sm flex gap-1"><input type="checkbox" data-on="${i}" ${a.on ? 'checked' : ''}> On</label><button class="btn btn-ghost text-sm" data-del="${i}">✕</button></div>`).join('') : '<p class="opacity-60 text-sm">No alarms yet.</p>';
    mount.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { alarms.splice(+b.dataset.del, 1); store.set('alarms', alarms); list(); });
    mount.querySelectorAll('[data-on]').forEach(b => b.onchange = () => { alarms[+b.dataset.on].on = b.checked; store.set('alarms', alarms); });
  }
  function ring(a) { $('#aRL', mount).textContent = (a.l || 'Alarm') + ' – ' + a.t; $('#aRing', mount).classList.remove('hidden'); beep(4); clearInterval(ringIv); ringIv = setInterval(() => beep(4), 2000); document.title = '⏰ ' + (a.l || 'Alarm'); }
  $('#aStop', mount).onclick = () => { clearInterval(ringIv); $('#aRing', mount).classList.add('hidden'); document.title = 'Online Alarm Clock'; };
  $('#aAdd', mount).onclick = () => { const t = $('#aT', mount).value; if (!t) return; alarms.push({ t, l: $('#aL', mount).value.trim(), on: true, last: '' }); store.set('alarms', alarms); list(); beep(1, 440); toast('Alarm set for ' + t); use(ctx); };
  let lastMin = '';
  setInterval(() => {
    const d = new Date(); $('#aNow', mount).textContent = d.toLocaleTimeString('en-IN');
    const hm = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'), day = d.toDateString();
    if (hm === lastMin) return; lastMin = hm;
    alarms.forEach(a => { if (a.on && a.t === hm && a.last !== day + hm) { a.last = day + hm; store.set('alarms', alarms); ring(a); } });
  }, 500);
  list();
  setActions([{ label: 'Test sound', icon: 'volume-2', onClick: () => beep(3) }]);
};

/* ======================= TO-DO ======================= */
K.todo = function (mount, opt, ctx) {
  let items = store.get('todo', [{ t: 'Buy vegetables', d: false, p: 1 }, { t: 'Pay electricity bill', d: false, p: 2 }]);
  let filter = 'all';
  mount.appendChild(el(`<div class="space-y-3">
    <div class="flex gap-2"><input class="kt-input flex-1" id="tdIn" placeholder="Add a task and press Enter"><select class="kt-select w-auto" id="tdP"><option value="0">Normal</option><option value="1">Important</option><option value="2">Urgent</option></select><button class="btn btn-cta" id="tdAdd">Add</button></div>
    <div class="flex gap-2 text-sm">${['all', 'active', 'done'].map(f => `<button class="btn btn-ghost text-sm" data-f="${f}">${f[0].toUpperCase() + f.slice(1)}</button>`).join('')}<span class="ml-auto opacity-60 self-center" id="tdCnt"></span></div>
    <div id="tdList" class="space-y-2 kt-result"></div></div>`));
  const P = ['', '🟡', '🔴'];
  function save() { store.set('todo', items); }
  function list() {
    const show = items.map((it, i) => [it, i]).filter(([it]) => filter === 'all' || (filter === 'done' ? it.d : !it.d)).sort((a, b) => (a[0].d - b[0].d) || (b[0].p - a[0].p));
    $('#tdList', mount).innerHTML = show.length ? show.map(([it, i]) => `<div class="kt-card p-3 flex items-center gap-3"><input type="checkbox" data-i="${i}" ${it.d ? 'checked' : ''} class="w-5 h-5"><span class="flex-1 ${it.d ? 'line-through opacity-50' : ''}">${P[it.p] || ''} ${esc(it.t)}</span><button class="btn btn-ghost text-sm" data-del="${i}">✕</button></div>`).join('') : '<p class="opacity-60 text-sm">Nothing here.</p>';
    $('#tdCnt', mount).textContent = `${items.filter(x => !x.d).length} left · ${items.filter(x => x.d).length} done`;
    mount.querySelectorAll('[data-i]').forEach(c => c.onchange = () => { items[+c.dataset.i].d = c.checked; save(); list(); });
    mount.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { items.splice(+b.dataset.del, 1); save(); list(); });
  }
  function add() { const t = $('#tdIn', mount).value.trim(); if (!t) return; items.push({ t, d: false, p: +$('#tdP', mount).value }); $('#tdIn', mount).value = ''; save(); list(); use(ctx); }
  $('#tdAdd', mount).onclick = add; $('#tdIn', mount).onkeydown = e => { if (e.key === 'Enter') add(); };
  mount.querySelectorAll('[data-f]').forEach(b => b.onclick = () => { filter = b.dataset.f; list(); });
  list();
  const text = () => 'My to-do list:\n' + items.map(x => (x.d ? '✅ ' : '⬜ ') + x.t).join('\n');
  setActions([{ label: 'Clear completed', icon: 'trash-2', onClick: () => { items = items.filter(x => !x.d); save(); list(); } }, { label: 'Copy', icon: 'copy', onClick: () => copy(text()) }, { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(text()) }]);
};

/* ======================= WORLD CLOCK ======================= */
K.worldclock = function (mount, opt, ctx) {
  const Z = [['India (IST)', 'Asia/Kolkata'], ['New York, USA', 'America/New_York'], ['Chicago, USA', 'America/Chicago'], ['Los Angeles, USA', 'America/Los_Angeles'], ['Toronto, Canada', 'America/Toronto'], ['Vancouver, Canada', 'America/Vancouver'], ['London, UK', 'Europe/London'], ['Paris, France', 'Europe/Paris'], ['Berlin, Germany', 'Europe/Berlin'], ['Moscow, Russia', 'Europe/Moscow'], ['Dubai, UAE', 'Asia/Dubai'], ['Riyadh, Saudi Arabia', 'Asia/Riyadh'], ['Kuwait', 'Asia/Kuwait'], ['Doha, Qatar', 'Asia/Qatar'], ['Muscat, Oman', 'Asia/Muscat'], ['Kathmandu, Nepal', 'Asia/Kathmandu'], ['Dhaka, Bangladesh', 'Asia/Dhaka'], ['Karachi, Pakistan', 'Asia/Karachi'], ['Singapore', 'Asia/Singapore'], ['Kuala Lumpur, Malaysia', 'Asia/Kuala_Lumpur'], ['Hong Kong', 'Asia/Hong_Kong'], ['Beijing, China', 'Asia/Shanghai'], ['Tokyo, Japan', 'Asia/Tokyo'], ['Sydney, Australia', 'Australia/Sydney'], ['Melbourne, Australia', 'Australia/Melbourne'], ['Auckland, New Zealand', 'Pacific/Auckland'], ['Nairobi, Kenya', 'Africa/Nairobi'], ['Johannesburg, South Africa', 'Africa/Johannesburg'], ['Lagos, Nigeria', 'Africa/Lagos'], ['São Paulo, Brazil', 'America/Sao_Paulo']];
  mount.appendChild(el(`<div><input class="kt-input mb-3" id="wcF" placeholder="Search city or country…"><div id="wcL" class="grid sm:grid-cols-2 gap-2"></div></div>`));
  const off = tz => { const d = new Date(), a = new Date(d.toLocaleString('en-US', { timeZone: tz })), b = new Date(d.toLocaleString('en-US', { timeZone: 'Asia/Kolkata' })); return Math.round((a - b) / 60000); };
  function paint() {
    const q = $('#wcF', mount).value.toLowerCase(), d = new Date();
    $('#wcL', mount).innerHTML = Z.filter(([n]) => n.toLowerCase().includes(q)).map(([n, tz]) => {
      const t = d.toLocaleTimeString('en-IN', { timeZone: tz, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
      const day = d.toLocaleDateString('en-IN', { timeZone: tz, weekday: 'short', day: 'numeric', month: 'short' });
      const o = off(tz), h = Math.floor(Math.abs(o) / 60), m = Math.abs(o) % 60;
      const diff = o === 0 ? 'Same as IST' : `${o > 0 ? '+' : '−'}${h}h${m ? ' ' + m + 'm' : ''} from IST`;
      return `<div class="kt-card p-3"><div class="text-sm opacity-70">${esc(n)}</div><div class="text-2xl font-extrabold" style="color:var(--krishna-blue)">${t}</div><div class="text-xs opacity-60">${day} · ${diff}</div></div>`;
    }).join('');
  }
  $('#wcF', mount).oninput = () => { paint(); use(ctx); };
  paint(); setInterval(paint, 1000);
  setActions([]);
};

/* ======================= TALLY COUNTER ======================= */
K.tally = function (mount, opt, ctx) {
  let s = store.get('tally', { n: 0, goal: 108 });
  mount.appendChild(el(`<div class="text-center space-y-3">
    <div class="flex justify-center gap-2 items-center text-sm"><label for="tyG">Round / mala size</label><input class="kt-input w-24" id="tyG" type="number" min="1" value="${s.goal}"></div>
    <div id="tyN" class="font-extrabold" style="font-size:5rem;line-height:1;color:var(--krishna-blue)">0</div>
    <div id="tyM" class="text-sm opacity-70"></div>
    <button id="tyPlus" class="mx-auto block rounded-full text-white font-extrabold select-none" style="width:180px;height:180px;font-size:3rem;background:linear-gradient(135deg,#1E3A8A,#059669);box-shadow:0 8px 24px rgba(0,0,0,.2)">+1</button>
    <div class="flex justify-center gap-2"><button class="btn btn-ghost" id="tyMinus">−1</button><button class="btn btn-ghost" id="tyReset">Reset</button></div>
    <p class="text-xs opacity-60">Tip: on a computer press Space or Enter to count.</p></div>`));
  function show() { const g = Math.max(1, s.goal); $('#tyN', mount).textContent = s.n; $('#tyM', mount).textContent = `Rounds completed: ${Math.floor(s.n / g)} · ${s.n % g} / ${g} in this round`; store.set('tally', s); }
  function inc(d) { s.n = Math.max(0, s.n + d); if (d > 0 && navigator.vibrate) navigator.vibrate(s.n % Math.max(1, s.goal) === 0 ? [200, 100, 200] : 15); if (d > 0 && s.n % Math.max(1, s.goal) === 0) { beep(1, 720); toast('🙏 Round complete!'); } show(); use(ctx); }
  $('#tyPlus', mount).onclick = () => inc(1); $('#tyMinus', mount).onclick = () => inc(-1);
  $('#tyReset', mount).onclick = () => { if (confirm('Reset the count to 0?')) { s.n = 0; show(); } };
  $('#tyG', mount).oninput = e => { s.goal = Math.max(1, parseInt(e.target.value) || 1); show(); };
  document.addEventListener('keydown', e => { if ((e.code === 'Space' || e.key === 'Enter') && !/INPUT|TEXTAREA|SELECT|BUTTON/.test(document.activeElement.tagName)) { e.preventDefault(); inc(1); } });
  show(); setActions([]);
};

/* ======================= EXPENSE SPLITTER ======================= */
K.splitter = function (mount, opt, ctx) {
  let rows = store.get('split', [['Rahul', 1200], ['Priya', 800], ['Amit', 0], ['Neha', 400]]);
  mount.appendChild(el(`<div class="space-y-3"><div class="kt-label">Who paid how much</div><div id="spRows" class="space-y-2"></div>
    <button class="btn btn-ghost text-sm" id="spAdd">+ Add person</button><div id="spOut" class="kt-result"></div></div>`));
  let text = '';
  function paint() {
    $('#spRows', mount).innerHTML = rows.map((r, i) => `<div class="grid grid-cols-12 gap-2"><input class="kt-input col-span-6" data-i="${i}" data-f="0" value="${esc(r[0])}" placeholder="Name"><input class="kt-input col-span-5" data-i="${i}" data-f="1" type="number" step="any" value="${r[1]}" placeholder="Paid ₹"><button class="btn btn-ghost col-span-1 !px-0" data-del="${i}">✕</button></div>`).join('');
    mount.querySelectorAll('#spRows input').forEach(n => n.oninput = () => { rows[+n.dataset.i][+n.dataset.f] = n.dataset.f === '1' ? (parseFloat(n.value) || 0) : n.value; calc(); });
    mount.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { rows.splice(+b.dataset.del, 1); paint(); });
    calc();
  }
  function calc() {
    store.set('split', rows);
    const ppl = rows.filter(r => String(r[0]).trim()); if (!ppl.length) { $('#spOut', mount).innerHTML = ''; return; }
    const total = ppl.reduce((s, r) => s + (+r[1] || 0), 0), share = total / ppl.length;
    const bal = ppl.map(r => [String(r[0]).trim(), (+r[1] || 0) - share]);
    const cred = bal.filter(b => b[1] > 0.005).sort((a, b) => b[1] - a[1]), deb = bal.filter(b => b[1] < -0.005).sort((a, b) => a[1] - b[1]);
    const pays = []; let i = 0, j = 0;
    while (i < deb.length && j < cred.length) { const amt = Math.min(-deb[i][1], cred[j][1]); pays.push([deb[i][0], cred[j][0], amt]); deb[i][1] += amt; cred[j][1] -= amt; if (-deb[i][1] < 0.005) i++; if (cred[j][1] < 0.005) j++; }
    $('#spOut', mount).innerHTML = `<div class="grid sm:grid-cols-2 gap-3 mb-3"><div class="kt-card p-4"><div class="text-xs opacity-60">Total spent</div><div class="text-2xl font-extrabold" style="color:var(--peacock-teal)">${inr(total)}</div></div><div class="kt-card p-4"><div class="text-xs opacity-60">Each person's share (${ppl.length} people)</div><div class="text-2xl font-extrabold" style="color:var(--peacock-teal)">${inr(share)}</div></div></div>
      <div class="kt-card p-4"><div class="font-bold mb-2">Settle up</div>${pays.length ? pays.map(p => `<div>💸 <b>${esc(p[0])}</b> pays <b>${esc(p[1])}</b> ${inr(p[2])}</div>`).join('') : 'Everyone is settled 🎉'}</div>`;
    text = `Expense split – Total ${inr(total)}, share ${inr(share)} each\n` + (pays.length ? pays.map(p => `${p[0]} → ${p[1]}: ${inr(p[2])}`).join('\n') : 'All settled');
  }
  $('#spAdd', mount).onclick = () => { rows.push(['', 0]); paint(); use(ctx); };
  paint();
  setActions([{ label: 'Copy', icon: 'copy', onClick: () => copy(text) }, { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => { waShareText(text); use(ctx); } }, { label: 'Clear all', icon: 'trash-2', onClick: () => { if (confirm('Clear all names?')) { rows = [['', 0], ['', 0]]; paint(); } } }]);
};

/* ======================= GROCERY LIST ======================= */
K.grocery = function (mount, opt, ctx) {
  const COMMON = ['Wheat atta', 'Rice', 'Toor dal', 'Moong dal', 'Sugar', 'Groundnut oil', 'Tea', 'Salt', 'Turmeric', 'Red chilli powder', 'Jeera', 'Ghee', 'Besan', 'Soap', 'Detergent', 'Toothpaste'];
  let rows = store.get('grocery', [['Wheat atta', '10 kg', 450, false], ['Rice', '5 kg', 350, false], ['Toor dal', '2 kg', 340, false], ['Sugar', '2 kg', 90, false], ['Tea', '500 g', 260, false]]);
  mount.appendChild(el(`<div class="space-y-3"><div class="grid grid-cols-12 gap-2 text-xs opacity-60"><span class="col-span-1">✓</span><span class="col-span-5">Item</span><span class="col-span-3">Qty</span><span class="col-span-2">Price ₹</span></div><div id="gR" class="space-y-2"></div>
    <div class="flex gap-2 flex-wrap"><button class="btn btn-ghost text-sm" id="gAdd">+ Add item</button><button class="btn btn-ghost text-sm" id="gCom">+ Add common kirana items</button></div>
    <div id="gTot" class="kt-result kt-card p-4"></div></div>`));
  let text = '';
  function paint() {
    $('#gR', mount).innerHTML = rows.map((r, i) => `<div class="grid grid-cols-12 gap-2 items-center"><input type="checkbox" class="col-span-1 w-5 h-5" data-i="${i}" data-f="3" ${r[3] ? 'checked' : ''}><input class="kt-input col-span-5 ${r[3] ? 'line-through opacity-50' : ''}" data-i="${i}" data-f="0" value="${esc(r[0])}"><input class="kt-input col-span-3" data-i="${i}" data-f="1" value="${esc(r[1])}"><input class="kt-input col-span-2" data-i="${i}" data-f="2" type="number" step="any" value="${r[2] || ''}"><button class="btn btn-ghost col-span-1 !px-0" data-del="${i}">✕</button></div>`).join('');
    mount.querySelectorAll('#gR input').forEach(n => { n.oninput = n.onchange = () => { const f = +n.dataset.f; rows[+n.dataset.i][f] = f === 3 ? n.checked : f === 2 ? (parseFloat(n.value) || 0) : n.value; if (f === 3) paint(); else calc(); }; });
    mount.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { rows.splice(+b.dataset.del, 1); paint(); });
    calc();
  }
  function calc() {
    store.set('grocery', rows);
    const tot = rows.reduce((s, r) => s + (+r[2] || 0), 0), got = rows.filter(r => r[3]).reduce((s, r) => s + (+r[2] || 0), 0);
    $('#gTot', mount).innerHTML = `<div class="flex justify-between"><span>Total (${rows.length} items)</span><b class="text-xl" style="color:var(--peacock-teal)">${inr(tot)}</b></div><div class="flex justify-between text-sm opacity-70"><span>Bought (${rows.filter(r => r[3]).length})</span><span>${inr(got)}</span></div><div class="flex justify-between text-sm opacity-70"><span>Remaining</span><span>${inr(tot - got)}</span></div>`;
    text = '🛒 Grocery list\n' + rows.filter(r => r[0]).map((r, i) => `${i + 1}. ${r[0]}${r[1] ? ' – ' + r[1] : ''}${r[2] ? ' (₹' + r[2] + ')' : ''}`).join('\n') + (tot ? `\nTotal: ${inr(tot)}` : '');
  }
  $('#gAdd', mount).onclick = () => { rows.push(['', '', 0, false]); paint(); use(ctx); };
  $('#gCom', mount).onclick = () => { COMMON.forEach(c => { if (!rows.some(r => r[0].toLowerCase() === c.toLowerCase())) rows.push([c, '', 0, false]); }); paint(); };
  paint();
  setActions([
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => { waShareText(text); use(ctx); } },
    { label: 'Copy', icon: 'copy', onClick: () => copy(text) },
    { label: 'Print', icon: 'printer', onClick: () => printHtml(`<h2 style="font-family:Arial">Grocery list</h2><table style="width:100%;border-collapse:collapse;font-family:Arial;font-size:14px">${rows.filter(r => r[0]).map((r, i) => `<tr style="border-bottom:1px solid #ddd"><td style="padding:6px;width:24px">☐</td><td style="padding:6px">${i + 1}. ${esc(r[0])}</td><td style="padding:6px">${esc(r[1])}</td><td style="padding:6px;text-align:right">${r[2] ? inr(r[2]) : ''}</td></tr>`).join('')}</table>`, 'Grocery list') },
    { label: 'Uncheck all', icon: 'square', onClick: () => { rows.forEach(r => { r[3] = false; }); paint(); } },
  ]);
};

/* ======================= BREATHING ======================= */
K.breathe = function (mount, opt, ctx) {
  const PAT = { box: [['Breathe in', 4], ['Hold', 4], ['Breathe out', 4], ['Hold', 4]], r478: [['Breathe in', 4], ['Hold', 7], ['Breathe out', 8]], calm: [['Breathe in', 4], ['Breathe out', 6]], bhramari: [['Breathe in', 4], ['Hum out (mmm…)', 8]] };
  mount.appendChild(el(`<div class="text-center space-y-4"><select class="kt-select w-auto mx-auto" id="bP"><option value="box">Box breathing (4-4-4-4) – focus</option><option value="r478">4-7-8 breathing – sleep & calm</option><option value="calm">Calm breathing (4-6) – stress</option><option value="bhramari">Bhramari pranayama (4-8)</option></select>
    <div style="height:260px" class="flex items-center justify-center"><div id="bC" style="width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,#5EEAD4,#0E7490);transition:transform 1s linear;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:2rem"></div></div>
    <div id="bL" class="text-2xl font-bold" style="color:var(--krishna-blue)">Press Start</div><div id="bN" class="text-sm opacity-60"></div></div>`));
  let iv = 0, on = false, cycles = 0;
  function start() {
    if (on) { clearInterval(iv); on = false; $('#bL', mount).textContent = 'Paused'; return; }
    on = true; use(ctx); const p = PAT[$('#bP', mount).value]; let step = 0, left = p[0][1];
    const c = $('#bC', mount);
    const apply = () => { const [lab, sec] = p[step]; $('#bL', mount).textContent = lab; c.style.transitionDuration = sec + 's'; c.style.transform = /in/i.test(lab) ? 'scale(2)' : (/out/i.test(lab) ? 'scale(1)' : c.style.transform); };
    apply(); c.textContent = left;
    iv = setInterval(() => {
      left--;
      if (left <= 0) { step = (step + 1) % p.length; if (!step) { cycles++; $('#bN', mount).textContent = cycles + ' cycles completed'; } left = p[step][1]; apply(); }
      c.textContent = left;
    }, 1000);
  }
  $('#bP', mount).onchange = () => { clearInterval(iv); on = false; $('#bL', mount).textContent = 'Press Start'; };
  setActions([{ label: 'Start / Pause', cls: 'btn-cta', icon: 'play', onClick: start }]);
};

/* ======================= EMOJI PICKER ======================= */
K.emoji = function (mount, opt, ctx) {
  const CAT = {
    'Smileys': '😀😃😄😁😆😅🤣😂🙂🙃😉😊😇🥰😍🤩😘😗😚😋😛😜🤪😝🤑🤗🤭🤫🤔🤐🤨😐😑😶😏😒🙄😬😮‍💨🤥😌😔😪🤤😴😷🤒🤕🤢🤮🥵🥶🥴😵🤯🤠🥳🥸😎🤓🧐😕😟🙁😮😯😲😳🥺😦😧😨😰😥😢😭😱😖😣😞😓😩😫🥱😤😡😠🤬😈👿💀☠️💩🤡👻👽🤖😺😸😹😻😼😽🙀😿😾',
    'Hearts & love': '❤️🧡💛💚💙💜🤎🖤🤍💔❣️💕💞💓💗💖💘💝💟❤️‍🔥❤️‍🩹💌💋💍💐🌹🥀🌷💑💏👩‍❤️‍👨',
    'Hands & people': '👋🤚🖐️✋🖖👌🤌🤏✌️🤞🤟🤘🤙👈👉👆🖕👇☝️👍👎✊👊🤛🤜👏🙌👐🤲🤝🙏✍️💅🤳💪🦾🦵🦶👂🦻👃🧠👀👁️👅👄👶🧒👦👧🧑👱👨🧔👩🧓👴👵🙍🙎🙅🙆💁🙋🧏🙇🤦🤷👮🕵️💂👷🤴👸👳👲🧕🤵👰🤰🤱👼🎅🤶🦸🦹🧙🧚🧛🧜🧝🧞🧟💆💇🚶🧍🧎🏃💃🕺👯🧖🧗🤸🏌️🏇⛹️🏋️🚴🚵🤼🤽🤾🤺🧘',
    'Festivals & celebration': '🪔✨🎉🎊🎈🎂🍰🧁🎁🎀🪅🎆🎇🧨🪁🕉️🛕🙏🌺🌼🌸🏵️💐🎶🎵🥁🪘🎺🎷🪕🎸🎤🏆🥇🥈🥉🏅🎖️🎗️🎫🎟️🕯️🔔🎄🌙☪️✝️🕎☸️🪷',
    'Food & drink': '🍏🍎🍐🍊🍋🍌🍉🍇🍓🫐🍈🍒🍑🥭🍍🥥🥝🍅🍆🥑🥦🥬🥒🌶️🫑🌽🥕🧄🧅🥔🍠🥐🥯🍞🥖🥨🧀🥚🍳🧈🥞🧇🥓🍗🍖🌭🍔🍟🍕🫓🥪🌮🌯🥗🥘🍝🍜🍲🍛🍣🍱🥟🍤🍙🍚🍘🍥🥮🍢🍡🍧🍨🍦🥧🧁🍰🎂🍮🍭🍬🍫🍿🍩🍪🌰🥜🍯🥛☕🫖🍵🧃🥤🧋🍶🍺🍻🥂🍷🥃🍸🍹🧉🧊',
    'Animals & nature': '🐶🐱🐭🐹🐰🦊🐻🐼🐨🐯🦁🐮🐷🐸🐵🙈🙉🙊🐔🐧🐦🐤🦆🦅🦉🦇🐺🐗🐴🦄🐝🐛🦋🐌🐞🐜🪲🐢🐍🦎🐙🦑🦐🦀🐡🐠🐟🐬🐳🐋🦈🐊🐅🐆🦓🦍🐘🦛🦏🐪🐫🦒🦘🐃🐂🐄🐎🐖🐏🐑🦙🐐🦌🐕🐩🐈🐓🦃🦚🦜🦢🕊️🐇🐿️🌵🎄🌲🌳🌴🌱🌿☘️🍀🎍🍃🍂🍁🍄🌾💐🌷🌹🥀🌺🌸🌼🌻🌞🌝🌛🌜🌚🌕🌙🌎⭐🌟💫✨⚡🔥🌈☀️🌤️⛅☁️🌧️⛈️🌩️❄️☃️⛄🌬️💨💧💦☔🌊',
    'Travel & objects': '🚗🚕🚙🚌🚎🏎️🚓🚑🚒🚐🛻🚚🚛🚜🛵🏍️🛺🚲🛴🚨🚔🚍🚘🚖✈️🛫🛬🚀🛸🚁⛵🚤🛳️🚢⚓⛽🚧🚦🗺️🗽🗼🏰🏯🏟️🎡🎢🎠⛲🏖️🏝️🏜️🌋⛰️🏔️🗻🏕️🏠🏡🏘️🏢🏬🏣🏤🏥🏦🏨🏪🏫🏩💒🏛️⛪🕌🛕🕍⛩️⌚📱💻⌨️🖥️🖨️🖱️💽💾💿📀📷📸📹🎥📞☎️📺📻🎙️⏰⏳⌛📡🔋🔌💡🔦🕯️🧯💸💵💴💶💷🪙💰💳💎⚖️🧰🔧🔨⚒️🛠️⛏️🔩⚙️🧱🔫💣🔪🗡️⚔️🛡️🔮📿🧿💈⚗️🔭🔬💊💉🩸🩺🧬🦠🧹🧺🧻🚽🚰🚿🛁🧼🪥🪒🧽🔑🗝️🚪🪑🛋️🛏️🖼️🛍️🛒🎁📦📮📬📄📃📑📊📈📉🗒️📆📅🗑️📁📂🗂️🗞️📰📓📔📒📕📗📘📙📚📖🔖🔗📎📐📏🧮📌📍✂️🖊️🖋️✒️🖌️🖍️📝✏️🔍🔎🔒🔓',
    'Symbols': '✅☑️✔️❌❎➕➖➗✖️♾️💯🔟🔢#️⃣*️⃣0️⃣1️⃣2️⃣3️⃣4️⃣5️⃣6️⃣7️⃣8️⃣9️⃣🆗🆕🆓🆙🆒🆘⛔🚫📵🔞☢️☣️⚠️🚸🔱⚜️🔰♻️🌀💤💢💬💭🗯️♠️♣️♥️♦️🃏🀄🎴🔇🔈🔉🔊📢📣🔔🔕🎵🎶⏩⏪⏫⏬▶️⏸️⏯️⏹️⏺️⏭️⏮️🔀🔁🔂◀️🔼🔽➡️⬅️⬆️⬇️↗️↘️↙️↖️↕️↔️↩️↪️⤴️⤵️🔄🔃🔺🔻🔸🔹🔶🔷🔳🔲▪️▫️◾◽◼️◻️⬛⬜🟥🟧🟨🟩🟦🟪🟫⚫⚪🔴🟠🟡🟢🔵🟣🟤©️®️™️〰️➰➿🔚🔙🔛🔝🔜💲💱',
    'Flags': '🇮🇳🇺🇸🇬🇧🇨🇦🇦🇺🇦🇪🇸🇦🇶🇦🇰🇼🇴🇲🇳🇵🇧🇩🇱🇰🇵🇰🇸🇬🇲🇾🇯🇵🇨🇳🇰🇷🇫🇷🇩🇪🇮🇹🇪🇸🇷🇺🇧🇷🇿🇦🇳🇿🇳🇱🇨🇭🇸🇪🇳🇴🏳️🏴🏁🚩🏳️‍🌈',
  };
  const seg = s => (window.Intl && Intl.Segmenter) ? Array.from(new Intl.Segmenter('en', { granularity: 'grapheme' }).segment(s), x => x.segment) : Array.from(s);
  mount.appendChild(el(`<div class="space-y-3"><div class="flex gap-2"><input class="kt-input flex-1 text-2xl" id="eOut" placeholder="Tap emojis to add them here"><button class="btn btn-ghost" id="eBk">⌫</button></div>
    <div id="eCats" class="space-y-3 kt-result"></div></div>`));
  $('#eCats', mount).innerHTML = Object.entries(CAT).map(([n, s]) => `<div><div class="font-bold text-sm mb-1">${n}</div><div class="flex flex-wrap gap-1">${seg(s).map(e => `<button class="text-3xl leading-none p-1 rounded hover:bg-black/5" data-e="${e}" title="Copy ${e}">${e}</button>`).join('')}</div></div>`).join('');
  const out = $('#eOut', mount);
  mount.querySelectorAll('[data-e]').forEach(b => b.onclick = () => { out.value += b.dataset.e; navigator.clipboard && navigator.clipboard.writeText(b.dataset.e).catch(() => {}); toast(b.dataset.e + ' copied'); use(ctx); });
  $('#eBk', mount).onclick = () => { const a = seg(out.value); a.pop(); out.value = a.join(''); };
  setActions([{ label: 'Copy all', cls: 'btn-cta', icon: 'copy', onClick: () => out.value ? copy(out.value) : toast('Tap some emojis first', true) }, { label: 'Clear', icon: 'x', onClick: () => { out.value = ''; } }, { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(out.value) }]);
};

/* ======================= SALARY SLIP ======================= */
K.salaryslip = function (mount, opt, ctx) {
  const co = store.get('slip-co', { n: '', a: '' });
  const d = new Date(); d.setMonth(d.getMonth() - 1);
  const month = d.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
  const info = [['co', 'Company name', co.n || 'AK Computer'], ['ca', 'Company address', co.a || 'Dwarka, Gujarat'], ['mo', 'Pay month', month], ['en', 'Employee name', 'Rahul Patel'], ['ei', 'Employee ID', 'EMP-012'], ['de', 'Designation', 'Accountant'], ['dp', 'Department', 'Accounts'], ['dj', 'Date of joining', '01-06-2023'], ['pa', 'PAN', ''], ['ua', 'UAN / PF no.', ''], ['ba', 'Bank A/c no.', ''], ['wd', 'Paid days / working days', '30 / 30']];
  let earn = store.get('slip-earn', [['Basic salary', 15000], ['House rent allowance (HRA)', 6000], ['Conveyance allowance', 1600], ['Medical allowance', 1250], ['Special allowance', 3150]]);
  let ded = store.get('slip-ded', [['Provident fund (PF)', 1800], ['Professional tax', 200], ['Income tax (TDS)', 0]]);
  mount.appendChild(el(`<div class="space-y-4"><div class="grid sm:grid-cols-3 gap-3">${info.map(([k, l, v]) => `<div><label class="kt-label" for="s_${k}">${l}</label><input class="kt-input" id="s_${k}" value="${esc(v)}"></div>`).join('')}</div>
    <div class="grid md:grid-cols-2 gap-4"><div><div class="kt-label">Earnings</div><div id="sE" class="space-y-2"></div><button class="btn btn-ghost text-sm mt-2" data-add="e">+ Add earning</button></div>
    <div><div class="kt-label">Deductions</div><div id="sD" class="space-y-2"></div><button class="btn btn-ghost text-sm mt-2" data-add="d">+ Add deduction</button></div></div>
    <div id="sPrev" class="kt-card p-0 overflow-auto kt-result"></div></div>`));
  let html = '';
  const rowsHtml = (arr, key) => arr.map((r, i) => `<div class="grid grid-cols-12 gap-2"><input class="kt-input col-span-7" data-k="${key}" data-i="${i}" data-f="0" value="${esc(r[0])}"><input class="kt-input col-span-4" type="number" step="any" data-k="${key}" data-i="${i}" data-f="1" value="${r[1]}"><button class="btn btn-ghost col-span-1 !px-0" data-del="${key}${i}">✕</button></div>`).join('');
  function lists() {
    $('#sE', mount).innerHTML = rowsHtml(earn, 'e'); $('#sD', mount).innerHTML = rowsHtml(ded, 'd');
    mount.querySelectorAll('[data-k]').forEach(n => n.oninput = () => { const a = n.dataset.k === 'e' ? earn : ded; a[+n.dataset.i][+n.dataset.f] = n.dataset.f === '1' ? (parseFloat(n.value) || 0) : n.value; render(); });
    mount.querySelectorAll('[data-del]').forEach(b => b.onclick = () => { const k = b.dataset.del[0], i = +b.dataset.del.slice(1); (k === 'e' ? earn : ded).splice(i, 1); lists(); });
    render();
  }
  mount.querySelectorAll('[data-add]').forEach(b => b.onclick = () => { (b.dataset.add === 'e' ? earn : ded).push(['', 0]); lists(); });
  const v = k => $('#s_' + k, mount).value.trim();
  function render() {
    store.set('slip-co', { n: v('co'), a: v('ca') }); store.set('slip-earn', earn); store.set('slip-ded', ded);
    const te = earn.reduce((s, r) => s + (+r[1] || 0), 0), td = ded.reduce((s, r) => s + (+r[1] || 0), 0), net = te - td;
    const n = Math.max(earn.length, ded.length), td_ = 'style="padding:6px 8px;border:1px solid #cbd5e1"';
    const info2 = [['Employee name', v('en')], ['Employee ID', v('ei')], ['Designation', v('de')], ['Department', v('dp')], ['Date of joining', v('dj')], ['Paid days', v('wd')], ['PAN', v('pa')], ['UAN / PF no.', v('ua')], ['Bank A/c', v('ba')]].filter(x => x[1]);
    html = `<div class="kt-page" style="font-family:Arial,sans-serif;color:#111;background:#fff;padding:28px;max-width:800px;margin:auto;font-size:13px">
      <div style="text-align:center;border-bottom:3px solid #1E3A8A;padding-bottom:8px"><div style="font-size:22px;font-weight:800;color:#1E3A8A">${esc(v('co'))}</div><div style="color:#444">${esc(v('ca'))}</div>
      <div style="margin-top:6px;font-size:15px;font-weight:700">Salary Slip for ${esc(v('mo'))}</div></div>
      <table style="width:100%;margin-top:12px;border-collapse:collapse">${info2.reduce((rows, x, i) => { if (i % 2 === 0) rows.push([x]); else rows[rows.length - 1].push(x); return rows; }, []).map(p => `<tr>${p.map(x => `<td style="padding:3px 6px;color:#555;width:20%">${x[0]}</td><td style="padding:3px 6px;font-weight:700;width:30%">${esc(x[1])}</td>`).join('')}</tr>`).join('')}</table>
      <table style="width:100%;border-collapse:collapse;margin-top:14px"><tr style="background:#1E3A8A;color:#fff"><th ${td_} align="left">Earnings</th><th ${td_} align="right">Amount (₹)</th><th ${td_} align="left">Deductions</th><th ${td_} align="right">Amount (₹)</th></tr>
      ${Array.from({ length: n }, (_, i) => `<tr><td ${td_}>${esc((earn[i] || [''])[0])}</td><td ${td_} align="right">${earn[i] ? (+earn[i][1]).toLocaleString('en-IN', { minimumFractionDigits: 2 }) : ''}</td><td ${td_}>${esc((ded[i] || [''])[0])}</td><td ${td_} align="right">${ded[i] ? (+ded[i][1]).toLocaleString('en-IN', { minimumFractionDigits: 2 }) : ''}</td></tr>`).join('')}
      <tr style="font-weight:700;background:#F1F5F9"><td ${td_}>Total earnings</td><td ${td_} align="right">${te.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td><td ${td_}>Total deductions</td><td ${td_} align="right">${td.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td></tr></table>
      <div style="margin-top:14px;padding:10px;border:2px solid #059669;border-radius:6px"><div style="font-size:16px"><b>Net pay: ${inr(net)}</b></div><div style="color:#444;margin-top:2px">${esc(rupeesWords(Math.max(0, net)))}</div></div>
      <div style="display:flex;justify-content:space-between;margin-top:48px;font-size:12px"><div>______________________<br>Employee signature</div><div style="text-align:right">______________________<br>Authorised signatory</div></div>
      <div style="margin-top:16px;font-size:10px;color:#777;text-align:center">This is a computer-generated salary slip.</div></div>`;
    $('#sPrev', mount).innerHTML = html;
  }
  mount.addEventListener('input', e => { if (e.target.id && e.target.id.startsWith('s_')) render(); });
  lists();
  setActions([
    { label: 'Download PDF', cls: 'btn-cta', icon: 'download', onClick: () => { render(); return htmlToPdf(html, 'salary-slip-' + (v('en') || 'employee').replace(/\W+/g, '-') + '.pdf', ctx); } },
    { label: 'Print', icon: 'printer', onClick: () => { render(); printHtml(`<style>@page{margin:10mm}</style>${html}`, 'Salary slip'); use(ctx); } },
  ]);
};

/* ======================= RENT RECEIPTS ======================= */
K.rentreceipt = function (mount, opt, ctx) {
  const s = store.get('rent', {});
  const y = new Date().getMonth() >= 3 ? new Date().getFullYear() : new Date().getFullYear() - 1;
  const F = [['tn', 'Tenant name (employee)', s.tn || 'Rahul Patel'], ['ln', 'Landlord name', s.ln || 'Mahesh Shah'], ['lp', 'Landlord PAN (needed if rent > ₹1 lakh/year)', s.lp || ''], ['ad', 'Rented house address', s.ad || '12, Shanti Nagar, Ahmedabad'], ['amt', 'Monthly rent (₹)', s.amt || '12000'], ['mode', 'Paid by', s.mode || 'Bank transfer / UPI'], ['from', 'From month', s.from || `${y}-04`], ['to', 'To month', s.to || `${y + 1}-03`]];
  mount.appendChild(el(`<div class="space-y-4"><div class="grid sm:grid-cols-2 gap-3">${F.map(([k, l, v]) => `<div class="${k === 'ad' ? 'sm:col-span-2' : ''}"><label class="kt-label" for="r_${k}">${l}</label><input class="kt-input" id="r_${k}" ${k === 'from' || k === 'to' ? 'type="month"' : (k === 'amt' ? 'type="number"' : '')} value="${esc(v)}"></div>`).join('')}</div>
    <div id="rInfo" class="text-sm opacity-70"></div><div id="rPrev" class="kt-card p-0 overflow-auto kt-result" style="max-height:640px"></div></div>`));
  let html = '';
  const v = k => $('#r_' + k, mount).value.trim();
  function render() {
    const o = {}; F.forEach(([k]) => { o[k] = v(k); }); store.set('rent', o);
    const amt = parseFloat(o.amt) || 0;
    const [fy, fm] = (o.from || '').split('-').map(Number), [ty, tm] = (o.to || '').split('-').map(Number);
    const months = [];
    if (fy && ty) for (let d = new Date(fy, fm - 1, 1); d <= new Date(ty, tm - 1, 1) && months.length < 24; d.setMonth(d.getMonth() + 1)) months.push(new Date(d));
    $('#rInfo', mount).textContent = `${months.length} receipts · total rent ${inr(amt * months.length)}${amt * 12 > 100000 && !o.lp ? ' · ⚠️ Yearly rent is above ₹1 lakh: add the landlord PAN' : ''}`;
    html = months.map((m, i) => {
      const last = new Date(m.getFullYear(), m.getMonth() + 1, 0);
      const mon = m.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
      return `<div class="kt-page" style="font-family:Arial,sans-serif;color:#111;background:#fff;padding:22px 28px;max-width:800px;margin:auto;font-size:14px;line-height:1.7;${i % 3 !== 2 ? 'border-bottom:1px dashed #94a3b8' : ''};page-break-inside:avoid">
        <div style="display:flex;justify-content:space-between"><b style="font-size:18px;color:#1E3A8A">RENT RECEIPT</b><span>Receipt no. ${i + 1} · Date: ${last.toLocaleDateString('en-IN')}</span></div>
        <p style="margin:8px 0">Received with thanks from <b>${esc(o.tn)}</b> a sum of <b>${inr(amt)}</b> (${esc(rupeesWords(amt))}) by ${esc(o.mode)} towards the rent of the property at <b>${esc(o.ad)}</b> for the month of <b>${mon}</b>.</p>
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:10px"><div>Landlord: <b>${esc(o.ln)}</b>${o.lp ? '<br>PAN: <b>' + esc(o.lp.toUpperCase()) + '</b>' : ''}</div>
        <div style="text-align:center">${amt > 5000 && /cash/i.test(o.mode) ? '<div style="width:70px;height:60px;border:1px dashed #94a3b8;font-size:10px;color:#64748b;display:flex;align-items:center;justify-content:center;margin:0 auto 4px">Revenue<br>stamp</div>' : ''}______________________<br>Signature of landlord</div></div></div>`;
    }).join('');
    $('#rPrev', mount).innerHTML = html || '<p class="p-4 opacity-60">Choose the from and to months.</p>';
  }
  mount.addEventListener('input', render); mount.addEventListener('change', render);
  render();
  // Three receipts fit on one A4 page.
  const pagesHtml = () => { const tmp = document.createElement('div'); tmp.innerHTML = html; const r = Array.from(tmp.children); let out = ''; for (let i = 0; i < r.length; i += 3) out += `<div class="kt-page" style="background:#fff;width:794px;padding:20px 0">${r.slice(i, i + 3).map(x => x.outerHTML.replace('class="kt-page"', '')).join('')}</div>`; return out; };
  setActions([
    { label: 'Download PDF', cls: 'btn-cta', icon: 'download', onClick: () => { render(); return htmlToPdf(pagesHtml(), 'rent-receipts.pdf', ctx); } },
    { label: 'Print', icon: 'printer', onClick: () => { render(); printHtml(`<style>@page{margin:8mm}</style>${html}`, 'Rent receipts'); use(ctx); } },
  ]);
};

/* ======================= REGISTER ======================= */
reg('daily', function (mount, opt, ctx) {
  mount.innerHTML = '';
  const fn = K[opt.kind];
  if (!fn) { mount.innerHTML = '<p class="opacity-60">This tool is coming soon.</p>'; return; }
  fn(mount, opt, ctx);
  if (window.lucide) lucide.createIcons();
});
})();
