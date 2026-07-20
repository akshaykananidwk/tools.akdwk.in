/* ================================================================
   KRISHNA TOOLS — file & generator engines (loaded on demand).
   Uses window.KTX exported by app.js. All processing is client-side
   via CDN libraries. Server fallbacks degrade gracefully.
   ================================================================ */
(function () {
'use strict';
const X = window.KTX;
if (!X) return;
const { $, T, GU, KT, toast, copy, downloadBlob, downloadDataUrl, loadScript, loadCss, need, logUsage,
        waShareText, waShareFile, setActions, el, fileInput, checkMax, fmtBytes, reg } = X;

/* ---------- shared helpers ---------- */
function resultBox(mount) { const b = el('<div class="mt-5"></div>'); mount.appendChild(b); return b; }
function loadImage(file) {
  return new Promise((res, rej) => {
    const img = new Image();
    img.onload = () => res(img); img.onerror = rej;
    img.src = URL.createObjectURL(file);
  });
}
function canvasToBlob(canvas, type, quality) {
  return new Promise(res => canvas.toBlob(res, type, quality));
}
function applyWatermark(ctx, w, h, ctxObj) {
  if (!ctxObj.watermark) return;
  ctx.save();
  ctx.globalAlpha = 0.28; ctx.fillStyle = '#F59E0B';
  ctx.font = `bold ${Math.max(14, w / 22)}px Inter, sans-serif`;
  ctx.textAlign = 'center';
  ctx.translate(w / 2, h / 2); ctx.rotate(-Math.PI / 9);
  ctx.fillText('कृष्णा टूल्स · tools.akdwk.in', 0, 0);
  ctx.restore();
}
function showPreview(box, blob, name, ctx, caption) {
  const url = URL.createObjectURL(blob);
  box.innerHTML = `<h3 class="section-title text-lg mb-3">${T('પરિણામ', 'Result')}</h3>
    <div class="kt-card p-3 text-center">
      ${blob.type.startsWith('image') ? `<img src="${url}" class="max-h-80 mx-auto rounded-lg">` : `<div class="py-6 text-4xl">📄</div>`}
      <div class="text-xs opacity-60 mt-2">${name} · ${fmtBytes(blob.size)}</div>
    </div>`;
  setActions([
    { label: T('ડાઉનલોડ', 'Download'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(blob, name) },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareFile(blob, name, caption || name) },
    { label: T('પ્રિન્ટ', 'Print'), icon: 'printer', onClick: () => { const w = window.open(); w.document.write(`<img src="${url}" onload="print()">`); } },
  ]);
  if (ctx.toolId) logUsage(ctx.toolId);
  if (window.lucide) lucide.createIcons();
}

/* ================= IMAGE ENGINES ================= */
function imageConvert(mount, opt, ctx) {
  const to = (opt && opt.to) || 'png';
  const type = 'image/' + (to === 'jpg' ? 'jpeg' : to);
  const input = fileInput(mount, { accept: 'image/*', hint: T('મહત્તમ', 'Max') + ' ' + ctx.maxMb + 'MB' });
  const q = el(`<div class="mt-3 ${to === 'png' ? 'hidden' : ''}"><label class="kt-label">${T('ક્વોલિટી', 'Quality')}: <b class="qv">92</b>%</label><input type="range" min="10" max="100" value="92" class="w-full qs"></div>`);
  mount.appendChild(q);
  const box = resultBox(mount);
  q.querySelector('.qs').oninput = e => q.querySelector('.qv').textContent = e.target.value;
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    const img = await loadImage(f);
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    const cx = c.getContext('2d');
    if (to !== 'png') { cx.fillStyle = '#fff'; cx.fillRect(0, 0, c.width, c.height); }
    cx.drawImage(img, 0, 0); applyWatermark(cx, c.width, c.height, ctx);
    const blob = await canvasToBlob(c, type, q.querySelector('.qs').value / 100);
    showPreview(box, blob, f.name.replace(/\.[^.]+$/, '') + '.' + to, ctx);
  };
}
reg('img-convert', imageConvert);

reg('img-compress', function (mount, opt, ctx) {
  const presets = (opt && opt.presetKb) || null;
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3">
    <label class="kt-label">${T('ટાર્ગેટ સાઇઝ', 'Target size')} (KB)</label>
    ${presets ? `<div class="flex gap-2 mb-2">${presets.map(p => `<button class="cat-chip pk" data-k="${p}">${p}KB</button>`).join('')}</div>` : ''}
    <input type="number" class="kt-input tk" value="${presets ? presets[0] : 100}"></div>`);
  mount.appendChild(ctrl);
  ctrl.querySelectorAll('.pk').forEach(b => b.onclick = () => ctrl.querySelector('.tk').value = b.dataset.k);
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    const targetKB = parseInt(ctrl.querySelector('.tk').value) || 100;
    const img = await loadImage(f);
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    c.getContext('2d').drawImage(img, 0, 0);
    let lo = 0.05, hi = 1, blob = null;
    for (let i = 0; i < 9; i++) {
      const qy = (lo + hi) / 2;
      blob = await canvasToBlob(c, 'image/jpeg', qy);
      if (blob.size / 1024 > targetKB) hi = qy; else lo = qy;
    }
    // Downscale if still too big.
    let scale = 1;
    while (blob.size / 1024 > targetKB * 1.05 && scale > 0.2) {
      scale -= 0.1;
      const c2 = document.createElement('canvas'); c2.width = c.width * scale; c2.height = c.height * scale;
      c2.getContext('2d').drawImage(img, 0, 0, c2.width, c2.height);
      blob = await canvasToBlob(c2, 'image/jpeg', 0.85);
    }
    showPreview(box, blob, f.name.replace(/\.[^.]+$/, '') + '_' + Math.round(blob.size / 1024) + 'kb.jpg', ctx);
  };
});

reg('img-resize', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3 grid grid-cols-2 gap-3">
    <div><label class="kt-label">${T('પહોળાઈ', 'Width')} (px)</label><input type="number" class="kt-input w"></div>
    <div><label class="kt-label">${T('ઊંચાઈ', 'Height')} (px)</label><input type="number" class="kt-input h"></div>
    <label class="col-span-2 flex items-center gap-2"><input type="checkbox" class="lock" checked> ${T('એસ્પેક્ટ રેશિયો લોક', 'Lock aspect ratio')}</label></div>`);
  mount.appendChild(ctrl);
  const box = resultBox(mount);
  let ratio = 1, src = null;
  const wI = ctrl.querySelector('.w'), hI = ctrl.querySelector('.h'), lk = ctrl.querySelector('.lock');
  wI.oninput = () => { if (lk.checked && ratio) hI.value = Math.round(wI.value / ratio); };
  hI.oninput = () => { if (lk.checked && ratio) wI.value = Math.round(hI.value * ratio); };
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    src = await loadImage(f); ratio = src.naturalWidth / src.naturalHeight;
    wI.value = src.naturalWidth; hI.value = src.naturalHeight;
    const btn = el(`<button class="btn btn-primary mt-3">${T('રિસાઇઝ કરો', 'Resize')}</button>`); ctrl.appendChild(btn);
    btn.onclick = async () => {
      const c = document.createElement('canvas'); c.width = +wI.value; c.height = +hI.value;
      const cx = c.getContext('2d'); cx.drawImage(src, 0, 0, c.width, c.height); applyWatermark(cx, c.width, c.height, ctx);
      const blob = await canvasToBlob(c, 'image/png');
      showPreview(box, blob, f.name.replace(/\.[^.]+$/, '') + '_' + c.width + 'x' + c.height + '.png', ctx);
    };
  };
});

reg('img-rotate', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const box = resultBox(mount);
  let img = null, angle = 0, flipH = false, flipV = false;
  const render = async () => {
    if (!img) return;
    const rad = angle * Math.PI / 180, swap = angle % 180 !== 0;
    const c = document.createElement('canvas');
    c.width = swap ? img.naturalHeight : img.naturalWidth;
    c.height = swap ? img.naturalWidth : img.naturalHeight;
    const cx = c.getContext('2d');
    cx.translate(c.width / 2, c.height / 2); cx.rotate(rad); cx.scale(flipH ? -1 : 1, flipV ? -1 : 1);
    cx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2);
    const blob = await canvasToBlob(c, 'image/png');
    showPreview(box, blob, 'rotated.png', ctx);
  };
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    img = await loadImage(f);
    const bar = el(`<div class="flex gap-2 mt-3 flex-wrap">
      <button class="btn btn-ghost r90">↻ 90°</button><button class="btn btn-ghost rm90">↺ 90°</button>
      <button class="btn btn-ghost fh">⇄ Flip H</button><button class="btn btn-ghost fv">⇅ Flip V</button></div>`);
    mount.insertBefore(bar, box);
    bar.querySelector('.r90').onclick = () => { angle = (angle + 90) % 360; render(); };
    bar.querySelector('.rm90').onclick = () => { angle = (angle + 270) % 360; render(); };
    bar.querySelector('.fh').onclick = () => { flipH = !flipH; render(); };
    bar.querySelector('.fv').onclick = () => { flipV = !flipV; render(); };
    render();
  };
});

reg('img-crop', async function (mount, opt, ctx) {
  loadCss(X.CDN.cropperCss); await need('cropper');
  const input = fileInput(mount, { accept: 'image/*' });
  const stage = el('<div class="mt-3"></div>'); mount.appendChild(stage);
  const box = resultBox(mount);
  let cropper = null;
  input.onchange = () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    stage.innerHTML = `<img class="cim max-w-full">
      <div class="flex gap-2 mt-2 flex-wrap">${[['NaN', T('ફ્રી', 'Free')], ['1', '1:1'], ['1.7777', '16:9'], ['1.3333', '4:3']].map(r => `<button class="cat-chip ar" data-r="${r[0]}">${r[1]}</button>`).join('')}
      <button class="btn btn-primary docrop">${T('ક્રોપ કરો', 'Crop')}</button></div>`;
    const im = stage.querySelector('.cim'); im.src = URL.createObjectURL(f);
    im.onload = () => { if (cropper) cropper.destroy(); cropper = new Cropper(im, { viewMode: 1 }); };
    stage.querySelectorAll('.ar').forEach(b => b.onclick = () => cropper.setAspectRatio(parseFloat(b.dataset.r)));
    stage.querySelector('.docrop').onclick = async () => {
      const c = cropper.getCroppedCanvas(); applyWatermark(c.getContext('2d'), c.width, c.height, ctx);
      const blob = await canvasToBlob(c, 'image/png');
      showPreview(box, blob, 'cropped.png', ctx);
    };
  };
});

reg('img-to-pdf', async function (mount, opt, ctx) {
  await need('jspdf');
  const input = fileInput(mount, { accept: 'image/*', multiple: true, hint: T('એક કે અનેક ફોટા', 'One or more images') });
  const box = resultBox(mount);
  input.onchange = async () => {
    const files = Array.from(input.files); if (!files.length) return;
    if (!ctx.allowed && files.length > 5) { toast(T('ફ્રીમાં 5 સુધી', 'Up to 5 in free'), true); return; }
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    const { jsPDF } = window.jspdf; const pdf = new jsPDF();
    for (let i = 0; i < files.length; i++) {
      const img = await loadImage(files[i]);
      const pw = pdf.internal.pageSize.getWidth(), ph = pdf.internal.pageSize.getHeight();
      const r = Math.min(pw / img.naturalWidth, ph / img.naturalHeight);
      const w = img.naturalWidth * r, h = img.naturalHeight * r;
      const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
      const cx = c.getContext('2d'); cx.fillStyle = '#fff'; cx.fillRect(0, 0, c.width, c.height); cx.drawImage(img, 0, 0);
      applyWatermark(cx, c.width, c.height, ctx);
      if (i > 0) pdf.addPage();
      pdf.addImage(c.toDataURL('image/jpeg', 0.92), 'JPEG', (pw - w) / 2, (ph - h) / 2, w, h);
    }
    const blob = pdf.output('blob');
    showPreview(box, blob, 'images.pdf', ctx);
  };
});

