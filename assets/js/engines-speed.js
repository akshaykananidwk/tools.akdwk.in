/* ================================================================
   KRISHNA TOOLS — Internet Speed Test engine.
   Measures ping, jitter, download and upload in the browser.
   Primary servers: Cloudflare's public speed endpoints (nearest edge,
   no load on our hosting). Fallback: /api/speed.php on our own server.
   ================================================================ */
(function () {
'use strict';
const X = window.KTX;
if (!X) return;
const { $, T, KT, toast, copy, waShareText, setActions, el, reg, logUsage } = X;

const CF = 'https://speed.cloudflare.com';
const OWN = KT.url + '/api/speed.php';
const LIMITS = {
  cf:  { downSec: 8, upSec: 7, downMax: 100e6, upMax: 25e6, streams: 4 },
  own: { downSec: 6, upSec: 6, downMax: 30e6,  upMax: 15e6, streams: 2 },
};
// Well-known Indian networks by ASN (shown next to the AS number).
const ASN = {
  55836: 'Reliance Jio', 64049: 'Reliance Jio', 45609: 'Airtel (mobile)', 24560: 'Airtel', 9498: 'Airtel',
  38266: 'Vodafone Idea (Vi)', 55410: 'Vodafone Idea (Vi)', 45271: 'Vodafone Idea (Vi)', 9829: 'BSNL',
  17488: 'Hathway', 24309: 'ACT Fibernet', 18209: 'ACT Fibernet', 133982: 'Excitel', 45820: 'Tata Play Fiber',
  4755: 'Tata Communications', 13335: 'Cloudflare',
};

const now = () => performance.now();
const fmtMbps = v => (v >= 100 ? v.toFixed(0) : v >= 10 ? v.toFixed(1) : v.toFixed(2));

/* ---------- gauge ---------- */
const TICKS = [0, 5, 10, 25, 50, 100, 250, 500, 1000];
function valToFrac(v) {
  if (v <= 0) return 0;
  for (let i = 1; i < TICKS.length; i++) {
    if (v <= TICKS[i]) return (i - 1 + (v - TICKS[i - 1]) / (TICKS[i] - TICKS[i - 1])) / (TICKS.length - 1);
  }
  return 1;
}
function arcPath(f) { // semicircle from left (f=0) to right (f=1), radius 100 centred at 120,120
  const a = Math.PI * (1 - f), x = 120 + 100 * Math.cos(a), y = 120 - 100 * Math.sin(a);
  return `M20 120 A100 100 0 ${f > 0.5 ? 1 : 0} 1 ${x.toFixed(2)} ${y.toFixed(2)}`;
}
function gaugeSvg() {
  const ticks = TICKS.map((t, i) => {
    const f = i / (TICKS.length - 1), a = Math.PI * (1 - f);
    const x = 120 + 80 * Math.cos(a), y = 120 - 80 * Math.sin(a) + 4;
    return `<text x="${x.toFixed(1)}" y="${y.toFixed(1)}" text-anchor="middle" font-size="10" fill="currentColor" opacity=".6">${t}</text>`;
  }).join('');
  return `<svg viewBox="0 0 240 140" style="width:100%;max-width:360px" aria-hidden="true">
    <path d="M20 120 A100 100 0 0 1 220 120" fill="none" stroke="currentColor" stroke-opacity=".12" stroke-width="14" stroke-linecap="round"/>
    <path class="sg-arc" d="M20 120 A100 100 0 0 1 20 120" fill="none" stroke="url(#sgGrad)" stroke-width="14" stroke-linecap="round"/>
    <defs><linearGradient id="sgGrad" x1="0" x2="1"><stop offset="0" stop-color="#0E7490"/><stop offset="1" stop-color="#EA580C"/></linearGradient></defs>
    ${ticks}
    <line class="sg-needle" x1="120" y1="120" x2="40" y2="120" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    <circle cx="120" cy="120" r="6" fill="currentColor"/>
  </svg>`;
}

reg('speed-test', function (mount, opt, ctx) {
  mount.innerHTML = '';
  const ui = el(`<div class="text-center">
    <div class="sg-wrap mx-auto" style="max-width:360px;color:var(--ink)">${gaugeSvg()}</div>
    <div class="-mt-4"><span class="sg-val text-5xl font-extrabold" style="color:var(--peacock-teal);font-variant-numeric:tabular-nums">0</span>
      <span class="sg-unit text-lg opacity-70">Mbps</span></div>
    <div class="sg-phase text-sm opacity-70 mt-1">${T('શરૂ કરવા માટે નીચેનું બટન દબાવો', 'Press the button below to start')}</div>
    <button type="button" class="sg-go btn btn-cta text-lg !px-10 !py-3 mt-4 rounded-full">${T('ટેસ્ટ શરૂ કરો', 'Start test')}</button>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 text-left">
      ${[['ping', T('પિંગ', 'Ping'), 'ms'], ['jitter', T('જિટર', 'Jitter'), 'ms'], ['down', T('ડાઉનલોડ', 'Download'), 'Mbps'], ['up', T('અપલોડ', 'Upload'), 'Mbps']]
        .map(([k, l, u]) => `<div class="kt-card p-4"><div class="text-xs opacity-60">${l}</div>
          <div class="text-2xl font-extrabold" style="font-variant-numeric:tabular-nums"><span class="sg-${k}">—</span> <span class="text-sm font-semibold opacity-60">${u}</span></div></div>`).join('')}
    </div>
    <div class="kt-card p-4 mt-3 text-left grid sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
      <div><span class="opacity-60">${T('તમારો IP', 'Your IP')}:</span> <b class="sg-ip font-mono break-all">—</b></div>
      <div><span class="opacity-60">${T('નેટવર્ક (ISP)', 'Network (ISP)')}:</span> <b class="sg-isp">—</b></div>
      <div><span class="opacity-60">${T('સ્થાન', 'Location')}:</span> <b class="sg-loc">—</b></div>
      <div><span class="opacity-60">${T('ટેસ્ટ સર્વર', 'Test server')}:</span> <b class="sg-srv">—</b></div>
      <div class="sm:col-span-2 text-xs opacity-60 sg-data"></div>
    </div>
  </div>`);
  mount.appendChild(ui);
  const q = s => ui.querySelector(s);
  const arc = q('.sg-arc'), needle = q('.sg-needle'), valEl = q('.sg-val'), unitEl = q('.sg-unit'), phase = q('.sg-phase'), go = q('.sg-go');

  function show(v, unit) {
    const f = unit === 'Mbps' ? valToFrac(v) : Math.min(1, v / 200);
    arc.setAttribute('d', f > 0.001 ? arcPath(f) : 'M20 120 A100 100 0 0 1 20 120');
    const a = Math.PI * (1 - f);
    needle.setAttribute('x2', (120 + 80 * Math.cos(a)).toFixed(2));
    needle.setAttribute('y2', (120 - 80 * Math.sin(a)).toFixed(2));
    valEl.textContent = unit === 'Mbps' ? fmtMbps(v) : Math.round(v);
    unitEl.textContent = unit;
  }

  let running = false, result = null;

  /* ----- server selection + connection info ----- */
  async function probe() {
    try {
      const r = await fetch(CF + '/__down?bytes=0', { cache: 'no-store' });
      if (!r.ok) throw new Error('cf');
      const h = k => r.headers.get(k) || '';
      const info = { mode: 'cf', ip: h('cf-meta-ip'), asn: h('cf-meta-asn'), city: h('cf-meta-city'), country: h('cf-meta-country'), colo: h('cf-meta-colo') };
      if (!info.colo || !info.country) { // some networks get no meta headers: read Cloudflare's trace instead
        try {
          const tr = Object.fromEntries((await (await fetch(CF + '/cdn-cgi/trace', { cache: 'no-store' })).text())
            .trim().split('\n').map(l => l.split('=')));
          info.ip = info.ip || tr.ip || ''; info.colo = info.colo || tr.colo || ''; info.country = info.country || tr.loc || '';
        } catch (e) { /* optional */ }
      }
      return info;
    } catch (e) {
      let ip = '';
      try { ip = (await (await fetch(KT.url + '/api/net.php?action=ip', { cache: 'no-store' })).json()).ip || ''; } catch (e2) {}
      return { mode: 'own', ip };
    }
  }
  const pingUrl = mode => mode === 'cf' ? CF + '/__down?bytes=0&r=' : OWN + '?a=ping&r=';
  const downUrl = (mode, n) => mode === 'cf' ? `${CF}/__down?bytes=${n}&r=${Math.random()}` : `${OWN}?a=down&bytes=${Math.min(n, 10e6)}&r=${Math.random()}`;
  const upUrl = mode => mode === 'cf' ? CF + '/__up' : OWN + '?a=up';

  /* ----- ping + jitter ----- */
  async function measurePing(mode) {
    const samples = [];
    for (let i = 0; i < 12; i++) {
      const t0 = now();
      try { await fetch(pingUrl(mode) + Math.random(), { cache: 'no-store' }); } catch (e) { continue; }
      const ms = now() - t0;
      if (i > 1) { samples.push(ms); show(ms, 'ms'); } // first two warm up the connection
    }
    if (!samples.length) throw new Error('ping');
    const sorted = samples.slice().sort((a, b) => a - b);
    const ping = sorted[Math.floor(sorted.length / 2)];
    let j = 0; for (let i = 1; i < samples.length; i++) j += Math.abs(samples[i] - samples[i - 1]);
    return { ping, jitter: samples.length > 1 ? j / (samples.length - 1) : 0 };
  }

  /* ----- download: parallel streams for a fixed time ----- */
  async function measureDown(mode) {
    const L = LIMITS[mode];
    let bytes = 0, stop = false;
    const ctrls = [];
    const t0 = now();
    const tick = setInterval(() => {
      const s = (now() - t0) / 1000;
      if (s > 0.3) show(bytes * 8 / s / 1e6, 'Mbps');
      if (s > L.downSec || bytes > L.downMax) stop = true;
      if (stop) ctrls.forEach(c => c.abort());
    }, 150);
    async function stream() {
      let size = mode === 'cf' ? 1e6 : 1e6;
      while (!stop) {
        const c = new AbortController(); ctrls.push(c);
        try {
          const r = await fetch(downUrl(mode, size), { cache: 'no-store', signal: c.signal });
          const rd = r.body.getReader();
          const ts = now();
          for (;;) { const { done, value } = await rd.read(); if (done) break; bytes += value.length; if (stop) break; }
          if (now() - ts < 1500) size = Math.min(size * 4, mode === 'cf' ? 50e6 : 10e6); // grow chunks on fast links
        } catch (e) { if (!stop) await new Promise(r => setTimeout(r, 200)); }
      }
    }
    await Promise.all(Array.from({ length: L.streams }, stream));
    clearInterval(tick);
    const secs = (now() - t0) / 1000;
    return { mbps: bytes * 8 / secs / 1e6, bytes };
  }

  /* ----- upload: XHR so partial progress counts ----- */
  async function measureUp(mode) {
    const L = LIMITS[mode];
    let done = 0, stop = false;
    const live = new Set();
    const t0 = now();
    const total = () => done + [...live].reduce((s, x) => s + x.loaded, 0);
    const tick = setInterval(() => {
      const s = (now() - t0) / 1000;
      if (s > 0.3) show(total() * 8 / s / 1e6, 'Mbps');
      if (s > L.upSec || total() > L.upMax) { stop = true; live.forEach(x => x.xhr.abort()); }
    }, 150);
    function send(size) {
      return new Promise(res => {
        const xhr = new XMLHttpRequest(), job = { xhr, loaded: 0 };
        live.add(job);
        xhr.open('POST', upUrl(mode));
        xhr.upload.onprogress = e => { job.loaded = e.loaded; };
        const end = ok => { live.delete(job); if (ok) done += size; else done += job.loaded; res(ok); };
        xhr.onload = () => end(true); xhr.onerror = () => end(false); xhr.onabort = () => end(false);
        xhr.send(new Blob([new Uint8Array(size)], { type: 'text/plain' }));
      });
    }
    async function stream() {
      let size = 256e3;
      while (!stop) {
        const ts = now();
        const ok = await send(size);
        if (!ok && !stop) await new Promise(r => setTimeout(r, 200));
        if (ok && now() - ts < 1500) size = Math.min(size * 2, mode === 'cf' ? 8e6 : 4e6);
      }
    }
    await Promise.all(Array.from({ length: L.streams }, stream));
    clearInterval(tick);
    const secs = (now() - t0) / 1000;
    return { mbps: total() * 8 / secs / 1e6, bytes: total() };
  }

  function shareText() {
    if (!result) return '';
    return `${T('🚀 ઇન્ટરનેટ સ્પીડ ટેસ્ટ પરિણામ', '🚀 Internet speed test result')}\n` +
      `⬇️ ${T('ડાઉનલોડ', 'Download')}: ${fmtMbps(result.down)} Mbps\n⬆️ ${T('અપલોડ', 'Upload')}: ${fmtMbps(result.up)} Mbps\n` +
      `📶 ${T('પિંગ', 'Ping')}: ${Math.round(result.ping)} ms · ${T('જિટર', 'Jitter')}: ${Math.round(result.jitter)} ms\n` +
      (result.isp ? `🌐 ${result.isp}\n` : '') + `\n${T('તમે પણ ચેક કરો', 'Test yours')}: ${KT.url}/tool/${ctx.slug || 'internet-speed-test'}`;
  }

  async function run() {
    if (running) return;
    running = true; result = null;
    go.disabled = true; go.textContent = T('ટેસ્ટ ચાલુ છે…', 'Testing…');
    ['ping', 'jitter', 'down', 'up'].forEach(k => q('.sg-' + k).textContent = '—');
    q('.sg-data').textContent = '';
    try {
      phase.textContent = T('સર્વર સાથે જોડાણ…', 'Connecting to server…');
      const info = await probe();
      const asnName = info.asn ? (ASN[+info.asn] ? `${ASN[+info.asn]} (AS${info.asn})` : `AS${info.asn}`) : '';
      q('.sg-ip').textContent = info.ip || '—';
      q('.sg-isp').textContent = asnName || '—';
      q('.sg-loc').textContent = [info.city, info.country].filter(Boolean).join(', ') || '—';
      q('.sg-srv').textContent = info.mode === 'cf' ? `Cloudflare ${info.colo || ''}`.trim() : 'tools.akdwk.in';

      phase.textContent = T('પિંગ માપી રહ્યા છીએ…', 'Measuring ping…');
      const p = await measurePing(info.mode);
      q('.sg-ping').textContent = Math.round(p.ping); q('.sg-jitter').textContent = Math.round(p.jitter);

      phase.textContent = T('ડાઉનલોડ સ્પીડ માપી રહ્યા છીએ…', 'Measuring download speed…');
      const d = await measureDown(info.mode);
      q('.sg-down').textContent = fmtMbps(d.mbps);

      phase.textContent = T('અપલોડ સ્પીડ માપી રહ્યા છીએ…', 'Measuring upload speed…');
      show(0, 'Mbps');
      const u = await measureUp(info.mode);
      q('.sg-up').textContent = fmtMbps(u.mbps);

      result = { ping: p.ping, jitter: p.jitter, down: d.mbps, up: u.mbps, isp: asnName };
      show(d.mbps, 'Mbps');
      phase.textContent = T('ટેસ્ટ પૂરો. ઉપર ડાઉનલોડ સ્પીડ બતાવી છે.', 'Test complete. The gauge shows your download speed.');
      const mb = (d.bytes + u.bytes) / 1e6;
      q('.sg-data').textContent = T(`આ ટેસ્ટમાં આશરે ${mb.toFixed(0)} MB ડેટા વપરાયો.`, `This test used about ${mb.toFixed(0)} MB of data.`);
      if (ctx.toolId) logUsage(ctx.toolId);
    } catch (e) {
      phase.textContent = T('ટેસ્ટ પૂરો થઈ શક્યો નહીં. ઇન્ટરનેટ ચેક કરીને ફરી પ્રયત્ન કરો.', 'The test could not finish. Check your connection and try again.');
      toast(T('ટેસ્ટ નિષ્ફળ', 'Test failed'), true);
    }
    running = false; go.disabled = false; go.textContent = T('ફરી ટેસ્ટ કરો', 'Test again');
  }

  go.onclick = run;
  setActions([
    { label: T('ટેસ્ટ શરૂ કરો', 'Start test'), cls: 'btn-primary', icon: 'gauge', onClick: run },
    { label: T('કોપી', 'Copy'), icon: 'copy', onClick: () => result ? copy(shareText()) : toast(T('પહેલાં ટેસ્ટ કરો', 'Run the test first'), true) },
    { label: 'WhatsApp', cls: 'btn-wa', icon: 'send', onClick: () => result ? waShareText(shareText()) : toast(T('પહેલાં ટેસ્ટ કરો', 'Run the test first'), true) },
  ]);
  show(0, 'Mbps');
});
})();
