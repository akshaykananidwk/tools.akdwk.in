/* ================================================================
   KRISHNA TOOLS — developer / API tool engines (loaded on demand).
   Uses window.KTX exported by app.js. Mostly client-side; a few call
   server helpers under /api (proxy.php, webhook_bin.php, net.php).
   English-first UI (developer tools).
   ================================================================ */
(function () {
'use strict';
const X = window.KTX;
if (!X) return;
const { $, T, GU, KT, toast, copy, downloadBlob, downloadDataUrl, loadScript, loadCss, need, CDN, logUsage,
        waShareText, waShareFile, setActions, el, fileInput, checkMax, fmtBytes, reg } = X;

/* ---------- shared helpers ---------- */
function resultBox(mount) { const b = el('<div class="mt-5"></div>'); mount.appendChild(b); return b; }
function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
function icons() { if (window.lucide) lucide.createIcons(); }
function done(ctx) { if (ctx && ctx.toolId) logUsage(ctx.toolId); }
function textArea(mount, rows, ph, val) {
  const t = el(`<textarea class="kt-input" rows="${rows || 6}" placeholder="${esc(ph || '')}" spellcheck="false"></textarea>`);
  if (val != null) t.value = val;
  mount.appendChild(t); return t;
}
function copyBtn(text, label) {
  return { label: label || 'Copy', cls: 'btn-primary', icon: 'copy', onClick: () => copy(typeof text === 'function' ? text() : text) };
}
/* base64url helpers */
function b64urlFromStr(str) { return btoa(unescape(encodeURIComponent(str))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''); }
function b64urlToStr(b64) { b64 = String(b64).replace(/-/g, '+').replace(/_/g, '/'); while (b64.length % 4) b64 += '='; return decodeURIComponent(escape(atob(b64))); }
function b64urlFromBytes(buf) { const b = new Uint8Array(buf); let s = ''; for (let i = 0; i < b.length; i++) s += String.fromCharCode(b[i]); return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''); }
async function hmacSha256(key, msg) { const enc = new TextEncoder(); const k = await crypto.subtle.importKey('raw', enc.encode(key), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']); return crypto.subtle.sign('HMAC', k, enc.encode(msg)); }

/* ================================================================
   1. API TESTER
   ================================================================ */
reg('api-tester', function (mount, opt, ctx) {
  const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
  const wrap = el(`<div>
    <div class="flex gap-2">
      <select class="kt-select met" style="max-width:130px">${methods.map(m => `<option>${m}</option>`).join('')}</select>
      <input class="kt-input url" placeholder="https://api.example.com/endpoint" value="https://httpbin.org/get">
    </div>
    <div class="mt-3">
      <label class="kt-label">Headers (one "Key: Value" per line)</label>
      <textarea class="kt-input hdr" rows="3" spellcheck="false" placeholder="Content-Type: application/json&#10;Authorization: Bearer ..."></textarea>
    </div>
    <div class="mt-3 bodyWrap">
      <label class="kt-label">Request body</label>
      <textarea class="kt-input body" rows="5" spellcheck="false" placeholder='{"key":"value"}'></textarea>
    </div>
    <button class="btn btn-primary mt-3 send"><i data-lucide="send"></i> Send</button>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  const met = wrap.querySelector('.met'), bodyWrap = wrap.querySelector('.bodyWrap');
  const toggleBody = () => { bodyWrap.style.display = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(met.value) ? '' : 'none'; };
  met.onchange = toggleBody; toggleBody();
  icons();
  function parseHeaders(txt) {
    const h = {};
    txt.split('\n').forEach(line => { const i = line.indexOf(':'); if (i > 0) { const k = line.slice(0, i).trim(); const v = line.slice(i + 1).trim(); if (k) h[k] = v; } });
    return h;
  }
  wrap.querySelector('.send').onclick = async () => {
    const url = wrap.querySelector('.url').value.trim();
    if (!url) { toast('Enter a URL', true); return; }
    box.innerHTML = '<div class="chakra-loader mx-auto"></div>';
    const payload = { method: met.value, url: url, headers: parseHeaders(wrap.querySelector('.hdr').value), body: wrap.querySelector('.body').value };
    try {
      const r = await fetch(KT.url + '/api/proxy.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': KT.csrf }, body: JSON.stringify(payload) });
      const j = await r.json();
      if (!j.ok) { box.innerHTML = `<div class="kt-card p-4 text-red-500">Error: ${esc(j.error || j.statusText || 'Request failed')}</div>`; return; }
      const st = j.status || 0;
      const color = st < 200 ? '#6b7280' : st < 300 ? '#16a34a' : st < 400 ? '#0ea5e9' : st < 500 ? '#f59e0b' : '#dc2626';
      let pretty = j.body;
      try { pretty = JSON.stringify(JSON.parse(j.body), null, 2); } catch (e) { /* raw */ }
      const hdrRows = Object.entries(j.headers || {}).map(([k, v]) => `<tr><td class="pr-4 py-0.5 opacity-60 font-mono align-top">${esc(k)}</td><td class="py-0.5 font-mono break-all">${esc(v)}</td></tr>`).join('');
      box.innerHTML = `
        <div class="flex items-center gap-3 flex-wrap mb-3">
          <span class="badge-premium" style="background:${color};color:#fff">${st} ${esc(j.statusText || '')}</span>
          <span class="text-sm opacity-60">${j.time_ms != null ? j.time_ms + ' ms' : ''}</span>
          <span class="text-sm opacity-60">${j.size != null ? fmtBytes(j.size) : ''}</span>
        </div>
        ${hdrRows ? `<div class="section-title text-sm mb-1">Response headers</div><div class="kt-card p-3 mb-3 overflow-x-auto"><table class="text-xs">${hdrRows}</table></div>` : ''}
        <div class="section-title text-sm mb-1">Response body</div>
        <textarea class="kt-input respBody" rows="12" readonly spellcheck="false">${esc(pretty)}</textarea>`;
      setActions([copyBtn(() => box.querySelector('.respBody').value, 'Copy response')]);
      done(ctx); icons();
    } catch (e) {
      box.innerHTML = `<div class="kt-card p-4 text-red-500">Request failed: ${esc(e.message)}</div>`;
    }
  };
});

/* ================================================================
   2. WEBHOOK TESTER
   ================================================================ */
reg('webhook-tester', function (mount, opt, ctx) {
  const wrap = el(`<div>
    <button class="btn btn-primary create"><i data-lucide="webhook"></i> Create test URL</button>
    <div class="urlWrap mt-3" style="display:none"></div>
  </div>`);
  mount.appendChild(wrap);
  const feed = el('<div class="mt-4"></div>'); mount.appendChild(feed);
  icons();
  let token = null, lastId = 0, timer = null;
  function stop() { if (timer) { clearInterval(timer); timer = null; } }
  function methodColor(m) { return { GET: '#16a34a', POST: '#0ea5e9', PUT: '#f59e0b', PATCH: '#f59e0b', DELETE: '#dc2626' }[m] || '#6b7280'; }
  function renderReq(rq) {
    let body = rq.body || '';
    try { body = JSON.stringify(JSON.parse(body), null, 2); } catch (e) { /* raw */ }
    const hdrRows = Object.entries(rq.headers || {}).map(([k, v]) => `<tr><td class="pr-3 opacity-60 font-mono align-top">${esc(k)}</td><td class="font-mono break-all">${esc(v)}</td></tr>`).join('');
    const card = el(`<div class="kt-card p-3 mb-3">
      <div class="flex items-center gap-2 flex-wrap mb-2">
        <span class="badge-premium" style="background:${methodColor(rq.method)};color:#fff">${esc(rq.method)}</span>
        <span class="text-xs opacity-60">${esc(rq.created_at || '')}</span>
        <span class="text-xs opacity-60">IP ${esc(rq.ip || '')}</span>
      </div>
      ${rq.query_string ? `<div class="text-xs font-mono opacity-70 mb-2 break-all">?${esc(rq.query_string)}</div>` : ''}
      ${hdrRows ? `<div class="text-xs mb-2 overflow-x-auto"><table>${hdrRows}</table></div>` : ''}
      ${body ? `<textarea class="kt-input" rows="4" readonly spellcheck="false">${esc(body)}</textarea>` : '<div class="text-xs opacity-50">(no body)</div>'}
    </div>`);
    feed.insertBefore(card, feed.firstChild.nextSibling || null);
  }
  async function poll() {
    if (!token) return;
    try {
      const r = await fetch(`${KT.url}/api/webhook_bin.php?action=list&token=${encodeURIComponent(token)}&since=${lastId}`);
      const j = await r.json();
      if (j.ok && j.requests && j.requests.length) {
        const list = j.requests.slice().sort((a, b) => a.id - b.id); // oldest first for append
        list.forEach(rq => { if (rq.id > lastId) lastId = rq.id; renderReq(rq); });
      }
    } catch (e) { /* keep polling */ }
  }
  wrap.querySelector('.create').onclick = async () => {
    stop();
    const fd = new FormData(); fd.append('action', 'create');
    try {
      const r = await fetch(KT.url + '/api/webhook_bin.php', { method: 'POST', headers: { 'X-CSRF': KT.csrf }, body: fd });
      const j = await r.json();
      if (!j.ok) { toast('Failed to create URL', true); return; }
      token = j.token; lastId = 0;
      const uw = wrap.querySelector('.urlWrap');
      uw.style.display = '';
      uw.innerHTML = `<label class="kt-label">Your catch URL (valid ${j.expires_hours || 24}h)</label>
        <div class="flex gap-2"><input class="kt-input catch" readonly value="${esc(j.url)}"><button class="btn btn-ghost cp"><i data-lucide="copy"></i></button></div>
        <div class="text-xs opacity-60 mt-1">Send a request to this URL to see it appear here.</div>`;
      uw.querySelector('.cp').onclick = () => copy(j.url);
      feed.innerHTML = `<div class="flex items-center justify-between mb-2"><div class="section-title text-sm">Incoming requests</div><button class="btn btn-ghost text-sm clr">Clear view</button></div>`;
      feed.querySelector('.clr').onclick = () => { const hdr = feed.firstChild; feed.innerHTML = ''; feed.appendChild(hdr); };
      done(ctx); icons();
      poll();
      timer = setInterval(poll, 3000);
    } catch (e) { toast('Failed to create URL', true); }
  };
});

/* ================================================================
   3. JWT DECODER
   ================================================================ */
reg('jwt-decoder', function (mount, opt, ctx) {
  const ta = textArea(mount, 4, 'Paste a JWT here (header.payload.signature)');
  const secWrap = el(`<div class="mt-3 flex gap-2">
    <input class="kt-input sec" placeholder="HS256 secret (optional, to verify)">
    <button class="btn btn-ghost vf">Verify</button></div>`);
  mount.appendChild(secWrap);
  const box = resultBox(mount);
  function fmtTime(v) { const n = Number(v); if (!n) return ''; return ' → ' + new Date(n * 1000).toISOString().replace('T', ' ').replace(/\.\d+Z/, ' UTC'); }
  function decode() {
    const tok = ta.value.trim();
    if (!tok) { box.innerHTML = ''; return null; }
    const parts = tok.split('.');
    if (parts.length < 2) { box.innerHTML = '<div class="text-red-500">Invalid JWT: expected header.payload.signature</div>'; return null; }
    let header, payload;
    try { header = JSON.parse(b64urlToStr(parts[0])); payload = JSON.parse(b64urlToStr(parts[1])); }
    catch (e) { box.innerHTML = `<div class="text-red-500">Decode failed: ${esc(e.message)}</div>`; return null; }
    const claims = [];
    ['iat', 'nbf', 'exp'].forEach(k => { if (payload[k] != null) claims.push(`${k}: ${payload[k]}${fmtTime(payload[k])}`); });
    const expNote = payload.exp ? (Date.now() / 1000 > payload.exp ? '<span class="text-red-500">EXPIRED</span>' : '<span class="text-green-600">valid (not expired)</span>') : '';
    box.innerHTML = `
      <div class="section-title text-sm mb-1">Header</div>
      <textarea class="kt-input" rows="4" readonly spellcheck="false">${esc(JSON.stringify(header, null, 2))}</textarea>
      <div class="section-title text-sm mt-3 mb-1">Payload</div>
      <textarea class="kt-input" rows="7" readonly spellcheck="false">${esc(JSON.stringify(payload, null, 2))}</textarea>
      ${claims.length ? `<div class="kt-card p-3 mt-3 text-sm font-mono">${claims.map(esc).join('<br>')}${expNote ? '<br>status: ' + expNote : ''}</div>` : ''}
      <div class="mt-3 vres text-sm"></div>`;
    return { parts, header };
  }
  ta.oninput = decode;
  secWrap.querySelector('.vf').onclick = async () => {
    const d = decode(); if (!d) return;
    const secret = secWrap.querySelector('.sec').value;
    const vres = box.querySelector('.vres');
    if (!secret) { vres.innerHTML = '<span class="opacity-60">Enter a secret to verify.</span>'; return; }
    if ((d.header.alg || '').toUpperCase() !== 'HS256') { vres.innerHTML = `<span class="text-amber-600">Verify only supports HS256 (token alg: ${esc(d.header.alg || 'none')}).</span>`; return; }
    try {
      const sig = b64urlFromBytes(await hmacSha256(secret, d.parts[0] + '.' + d.parts[1]));
      vres.innerHTML = sig === d.parts[2] ? '<span class="text-green-600 font-semibold">✓ Valid signature</span>' : '<span class="text-red-500 font-semibold">✗ Invalid signature</span>';
    } catch (e) { vres.innerHTML = `<span class="text-red-500">${esc(e.message)}</span>`; }
    done(ctx);
  };
  decode();
});

/* ================================================================
   4. JWT GENERATOR
   ================================================================ */
reg('jwt-gen', function (mount, opt, ctx) {
  const wrap = el(`<div>
    <label class="kt-label">Payload (JSON)</label>
    <textarea class="kt-input pl" rows="6" spellcheck="false">{
  "sub": "1234567890",
  "name": "Krishna",
  "iat": 1700000000
}</textarea>
    <label class="kt-label mt-3">Secret</label>
    <input class="kt-input sec" value="your-256-bit-secret">
    <button class="btn btn-primary mt-3 gen"><i data-lucide="key"></i> Generate</button>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  icons();
  wrap.querySelector('.gen').onclick = async () => {
    let payload;
    try { payload = JSON.parse(wrap.querySelector('.pl').value); }
    catch (e) { toast('Payload is not valid JSON', true); return; }
    const header = { alg: 'HS256', typ: 'JWT' };
    const h = b64urlFromStr(JSON.stringify(header)), p = b64urlFromStr(JSON.stringify(payload));
    try {
      const sig = b64urlFromBytes(await hmacSha256(wrap.querySelector('.sec').value, h + '.' + p));
      const token = `${h}.${p}.${sig}`;
      box.innerHTML = `<label class="kt-label">JWT</label><textarea class="kt-input out" rows="5" readonly spellcheck="false">${esc(token)}</textarea>`;
      setActions([copyBtn(token, 'Copy JWT')]);
      done(ctx); icons();
    } catch (e) { toast(e.message, true); }
  };
});

/* ================================================================
   5. REGEX TESTER
   ================================================================ */
reg('regex-tester', function (mount, opt, ctx) {
  const wrap = el(`<div class="grid gap-3">
    <div class="flex gap-2 items-center">
      <span class="font-mono opacity-60">/</span>
      <input class="kt-input pat" placeholder="pattern" value="\\b\\w+@\\w+\\.\\w+\\b">
      <span class="font-mono opacity-60">/</span>
      <input class="kt-input flg" style="max-width:90px" value="g" placeholder="flags">
    </div>
    <textarea class="kt-input txt" rows="6" spellcheck="false" placeholder="Test string...">Contact us at hello@example.com or admin@test.org</textarea>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  const pat = wrap.querySelector('.pat'), flg = wrap.querySelector('.flg'), txt = wrap.querySelector('.txt');
  function run() {
    const p = pat.value; let flags = flg.value || '';
    if (!flags.includes('g')) flags += 'g';
    let re;
    try { re = new RegExp(p, flags); }
    catch (e) { box.innerHTML = `<div class="text-red-500">Invalid regex: ${esc(e.message)}</div>`; return; }
    const src = txt.value; let m, count = 0, last = 0, html = '', groups = [];
    re.lastIndex = 0;
    while ((m = re.exec(src)) !== null) {
      count++;
      html += esc(src.slice(last, m.index)) + '<mark>' + esc(m[0]) + '</mark>';
      last = m.index + m[0].length;
      if (m.length > 1) groups.push({ n: count, g: m.slice(1) });
      if (m.index === re.lastIndex) re.lastIndex++;
      if (count > 5000) break;
    }
    html += esc(src.slice(last));
    box.innerHTML = `
      <div class="text-sm mb-2"><b>${count}</b> match${count === 1 ? '' : 'es'}</div>
      <div class="kt-card p-3 whitespace-pre-wrap break-words" style="line-height:1.6">${html || '<span class="opacity-50">(no text)</span>'}</div>
      ${groups.length ? `<div class="section-title text-sm mt-3 mb-1">Capture groups</div><div class="kt-card p-3 text-sm font-mono">${groups.map(x => `#${x.n}: ${x.g.map(g => esc(g == null ? '∅' : g)).join(' | ')}`).join('<br>')}</div>` : ''}`;
    done(ctx);
  }
  [pat, flg, txt].forEach(e => e.oninput = run);
  run();
});

/* ================================================================
   6. CRON PARSER
   ================================================================ */
reg('cron-parser', function (mount, opt, ctx) {
  const wrap = el(`<div>
    <label class="kt-label">Cron expression (minute hour day-of-month month day-of-week)</label>
    <input class="kt-input cr" value="*/15 9-17 * * 1-5" spellcheck="false">
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  const cr = wrap.querySelector('.cr');
  function parseField(expr, min, max) {
    const set = new Set();
    for (const part of expr.split(',')) {
      let step = 1, range = part;
      const sm = part.split('/');
      if (sm.length === 2) { range = sm[0]; step = parseInt(sm[1], 10); if (!step || step < 1) throw new Error('bad step'); }
      let lo, hi;
      if (range === '*') { lo = min; hi = max; }
      else if (range.includes('-')) { const [a, b] = range.split('-'); lo = parseInt(a, 10); hi = parseInt(b, 10); }
      else { lo = hi = parseInt(range, 10); }
      if (isNaN(lo) || isNaN(hi) || lo < min || hi > max || lo > hi) throw new Error('out of range');
      for (let v = lo; v <= hi; v += step) set.add(v);
    }
    return set;
  }
  const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  function describe(f, name, min, max, labels) {
    if (f === '*') return `every ${name}`;
    return `${name} ${f}`;
  }
  function run() {
    const parts = cr.value.trim().split(/\s+/);
    if (parts.length !== 5) { box.innerHTML = '<div class="text-red-500">Expected 5 fields (min hour dom month dow).</div>'; return; }
    let mins, hrs, doms, mons, dows;
    try {
      mins = parseField(parts[0], 0, 59); hrs = parseField(parts[1], 0, 23);
      doms = parseField(parts[2], 1, 31); mons = parseField(parts[3], 1, 12);
      dows = parseField(parts[4], 0, 6);
    } catch (e) { box.innerHTML = `<div class="text-red-500">Invalid cron: ${esc(e.message)}</div>`; return; }
    const desc = `At ${describe(parts[0], 'minute', 0, 59)}, ${describe(parts[1], 'hour', 0, 23)}, on ${describe(parts[2], 'day-of-month', 1, 31)}, in ${describe(parts[3], 'month', 1, 12)}, and ${describe(parts[4], 'day-of-week', 0, 6)}.`;
    const domStar = parts[2] === '*', dowStar = parts[4] === '*';
    const runs = [];
    let d = new Date(); d.setSeconds(0, 0); d.setMinutes(d.getMinutes() + 1);
    const cap = 366 * 24 * 60; // one year of minutes
    for (let i = 0; i < cap && runs.length < 5; i++) {
      if (mins.has(d.getMinutes()) && hrs.has(d.getHours()) && mons.has(d.getMonth() + 1)) {
        const domOk = doms.has(d.getDate()), dowOk = dows.has(d.getDay());
        // cron OR-semantics when either dom or dow is restricted
        const match = (domStar && dowStar) ? true : (domStar ? dowOk : (dowStar ? domOk : (domOk || dowOk)));
        if (match) runs.push(new Date(d));
      }
      d.setMinutes(d.getMinutes() + 1);
    }
    box.innerHTML = `
      <div class="kt-card p-3 mb-3">${esc(desc)}</div>
      <div class="section-title text-sm mb-1">Next 5 runs</div>
      <div class="kt-card p-3 text-sm font-mono">${runs.length ? runs.map(r => esc(r.toString())).join('<br>') : '<span class="opacity-60">No upcoming runs within a year.</span>'}</div>`;
    done(ctx);
  }
  cr.oninput = run; run();
});

/* ================================================================
   7. UUID / ULID GENERATOR
   ================================================================ */
reg('uuid-gen', function (mount, opt, ctx) {
  const wrap = el(`<div class="flex gap-2 items-end flex-wrap">
    <div><label class="kt-label">Type</label><select class="kt-select ty"><option value="uuid">UUID v4</option><option value="ulid">ULID</option></select></div>
    <div><label class="kt-label">Count</label><input type="number" class="kt-input cnt" value="5" min="1" max="500" style="max-width:110px"></div>
    <button class="btn btn-primary gen"><i data-lucide="refresh-cw"></i> Generate</button>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  icons();
  function uuidv4() {
    const b = crypto.getRandomValues(new Uint8Array(16));
    b[6] = (b[6] & 0x0f) | 0x40; b[8] = (b[8] & 0x3f) | 0x80;
    const h = [...b].map(x => x.toString(16).padStart(2, '0'));
    return `${h[0]}${h[1]}${h[2]}${h[3]}-${h[4]}${h[5]}-${h[6]}${h[7]}-${h[8]}${h[9]}-${h[10]}${h[11]}${h[12]}${h[13]}${h[14]}${h[15]}`;
  }
  const B32 = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
  function ulid() {
    let ts = Date.now(), time = '';
    for (let i = 0; i < 10; i++) { time = B32[ts % 32] + time; ts = Math.floor(ts / 32); }
    const rnd = crypto.getRandomValues(new Uint8Array(16));
    let rand = '';
    for (let i = 0; i < 16; i++) rand += B32[rnd[i] % 32];
    return time + rand;
  }
  wrap.querySelector('.gen').onclick = () => {
    const n = Math.min(500, Math.max(1, parseInt(wrap.querySelector('.cnt').value, 10) || 1));
    const ty = wrap.querySelector('.ty').value;
    const list = []; for (let i = 0; i < n; i++) list.push(ty === 'ulid' ? ulid() : uuidv4());
    const text = list.join('\n');
    box.innerHTML = `<textarea class="kt-input out" rows="${Math.min(14, n + 1)}" readonly spellcheck="false">${esc(text)}</textarea>`;
    setActions([copyBtn(text, 'Copy all')]);
    done(ctx); icons();
  };
});

/* ================================================================
   8. JSON <-> YAML
   ================================================================ */
reg('json-yaml', async function (mount, opt, ctx) {
  await loadScript('https://cdn.jsdelivr.net/npm/js-yaml@4.1.0/dist/js-yaml.min.js');
  const wrap = el(`<div>
    <div class="flex gap-2 mb-2"><select class="kt-select dir" style="max-width:180px"><option value="j2y">JSON → YAML</option><option value="y2j">YAML → JSON</option></select></div>
    <textarea class="kt-input inp" rows="8" spellcheck="false">{"name":"Krishna","tools":["json","yaml"],"count":28}</textarea>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    const dir = wrap.querySelector('.dir').value, src = wrap.querySelector('.inp').value;
    let out;
    try {
      if (dir === 'j2y') out = window.jsyaml.dump(JSON.parse(src));
      else out = JSON.stringify(window.jsyaml.load(src), null, 2);
    } catch (e) { box.innerHTML = `<div class="text-red-500">Parse error: ${esc(e.message)}</div>`; return; }
    box.innerHTML = `<textarea class="kt-input out" rows="10" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]);
    done(ctx); icons();
  }
  wrap.querySelector('.dir').onchange = run; wrap.querySelector('.inp').oninput = run; run();
});

/* ================================================================
   9. JSON -> TypeScript interfaces
   ================================================================ */
reg('json-ts', function (mount, opt, ctx) {
  const ta = textArea(mount, 8, 'Paste JSON...', '{"id":1,"name":"Krishna","active":true,"tags":["a","b"],"meta":{"role":"admin","level":3}}');
  const box = resultBox(mount);
  function tsType(val, name, out) {
    if (val === null) return 'any';
    if (Array.isArray(val)) { if (!val.length) return 'any[]'; return tsType(val[0], name, out) + '[]'; }
    const t = typeof val;
    if (t === 'string') return 'string';
    if (t === 'number') return 'number';
    if (t === 'boolean') return 'boolean';
    if (t === 'object') { const iname = cap(name); genInterface(val, iname, out); return iname; }
    return 'any';
  }
  function cap(s) { return (s || 'Root').replace(/[^a-zA-Z0-9]/g, '') .replace(/^(.)/, c => c.toUpperCase()) || 'Root'; }
  function genInterface(obj, name, out) {
    if (out.seen.has(name)) return;
    out.seen.add(name);
    const lines = [`interface ${name} {`];
    for (const [k, v] of Object.entries(obj)) {
      const key = /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(k) ? k : `"${k}"`;
      lines.push(`  ${key}: ${tsType(v, cap(k), out)};`);
    }
    lines.push('}');
    out.blocks.push(lines.join('\n'));
  }
  function run() {
    let data;
    try { data = JSON.parse(ta.value); } catch (e) { box.innerHTML = `<div class="text-red-500">Invalid JSON: ${esc(e.message)}</div>`; return; }
    if (typeof data !== 'object' || data === null || Array.isArray(data)) { data = { root: data }; }
    const out = { blocks: [], seen: new Set() };
    genInterface(data, 'Root', out);
    const code = out.blocks.reverse().join('\n\n');
    box.innerHTML = `<textarea class="kt-input out" rows="12" readonly spellcheck="false">${esc(code)}</textarea>`;
    setActions([copyBtn(code)]);
    done(ctx); icons();
  }
  ta.oninput = run; run();
});

/* ================================================================
   10. SQL FORMATTER / MINIFY
   ================================================================ */
reg('sql-format', async function (mount, opt, ctx) {
  await loadScript('https://cdn.jsdelivr.net/npm/sql-formatter@15.4.11/dist/sql-formatter.min.js');
  const ta = textArea(mount, 8, 'SQL...', 'select id,name from users where active=1 and age>18 order by name');
  const bar = el(`<div class="flex gap-2 mt-3"><button class="btn btn-primary fmt"><i data-lucide="align-left"></i> Format</button><button class="btn btn-ghost min"><i data-lucide="minimize-2"></i> Minify</button></div>`);
  mount.appendChild(bar);
  const box = resultBox(mount); icons();
  function out(text) { box.innerHTML = `<textarea class="kt-input out" rows="10" readonly spellcheck="false">${esc(text)}</textarea>`; setActions([copyBtn(text)]); done(ctx); icons(); }
  bar.querySelector('.fmt').onclick = () => {
    try { out(window.sqlFormatter.format(ta.value)); } catch (e) { toast(e.message, true); }
  };
  bar.querySelector('.min').onclick = () => { out(ta.value.replace(/\s+/g, ' ').replace(/\s*([(),])\s*/g, '$1').trim()); };
});

/* ================================================================
   11. HTTP STATUS CODES
   ================================================================ */
reg('http-status', function (mount, opt, ctx) {
  const CODES = [
    [100, 'Continue', 'Client should continue the request.'],
    [101, 'Switching Protocols', 'Server is switching protocols per Upgrade header.'],
    [102, 'Processing', 'Server has received and is processing the request.'],
    [200, 'OK', 'Request succeeded.'],
    [201, 'Created', 'Request succeeded and a new resource was created.'],
    [202, 'Accepted', 'Request accepted for processing, not yet completed.'],
    [204, 'No Content', 'Success, but no content to return.'],
    [206, 'Partial Content', 'Partial resource delivered (range request).'],
    [301, 'Moved Permanently', 'Resource permanently moved to a new URL.'],
    [302, 'Found', 'Resource temporarily located at a different URL.'],
    [303, 'See Other', 'Fetch the resource from another URI with GET.'],
    [304, 'Not Modified', 'Cached version is still valid.'],
    [307, 'Temporary Redirect', 'Repeat request at new URL, keep method.'],
    [308, 'Permanent Redirect', 'Resource permanently moved, keep method.'],
    [400, 'Bad Request', 'Server cannot process the malformed request.'],
    [401, 'Unauthorized', 'Authentication required or failed.'],
    [402, 'Payment Required', 'Reserved for future / payment systems.'],
    [403, 'Forbidden', 'Server understood but refuses to authorize.'],
    [404, 'Not Found', 'Resource could not be found.'],
    [405, 'Method Not Allowed', 'HTTP method not supported for this resource.'],
    [406, 'Not Acceptable', 'Cannot produce a response matching Accept headers.'],
    [408, 'Request Timeout', 'Server timed out waiting for the request.'],
    [409, 'Conflict', 'Request conflicts with current server state.'],
    [410, 'Gone', 'Resource is permanently unavailable.'],
    [411, 'Length Required', 'Content-Length header is required.'],
    [413, 'Payload Too Large', 'Request body is too large.'],
    [414, 'URI Too Long', 'The requested URI is too long.'],
    [415, 'Unsupported Media Type', 'Media format not supported.'],
    [418, "I'm a teapot", 'Refuses to brew coffee (RFC 2324 joke).'],
    [422, 'Unprocessable Entity', 'Semantic errors in the request.'],
    [425, 'Too Early', 'Server unwilling to risk replaying the request.'],
    [429, 'Too Many Requests', 'Rate limit exceeded.'],
    [431, 'Request Header Fields Too Large', 'Headers too large to process.'],
    [451, 'Unavailable For Legal Reasons', 'Blocked for legal reasons.'],
    [500, 'Internal Server Error', 'Generic server-side error.'],
    [501, 'Not Implemented', 'Server does not support the functionality.'],
    [502, 'Bad Gateway', 'Invalid response from an upstream server.'],
    [503, 'Service Unavailable', 'Server overloaded or down for maintenance.'],
    [504, 'Gateway Timeout', 'Upstream server did not respond in time.'],
    [505, 'HTTP Version Not Supported', 'HTTP version not supported.'],
    [511, 'Network Authentication Required', 'Client must authenticate to gain network access.'],
  ];
  const wrap = el(`<div><input class="kt-input flt" placeholder="Filter by code or name (e.g. 404, timeout)"></div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function color(c) { return c < 200 ? '#6b7280' : c < 300 ? '#16a34a' : c < 400 ? '#0ea5e9' : c < 500 ? '#f59e0b' : '#dc2626'; }
  function render(q) {
    q = (q || '').toLowerCase().trim();
    const rows = CODES.filter(c => !q || String(c[0]).includes(q) || c[1].toLowerCase().includes(q) || c[2].toLowerCase().includes(q))
      .map(c => `<tr class="border-t" style="border-color:var(--border)"><td class="py-1.5 pr-3"><span class="badge-premium" style="background:${color(c[0])};color:#fff">${c[0]}</span></td><td class="py-1.5 pr-3 font-semibold">${esc(c[1])}</td><td class="py-1.5 opacity-70 text-sm">${esc(c[2])}</td></tr>`).join('');
    box.innerHTML = `<div class="kt-card p-3 overflow-x-auto"><table class="w-full text-sm">${rows || '<tr><td class="opacity-60 py-2">No matches</td></tr>'}</table></div>`;
  }
  wrap.querySelector('.flt').oninput = e => render(e.target.value);
  render(''); done(ctx);
});

/* ================================================================
   12. MIME LOOKUP
   ================================================================ */
reg('mime-lookup', function (mount, opt, ctx) {
  const MAP = {
    txt: 'text/plain', html: 'text/html', htm: 'text/html', css: 'text/css', csv: 'text/csv', xml: 'application/xml',
    js: 'text/javascript', mjs: 'text/javascript', json: 'application/json', md: 'text/markdown',
    pdf: 'application/pdf', doc: 'application/msword', docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    xls: 'application/vnd.ms-excel', xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ppt: 'application/vnd.ms-powerpoint', pptx: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    zip: 'application/zip', rar: 'application/vnd.rar', '7z': 'application/x-7z-compressed', tar: 'application/x-tar', gz: 'application/gzip',
    png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', gif: 'image/gif', webp: 'image/webp', svg: 'image/svg+xml',
    bmp: 'image/bmp', ico: 'image/vnd.microsoft.icon', tif: 'image/tiff', tiff: 'image/tiff', heic: 'image/heic', avif: 'image/avif',
    mp3: 'audio/mpeg', wav: 'audio/wav', ogg: 'audio/ogg', m4a: 'audio/mp4', flac: 'audio/flac', aac: 'audio/aac', weba: 'audio/webm',
    mp4: 'video/mp4', webm: 'video/webm', avi: 'video/x-msvideo', mov: 'video/quicktime', mkv: 'video/x-matroska', mpeg: 'video/mpeg', wmv: 'video/x-ms-wmv',
    ttf: 'font/ttf', otf: 'font/otf', woff: 'font/woff', woff2: 'font/woff2', eot: 'application/vnd.ms-fontobject',
    bin: 'application/octet-stream', exe: 'application/vnd.microsoft.portable-executable', apk: 'application/vnd.android.package-archive',
    wasm: 'application/wasm', ics: 'text/calendar', rtf: 'application/rtf', epub: 'application/epub+zip',
    yaml: 'application/yaml', yml: 'application/yaml', toml: 'application/toml', sh: 'application/x-sh', php: 'application/x-httpd-php',
    sql: 'application/sql', ts: 'application/typescript', jsonld: 'application/ld+json', wgt: 'application/widget'
  };
  const wrap = el(`<div>
    <label class="kt-label">Extension (e.g. pdf or .png) or MIME type (e.g. image/png)</label>
    <input class="kt-input q" placeholder="pdf">
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    let q = wrap.querySelector('.q').value.trim().toLowerCase().replace(/^\./, '');
    if (!q) { box.innerHTML = ''; return; }
    if (q.includes('/')) {
      const exts = Object.entries(MAP).filter(([, v]) => v === q).map(([k]) => k);
      box.innerHTML = exts.length
        ? `<div class="kt-card p-3">Extensions for <b>${esc(q)}</b>: <span class="font-mono">${exts.map(e => '.' + e).join(', ')}</span></div>`
        : `<div class="kt-card p-3 opacity-60">No known extensions for ${esc(q)}.</div>`;
    } else {
      const mime = MAP[q];
      box.innerHTML = mime
        ? `<div class="kt-card p-3">MIME type for <b>.${esc(q)}</b>: <span class="font-mono">${esc(mime)}</span><button class="btn btn-ghost text-sm ml-2 cp"><i data-lucide="copy"></i></button></div>`
        : `<div class="kt-card p-3 opacity-60">Unknown extension: .${esc(q)}</div>`;
      const cp = box.querySelector('.cp'); if (cp) cp.onclick = () => copy(mime);
    }
    done(ctx); icons();
  }
  wrap.querySelector('.q').oninput = run;
});

/* ================================================================
   13. USER-AGENT PARSER
   ================================================================ */
reg('ua-parser', function (mount, opt, ctx) {
  const ta = textArea(mount, 3, 'User-Agent string', navigator.userAgent);
  const box = resultBox(mount);
  function parse(ua) {
    let browser = 'Unknown', bver = '', engine = 'Unknown', os = 'Unknown', over = '', device = 'Desktop';
    const m = (re) => { const r = ua.match(re); return r ? r[1] : ''; };
    if (/Edg\//.test(ua)) { browser = 'Edge'; bver = m(/Edg\/([\d.]+)/); }
    else if (/OPR\/|Opera/.test(ua)) { browser = 'Opera'; bver = m(/(?:OPR|Opera)\/([\d.]+)/); }
    else if (/Firefox\//.test(ua)) { browser = 'Firefox'; bver = m(/Firefox\/([\d.]+)/); }
    else if (/Chrome\//.test(ua)) { browser = 'Chrome'; bver = m(/Chrome\/([\d.]+)/); }
    else if (/Safari\//.test(ua) && /Version\//.test(ua)) { browser = 'Safari'; bver = m(/Version\/([\d.]+)/); }
    else if (/MSIE|Trident/.test(ua)) { browser = 'Internet Explorer'; bver = m(/(?:MSIE |rv:)([\d.]+)/); }
    if (/Gecko\//.test(ua) && browser === 'Firefox') engine = 'Gecko';
    else if (/AppleWebKit/.test(ua)) engine = /Chrome|Edg|OPR/.test(ua) ? 'Blink' : 'WebKit';
    else if (/Trident/.test(ua)) engine = 'Trident';
    if (/Windows NT/.test(ua)) { os = 'Windows'; const v = m(/Windows NT ([\d.]+)/); over = { '10.0': '10/11', '6.3': '8.1', '6.2': '8', '6.1': '7' }[v] || v; }
    else if (/iPhone|iPad|iPod/.test(ua)) { os = 'iOS'; over = m(/OS ([\d_]+)/).replace(/_/g, '.'); }
    else if (/Mac OS X/.test(ua)) { os = 'macOS'; over = m(/Mac OS X ([\d_]+)/).replace(/_/g, '.'); }
    else if (/Android/.test(ua)) { os = 'Android'; over = m(/Android ([\d.]+)/); }
    else if (/Linux/.test(ua)) { os = 'Linux'; }
    if (/iPad|Tablet/.test(ua)) device = 'Tablet';
    else if (/Mobi|iPhone|Android.*Mobile/.test(ua)) device = 'Mobile';
    return { browser: (browser + ' ' + bver).trim(), engine, os: (os + ' ' + over).trim(), device };
  }
  function run() {
    const r = parse(ta.value);
    box.innerHTML = `<div class="kt-card p-3"><table class="text-sm">
      <tr><td class="pr-4 py-1 opacity-60">Browser</td><td class="font-semibold">${esc(r.browser)}</td></tr>
      <tr><td class="pr-4 py-1 opacity-60">Engine</td><td class="font-semibold">${esc(r.engine)}</td></tr>
      <tr><td class="pr-4 py-1 opacity-60">OS</td><td class="font-semibold">${esc(r.os)}</td></tr>
      <tr><td class="pr-4 py-1 opacity-60">Device</td><td class="font-semibold">${esc(r.device)}</td></tr>
    </table></div>`;
    done(ctx);
  }
  ta.oninput = run; run();
});

/* ================================================================
   14. MY IP
   ================================================================ */
reg('my-ip', async function (mount, opt, ctx) {
  const box = resultBox(mount);
  box.innerHTML = '<div class="chakra-loader mx-auto"></div>';
  try {
    const r = await fetch(KT.url + '/api/net.php?action=ip');
    const j = await r.json();
    if (!j.ok) { box.innerHTML = '<div class="text-red-500">Could not fetch IP.</div>'; return; }
    const hdrRows = Object.entries(j.headers || {}).map(([k, v]) => `<tr class="border-t" style="border-color:var(--border)"><td class="pr-4 py-1 opacity-60 font-mono align-top">${esc(k)}</td><td class="py-1 font-mono break-all">${esc(v)}</td></tr>`).join('');
    box.innerHTML = `
      <div class="kt-card p-5 text-center mb-3">
        <div class="text-xs opacity-60 mb-1">Your IP address</div>
        <div class="text-3xl font-bold" style="color:var(--peacock-teal)">${esc(j.ip || 'unknown')}</div>
      </div>
      ${hdrRows ? `<div class="section-title text-sm mb-1">Request headers</div><div class="kt-card p-3 overflow-x-auto"><table class="text-xs w-full">${hdrRows}</table></div>` : ''}`;
    setActions([copyBtn(j.ip || '', 'Copy IP')]);
    done(ctx); icons();
  } catch (e) { box.innerHTML = `<div class="text-red-500">${esc(e.message)}</div>`; }
});

/* ================================================================
   15. DNS LOOKUP
   ================================================================ */
reg('dns-lookup', function (mount, opt, ctx) {
  const types = ['A', 'AAAA', 'MX', 'TXT', 'NS', 'CNAME', 'SOA'];
  const wrap = el(`<div class="flex gap-2 items-end flex-wrap">
    <div class="flex-1" style="min-width:200px"><label class="kt-label">Domain</label><input class="kt-input dom" placeholder="example.com" value="google.com"></div>
    <div><label class="kt-label">Type</label><select class="kt-select ty">${types.map(t => `<option>${t}</option>`).join('')}</select></div>
    <button class="btn btn-primary go"><i data-lucide="search"></i> Lookup</button>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount); icons();
  wrap.querySelector('.go').onclick = async () => {
    const dom = wrap.querySelector('.dom').value.trim(), ty = wrap.querySelector('.ty').value;
    if (!dom) { toast('Enter a domain', true); return; }
    box.innerHTML = '<div class="chakra-loader mx-auto"></div>';
    try {
      const r = await fetch(`${KT.url}/api/net.php?action=dns&domain=${encodeURIComponent(dom)}&type=${ty}`);
      const j = await r.json();
      if (!j.ok) { box.innerHTML = `<div class="text-red-500">${esc(j.error || 'Lookup failed')}</div>`; return; }
      const recs = j.records || [];
      const rows = recs.map(rec => `<tr class="border-t" style="border-color:var(--border)"><td class="py-1.5 font-mono break-all">${esc(typeof rec === 'object' ? JSON.stringify(rec) : rec)}</td></tr>`).join('');
      box.innerHTML = `<div class="kt-card p-3 overflow-x-auto"><div class="text-sm opacity-60 mb-1">${esc(j.domain)} · ${esc(j.type)} · ${recs.length} record(s)</div><table class="w-full text-sm">${rows || '<tr><td class="opacity-60 py-2">No records</td></tr>'}</table></div>`;
      done(ctx);
    } catch (e) { box.innerHTML = `<div class="text-red-500">${esc(e.message)}</div>`; }
  };
});

/* ================================================================
   16. HTML MINIFY
   ================================================================ */
function minifyResult(box, orig, out, name, mime, ctx) {
  const saved = orig.length ? Math.round((1 - out.length / orig.length) * 100) : 0;
  box.innerHTML = `<div class="text-sm opacity-70 mb-2">${fmtBytes(orig.length)} → ${fmtBytes(out.length)} (<b>${saved}%</b> saved)</div>
    <textarea class="kt-input out" rows="8" readonly spellcheck="false">${esc(out)}</textarea>`;
  setActions([copyBtn(out), { label: 'Download', cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(new Blob([out], { type: mime }), name) }]);
  done(ctx); icons();
}
reg('html-min', function (mount, opt, ctx) {
  const ta = textArea(mount, 8, 'Paste HTML...', '<!-- comment -->\n<div class="box">\n  <p>  Hello   world  </p>\n</div>');
  const bar = el(`<button class="btn btn-primary mt-3 go"><i data-lucide="minimize-2"></i> Minify</button>`); mount.appendChild(bar);
  const box = resultBox(mount); icons();
  bar.onclick = () => {
    const out = ta.value
      .replace(/<!--(?!\[if)[\s\S]*?-->/g, '')
      .replace(/>\s+</g, '><')
      .replace(/\s{2,}/g, ' ')
      .replace(/\s+>/g, '>')
      .trim();
    minifyResult(box, ta.value, out, 'min.html', 'text/html', ctx);
  };
});

/* ================================================================
   17. CSS MINIFY
   ================================================================ */
reg('css-min', function (mount, opt, ctx) {
  const ta = textArea(mount, 8, 'Paste CSS...', '/* main */\n.box {\n  color: red;\n  margin: 0 auto;\n}\n');
  const bar = el(`<button class="btn btn-primary mt-3 go"><i data-lucide="minimize-2"></i> Minify</button>`); mount.appendChild(bar);
  const box = resultBox(mount); icons();
  bar.onclick = () => {
    const out = ta.value
      .replace(/\/\*[\s\S]*?\*\//g, '')
      .replace(/\s*([{}:;,>])\s*/g, '$1')
      .replace(/;}/g, '}')
      .replace(/\s+/g, ' ')
      .trim();
    minifyResult(box, ta.value, out, 'min.css', 'text/css', ctx);
  };
});

/* ================================================================
   18. JS MINIFY (lightweight)
   ================================================================ */
reg('js-min', function (mount, opt, ctx) {
  const info = el('<div class="text-xs opacity-60 mb-2">Lightweight minifier: strips comments and collapses whitespace. Not a full compressor.</div>');
  mount.appendChild(info);
  const ta = textArea(mount, 8, 'Paste JavaScript...', '// hello\nfunction add(a, b) {\n  /* sum */\n  return a + b;\n}\n');
  const bar = el(`<button class="btn btn-primary mt-3 go"><i data-lucide="minimize-2"></i> Minify</button>`); mount.appendChild(bar);
  const box = resultBox(mount); icons();
  bar.onclick = () => {
    let s = ta.value;
    // remove block comments and line comments while trying not to touch strings/regex (best effort)
    let out = '', i = 0, n = s.length;
    while (i < n) {
      const c = s[i], d = s[i + 1];
      if (c === '/' && d === '*') { i += 2; while (i < n && !(s[i] === '*' && s[i + 1] === '/')) i++; i += 2; continue; }
      if (c === '/' && d === '/') { i += 2; while (i < n && s[i] !== '\n') i++; continue; }
      if (c === '"' || c === "'" || c === '`') { const q = c; out += c; i++; while (i < n) { out += s[i]; if (s[i] === '\\') { out += s[i + 1] || ''; i += 2; continue; } if (s[i] === q) { i++; break; } i++; } continue; }
      out += c; i++;
    }
    out = out.replace(/[ \t]+/g, ' ').replace(/\s*\n\s*/g, '\n').replace(/\n{2,}/g, '\n').trim();
    minifyResult(box, ta.value, out, 'min.js', 'text/javascript', ctx);
  };
});

/* ================================================================
   19. CODE BEAUTIFIER
   ================================================================ */
reg('code-beautify', async function (mount, opt, ctx) {
  await loadScript('https://cdn.jsdelivr.net/npm/js-beautify@1.15.1/js/lib/beautify.min.js');
  await loadScript('https://cdn.jsdelivr.net/npm/js-beautify@1.15.1/js/lib/beautify-css.min.js');
  await loadScript('https://cdn.jsdelivr.net/npm/js-beautify@1.15.1/js/lib/beautify-html.min.js');
  const wrap = el(`<div>
    <select class="kt-select lang mb-2" style="max-width:150px"><option value="js">JavaScript</option><option value="css">CSS</option><option value="html">HTML</option></select>
    <textarea class="kt-input inp" rows="8" spellcheck="false">function add(a,b){return a+b;}</textarea>
  </div>`);
  mount.appendChild(wrap);
  const bar = el(`<button class="btn btn-primary mt-3 go"><i data-lucide="align-left"></i> Beautify</button>`); mount.appendChild(bar);
  const box = resultBox(mount); icons();
  bar.onclick = () => {
    const lang = wrap.querySelector('.lang').value, src = wrap.querySelector('.inp').value;
    let out;
    try {
      if (lang === 'css') out = (window.css_beautify || window.js_beautify.css)(src, { indent_size: 2 });
      else if (lang === 'html') out = (window.html_beautify || window.js_beautify.html)(src, { indent_size: 2 });
      else out = window.js_beautify(src, { indent_size: 2 });
    } catch (e) { toast(e.message, true); return; }
    box.innerHTML = `<textarea class="kt-input out" rows="10" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]); done(ctx); icons();
  };
});

/* ================================================================
   20. SLUGIFY
   ================================================================ */
reg('slugify', function (mount, opt, ctx) {
  const wrap = el(`<div>
    <input class="kt-input txt" placeholder="Enter text..." value="Héllo World! This is KRISHNA Tools">
    <div class="flex gap-4 mt-3 items-center flex-wrap">
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" class="lc" checked> lowercase</label>
      <label class="flex items-center gap-2 text-sm">separator
        <select class="kt-select sep" style="max-width:90px"><option value="-">- (dash)</option><option value="_">_ (underscore)</option></select>
      </label>
    </div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    let s = wrap.querySelector('.txt').value;
    const sep = wrap.querySelector('.sep').value;
    s = s.normalize('NFKD').replace(/[\u0300-\u036f]/g, '');
    if (wrap.querySelector('.lc').checked) s = s.toLowerCase();
    s = s.replace(/[^a-zA-Z0-9]+/g, sep).replace(new RegExp('\\' + sep + '{2,}', 'g'), sep).replace(new RegExp('^\\' + sep + '+|\\' + sep + '+$', 'g'), '');
    box.innerHTML = `<div class="flex gap-2"><input class="kt-input out" readonly value="${esc(s)}"><button class="btn btn-ghost cp"><i data-lucide="copy"></i></button></div>`;
    box.querySelector('.cp').onclick = () => copy(s);
    done(ctx); icons();
  }
  wrap.querySelectorAll('input,select').forEach(e => { e.oninput = run; e.onchange = run; });
  run();
});

/* ================================================================
   21. STRING ESCAPE / UNESCAPE
   ================================================================ */
reg('string-escape', function (mount, opt, ctx) {
  const ta = textArea(mount, 6, 'Enter text...', 'Hello "World"\nLine 2\tTabbed <b>bold</b>');
  const bar = el(`<div class="flex gap-2 mt-3 flex-wrap items-end">
    <div><label class="kt-label">Target</label><select class="kt-select tg"><option>JSON</option><option>JavaScript</option><option>SQL</option><option>HTML</option><option>URL</option></select></div>
    <div><label class="kt-label">Mode</label><select class="kt-select md"><option>Escape</option><option>Unescape</option></select></div>
    <button class="btn btn-primary go">Convert</button>
  </div>`);
  mount.appendChild(bar);
  const box = resultBox(mount);
  function transform(s, tg, unesc) {
    if (tg === 'JSON') return unesc ? JSON.parse('"' + s.replace(/^"|"$/g, '') + '"') : JSON.stringify(s).slice(1, -1);
    if (tg === 'JavaScript') return unesc ? s.replace(/\\n/g, '\n').replace(/\\t/g, '\t').replace(/\\r/g, '\r').replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\\\/g, '\\') : s.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\t/g, '\\t').replace(/\r/g, '\\r');
    if (tg === 'SQL') return unesc ? s.replace(/''/g, "'") : s.replace(/'/g, "''");
    if (tg === 'HTML') {
      if (unesc) { const d = document.createElement('textarea'); d.innerHTML = s; return d.value; }
      return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    if (tg === 'URL') return unesc ? decodeURIComponent(s) : encodeURIComponent(s);
    return s;
  }
  bar.querySelector('.go').onclick = () => {
    const tg = bar.querySelector('.tg').value, unesc = bar.querySelector('.md').value === 'Unescape';
    let out;
    try { out = transform(ta.value, tg, unesc); } catch (e) { toast(e.message, true); return; }
    box.innerHTML = `<textarea class="kt-input out" rows="6" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]); done(ctx); icons();
  };
});

/* ================================================================
   22. CHMOD CALCULATOR
   ================================================================ */
reg('chmod-calc', function (mount, opt, ctx) {
  const groups = ['Owner', 'Group', 'Other'], perms = ['Read', 'Write', 'Execute'];
  const rows = groups.map((g, gi) => `<tr><td class="pr-4 py-1 font-semibold">${g}</td>${perms.map((p, pi) => `<td class="px-3 text-center"><label class="flex flex-col items-center gap-1 text-xs opacity-70"><input type="checkbox" class="cb" data-g="${gi}" data-p="${pi}">${p[0]}</label></td>`).join('')}</tr>`).join('');
  const wrap = el(`<div>
    <div class="kt-card p-3 overflow-x-auto"><table><thead><tr><td></td>${perms.map(p => `<td class="px-3 text-center text-xs opacity-60">${p}</td>`).join('')}</tr></thead><tbody>${rows}</tbody></table></div>
    <div class="flex gap-2 mt-3 items-end"><div><label class="kt-label">Octal</label><input class="kt-input oct" style="max-width:120px" placeholder="755" maxlength="4"></div></div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  const cbs = [...wrap.querySelectorAll('.cb')], octIn = wrap.querySelector('.oct');
  function fromCheckboxes() {
    const bits = [0, 0, 0];
    cbs.forEach(cb => { if (cb.checked) bits[+cb.dataset.g] += [4, 2, 1][+cb.dataset.p]; });
    return bits;
  }
  function render(bits) {
    const octal = bits.join('');
    const sym = bits.map(b => (b & 4 ? 'r' : '-') + (b & 2 ? 'w' : '-') + (b & 1 ? 'x' : '-')).join('');
    box.innerHTML = `<div class="kt-card p-4 font-mono">
      <div class="text-2xl font-bold" style="color:var(--peacock-teal)">${octal}</div>
      <div class="mt-1">${esc(sym)}</div>
      <div class="mt-2 opacity-70">chmod ${octal} file</div>
      <button class="btn btn-ghost text-sm mt-2 cp"><i data-lucide="copy"></i> Copy command</button>
    </div>`;
    box.querySelector('.cp').onclick = () => copy(`chmod ${octal} file`);
    done(ctx); icons();
  }
  function update() { render(fromCheckboxes()); }
  cbs.forEach(cb => cb.onchange = () => { const b = fromCheckboxes(); octIn.value = b.join(''); render(b); });
  octIn.oninput = () => {
    const v = octIn.value.replace(/[^0-7]/g, '').slice(-3).padStart(3, '0');
    if (!/^[0-7]{3}$/.test(v)) return;
    const bits = v.split('').map(Number);
    cbs.forEach(cb => { const g = +cb.dataset.g, p = +cb.dataset.p; cb.checked = !!(bits[g] & [4, 2, 1][p]); });
    render(bits);
  };
  update();
});

/* ================================================================
   23. COLOR CONVERTER
   ================================================================ */
reg('color-convert', function (mount, opt, ctx) {
  const wrap = el(`<div class="flex gap-3 items-center flex-wrap">
    <input type="color" class="pick" value="#1aa39c" style="width:56px;height:44px;border:none;background:none;cursor:pointer">
    <input class="kt-input txt" style="max-width:260px" placeholder="#1aa39c / rgb(...) / hsl(...)" value="#1aa39c">
    <div class="swatch" style="width:44px;height:44px;border-radius:8px;border:1px solid var(--border)"></div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  const pick = wrap.querySelector('.pick'), txt = wrap.querySelector('.txt'), sw = wrap.querySelector('.swatch');
  function parse(s) {
    s = s.trim();
    let m;
    if ((m = s.match(/^#?([0-9a-f]{3})$/i))) { const h = m[1]; return [parseInt(h[0] + h[0], 16), parseInt(h[1] + h[1], 16), parseInt(h[2] + h[2], 16)]; }
    if ((m = s.match(/^#?([0-9a-f]{6})$/i))) { const h = m[1]; return [parseInt(h.slice(0, 2), 16), parseInt(h.slice(2, 4), 16), parseInt(h.slice(4, 6), 16)]; }
    if ((m = s.match(/rgba?\(\s*(\d+)[,\s]+(\d+)[,\s]+(\d+)/i))) return [+m[1], +m[2], +m[3]];
    if ((m = s.match(/hsla?\(\s*(\d+)[,\s]+(\d+)%?[,\s]+(\d+)%?/i))) return hslToRgb(+m[1], +m[2], +m[3]);
    return null;
  }
  function hslToRgb(h, s, l) {
    s /= 100; l /= 100; const k = n => (n + h / 30) % 12; const a = s * Math.min(l, 1 - l);
    const f = n => Math.round(255 * (l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)))));
    return [f(0), f(8), f(4)];
  }
  function rgbToHsl(r, g, b) {
    r /= 255; g /= 255; b /= 255; const mx = Math.max(r, g, b), mn = Math.min(r, g, b); let h = 0, s = 0, l = (mx + mn) / 2;
    if (mx !== mn) { const d = mx - mn; s = l > 0.5 ? d / (2 - mx - mn) : d / (mx + mn); h = mx === r ? (g - b) / d + (g < b ? 6 : 0) : mx === g ? (b - r) / d + 2 : (r - g) / d + 4; h /= 6; }
    return [Math.round(h * 360), Math.round(s * 100), Math.round(l * 100)];
  }
  function toHex(rgb) { return '#' + rgb.map(v => Math.max(0, Math.min(255, v)).toString(16).padStart(2, '0')).join(''); }
  function render(rgb) {
    const hex = toHex(rgb), rgbStr = `rgb(${rgb.join(', ')})`, hsl = rgbToHsl(...rgb), hslStr = `hsl(${hsl[0]}, ${hsl[1]}%, ${hsl[2]}%)`;
    sw.style.background = hex; pick.value = hex;
    const line = (label, val) => `<div class="flex items-center gap-2 py-1"><span class="opacity-60 text-sm" style="width:48px">${label}</span><span class="font-mono">${esc(val)}</span><button class="btn btn-ghost text-xs cp" data-v="${esc(val)}"><i data-lucide="copy"></i></button></div>`;
    box.innerHTML = `<div class="kt-card p-3">${line('HEX', hex)}${line('RGB', rgbStr)}${line('HSL', hslStr)}</div>`;
    box.querySelectorAll('.cp').forEach(b => b.onclick = () => copy(b.dataset.v));
    done(ctx); icons();
  }
  function fromText() { const rgb = parse(txt.value); if (rgb) render(rgb); }
  txt.oninput = fromText;
  pick.oninput = () => { txt.value = pick.value; render(parse(pick.value)); };
  render(parse(txt.value));
});

/* ================================================================
   24. BASE CONVERTER
   ================================================================ */
reg('base-convert', function (mount, opt, ctx) {
  const wrap = el(`<div class="flex gap-2 items-end flex-wrap">
    <div class="flex-1" style="min-width:180px"><label class="kt-label">Value</label><input class="kt-input val" value="255" spellcheck="false"></div>
    <div><label class="kt-label">From base</label><input type="number" class="kt-input from" value="10" min="2" max="36" style="max-width:110px"></div>
    <div><label class="kt-label">Target base</label><input type="number" class="kt-input tgt" value="36" min="2" max="36" style="max-width:110px"></div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    const val = wrap.querySelector('.val').value.trim();
    const from = parseInt(wrap.querySelector('.from').value, 10) || 10;
    const tgt = Math.min(36, Math.max(2, parseInt(wrap.querySelector('.tgt').value, 10) || 10));
    if (!val) { box.innerHTML = ''; return; }
    const valid = new RegExp('^-?[0-9a-z]+$', 'i').test(val);
    const num = parseInt(val, from);
    if (!valid || isNaN(num) || val.toLowerCase().split('').some(ch => ch !== '-' && parseInt(ch, 36) >= from)) {
      box.innerHTML = `<div class="text-red-500">Invalid digits for base ${from}.</div>`; return;
    }
    const rows = [['Binary (2)', num.toString(2)], ['Octal (8)', num.toString(8)], ['Decimal (10)', num.toString(10)], ['Hex (16)', num.toString(16).toUpperCase()], [`Base ${tgt}`, num.toString(tgt).toUpperCase()]];
    box.innerHTML = `<div class="kt-card p-3">${rows.map(r => `<div class="flex items-center gap-2 py-1"><span class="opacity-60 text-sm" style="width:110px">${esc(r[0])}</span><span class="font-mono break-all">${esc(r[1])}</span><button class="btn btn-ghost text-xs cp" data-v="${esc(r[1])}"><i data-lucide="copy"></i></button></div>`).join('')}</div>`;
    box.querySelectorAll('.cp').forEach(b => b.onclick = () => copy(b.dataset.v));
    done(ctx); icons();
  }
  wrap.querySelectorAll('input').forEach(e => e.oninput = run);
  run();
});

/* ================================================================
   25. META TAG GENERATOR
   ================================================================ */
reg('meta-gen', function (mount, opt, ctx) {
  const fields = [
    ['title', 'Page title'], ['desc', 'Meta description'], ['keywords', 'Keywords (comma separated)'],
    ['author', 'Author'], ['url', 'Site URL (https://...)'], ['image', 'Image URL (https://...)']
  ];
  const wrap = el(`<div class="grid gap-2">${fields.map(f => `<div><label class="kt-label">${f[1]}</label><input class="kt-input" data-k="${f[0]}"></div>`).join('')}</div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function val(k) { return (wrap.querySelector(`[data-k="${k}"]`).value || '').trim(); }
  function e(s) { return s.replace(/"/g, '&quot;'); }
  function run() {
    const t = val('title'), d = val('desc'), kw = val('keywords'), au = val('author'), url = val('url'), img = val('image');
    const lines = [];
    if (t) lines.push(`<title>${e(t)}</title>`);
    if (d) lines.push(`<meta name="description" content="${e(d)}">`);
    if (kw) lines.push(`<meta name="keywords" content="${e(kw)}">`);
    if (au) lines.push(`<meta name="author" content="${e(au)}">`);
    lines.push('');
    lines.push('<!-- Open Graph -->');
    if (t) lines.push(`<meta property="og:title" content="${e(t)}">`);
    if (d) lines.push(`<meta property="og:description" content="${e(d)}">`);
    if (url) lines.push(`<meta property="og:url" content="${e(url)}">`);
    if (img) lines.push(`<meta property="og:image" content="${e(img)}">`);
    lines.push('<meta property="og:type" content="website">');
    lines.push('');
    lines.push('<!-- Twitter -->');
    lines.push(`<meta name="twitter:card" content="${img ? 'summary_large_image' : 'summary'}">`);
    if (t) lines.push(`<meta name="twitter:title" content="${e(t)}">`);
    if (d) lines.push(`<meta name="twitter:description" content="${e(d)}">`);
    if (img) lines.push(`<meta name="twitter:image" content="${e(img)}">`);
    const out = lines.join('\n');
    box.innerHTML = `<textarea class="kt-input out" rows="14" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]); done(ctx); icons();
  }
  wrap.querySelectorAll('input').forEach(i => i.oninput = run);
  run();
});

/* ================================================================
   26. ROBOTS.TXT GENERATOR
   ================================================================ */
reg('robots-gen', function (mount, opt, ctx) {
  const wrap = el(`<div class="grid gap-3">
    <div><label class="kt-label">Preset</label><select class="kt-select mode"><option value="allow">Allow all</option><option value="disallow">Disallow all</option><option value="custom">Custom</option></select></div>
    <div><label class="kt-label">User-agent</label><input class="kt-input ua" value="*"></div>
    <div class="cust"><label class="kt-label">Disallow paths (one per line)</label><textarea class="kt-input paths" rows="4" spellcheck="false" placeholder="/admin/&#10;/private/"></textarea></div>
    <div><label class="kt-label">Sitemap URL (optional)</label><input class="kt-input sm" placeholder="https://example.com/sitemap.xml"></div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    const mode = wrap.querySelector('.mode').value, ua = wrap.querySelector('.ua').value.trim() || '*';
    wrap.querySelector('.cust').style.display = mode === 'custom' ? '' : 'none';
    const lines = [`User-agent: ${ua}`];
    if (mode === 'allow') lines.push('Disallow:');
    else if (mode === 'disallow') lines.push('Disallow: /');
    else {
      const paths = wrap.querySelector('.paths').value.split('\n').map(p => p.trim()).filter(Boolean);
      if (paths.length) paths.forEach(p => lines.push(`Disallow: ${p}`)); else lines.push('Disallow:');
    }
    const sm = wrap.querySelector('.sm').value.trim();
    if (sm) { lines.push(''); lines.push(`Sitemap: ${sm}`); }
    const out = lines.join('\n');
    box.innerHTML = `<textarea class="kt-input out" rows="8" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out), { label: 'Download', cls: 'btn-cta', icon: 'download', onClick: () => downloadBlob(new Blob([out], { type: 'text/plain' }), 'robots.txt') }]);
    done(ctx); icons();
  }
  wrap.querySelectorAll('input,select,textarea').forEach(e => e.oninput = run);
  wrap.querySelector('.mode').onchange = run;
  run();
});

/* ================================================================
   27. HTACCESS REDIRECT GENERATOR
   ================================================================ */
reg('htaccess-gen', function (mount, opt, ctx) {
  const wrap = el(`<div class="grid gap-3">
    <div><label class="kt-label">Preset</label><select class="kt-select preset">
      <option value="redirect">Single URL redirect</option>
      <option value="https">Force HTTPS</option>
      <option value="www">Non-www → www</option>
      <option value="nonwww">www → Non-www</option>
    </select></div>
    <div class="rd"><div class="grid sm:grid-cols-2 gap-3">
      <div><label class="kt-label">Old path (e.g. /old-page)</label><input class="kt-input oldp" value="/old-page"></div>
      <div><label class="kt-label">New URL</label><input class="kt-input newu" value="https://example.com/new-page"></div>
    </div>
    <div class="mt-2"><label class="kt-label">Redirect type</label><select class="kt-select rt"><option value="301">301 Permanent</option><option value="302">302 Temporary</option></select></div>
    </div>
  </div>`);
  mount.appendChild(wrap);
  const box = resultBox(mount);
  function run() {
    const preset = wrap.querySelector('.preset').value;
    wrap.querySelector('.rd').style.display = preset === 'redirect' ? '' : 'none';
    let out;
    if (preset === 'redirect') {
      const oldp = wrap.querySelector('.oldp').value.trim() || '/old', newu = wrap.querySelector('.newu').value.trim() || 'https://example.com/new', rt = wrap.querySelector('.rt').value;
      out = `Redirect ${rt} ${oldp} ${newu}`;
    } else if (preset === 'https') {
      out = `RewriteEngine On\nRewriteCond %{HTTPS} off\nRewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]`;
    } else if (preset === 'www') {
      out = `RewriteEngine On\nRewriteCond %{HTTP_HOST} !^www\\. [NC]\nRewriteRule ^(.*)$ https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]`;
    } else {
      out = `RewriteEngine On\nRewriteCond %{HTTP_HOST} ^www\\.(.*)$ [NC]\nRewriteRule ^(.*)$ https://%1%{REQUEST_URI} [L,R=301]`;
    }
    box.innerHTML = `<textarea class="kt-input out" rows="7" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]); done(ctx); icons();
  }
  wrap.querySelectorAll('input,select').forEach(e => e.oninput = run);
  wrap.querySelector('.preset').onchange = run;
  run();
});

/* ================================================================
   28. TEXT <-> BINARY / DECIMAL / HEX
   ================================================================ */
reg('text-binary', function (mount, opt, ctx) {
  const ta = textArea(mount, 5, 'Enter text or code...', 'Hello');
  const bar = el(`<div class="flex gap-2 mt-3 flex-wrap items-end">
    <div><label class="kt-label">Format</label><select class="kt-select fmt"><option value="bin">Binary</option><option value="dec">Decimal ASCII</option><option value="hex">Hexadecimal</option></select></div>
    <div><label class="kt-label">Mode</label><select class="kt-select md"><option value="enc">Text → code</option><option value="dec">Code → text</option></select></div>
    <button class="btn btn-primary go">Convert</button>
  </div>`);
  mount.appendChild(bar);
  const box = resultBox(mount);
  bar.querySelector('.go').onclick = () => {
    const fmt = bar.querySelector('.fmt').value, mode = bar.querySelector('.md').value, src = ta.value;
    let out;
    try {
      if (mode === 'enc') {
        const codes = Array.from(src).map(ch => ch.codePointAt(0));
        out = codes.map(c => fmt === 'bin' ? c.toString(2).padStart(8, '0') : fmt === 'hex' ? c.toString(16).padStart(2, '0') : c.toString(10)).join(' ');
      } else {
        const base = fmt === 'bin' ? 2 : fmt === 'hex' ? 16 : 10;
        out = src.trim().split(/\s+/).filter(Boolean).map(tok => String.fromCodePoint(parseInt(tok, base))).join('');
      }
    } catch (e) { toast('Invalid input: ' + e.message, true); return; }
    box.innerHTML = `<textarea class="kt-input out" rows="5" readonly spellcheck="false">${esc(out)}</textarea>`;
    setActions([copyBtn(out)]); done(ctx); icons();
  };
});

})();