reg('img-watermark', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3 grid sm:grid-cols-2 gap-3">
    <div><label class="kt-label">${T('વોટરમાર્ક ટેક્સ્ટ', 'Watermark text')}</label><input class="kt-input wt" value="AK Computer"></div>
    <div><label class="kt-label">${T('ઓપેસિટી', 'Opacity')} %</label><input type="number" class="kt-input wo" value="35"></div>
    <div><label class="kt-label">${T('પોઝિશન', 'Position')}</label><select class="kt-select wp">
      <option value="center">Center</option><option value="br">Bottom-right</option><option value="tile">Tile</option></select></div></div>`);
  mount.appendChild(ctrl);
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    const img = await loadImage(f);
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    const cx = c.getContext('2d'); cx.drawImage(img, 0, 0);
    const txt = ctrl.querySelector('.wt').value, op = ctrl.querySelector('.wo').value / 100, pos = ctrl.querySelector('.wp').value;
    cx.fillStyle = '#ffffff'; cx.globalAlpha = op; cx.font = `bold ${Math.max(20, c.width / 18)}px Inter`;
    if (pos === 'tile') { cx.rotate(-0.3); for (let y = 0; y < c.height * 1.4; y += c.height / 6) for (let x = -c.width; x < c.width * 1.4; x += c.width / 3) cx.fillText(txt, x, y); }
    else if (pos === 'br') { cx.textAlign = 'right'; cx.fillText(txt, c.width - 20, c.height - 20); }
    else { cx.textAlign = 'center'; cx.fillText(txt, c.width / 2, c.height / 2); }
    const blob = await canvasToBlob(c, 'image/png');
    showPreview(box, blob, 'watermarked.png', ctx);
  };
});

reg('signature-clean', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*', hint: T('સફેદ બેકગ્રાઉન્ડ પર સહી', 'Signature on white bg') });
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('થ્રેશોલ્ડ', 'Threshold')}: <b class="tv">200</b></label><input type="range" min="100" max="245" value="200" class="w-full ts"></div>`);
  mount.appendChild(ctrl);
  const box = resultBox(mount);
  ctrl.querySelector('.ts').oninput = e => { ctrl.querySelector('.tv').textContent = e.target.value; render(); };
  let img = null;
  async function render() {
    if (!img) return;
    const thr = +ctrl.querySelector('.ts').value;
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    const cx = c.getContext('2d'); cx.drawImage(img, 0, 0);
    const d = cx.getImageData(0, 0, c.width, c.height); const p = d.data;
    for (let i = 0; i < p.length; i += 4) { if (p[i] > thr && p[i + 1] > thr && p[i + 2] > thr) p[i + 3] = 0; }
    cx.putImageData(d, 0, 0);
    let blob = await canvasToBlob(c, 'image/png');
    showPreview(box, blob, 'signature.png', ctx);
  }
  input.onchange = async () => { const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return; img = await loadImage(f); render(); };
});

reg('img-base64', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const box = resultBox(mount);
  input.onchange = () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    const r = new FileReader();
    r.onload = () => {
      box.innerHTML = `<label class="kt-label">Base64 (data URI)</label><textarea class="kt-input" rows="6" readonly>${r.result}</textarea>`;
      setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(r.result) }]);
      if (ctx.toolId) logUsage(ctx.toolId); if (window.lucide) lucide.createIcons();
    };
    r.readAsDataURL(f);
  };
});

reg('dpi-changer', async function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3"><label class="kt-label">DPI</label><select class="kt-select dpi"><option>72</option><option selected>300</option><option>600</option></select></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    // Re-encode PNG with pHYs chunk for target DPI.
    const img = await loadImage(f); const dpi = +ctrl.querySelector('.dpi').value;
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    c.getContext('2d').drawImage(img, 0, 0);
    const blob = await canvasToBlob(c, 'image/png');
    const buf = new Uint8Array(await blob.arrayBuffer());
    const ppm = Math.round(dpi / 0.0254);
    const phys = new Uint8Array(21);
    const dv = new DataView(phys.buffer);
    dv.setUint32(0, 9); phys.set([0x70, 0x48, 0x59, 0x73], 4); // 'pHYs'
    dv.setUint32(8, ppm); dv.setUint32(12, ppm); phys[16] = 1;
    dv.setUint32(17, crc32(phys.subarray(4, 17)));
    // Insert after IHDR (first 33 bytes: 8 sig + 25 IHDR).
    const out = new Uint8Array(buf.length + 21);
    out.set(buf.subarray(0, 33), 0); out.set(phys, 33); out.set(buf.subarray(33), 54);
    showPreview(box, new Blob([out], { type: 'image/png' }), 'image_' + dpi + 'dpi.png', ctx);
  };
});
function crc32(bytes) {
  let c, crc = 0xffffffff;
  for (let n = 0; n < bytes.length; n++) { c = (crc ^ bytes[n]) & 0xff; for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1; crc = (crc >>> 8) ^ c; }
  return (crc ^ 0xffffffff) >>> 0;
}

reg('heic-convert', async function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: '.heic,.heif,image/heic' });
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f) return;
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    try { await need('heic'); const blob = await heic2any({ blob: f, toType: 'image/jpeg', quality: 0.9 }); showPreview(box, blob, f.name.replace(/\.[^.]+$/, '') + '.jpg', ctx); }
    catch (e) { box.innerHTML = `<div class="text-red-500">${T('HEIC કન્વર્ઝન નિષ્ફળ', 'HEIC conversion failed')}</div>`; }
  };
});

reg('exif-tool', async function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f || !checkMax(f, ctx.maxMb)) return;
    await need('exif');
    const data = await exifr.parse(f).catch(() => null);
    const rows = data ? Object.entries(data).slice(0, 20).map(([k, v]) => `<tr><td class="pr-4 opacity-60">${k}</td><td>${String(v).slice(0, 40)}</td></tr>`).join('') : `<tr><td>${T('કોઈ EXIF ડેટા નથી', 'No EXIF data')}</td></tr>`;
    box.innerHTML = `<table class="text-sm">${rows}</table>`;
    // Provide a stripped (no-EXIF) copy by canvas re-encode.
    const img = await loadImage(f); const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight; c.getContext('2d').drawImage(img, 0, 0);
    const clean = await canvasToBlob(c, 'image/jpeg', 0.95);
    setActions([{ label: T('EXIF દૂર કરી ડાઉનલોડ', 'Download without EXIF'), cls: 'btn-cta', icon: 'shield', onClick: () => downloadBlob(clean, 'clean_' + f.name) }]);
    if (window.lucide) lucide.createIcons();
  };
});

reg('meme-generator', function (mount, opt, ctx) { memeEngine(mount, opt, ctx); });
reg('meme-gen', function (mount, opt, ctx) { memeEngine(mount, opt, ctx); });
function memeEngine(mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3 grid grid-cols-2 gap-3">
    <input class="kt-input top" placeholder="${T('ઉપરનું ટેક્સ્ટ', 'Top text')}">
    <input class="kt-input bot" placeholder="${T('નીચેનું ટેક્સ્ટ', 'Bottom text')}"></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount); let img = null;
  async function render() {
    if (!img) return;
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight;
    const cx = c.getContext('2d'); cx.drawImage(img, 0, 0);
    const fs = c.width / 10; cx.font = `bold ${fs}px Impact, Inter`; cx.textAlign = 'center'; cx.fillStyle = '#fff'; cx.strokeStyle = '#000'; cx.lineWidth = fs / 14;
    const draw = (t, y) => { cx.strokeText(t.toUpperCase(), c.width / 2, y); cx.fillText(t.toUpperCase(), c.width / 2, y); };
    draw(ctrl.querySelector('.top').value, fs * 1.1); draw(ctrl.querySelector('.bot').value, c.height - fs * 0.4);
    const blob = await canvasToBlob(c, 'image/png'); showPreview(box, blob, 'meme.png', ctx);
  }
  ctrl.querySelectorAll('input').forEach(i => i.oninput = render);
  input.onchange = async () => { const f = input.files[0]; if (!f) return; img = await loadImage(f); render(); };
}

reg('color-picker', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: 'image/*' });
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f) return; const img = await loadImage(f);
    const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight; c.getContext('2d').drawImage(img, 0, 0);
    box.innerHTML = ''; const cv = document.createElement('canvas'); cv.width = c.width; cv.height = c.height; cv.style.maxWidth = '100%'; cv.style.cursor = 'crosshair'; cv.getContext('2d').drawImage(c, 0, 0); box.appendChild(cv);
    const out = el('<div class="mt-3 flex items-center gap-3"></div>'); box.appendChild(out);
    cv.onclick = e => {
      const r = cv.getBoundingClientRect(); const x = (e.clientX - r.left) * cv.width / r.width, y = (e.clientY - r.top) * cv.height / r.height;
      const p = c.getContext('2d').getImageData(x, y, 1, 1).data;
      const hex = '#' + [p[0], p[1], p[2]].map(v => v.toString(16).padStart(2, '0')).join('');
      out.innerHTML = `<div style="width:40px;height:40px;border-radius:8px;background:${hex}"></div><div><div class="font-mono">${hex}</div><div class="text-sm opacity-60">rgb(${p[0]}, ${p[1]}, ${p[2]})</div></div><button class="btn btn-ghost text-sm cp">${T('કોપી', 'Copy')}</button>`;
      out.querySelector('.cp').onclick = () => copy(hex);
    };
    if (ctx.toolId) logUsage(ctx.toolId);
  };
});

reg('favicon-gen', faviconEngine);
reg('favicon-generator', faviconEngine);
async function faviconEngine(mount, opt, ctx) {
  await need('jszip');
  const input = fileInput(mount, { accept: 'image/*' });
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f) return; const img = await loadImage(f);
    const sizes = [16, 32, 48, 64, 128, 180, 192, 256, 512]; const zip = new JSZip();
    for (const s of sizes) { const c = document.createElement('canvas'); c.width = c.height = s; c.getContext('2d').drawImage(img, 0, 0, s, s); const b = await canvasToBlob(c, 'image/png'); zip.file(`favicon-${s}x${s}.png`, b); }
    const blob = await zip.generateAsync({ type: 'blob' });
    box.innerHTML = `<div class="kt-card p-4">${sizes.map(s => `<span class="cat-chip mr-1 mb-1 inline-block">${s}px</span>`).join('')}</div>`;
    setActions([{ label: T('ZIP ડાઉનલોડ', 'Download ZIP'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(blob, 'favicons.zip') }]);
    if (ctx.toolId) logUsage(ctx.toolId); if (window.lucide) lucide.createIcons();
  };
}

reg('img-bulk', async function (mount, opt, ctx) {
  await need('jszip');
  const input = fileInput(mount, { accept: 'image/*', multiple: true });
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('આઉટપુટ ફોર્મેટ', 'Output format')}</label><select class="kt-select fmt"><option value="jpeg">JPG</option><option value="png">PNG</option><option value="webp">WEBP</option></select></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  input.onchange = async () => {
    const files = Array.from(input.files); if (!files.length) return;
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    const fmt = ctrl.querySelector('.fmt').value; const zip = new JSZip();
    for (const f of files) { const img = await loadImage(f); const c = document.createElement('canvas'); c.width = img.naturalWidth; c.height = img.naturalHeight; const cx = c.getContext('2d'); if (fmt === 'jpeg') { cx.fillStyle = '#fff'; cx.fillRect(0, 0, c.width, c.height); } cx.drawImage(img, 0, 0); const b = await canvasToBlob(c, 'image/' + fmt, 0.92); zip.file(f.name.replace(/\.[^.]+$/, '') + '.' + (fmt === 'jpeg' ? 'jpg' : fmt), b); }
    const blob = await zip.generateAsync({ type: 'blob' });
    box.innerHTML = `<div class="kt-card p-4">${files.length} ${T('ફાઇલ કન્વર્ટ થઈ', 'files converted')}</div>`;
    setActions([{ label: T('ZIP ડાઉનલોડ', 'Download ZIP'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(blob, 'converted.zip') }]);
    if (window.lucide) lucide.createIcons();
  };
});

reg('passport-photo', async function (mount, opt, ctx) {
  loadCss(X.CDN.cropperCss); await need('cropper', 'jspdf');
  const input = fileInput(mount, { accept: 'image/*' });
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('પ્રીસેટ', 'Preset')}</label><select class="kt-select ps">
    <option value="35,45">India 35×45mm</option><option value="51,51">US 2×2 inch</option><option value="35,45">UK 35×45mm</option></select></div>`);
  mount.appendChild(ctrl);
  const stage = el('<div class="mt-3"></div>'); mount.appendChild(stage); const box = resultBox(mount); let cropper = null;
  input.onchange = () => {
    const f = input.files[0]; if (!f) return;
    stage.innerHTML = `<img class="pim max-w-full"><button class="btn btn-primary mt-2 mk">${T('શીટ બનાવો (6 ફોટા)', 'Make sheet (6 photos)')}</button>`;
    const im = stage.querySelector('.pim'); im.src = URL.createObjectURL(f);
    const [w, h] = ctrl.querySelector('.ps').value.split(',').map(Number);
    im.onload = () => { if (cropper) cropper.destroy(); cropper = new Cropper(im, { viewMode: 1, aspectRatio: w / h }); };
    stage.querySelector('.mk').onclick = async () => {
      const c = cropper.getCroppedCanvas({ width: 413, height: 531 });
      const { jsPDF } = window.jspdf; const pdf = new jsPDF({ unit: 'mm', format: [152, 102] }); // 6x4 inch
      const img = c.toDataURL('image/jpeg', 0.95);
      let x = 6, y = 6;
      for (let i = 0; i < 6; i++) { pdf.addImage(img, 'JPEG', x, y, 35, 45); x += 40; if (x > 130) { x = 6; y += 48; } }
      showPreview(box, pdf.output('blob'), 'passport-sheet.pdf', ctx);
    };
  };
});

/* ================= PDF ENGINES (pdf-lib) ================= */
async function loadPdfLib() { await need('pdflib'); return window.PDFLib; }
function multiPdfInput(mount, cb) {
  const input = fileInput(mount, { accept: 'application/pdf', multiple: true, hint: 'PDF' });
  input.onchange = () => cb(Array.from(input.files), input);
  return input;
}
reg('pdf-merge', function (mount, opt, ctx) {
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    if (files.length < 2) { toast(T('ઓછામાં ઓછી 2 PDF', 'At least 2 PDFs'), true); return; }
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    const { PDFDocument } = await loadPdfLib(); const out = await PDFDocument.create();
    for (const f of files) { const src = await PDFDocument.load(await f.arrayBuffer()); const pages = await out.copyPages(src, src.getPageIndices()); pages.forEach(p => out.addPage(p)); }
    showPreview(box, new Blob([await out.save()], { type: 'application/pdf' }), 'merged.pdf', ctx);
  });
});
reg('pdf-split', function (mount, opt, ctx) {
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('પાના રેન્જ (દા.ત. 1-3,5)', 'Pages (e.g. 1-3,5)')}</label><input class="kt-input rg" placeholder="1-3,5"></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument } = await loadPdfLib(); const src = await PDFDocument.load(await files[0].arrayBuffer());
    const idx = parseRange(ctrl.querySelector('.rg').value, src.getPageCount());
    const out = await PDFDocument.create(); const pages = await out.copyPages(src, idx); pages.forEach(p => out.addPage(p));
    showPreview(box, new Blob([await out.save()], { type: 'application/pdf' }), 'split.pdf', ctx);
  });
});
reg('pdf-extract', Engines_pdf_pages('extract'));
reg('pdf-delete-pages', Engines_pdf_pages('delete'));
reg('pdf-delete', Engines_pdf_pages('delete'));
function Engines_pdf_pages(mode) {
  return function (mount, opt, ctx) {
    const ctrl = el(`<div class="mt-3"><label class="kt-label">${mode === 'delete' ? T('દૂર કરવાના પાના', 'Pages to delete') : T('કાઢવાના પાના', 'Pages to extract')} (1-3,5)</label><input class="kt-input rg"></div>`); mount.appendChild(ctrl);
    const box = resultBox(mount);
    multiPdfInput(mount, async (files) => {
      const { PDFDocument } = await loadPdfLib(); const src = await PDFDocument.load(await files[0].arrayBuffer());
      const total = src.getPageCount(); let idx = parseRange(ctrl.querySelector('.rg').value, total);
      if (mode === 'delete') { const del = new Set(idx); idx = []; for (let i = 0; i < total; i++) if (!del.has(i)) idx.push(i); }
      const out = await PDFDocument.create(); const pages = await out.copyPages(src, idx); pages.forEach(p => out.addPage(p));
      showPreview(box, new Blob([await out.save()], { type: 'application/pdf' }), mode + '.pdf', ctx);
    });
  };
}
reg('pdf-rotate', function (mount, opt, ctx) {
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('કોણ', 'Angle')}</label><select class="kt-select ang"><option>90</option><option>180</option><option>270</option></select></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument, degrees } = await loadPdfLib(); const doc = await PDFDocument.load(await files[0].arrayBuffer());
    const a = +ctrl.querySelector('.ang').value; doc.getPages().forEach(p => p.setRotation(degrees((p.getRotation().angle + a) % 360)));
    showPreview(box, new Blob([await doc.save()], { type: 'application/pdf' }), 'rotated.pdf', ctx);
  });
});
reg('pdf-page-num', function (mount, opt, ctx) {
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument, rgb, StandardFonts } = await loadPdfLib(); const doc = await PDFDocument.load(await files[0].arrayBuffer());
    const font = await doc.embedFont(StandardFonts.Helvetica); const pages = doc.getPages();
    pages.forEach((p, i) => { const { width } = p.getSize(); p.drawText(`${i + 1} / ${pages.length}`, { x: width / 2 - 15, y: 20, size: 10, font, color: rgb(0.3, 0.3, 0.3) }); });
    showPreview(box, new Blob([await doc.save()], { type: 'application/pdf' }), 'numbered.pdf', ctx);
  });
});
reg('pdf-watermark', function (mount, opt, ctx) {
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('વોટરમાર્ક ટેક્સ્ટ', 'Watermark text')}</label><input class="kt-input wt" value="CONFIDENTIAL"></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument, rgb, StandardFonts, degrees } = await loadPdfLib(); const doc = await PDFDocument.load(await files[0].arrayBuffer());
    const font = await doc.embedFont(StandardFonts.HelveticaBold); const t = ctrl.querySelector('.wt').value;
    doc.getPages().forEach(p => { const { width, height } = p.getSize(); p.drawText(t, { x: width / 5, y: height / 2, size: 50, font, color: rgb(0.95, 0.62, 0.04), opacity: 0.3, rotate: degrees(35) }); });
    showPreview(box, new Blob([await doc.save()], { type: 'application/pdf' }), 'watermarked.pdf', ctx);
  });
});
reg('pdf-metadata', function (mount, opt, ctx) {
  const ctrl = el(`<div class="mt-3 grid sm:grid-cols-2 gap-3"><div><label class="kt-label">Title</label><input class="kt-input tt"></div><div><label class="kt-label">Author</label><input class="kt-input au"></div></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument } = await loadPdfLib(); const doc = await PDFDocument.load(await files[0].arrayBuffer());
    if (ctrl.querySelector('.tt').value) doc.setTitle(ctrl.querySelector('.tt').value);
    if (ctrl.querySelector('.au').value) doc.setAuthor(ctrl.querySelector('.au').value);
    showPreview(box, new Blob([await doc.save()], { type: 'application/pdf' }), 'metadata.pdf', ctx);
  });
});
reg('pdf-reorder', function (mount, opt, ctx) {
  const ctrl = el(`<div class="mt-3"><label class="kt-label">${T('નવો ક્રમ (દા.ત. 3,1,2)', 'New order (e.g. 3,1,2)')}</label><input class="kt-input od"></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument } = await loadPdfLib(); const src = await PDFDocument.load(await files[0].arrayBuffer());
    const order = ctrl.querySelector('.od').value.split(',').map(x => parseInt(x) - 1).filter(x => x >= 0);
    const out = await PDFDocument.create(); const pages = await out.copyPages(src, order.length ? order : src.getPageIndices()); pages.forEach(p => out.addPage(p));
    showPreview(box, new Blob([await out.save()], { type: 'application/pdf' }), 'reordered.pdf', ctx);
  });
});
reg('pdf-esign', async function (mount, opt, ctx) {
  const box = resultBox(mount);
  const pad = el(`<div class="mt-3"><label class="kt-label">${T('અહીં સહી દોરો', 'Draw signature here')}</label>
    <canvas class="sig border rounded-lg bg-white" width="400" height="150" style="touch-action:none;border-color:var(--border)"></canvas>
    <div class="flex gap-2 mt-2"><button class="btn btn-ghost text-sm clr">${T('સાફ', 'Clear')}</button></div></div>`);
  mount.appendChild(pad);
  const cv = pad.querySelector('.sig'), cx = cv.getContext('2d'); cx.lineWidth = 2; cx.lineCap = 'round'; let drawing = false;
  const pos = e => { const r = cv.getBoundingClientRect(); const t = e.touches ? e.touches[0] : e; return [t.clientX - r.left, t.clientY - r.top]; };
  cv.addEventListener('pointerdown', e => { drawing = true; const [x, y] = pos(e); cx.beginPath(); cx.moveTo(x, y); });
  cv.addEventListener('pointermove', e => { if (!drawing) return; const [x, y] = pos(e); cx.lineTo(x, y); cx.stroke(); });
  window.addEventListener('pointerup', () => drawing = false);
  pad.querySelector('.clr').onclick = () => cx.clearRect(0, 0, cv.width, cv.height);
  multiPdfInput(mount, async (files) => {
    const { PDFDocument } = await loadPdfLib(); const doc = await PDFDocument.load(await files[0].arrayBuffer());
    const png = await doc.embedPng(cv.toDataURL('image/png')); const p = doc.getPages()[doc.getPageCount() - 1];
    p.drawImage(png, { x: p.getSize().width - 160, y: 40, width: 140, height: 52 });
    showPreview(box, new Blob([await doc.save()], { type: 'application/pdf' }), 'signed.pdf', ctx);
  });
});
reg('pdf-to-text', async function (mount, opt, ctx) {
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    await need('pdfjs'); pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    const pdf = await pdfjsLib.getDocument({ data: await files[0].arrayBuffer() }).promise; let text = '';
    for (let i = 1; i <= pdf.numPages; i++) { const pg = await pdf.getPage(i); const c = await pg.getTextContent(); text += c.items.map(it => it.str).join(' ') + '\n\n'; }
    box.innerHTML = `<textarea class="kt-input" rows="12" readonly>${text.replace(/</g, '&lt;')}</textarea>`;
    setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(text) }, { label: T('ડાઉનલોડ', 'Download'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(new Blob([text], { type: 'text/plain' }), 'text.txt') }]);
    if (ctx.toolId) logUsage(ctx.toolId); if (window.lucide) lucide.createIcons();
  });
});
reg('pdf-to-img', async function (mount, opt, ctx) {
  await need('jszip');
  const box = resultBox(mount);
  multiPdfInput(mount, async (files) => {
    box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
    await need('pdfjs'); pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    const pdf = await pdfjsLib.getDocument({ data: await files[0].arrayBuffer() }).promise; const zip = new JSZip(); let first = null;
    for (let i = 1; i <= pdf.numPages; i++) {
      const pg = await pdf.getPage(i); const vp = pg.getViewport({ scale: 2 });
      const c = document.createElement('canvas'); c.width = vp.width; c.height = vp.height; await pg.render({ canvasContext: c.getContext('2d'), viewport: vp }).promise;
      const b = await canvasToBlob(c, 'image/jpeg', 0.92); zip.file(`page-${i}.jpg`, b); if (i === 1) first = b;
    }
    if (pdf.numPages === 1) { showPreview(box, first, 'page-1.jpg', ctx); return; }
    const blob = await zip.generateAsync({ type: 'blob' });
    box.innerHTML = `<div class="kt-card p-4">${pdf.numPages} ${T('પાના કન્વર્ટ થયા', 'pages converted')}</div>`;
    setActions([{ label: T('ZIP ડાઉનલોડ', 'Download ZIP'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(blob, 'pages.zip') }]);
    if (window.lucide) lucide.createIcons();
  });
});
/* PDF ops that need a server binary: try /api/convert.php, else friendly message. */
function serverPdf(label) {
  return function (mount, opt, ctx) {
    mount.innerHTML = `<div class="text-center py-4">
      <div class="text-4xl mb-2">🦚</div>
      <p class="font-semibold">${label}</p>
      <p class="opacity-60 text-sm mt-1">${T('આ સુવિધા સર્વર પર Ghostscript/LibreOffice હોય તો ચાલે છે. અપલોડ કરો — ન હોય તો સંદેશ મળશે.', 'This uses a server binary (Ghostscript/LibreOffice) if installed. Upload to try.')}</p></div>`;
    const box = resultBox(mount);
    multiPdfInput(mount, async (files) => {
      box.innerHTML = `<div class="chakra-loader mx-auto"></div>`;
      const fd = new FormData(); fd.append('file', files[0]); fd.append('op', opt.op || ''); fd.append('from', opt.from || ''); fd.append('to', opt.to || '');
      try {
        const r = await fetch(KT.url + '/api/convert.php', { method: 'POST', headers: { 'X-CSRF': KT.csrf }, body: fd });
        if (!r.ok) throw new Error('server');
        const ct = r.headers.get('content-type') || '';
        if (ct.includes('json')) { const j = await r.json(); box.innerHTML = `<div class="text-amber-600">${j.error || T('સર્વર બાઈનરી ઉપલબ્ધ નથી', 'Server binary not available')}</div>`; return; }
        const blob = await r.blob(); showPreview(box, blob, 'output.' + (opt.to || 'pdf'), ctx);
      } catch (e) { box.innerHTML = `<div class="text-amber-600">${T('સર્વર પર બાઈનરી ઉપલબ્ધ નથી. કૃપા કરી હોસ્ટ પર Ghostscript/LibreOffice ઇન્સ્ટોલ કરો.', 'Server binary unavailable. Install Ghostscript/LibreOffice on the host.')}</div>`; }
    });
  };
}
reg('pdf-compress', serverPdf(T('PDF કોમ્પ્રેસ', 'Compress PDF')));
reg('pdf-protect', serverPdf(T('PDF પાસવર્ડ પ્રોટેક્ટ', 'Password Protect')));
reg('pdf-unlock', serverPdf(T('PDF પાસવર્ડ દૂર', 'Password Remove')));
reg('pdf-to-word', serverPdf(T('PDF થી Word', 'PDF to Word')));
reg('pdf-to-excel', serverPdf(T('PDF થી Excel', 'PDF to Excel')));
reg('pdf-to-ppt', serverPdf(T('PDF થી PPT', 'PDF to PPT')));
reg('server-convert', serverPdf(T('ડોક્યુમેન્ટ કન્વર્ટ', 'Document Convert')));

/* ================= TEXT / DOC ENGINES ================= */
function textArea(mount, ph) { const t = el(`<textarea class="kt-input" rows="8" placeholder="${ph || ''}"></textarea>`); mount.appendChild(t); return t; }
reg('word-count', function (mount, opt, ctx) {
  const ta = textArea(mount, T('અહીં ટેક્સ્ટ પેસ્ટ કરો...', 'Paste text here...')); const box = resultBox(mount);
  const upd = () => {
    const t = ta.value; const words = (t.match(/\S+/g) || []).length; const chars = t.length; const sent = (t.match(/[.!?।]+/g) || []).length; const para = (t.split(/\n\s*\n/).filter(x => x.trim()).length);
    box.innerHTML = `<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">${[[words, T('શબ્દ', 'Words')], [chars, T('અક્ષર', 'Chars')], [sent, T('વાક્ય', 'Sentences')], [para, T('ફકરા', 'Paragraphs')], [Math.ceil(words / 200), T('વાંચન મિનિટ', 'Read min')]].map(r => `<div class="kt-card p-3 text-center"><div class="text-xl font-bold" style="color:var(--peacock-teal)">${r[0]}</div><div class="text-xs opacity-60">${r[1]}</div></div>`).join('')}</div>`;
  };
  ta.oninput = upd; upd();
});
reg('case-convert', function (mount, opt, ctx) {
  const ta = textArea(mount); const box = resultBox(mount);
  const ops = { UPPER: s => s.toUpperCase(), lower: s => s.toLowerCase(), Title: s => s.replace(/\w\S*/g, w => w[0].toUpperCase() + w.slice(1).toLowerCase()), Sentence: s => s.replace(/(^\s*\w|[.!?]\s*\w)/g, c => c.toUpperCase()), aLtErNaTe: s => s.split('').map((c, i) => i % 2 ? c.toUpperCase() : c.toLowerCase()).join('') };
  box.innerHTML = `<div class="flex gap-2 flex-wrap">${Object.keys(ops).map(k => `<button class="cat-chip op" data-k="${k}">${k}</button>`).join('')}</div><textarea class="kt-input mt-3 out" rows="6" readonly></textarea>`;
  box.querySelectorAll('.op').forEach(b => b.onclick = () => { const r = ops[b.dataset.k](ta.value); box.querySelector('.out').value = r; ctx._resultText = r; });
  setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(box.querySelector('.out').value) }]);
});
reg('find-replace', function (mount, opt, ctx) {
  const ta = textArea(mount);
  const ctrl = el(`<div class="grid grid-cols-2 gap-3 mt-3"><input class="kt-input fnd" placeholder="${T('શોધો', 'Find')}"><input class="kt-input rep" placeholder="${T('બદલો', 'Replace')}"><label class="flex items-center gap-2 text-sm"><input type="checkbox" class="re"> Regex</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" class="ci" checked> ${T('કેસ અવગણો', 'Ignore case')}</label></div><button class="btn btn-primary mt-3 go">${T('બદલો', 'Replace')}</button>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  ctrl.querySelector('.go').onclick = () => {
    let out; const f = ctrl.querySelector('.fnd').value, rp = ctrl.querySelector('.rep').value, flags = 'g' + (ctrl.querySelector('.ci').checked ? 'i' : '');
    try { out = ta.value.replace(ctrl.querySelector('.re').checked ? new RegExp(f, flags) : new RegExp(f.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), flags), rp); }
    catch (e) { toast(e.message, true); return; }
    box.innerHTML = `<textarea class="kt-input out" rows="8" readonly>${out.replace(/</g, '&lt;')}</textarea>`;
    setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(out) }]); if (window.lucide) lucide.createIcons();
  };
});
reg('dedupe-lines', function (mount, opt, ctx) {
  const ta = textArea(mount);
  const ctrl = el(`<div class="flex gap-3 mt-3 flex-wrap"><label class="flex items-center gap-2 text-sm"><input type="checkbox" class="srt"> ${T('સોર્ટ', 'Sort')}</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" class="ci" checked> ${T('કેસ અવગણો', 'Ignore case')}</label><button class="btn btn-primary go">${T('પ્રોસેસ', 'Process')}</button></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  ctrl.querySelector('.go').onclick = () => {
    let lines = ta.value.split('\n'); const seen = new Set(); const ci = ctrl.querySelector('.ci').checked;
    let out = lines.filter(l => { const k = ci ? l.toLowerCase() : l; if (seen.has(k)) return false; seen.add(k); return true; });
    if (ctrl.querySelector('.srt').checked) out.sort();
    const r = out.join('\n');
    box.innerHTML = `<div class="text-sm opacity-60 mb-2">${lines.length}→${out.length} ${T('લાઇન', 'lines')}</div><textarea class="kt-input" rows="8" readonly>${r.replace(/</g, '&lt;')}</textarea>`;
    setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(r) }]); if (window.lucide) lucide.createIcons();
  };
});
reg('text-diff', function (mount, opt, ctx) {
  mount.innerHTML = `<div class="grid sm:grid-cols-2 gap-3"><textarea class="kt-input a" rows="8" placeholder="Text A"></textarea><textarea class="kt-input b" rows="8" placeholder="Text B"></textarea></div><button class="btn btn-primary mt-3 go">${T('સરખાવો', 'Compare')}</button>`;
  const box = resultBox(mount);
  mount.querySelector('.go').onclick = () => {
    const a = mount.querySelector('.a').value.split('\n'), b = mount.querySelector('.b').value.split('\n'); const max = Math.max(a.length, b.length); let html = '';
    for (let i = 0; i < max; i++) { const same = a[i] === b[i]; html += `<div class="grid grid-cols-2 gap-2 text-sm font-mono ${same ? '' : 'bg-red-500/10'}"><div>${(a[i] || '').replace(/</g, '&lt;')}</div><div>${(b[i] || '').replace(/</g, '&lt;')}</div></div>`; }
    box.innerHTML = html; if (ctx.toolId) logUsage(ctx.toolId);
  };
});
reg('md-html', async function (mount, opt, ctx) {
  await need('marked');
  const ta = textArea(mount, '# Hello\n\n**Markdown** here'); const box = resultBox(mount);
  const upd = () => { const html = marked.parse(ta.value); box.innerHTML = `<div class="grid sm:grid-cols-2 gap-3"><div class="kt-card p-3 prose max-w-none">${html}</div><textarea class="kt-input" rows="8" readonly>${html.replace(/</g, '&lt;')}</textarea></div>`; ctx._resultText = html; };
  ta.oninput = upd; upd();
  setActions([{ label: T('HTML કોપી', 'Copy HTML'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(marked.parse(ta.value)) }]);
});
reg('html-text', function (mount, opt, ctx) {
  const ta = textArea(mount, '<p>Hello <b>world</b></p>'); const box = resultBox(mount);
  const upd = () => { const d = document.createElement('div'); d.innerHTML = ta.value; const txt = d.textContent || ''; box.innerHTML = `<textarea class="kt-input" rows="8" readonly>${txt.replace(/</g, '&lt;')}</textarea>`; ctx._resultText = txt; };
  ta.oninput = upd; upd();
  setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy((() => { const d = document.createElement('div'); d.innerHTML = ta.value; return d.textContent; })()) }]);
});
reg('json-format', function (mount, opt, ctx) {
  const ta = textArea(mount, '{"name":"Krishna","tools":120}'); const box = resultBox(mount);
  const run = (min) => { try { const o = JSON.parse(ta.value); const r = min ? JSON.stringify(o) : JSON.stringify(o, null, 2); box.innerHTML = `<textarea class="kt-input" rows="10" readonly>${r.replace(/</g, '&lt;')}</textarea>`; ctx._resultText = r; } catch (e) { box.innerHTML = `<div class="text-red-500">❌ ${e.message}</div>`; } };
  setActions([{ label: T('ફોર્મેટ', 'Beautify'), cls: 'btn-primary', icon: 'braces', onClick: () => run(false) }, { label: 'Minify', icon: 'minimize-2', onClick: () => run(true) }, { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => copy(ctx._resultText || '') }]);
  run(false);
});
reg('xml-json', function (mount, opt, ctx) {
  const ta = textArea(mount, '<root><name>Krishna</name></root>'); const box = resultBox(mount);
  const x2j = () => { try { const doc = new DOMParser().parseFromString(ta.value, 'text/xml'); const o = xmlToObj(doc.documentElement); const r = JSON.stringify(o, null, 2); box.innerHTML = `<textarea class="kt-input" rows="10" readonly>${r}</textarea>`; ctx._resultText = r; } catch (e) { toast(e.message, true); } };
  setActions([{ label: 'XML→JSON', cls: 'btn-primary', icon: 'code', onClick: x2j }, { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => copy(ctx._resultText || '') }]);
  x2j();
  function xmlToObj(node) { const o = {}; for (const c of node.children) { const v = c.children.length ? xmlToObj(c) : c.textContent; if (o[c.tagName]) { if (!Array.isArray(o[c.tagName])) o[c.tagName] = [o[c.tagName]]; o[c.tagName].push(v); } else o[c.tagName] = v; } return node.children.length ? o : node.textContent; }
});
reg('excel-csv', async function (mount, opt, ctx) {
  await need('xlsx'); const to = (opt && opt.to) || 'csv';
  const input = fileInput(mount, { accept: to === 'csv' ? '.xlsx,.xls' : '.csv', hint: to === 'csv' ? 'Excel' : 'CSV' });
  const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f) return; const wb = XLSX.read(await f.arrayBuffer());
    const ws = wb.Sheets[wb.SheetNames[0]];
    if (to === 'csv') { const csv = XLSX.utils.sheet_to_csv(ws); showText(box, csv, f.name.replace(/\.[^.]+$/, '') + '.csv', 'text/csv', ctx); }
    else { const out = XLSX.write(wb, { bookType: 'xlsx', type: 'array' }); showPreview(box, new Blob([out]), f.name.replace(/\.[^.]+$/, '') + '.xlsx', ctx); }
  };
});
reg('json-excel', async function (mount, opt, ctx) {
  await need('xlsx'); const to = (opt && opt.to) || 'xlsx';
  if (to === 'xlsx') {
    const ta = textArea(mount, '[{"name":"Krishna","tools":120}]'); const box = resultBox(mount);
    setActions([{ label: T('કન્વર્ટ', 'Convert'), cls: 'btn-primary', icon: 'table', onClick: () => { try { const arr = JSON.parse(ta.value); const ws = XLSX.utils.json_to_sheet(Array.isArray(arr) ? arr : [arr]); const wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Sheet1'); const out = XLSX.write(wb, { bookType: 'xlsx', type: 'array' }); showPreview(box, new Blob([out]), 'data.xlsx', ctx); } catch (e) { toast(e.message, true); } } }]);
  } else {
    const input = fileInput(mount, { accept: '.xlsx,.xls' }); const box = resultBox(mount);
    input.onchange = async () => { const f = input.files[0]; if (!f) return; const wb = XLSX.read(await f.arrayBuffer()); const json = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]]); showText(box, JSON.stringify(json, null, 2), 'data.json', 'application/json', ctx); };
  }
});
reg('csv-merge', function (mount, opt, ctx) {
  const input = fileInput(mount, { accept: '.csv', multiple: true, hint: T('અનેક CSV', 'Multiple CSV') }); const box = resultBox(mount);
  input.onchange = async () => {
    const files = Array.from(input.files); if (!files.length) return; let merged = ''; let header = '';
    for (let i = 0; i < files.length; i++) { const t = await files[i].text(); const lines = t.split('\n'); if (i === 0) { header = lines[0]; merged = t.trim(); } else { merged += '\n' + lines.slice(1).join('\n').trim(); } }
    showText(box, merged, 'merged.csv', 'text/csv', ctx);
  };
});
reg('sheet-to-pdf', async function (mount, opt, ctx) {
  await need('xlsx', 'jspdf'); const input = fileInput(mount, { accept: '.xlsx,.xls,.csv' }); const box = resultBox(mount);
  input.onchange = async () => {
    const f = input.files[0]; if (!f) return; const wb = XLSX.read(await f.arrayBuffer()); const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1 });
    const { jsPDF } = window.jspdf; const pdf = new jsPDF(); let y = 15;
    rows.slice(0, 60).forEach(r => { pdf.setFontSize(9); pdf.text(r.map(c => String(c)).join('  |  ').slice(0, 90), 10, y); y += 6; if (y > 285) { pdf.addPage(); y = 15; } });
    showPreview(box, pdf.output('blob'), f.name.replace(/\.[^.]+$/, '') + '.pdf', ctx);
  };
});
function showText(box, text, name, mime, ctx) {
  box.innerHTML = `<textarea class="kt-input" rows="10" readonly>${text.replace(/</g, '&lt;')}</textarea>`;
  setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(text) }, { label: T('ડાઉનલોડ', 'Download'), cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(new Blob([text], { type: mime }), name) }, { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(text.slice(0, 3000)) }]);
  if (ctx.toolId) logUsage(ctx.toolId); if (window.lucide) lucide.createIcons();
}

/* ================= GENERATORS / MISC ================= */
reg('qr-gen', async function (mount, opt, ctx) { qrEngine(mount, ctx, false); });
reg('wifi-qr', async function (mount, opt, ctx) { qrEngine(mount, ctx, true); });
async function qrEngine(mount, ctx, wifiOnly) {
  await need('qrcode');
  const types = wifiOnly ? ['wifi'] : ['url', 'text', 'wifi', 'vcard', 'upi'];
  const ctrl = el(`<div><label class="kt-label">${T('પ્રકાર', 'Type')}</label><select class="kt-select ty">${types.map(t => `<option value="${t}">${t.toUpperCase()}</option>`).join('')}</select><div class="fields mt-3"></div></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount); const fields = ctrl.querySelector('.fields');
  const forms = {
    url: `<input class="kt-input v1" placeholder="https://tools.akdwk.in">`,
    text: `<textarea class="kt-input v1" placeholder="${T('ટેક્સ્ટ', 'Text')}"></textarea>`,
    wifi: `<input class="kt-input v1 mb-2" placeholder="WiFi SSID"><input class="kt-input v2 mb-2" placeholder="${T('પાસવર્ડ', 'Password')}"><select class="kt-select v3"><option>WPA</option><option>WEP</option><option value="nopass">${T('પાસવર્ડ વગર', 'None')}</option></select>`,
    vcard: `<input class="kt-input v1 mb-2" placeholder="${T('નામ', 'Name')}"><input class="kt-input v2 mb-2" placeholder="${T('ફોન', 'Phone')}"><input class="kt-input v3" placeholder="Email">`,
    upi: `<input class="kt-input v1 mb-2" placeholder="UPI ID (name@bank)"><input class="kt-input v2 mb-2" placeholder="${T('નામ', 'Name')}"><input class="kt-input v3" placeholder="${T('રકમ (વૈકલ્પિક)', 'Amount (optional)')}">`,
  };
  function payload() {
    const ty = ctrl.querySelector('.ty').value; const v1 = (fields.querySelector('.v1') || {}).value || '', v2 = (fields.querySelector('.v2') || {}).value || '', v3 = (fields.querySelector('.v3') || {}).value || '';
    if (ty === 'wifi') return `WIFI:T:${v3 || 'WPA'};S:${v1};P:${v2};;`;
    if (ty === 'vcard') return `BEGIN:VCARD\nVERSION:3.0\nFN:${v1}\nTEL:${v2}\nEMAIL:${v3}\nEND:VCARD`;
    if (ty === 'upi') return `upi://pay?pa=${v1}&pn=${encodeURIComponent(v2)}${v3 ? '&am=' + v3 : ''}&cu=INR`;
    return v1;
  }
  function render() {
    const data = payload(); if (!data) return; box.innerHTML = '<div class="qrc inline-block p-4 bg-white rounded-xl"></div>';
    const holder = box.querySelector('.qrc'); new QRCode(holder, { text: data, width: 240, height: 240, correctLevel: QRCode.CorrectLevel.H });
    setTimeout(() => { const img = holder.querySelector('img') || holder.querySelector('canvas'); setActions([{ label: T('ડાઉનલોડ', 'Download'), cls: 'btn-cta', icon: 'download', onClick: () => { const src = img.tagName === 'IMG' ? img.src : img.toDataURL(); downloadDataUrl(src, 'qr.png'); } }, { label: T('પ્રિન્ટ', 'Print'), icon: 'printer', onClick: () => window.print() }]); if (window.lucide) lucide.createIcons(); }, 150);
    if (ctx.toolId) logUsage(ctx.toolId);
  }
  function fillForm() { fields.innerHTML = forms[ctrl.querySelector('.ty').value]; fields.oninput = render; render(); }
  ctrl.querySelector('.ty').onchange = fillForm; fillForm();
}
reg('qr-scanner', async function (mount, opt, ctx) { qrScan(mount, ctx); });
reg('qr-scan', async function (mount, opt, ctx) { qrScan(mount, ctx); });
async function qrScan(mount, ctx) {
  await need('qrscan');
  mount.innerHTML = `<div id="qrReader" style="max-width:400px"></div><div class="mt-3"><label class="btn btn-ghost"><i data-lucide="image"></i> ${T('ઇમેજથી સ્કેન', 'Scan from image')}<input type="file" accept="image/*" class="hidden qf"></label></div>`;
  const box = resultBox(mount);
  const show = txt => { box.innerHTML = `<div class="kt-card p-4"><div class="text-xs opacity-60">${T('પરિણામ', 'Result')}</div><div class="font-mono break-all">${txt.replace(/</g, '&lt;')}</div></div>`; setActions([{ label: T('કોપી', 'Copy'), cls: 'btn-primary', icon: 'copy', onClick: () => copy(txt) }]); if (/^https?:/.test(txt)) window.open(txt, '_blank'); if (window.lucide) lucide.createIcons(); if (ctx.toolId) logUsage(ctx.toolId); };
  try { const h = new Html5Qrcode('qrReader'); h.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, t => { show(t); h.stop(); }).catch(() => {}); } catch (e) {}
  mount.querySelector('.qf').onchange = async e => { const h = new Html5Qrcode('qrReader'); try { const r = await h.scanFile(e.target.files[0], true); show(r); } catch (er) { toast(T('QR મળ્યો નહીં', 'No QR found'), true); } };
  if (window.lucide) lucide.createIcons();
}
reg('barcode-gen', async function (mount, opt, ctx) {
  await need('jsbarcode');
  const ctrl = el(`<div class="grid sm:grid-cols-2 gap-3"><div><label class="kt-label">${T('ડેટા', 'Data')}</label><input class="kt-input v" value="AKC12345"></div><div><label class="kt-label">${T('ફોર્મેટ', 'Format')}</label><select class="kt-select fmt"><option>CODE128</option><option>EAN13</option><option>UPC</option><option>CODE39</option></select></div></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  const render = () => { box.innerHTML = '<div class="bg-white inline-block p-4 rounded-xl"><svg class="bc"></svg></div>'; try { JsBarcode(box.querySelector('.bc'), ctrl.querySelector('.v').value, { format: ctrl.querySelector('.fmt').value }); setActions([{ label: T('ડાઉનલોડ', 'Download'), cls: 'btn-cta', icon: 'download', onClick: () => { const svg = box.querySelector('.bc'); const s = new XMLSerializer().serializeToString(svg); downloadBlob(new Blob([s], { type: 'image/svg+xml' }), 'barcode.svg'); } }, { label: T('પ્રિન્ટ', 'Print'), icon: 'printer', onClick: () => window.print() }]); if (window.lucide) lucide.createIcons(); if (ctx.toolId) logUsage(ctx.toolId); } catch (e) { box.innerHTML = `<div class="text-red-500">${T('અમાન્ય ડેટા', 'Invalid data')}</div>`; } };
  ctrl.oninput = render; render();
});
reg('password-gen', function (mount, opt, ctx) {
  const ctrl = el(`<div><label class="kt-label">${T('લંબાઈ', 'Length')}: <b class="lv">16</b></label><input type="range" min="6" max="40" value="16" class="w-full ls">
    <div class="flex gap-3 flex-wrap mt-2 text-sm">${[['up', 'A-Z', 1], ['lo', 'a-z', 1], ['nu', '0-9', 1], ['sy', '!@#', 1]].map(o => `<label class="flex items-center gap-2"><input type="checkbox" class="${o[0]}" ${o[2] ? 'checked' : ''}> ${o[1]}</label>`).join('')}</div></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  const gen = () => {
    let set = ''; if (ctrl.querySelector('.up').checked) set += 'ABCDEFGHJKLMNPQRSTUVWXYZ'; if (ctrl.querySelector('.lo').checked) set += 'abcdefghijkmnpqrstuvwxyz'; if (ctrl.querySelector('.nu').checked) set += '23456789'; if (ctrl.querySelector('.sy').checked) set += '!@#$%^&*-_=+';
    if (!set) return; const len = +ctrl.querySelector('.ls').value; const arr = crypto.getRandomValues(new Uint32Array(len)); let pw = ''; for (let i = 0; i < len; i++) pw += set[arr[i] % set.length];
    const strength = Math.min(100, len * 4 + (new Set(pw).size)); const col = strength > 70 ? 'var(--morpankh-green)' : strength > 40 ? 'var(--gold)' : 'var(--saffron)';
    box.innerHTML = `<div class="kt-card p-4"><div class="font-mono text-lg break-all">${pw}</div><div class="h-2 rounded mt-3" style="background:linear-gradient(90deg,${col} ${strength}%,rgba(0,0,0,.1) ${strength}%)"></div><div class="text-xs opacity-60 mt-1">${T('મજબૂતાઈ', 'Strength')}: ${strength}%</div></div>`;
    ctx._resultText = pw; setActions([{ label: T('નવો', 'New'), cls: 'btn-primary', icon: 'refresh-cw', onClick: gen }, { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => copy(pw) }]); if (window.lucide) lucide.createIcons();
  };
  ctrl.querySelector('.ls').oninput = e => { ctrl.querySelector('.lv').textContent = e.target.value; gen(); }; ctrl.oninput = gen; gen();
});
reg('hash-gen', function (mount, opt, ctx) {
  const ta = textArea(mount, 'Hare Krishna'); const box = resultBox(mount);
  const upd = async () => {
    const enc = new TextEncoder().encode(ta.value); const algos = { 'SHA-1': 'SHA-1', 'SHA-256': 'SHA-256', 'SHA-512': 'SHA-512' }; let rows = '';
    rows += `<div class="kt-card p-3 mb-2"><div class="text-xs opacity-60">MD5</div><div class="font-mono text-sm break-all">${md5(ta.value)}</div></div>`;
    for (const [name, a] of Object.entries(algos)) { const h = await crypto.subtle.digest(a, enc); const hex = [...new Uint8Array(h)].map(b => b.toString(16).padStart(2, '0')).join(''); rows += `<div class="kt-card p-3 mb-2"><div class="text-xs opacity-60">${name}</div><div class="font-mono text-sm break-all">${hex}</div></div>`; }
    box.innerHTML = rows; if (ctx.toolId) logUsage(ctx.toolId);
  };
  ta.oninput = upd; upd();
});
reg('base64-tool', function (mount, opt, ctx) {
  const ta = textArea(mount, 'Hare Krishna'); const box = resultBox(mount);
  const enc = () => { try { const r = btoa(unescape(encodeURIComponent(ta.value))); showText(box, r, 'encoded.txt', 'text/plain', ctx); } catch (e) { toast(e.message, true); } };
  const dec = () => { try { const r = decodeURIComponent(escape(atob(ta.value.trim()))); showText(box, r, 'decoded.txt', 'text/plain', ctx); } catch (e) { toast(T('અમાન્ય Base64', 'Invalid Base64'), true); } };
  setActions([{ label: 'Encode', cls: 'btn-primary', icon: 'lock', onClick: enc }, { label: 'Decode', icon: 'unlock', onClick: dec }]); enc();
});
reg('url-tool', function (mount, opt, ctx) {
  const ta = textArea(mount, 'https://tools.akdwk.in/?q=હરે કૃષ્ણ'); const box = resultBox(mount);
  setActions([{ label: 'Encode', cls: 'btn-primary', icon: 'lock', onClick: () => showText(box, encodeURIComponent(ta.value), 'encoded.txt', 'text/plain', ctx) }, { label: 'Decode', icon: 'unlock', onClick: () => { try { showText(box, decodeURIComponent(ta.value), 'decoded.txt', 'text/plain', ctx); } catch (e) { toast('Invalid', true); } } }]);
});
reg('lorem-gen', function (mount, opt, ctx) {
  const ctrl = el(`<div class="grid sm:grid-cols-3 gap-3"><div><label class="kt-label">${T('ભાષા', 'Language')}</label><select class="kt-select lg"><option value="en">Latin</option><option value="gu">${T('ગુજરાતી', 'Gujarati')}</option></select></div><div><label class="kt-label">${T('ફકરા', 'Paragraphs')}</label><input type="number" class="kt-input np" value="3"></div></div>`); mount.appendChild(ctrl);
  const box = resultBox(mount);
  const EN = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
  const GUJ = 'કૃષ્ણ ભગવાનની કૃપાથી આ ટૂલ્સ બન્યાં છે. હરે કૃષ્ણ હરે રામ. કમ્પ્યુટર અને CCTV માટે શ્રેષ્ઠ સેવા દ્વારકામાં ઉપલબ્ધ છે.';
  const gen = () => { const n = +ctrl.querySelector('.np').value; const src = ctrl.querySelector('.lg').value === 'gu' ? GUJ : EN; const r = Array(n).fill(src).join('\n\n'); showText(box, r, 'lorem.txt', 'text/plain', ctx); };
  ctrl.oninput = gen; gen();
});
reg('sci-calc', function (mount, opt, ctx) {
  mount.innerHTML = `<input class="kt-input font-mono text-right text-lg exp" placeholder="0"><div class="grid grid-cols-5 gap-2 mt-3">${['sin', 'cos', 'tan', 'log', 'ln', '(', ')', '^', '√', 'π', '7', '8', '9', '/', 'C', '4', '5', '6', '*', '⌫', '1', '2', '3', '-', '=', '0', '.', 'e', '+', ''].map(k => k === '' ? '' : `<button class="btn btn-ghost k" data-k="${k}">${k}</button>`).join('')}</div>`;
  const exp = mount.querySelector('.exp');
  mount.querySelectorAll('.k').forEach(b => b.onclick = () => {
    const k = b.dataset.k;
    if (k === 'C') exp.value = ''; else if (k === '⌫') exp.value = exp.value.slice(0, -1);
    else if (k === '=') { try { exp.value = String(evalExpr(exp.value)); } catch (e) { toast('Error', true); } }
    else if (['sin', 'cos', 'tan', 'log', 'ln', '√'].includes(k)) exp.value += k + '(';
    else if (k === 'π') exp.value += 'π'; else if (k === 'e') exp.value += 'e'; else exp.value += k;
  });
  function evalExpr(s) {
    s = s.replace(/π/g, Math.PI).replace(/(?<![a-z])e/g, Math.E).replace(/√/g, 'Math.sqrt').replace(/\^/g, '**').replace(/sin/g, 'Math.sin').replace(/cos/g, 'Math.cos').replace(/tan/g, 'Math.tan').replace(/log/g, 'Math.log10').replace(/ln/g, 'Math.log');
    if (/[^0-9+\-*/(). Mathsqrtinclog\*e]/.test(s.replace(/Math\.\w+/g, ''))) {}
    return Function('"use strict";return (' + s + ')')();
  }
});
reg('timer-tool', function (mount, opt, ctx) {
  mount.innerHTML = `<div class="flex gap-2 mb-4"><button class="cat-chip active md" data-m="cd">${T('કાઉન્ટડાઉન', 'Countdown')}</button><button class="cat-chip md" data-m="sw">${T('સ્ટોપવોચ', 'Stopwatch')}</button></div>
    <div class="cdw"><input type="number" class="kt-input mins" value="5" style="max-width:120px"> ${T('મિનિટ', 'minutes')}</div>
    <div class="text-5xl font-mono text-center my-6 disp" style="color:var(--peacock-teal)">00:00</div>
    <div class="flex gap-2 justify-center"><button class="btn btn-primary st">${T('શરૂ', 'Start')}</button><button class="btn btn-ghost rs">${T('રીસેટ', 'Reset')}</button></div>`;
  let mode = 'cd', running = false, remain = 0, elapsed = 0, iv = null;
  const disp = mount.querySelector('.disp');
  const fmt = s => `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(Math.floor(s % 60)).padStart(2, '0')}`;
  mount.querySelectorAll('.md').forEach(b => b.onclick = () => { mount.querySelectorAll('.md').forEach(x => x.classList.remove('active')); b.classList.add('active'); mode = b.dataset.m; mount.querySelector('.cdw').style.display = mode === 'cd' ? '' : 'none'; reset(); });
  function reset() { clearInterval(iv); running = false; remain = 0; elapsed = 0; disp.textContent = '00:00'; mount.querySelector('.st').textContent = T('શરૂ', 'Start'); }
  mount.querySelector('.rs').onclick = reset;
  mount.querySelector('.st').onclick = function () {
    if (running) { clearInterval(iv); running = false; this.textContent = T('શરૂ', 'Start'); return; }
    running = true; this.textContent = T('થોભો', 'Pause');
    if (mode === 'cd' && remain === 0) remain = (+mount.querySelector('.mins').value) * 60;
    iv = setInterval(() => { if (mode === 'cd') { remain--; disp.textContent = fmt(remain); if (remain <= 0) { clearInterval(iv); toast('⏰ ' + T('સમય પૂરો!', 'Time up!')); running = false; } } else { elapsed++; disp.textContent = fmt(elapsed); } }, 1000);
  };
});
reg('currency-convert', async function (mount, opt, ctx) {
  const ctrl = el(`<div class="grid sm:grid-cols-3 gap-3"><div><label class="kt-label">${T('રકમ', 'Amount')}</label><input type="number" class="kt-input am" value="100"></div><div><label class="kt-label">${T('થી', 'From')}</label><select class="kt-select fr"></select></div><div><label class="kt-label">${T('માં', 'To')}</label><select class="kt-select to"></select></div></div>`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  const curr = ['INR', 'USD', 'EUR', 'GBP', 'AED', 'AUD', 'JPY', 'SGD', 'CAD'];
  ctrl.querySelector('.fr').innerHTML = curr.map(c => `<option ${c === 'USD' ? 'selected' : ''}>${c}</option>`).join('');
  ctrl.querySelector('.to').innerHTML = curr.map(c => `<option ${c === 'INR' ? 'selected' : ''}>${c}</option>`).join('');
  // Manual fallback rates (relative to USD) if live API fails.
  const fallback = { USD: 1, INR: 83, EUR: 0.92, GBP: 0.79, AED: 3.67, AUD: 1.52, JPY: 149, SGD: 1.35, CAD: 1.36 };
  let rates = fallback;
  try { const r = await fetch('https://open.er-api.com/v6/latest/USD'); const j = await r.json(); if (j && j.rates) rates = j.rates; } catch (e) {}
  const conv = () => { const a = +ctrl.querySelector('.am').value, f = ctrl.querySelector('.fr').value, t = ctrl.querySelector('.to').value; const usd = a / (rates[f] || fallback[f]); const res = usd * (rates[t] || fallback[t]); box.innerHTML = `<div class="kt-card p-5 text-center"><div class="text-2xl font-extrabold" style="color:var(--peacock-teal)">${res.toFixed(2)} ${t}</div><div class="text-xs opacity-60 mt-1">1 ${f} = ${((rates[t] || fallback[t]) / (rates[f] || fallback[f])).toFixed(4)} ${t}</div></div>`; ctx._resultText = `${a} ${f} = ${res.toFixed(2)} ${t}`; };
  ctrl.oninput = conv; conv();
  setActions([{ label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => waShareText(ctx._resultText) }]);
});
reg('cctv-reference', function (mount, opt, ctx) {
  const data = [
    ['Hikvision', 'admin / (set on first use)', '80, 8000, 554, 443'],
    ['Dahua', 'admin / admin', '80, 37777, 554, 443'],
    ['CP Plus', 'admin / admin', '80, 8000, 554'],
    ['Prama / Hikvision OEM', 'admin / (set)', '80, 8000, 554'],
    ['Godrej', 'admin / 1234', '80, 8000, 554'],
    ['Generic XMEye/Xmeye', 'admin / (blank)', '80, 34567, 554'],
  ];
  mount.innerHTML = `<input class="kt-input mb-3 srch" placeholder="${T('બ્રાન્ડ શોધો...', 'Search brand...')}">
    <table class="w-full text-sm tbl"><tr class="opacity-60 text-left"><th class="py-2">Brand</th><th>${T('ડિફોલ્ટ લોગિન', 'Default login')}</th><th>Ports</th></tr>
    ${data.map(r => `<tr class="row border-t" style="border-color:var(--border)"><td class="py-2 font-semibold">${r[0]}</td><td>${r[1]}</td><td class="font-mono text-xs">${r[2]}</td></tr>`).join('')}</table>
    <p class="text-xs opacity-60 mt-3">⚠️ ${T('સુરક્ષા માટે ડિફોલ્ટ પાસવર્ડ તરત બદલો.', 'Change default passwords immediately for security.')}</p>`;
  mount.querySelector('.srch').oninput = e => { const t = e.target.value.toLowerCase(); mount.querySelectorAll('.row').forEach(r => r.style.display = r.textContent.toLowerCase().includes(t) ? '' : 'none'); };
  if (ctx.toolId) logUsage(ctx.toolId);
});
reg('cctv-fov', fovEngine);
reg('fov-visualizer', fovEngine);
function fovEngine(mount, opt, ctx) {
  const ctrl = el(`<div class="grid grid-cols-2 gap-3"><div><label class="kt-label">${T('કોણ', 'Angle')}°</label><input type="number" class="kt-input ang" value="90"></div><div><label class="kt-label">${T('રેન્જ', 'Range')} m</label><input type="number" class="kt-input rng" value="15"></div></div><canvas class="cv mt-3 w-full bg-white rounded-xl" width="400" height="300" style="border:1px solid var(--border)"></canvas>`);
  mount.appendChild(ctrl);
  const draw = () => {
    const c = ctrl.querySelector('.cv'), cx = c.getContext('2d'); cx.clearRect(0, 0, c.width, c.height);
    const ang = (+ctrl.querySelector('.ang').value) * Math.PI / 180, rng = +ctrl.querySelector('.rng').value; const scale = 260 / rng;
    const ox = c.width / 2, oy = c.height - 20;
    cx.fillStyle = 'rgba(14,116,144,.25)'; cx.beginPath(); cx.moveTo(ox, oy); cx.arc(ox, oy, rng * scale, -Math.PI / 2 - ang / 2, -Math.PI / 2 + ang / 2); cx.closePath(); cx.fill();
    cx.fillStyle = '#1E3A8A'; cx.beginPath(); cx.arc(ox, oy, 7, 0, 7); cx.fill();
    cx.fillStyle = '#333'; cx.font = '12px Inter'; cx.textAlign = 'center'; cx.fillText(`${ctrl.querySelector('.ang').value}° / ${rng}m`, ox, oy - rng * scale - 6);
  };
  ctrl.oninput = draw; draw(); if (ctx.toolId) logUsage(ctx.toolId);
}

/* ---------- Document PDF generators (jsPDF) ---------- */
function shopHeader(pdf, title) {
  pdf.setFillColor(30, 58, 138); pdf.rect(0, 0, 210, 26, 'F');
  pdf.setTextColor(255); pdf.setFontSize(18); pdf.text('AK Computer', 14, 12);
  pdf.setFontSize(9); pdf.text('Dwarka, Gujarat · Computer & CCTV', 14, 19);
  pdf.setTextColor(245, 158, 11); pdf.setFontSize(14); pdf.text(title, 196, 14, { align: 'right' });
  pdf.setTextColor(0);
}
function docBuilder(mount, ctx, cfg) {
  const ctrl = el(`<div class="grid sm:grid-cols-2 gap-3">${cfg.fields.map(f => `<div class="${f.full ? 'sm:col-span-2' : ''}"><label class="kt-label">${f.label}</label>${f.type === 'textarea' ? `<textarea class="kt-input" data-k="${f.k}" rows="4">${f.val || ''}</textarea>` : `<input class="kt-input" data-k="${f.k}" value="${f.val || ''}" ${f.type === 'date' ? 'type=date' : ''}>`}</div>`).join('')}</div>
    ${cfg.items ? `<div class="mt-4"><label class="kt-label">${T('આઇટમ (નામ,જથ્થો,ભાવ)', 'Items (name,qty,price)')}</label><textarea class="kt-input items" rows="5">${cfg.itemsDefault || ''}</textarea></div>` : ''}`);
  mount.appendChild(ctrl); const box = resultBox(mount);
  const build = async () => {
    await need('jspdf'); const { jsPDF } = window.jspdf; const pdf = new jsPDF();
    const v = {}; ctrl.querySelectorAll('[data-k]').forEach(n => v[n.dataset.k] = n.value);
    shopHeader(pdf, cfg.title); let y = 38; pdf.setFontSize(10);
    cfg.fields.forEach(f => { if (v[f.k]) { pdf.setFont(undefined, 'bold'); pdf.text(f.label + ':', 14, y); pdf.setFont(undefined, 'normal'); pdf.text(String(v[f.k]).slice(0, 70), 55, y); y += 7; } });
    let grand = 0;
    if (cfg.items) {
      y += 4; pdf.setFillColor(240); pdf.rect(14, y - 5, 182, 8, 'F'); pdf.setFont(undefined, 'bold');
      pdf.text('Item', 16, y); pdf.text('Qty', 120, y); pdf.text('Rate', 145, y); pdf.text('Amount', 178, y); pdf.setFont(undefined, 'normal'); y += 8;
      (ctrl.querySelector('.items').value || '').split('\n').forEach(l => { const [n, q, p] = l.split(','); if (!n) return; const amt = (parseFloat(q) || 0) * (parseFloat(p) || 0); grand += amt; pdf.text(String(n).slice(0, 45), 16, y); pdf.text(String(q || ''), 122, y); pdf.text(String(p || ''), 147, y); pdf.text(amt.toFixed(0), 180, y); y += 7; });
      const gst = grand * 0.18; y += 3; pdf.line(14, y, 196, y); y += 7;
      pdf.text('Subtotal: ₹' + grand.toFixed(2), 140, y); y += 6; pdf.text('GST 18%: ₹' + gst.toFixed(2), 140, y); y += 6;
      pdf.setFont(undefined, 'bold'); pdf.text('Total: ₹' + (grand + gst).toFixed(2), 140, y); pdf.setFont(undefined, 'normal');
      grand = grand + gst;
    }
    pdf.setFontSize(8); pdf.setTextColor(120); pdf.text('Generated by Krishna Tools · tools.akdwk.in · हरे कृष्ण', 14, 285);
    const blob = pdf.output('blob'); showPreview(box, blob, cfg.file + '.pdf', ctx, cfg.title);
  };
  setActions([{ label: T('PDF બનાવો', 'Generate PDF'), cls: 'btn-primary', icon: 'file-text', onClick: build }]);
}
reg('cctv-quote', (m, o, c) => docBuilder(m, c, { title: 'CCTV QUOTATION', file: 'cctv-quote', items: true, itemsDefault: '2MP Dome Camera,4,1200\nDVR 4CH,1,3500\nHDD 1TB,1,3200\nCable+Installation,1,2500', fields: [{ k: 'client', label: T('ક્લાયન્ટ નામ', 'Client name'), full: true }, { k: 'phone', label: T('ફોન', 'Phone') }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'site', label: T('સાઇટ સરનામું', 'Site address'), full: true, type: 'textarea' }] }));
reg('cctv-amc', (m, o, c) => docBuilder(m, c, { title: 'CCTV AMC CONTRACT', file: 'cctv-amc', fields: [{ k: 'client', label: T('ક્લાયન્ટ', 'Client'), full: true }, { k: 'phone', label: T('ફોન', 'Phone') }, { k: 'amount', label: T('વાર્ષિક રકમ ₹', 'Yearly amount ₹') }, { k: 'cameras', label: T('કેમેરા સંખ્યા', 'No. of cameras') }, { k: 'start', label: T('શરૂ તારીખ', 'Start date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'terms', label: T('શરતો', 'Terms'), full: true, type: 'textarea', val: '2 free visits/year, spare parts extra, response within 48 hrs.' }] }));
reg('invoice-gen', (m, o, c) => docBuilder(m, c, { title: 'TAX INVOICE', file: 'invoice', items: true, itemsDefault: 'Product A,2,500\nProduct B,1,1200', fields: [{ k: 'inv', label: T('ઇન્વોઇસ નં', 'Invoice no'), val: 'INV-' + Math.floor(1000 + (new Date().getMonth() + 1) * 100) }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'client', label: T('ગ્રાહક નામ', 'Customer name'), full: true }, { k: 'gstin', label: 'GSTIN' }, { k: 'phone', label: T('ફોન', 'Phone') }] }));
reg('quote-gen', (m, o, c) => docBuilder(m, c, { title: 'QUOTATION', file: 'quotation', items: true, itemsDefault: 'Item 1,1,1000', fields: [{ k: 'client', label: T('ક્લાયન્ટ', 'Client'), full: true }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'valid', label: T('માન્યતા', 'Valid until') }] }));
reg('po-gen', (m, o, c) => docBuilder(m, c, { title: 'PURCHASE ORDER', file: 'purchase-order', items: true, itemsDefault: 'Item 1,10,500', fields: [{ k: 'supplier', label: T('સપ્લાયર', 'Supplier'), full: true }, { k: 'po', label: 'PO No', val: 'PO-' + ((new Date().getMonth() + 1) * 111) }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }] }));
reg('job-sheet', function (mount, opt, ctx) {
  const jobNo = 'JOB-' + String((new Date().getMonth() + 1) * 137 + new Date().getDate());
  docBuilder(mount, ctx, { title: 'SERVICE JOB SHEET', file: 'job-sheet', fields: [{ k: 'job', label: T('જોબ નં', 'Job no'), val: jobNo }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'customer', label: T('ગ્રાહક', 'Customer'), full: true }, { k: 'phone', label: T('ફોન', 'Phone') }, { k: 'device', label: T('ડિવાઇસ', 'Device') }, { k: 'problem', label: T('સમસ્યા', 'Problem'), full: true, type: 'textarea' }, { k: 'estimate', label: T('અંદાજ ₹', 'Estimate ₹') }, { k: 'delivery', label: T('ડિલિવરી તારીખ', 'Delivery date'), type: 'date' }] });
});
reg('site-survey', (m, o, c) => docBuilder(m, c, { title: 'CCTV SITE SURVEY', file: 'site-survey', fields: [{ k: 'client', label: T('ક્લાયન્ટ', 'Client'), full: true }, { k: 'date', label: T('તારીખ', 'Date'), type: 'date', val: new Date().toISOString().slice(0, 10) }, { k: 'site', label: T('સાઇટ', 'Site'), full: true, type: 'textarea' }, { k: 'notes', label: T('નોંધ: કેમેરા સ્થાન, પાવર, કેબલ રૂટ, DVR સ્થાન', 'Notes: camera positions, power, cable route, DVR location'), full: true, type: 'textarea' }] }));

/* ---------- Tiny MD5 (for hash-gen; public-domain style impl) ---------- */
function md5(s) {
  function rl(n, c) { return (n << c) | (n >>> (32 - c)); }
  function au(x, y) { const l = (x & 0xFFFF) + (y & 0xFFFF), m = (x >> 16) + (y >> 16) + (l >> 16); return (m << 16) | (l & 0xFFFF); }
  function cmn(q, a, b, x, s, t) { return au(rl(au(au(a, q), au(x, t)), s), b); }
  function ff(a, b, c, d, x, s, t) { return cmn((b & c) | (~b & d), a, b, x, s, t); }
  function gg(a, b, c, d, x, s, t) { return cmn((b & d) | (c & ~d), a, b, x, s, t); }
  function hh(a, b, c, d, x, s, t) { return cmn(b ^ c ^ d, a, b, x, s, t); }
  function ii(a, b, c, d, x, s, t) { return cmn(c ^ (b | ~d), a, b, x, s, t); }
  function tb(str) { const u = unescape(encodeURIComponent(str)); const b = []; for (let i = 0; i < u.length; i++) b[i >> 2] |= u.charCodeAt(i) << ((i % 4) * 8); return b; }
  function rh(n) { let s = ''; for (let j = 0; j < 4; j++) s += ((n >> (j * 8 + 4)) & 0x0F).toString(16) + ((n >> (j * 8)) & 0x0F).toString(16); return s; }
  const x = tb(s); const len = unescape(encodeURIComponent(s)).length * 8;
  x[len >> 5] |= 0x80 << (len % 32); x[(((len + 64) >>> 9) << 4) + 14] = len;
  let a = 1732584193, b = -271733879, c = -1732584194, d = 271733878;
  for (let i = 0; i < x.length; i += 16) {
    const oa = a, ob = b, oc = c, od = d;
    a = ff(a, b, c, d, x[i] | 0, 7, -680876936); d = ff(d, a, b, c, x[i + 1] | 0, 12, -389564586); c = ff(c, d, a, b, x[i + 2] | 0, 17, 606105819); b = ff(b, c, d, a, x[i + 3] | 0, 22, -1044525330);
    a = ff(a, b, c, d, x[i + 4] | 0, 7, -176418897); d = ff(d, a, b, c, x[i + 5] | 0, 12, 1200080426); c = ff(c, d, a, b, x[i + 6] | 0, 17, -1473231341); b = ff(b, c, d, a, x[i + 7] | 0, 22, -45705983);
    a = ff(a, b, c, d, x[i + 8] | 0, 7, 1770035416); d = ff(d, a, b, c, x[i + 9] | 0, 12, -1958414417); c = ff(c, d, a, b, x[i + 10] | 0, 17, -42063); b = ff(b, c, d, a, x[i + 11] | 0, 22, -1990404162);
    a = ff(a, b, c, d, x[i + 12] | 0, 7, 1804603682); d = ff(d, a, b, c, x[i + 13] | 0, 12, -40341101); c = ff(c, d, a, b, x[i + 14] | 0, 17, -1502002290); b = ff(b, c, d, a, x[i + 15] | 0, 22, 1236535329);
    a = gg(a, b, c, d, x[i + 1] | 0, 5, -165796510); d = gg(d, a, b, c, x[i + 6] | 0, 9, -1069501632); c = gg(c, d, a, b, x[i + 11] | 0, 14, 643717713); b = gg(b, c, d, a, x[i] | 0, 20, -373897302);
    a = gg(a, b, c, d, x[i + 5] | 0, 5, -701558691); d = gg(d, a, b, c, x[i + 10] | 0, 9, 38016083); c = gg(c, d, a, b, x[i + 15] | 0, 14, -660478335); b = gg(b, c, d, a, x[i + 4] | 0, 20, -405537848);
    a = gg(a, b, c, d, x[i + 9] | 0, 5, 568446438); d = gg(d, a, b, c, x[i + 14] | 0, 9, -1019803690); c = gg(c, d, a, b, x[i + 3] | 0, 14, -187363961); b = gg(b, c, d, a, x[i + 8] | 0, 20, 1163531501);
    a = gg(a, b, c, d, x[i + 13] | 0, 5, -1444681467); d = gg(d, a, b, c, x[i + 2] | 0, 9, -51403784); c = gg(c, d, a, b, x[i + 7] | 0, 14, 1735328473); b = gg(b, c, d, a, x[i + 12] | 0, 20, -1926607734);
    a = hh(a, b, c, d, x[i + 5] | 0, 4, -378558); d = hh(d, a, b, c, x[i + 8] | 0, 11, -2022574463); c = hh(c, d, a, b, x[i + 11] | 0, 16, 1839030562); b = hh(b, c, d, a, x[i + 14] | 0, 23, -35309556);
    a = hh(a, b, c, d, x[i + 1] | 0, 4, -1530992060); d = hh(d, a, b, c, x[i + 4] | 0, 11, 1272893353); c = hh(c, d, a, b, x[i + 7] | 0, 16, -155497632); b = hh(b, c, d, a, x[i + 10] | 0, 23, -1094730640);
    a = hh(a, b, c, d, x[i + 13] | 0, 4, 681279174); d = hh(d, a, b, c, x[i] | 0, 11, -358537222); c = hh(c, d, a, b, x[i + 3] | 0, 16, -722521979); b = hh(b, c, d, a, x[i + 6] | 0, 23, 76029189);
    a = hh(a, b, c, d, x[i + 9] | 0, 4, -640364487); d = hh(d, a, b, c, x[i + 12] | 0, 11, -421815835); c = hh(c, d, a, b, x[i + 15] | 0, 16, 530742520); b = hh(b, c, d, a, x[i + 2] | 0, 23, -995338651);
    a = ii(a, b, c, d, x[i] | 0, 6, -198630844); d = ii(d, a, b, c, x[i + 7] | 0, 10, 1126891415); c = ii(c, d, a, b, x[i + 14] | 0, 15, -1416354905); b = ii(b, c, d, a, x[i + 5] | 0, 21, -57434055);
    a = ii(a, b, c, d, x[i + 12] | 0, 6, 1700485571); d = ii(d, a, b, c, x[i + 3] | 0, 10, -1894986606); c = ii(c, d, a, b, x[i + 10] | 0, 15, -1051523); b = ii(b, c, d, a, x[i + 1] | 0, 21, -2054922799);
    a = ii(a, b, c, d, x[i + 8] | 0, 6, 1873313359); d = ii(d, a, b, c, x[i + 15] | 0, 10, -30611744); c = ii(c, d, a, b, x[i + 6] | 0, 15, -1560198380); b = ii(b, c, d, a, x[i + 13] | 0, 21, 1309151649);
    a = ii(a, b, c, d, x[i + 4] | 0, 6, -145523070); d = ii(d, a, b, c, x[i + 11] | 0, 10, -1120210379); c = ii(c, d, a, b, x[i + 2] | 0, 15, 718787259); b = ii(b, c, d, a, x[i + 9] | 0, 21, -343485551);
    a = au(a, oa); b = au(b, ob); c = au(c, oc); d = au(d, od);
  }
  return rh(a) + rh(b) + rh(c) + rh(d);
}
function parseRange(str, max) {
  const idx = new Set();
  (str || '').split(',').forEach(part => { part = part.trim(); if (!part) return; if (part.includes('-')) { const [a, b] = part.split('-').map(n => parseInt(n)); for (let i = a; i <= b; i++) if (i >= 1 && i <= max) idx.add(i - 1); } else { const n = parseInt(part); if (n >= 1 && n <= max) idx.add(n - 1); } });
  return idx.size ? [...idx].sort((a, b) => a - b) : Array.from({ length: max }, (_, i) => i);
}

})();
