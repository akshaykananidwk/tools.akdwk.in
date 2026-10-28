<?php
/**
 * KRISHNA TOOLS — installation wizard (5 steps, bilingual, themed).
 * Upload files, open /install/, and everything is set up automatically.
 */
session_start();
require_once __DIR__ . '/lib.php';

// If already installed, redirect home.
if (install_is_locked()) {
    header('Location: ../?installed=1');
    exit;
}
$reqs = install_requirements();
$hasFail = false;
foreach ($reqs as $r) if ($r[0] === 'fail') $hasFail = true;
$siteUrl = install_site_url();
?><!DOCTYPE html>
<html lang="gu">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Krishna Tools — Installation</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Baloo+Bhai+2:wght@500;700;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/krishna.css">
<style>body{font-family:'Baloo Bhai 2','Inter',sans-serif}</style>
</head>
<body class="morpankh-bg min-h-screen">
<div class="hero-krishna">
  <div class="max-w-3xl mx-auto px-4 py-8 text-center">
    <div class="text-5xl mb-2">🦚</div>
    <h1 class="brand-title text-3xl md:text-4xl">Krishna Tools Installation</h1>
    <p class="opacity-70">Krishna Tools Setup Wizard · AK Computer, Dwarka</p>
  </div>
</div>

<div class="max-w-3xl mx-auto px-4 pb-16">
  <!-- Step indicator -->
  <div class="flex items-center justify-between mb-6 text-xs" id="stepBar">
    <?php foreach (['System Check', 'Database', 'Site & Admin', 'Integrations', 'Install'] as $i => $label): ?>
      <div class="flex-1 text-center step-ind" data-s="<?= $i + 1 ?>">
        <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center font-bold border-2 dot" style="border-color:var(--border)"><?= $i + 1 ?></div>
        <div class="mt-1 opacity-70"><?= $label ?></div>
      </div>
      <?php if ($i < 4): ?><div class="h-0.5 w-6 mb-4" style="background:var(--border)"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- STEP 1: Requirements -->
  <section class="kt-card p-5 step-panel" data-step="1">
    <h2 class="section-title text-xl mb-1">1. System Requirements Check</h2>
    <div class="divider-gold mb-4"></div>
    <table class="w-full text-sm">
      <?php foreach ($reqs as $key => $r):
        $icon = $r[0] === 'ok' ? '✅' : ($r[0] === 'warn' ? '⚠️' : '❌');
        $col = $r[0] === 'ok' ? 'var(--morpankh-green)' : ($r[0] === 'warn' ? 'var(--gold)' : '#DC2626'); ?>
        <tr class="border-b" style="border-color:var(--border)">
          <td class="py-2 w-8 text-lg"><?= $icon ?></td>
          <td class="py-2"><?= htmlspecialchars($r[1]) ?>
            <?php if ($r[0] !== 'ok' && !empty($r[2])): ?><div class="text-xs opacity-70 mt-0.5" style="color:<?= $col ?>"><?= htmlspecialchars($r[2]) ?></div><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    <?php if ($hasFail): ?>
      <div class="mt-4 p-3 rounded-lg text-sm" style="background:rgba(220,38,38,.1);color:#DC2626">
        ❌ Some requirements are not met. Fix the items above, then click <b>Check Again</b>.</div>
    <?php endif; ?>
    <div class="flex gap-2 mt-5">
      <button onclick="location.reload()" class="btn btn-ghost"><i>↻</i> Check Again</button>
      <button onclick="goStep(2)" class="btn btn-primary <?= $hasFail ? 'opacity-40 pointer-events-none' : '' ?>">Continue →</button>
    </div>
  </section>

  <!-- STEP 2: Database -->
  <section class="kt-card p-5 step-panel hidden" data-step="2">
    <h2 class="section-title text-xl mb-1">2. Database Connection</h2>
    <div class="divider-gold mb-4"></div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label class="kt-label">Database Host <span class="opacity-50">(localhost)</span></label><input class="kt-input" id="db_host" value="localhost"></div>
      <div><label class="kt-label">Table Prefix</label><input class="kt-input" id="db_prefix" value="kt_"></div>
      <div><label class="kt-label">Database Name <span class="opacity-50">(akdwk_krishnatools)</span></label><input class="kt-input" id="db_name" placeholder="akdwk_krishnatools"></div>
      <div><label class="kt-label">Database Username <span class="opacity-50">(akdwk_ktuser)</span></label><input class="kt-input" id="db_user" placeholder="akdwk_ktuser"></div>
      <div class="sm:col-span-2"><label class="kt-label">Database Password</label><input type="password" class="kt-input" id="db_pass"></div>
    </div>
    <div id="dbMsg" class="mt-3 text-sm"></div>
    <div class="flex gap-2 mt-5">
      <button onclick="goStep(1)" class="btn btn-ghost">← Back</button>
      <button onclick="testDb()" class="btn btn-gold">Test Connection</button>
      <button onclick="goStep(3)" id="db_next" class="btn btn-primary opacity-40 pointer-events-none">Continue →</button>
    </div>
  </section>

  <!-- STEP 3: Site + Admin -->
  <section class="kt-card p-5 step-panel hidden" data-step="3">
    <h2 class="section-title text-xl mb-1">3. Site & Admin Setup</h2>
    <div class="divider-gold mb-4"></div>
    <div class="grid sm:grid-cols-2 gap-4">
      <div><label class="kt-label">Site Name</label><input class="kt-input" id="site_name" value="Krishna Tools"></div>
      <div><label class="kt-label">Site URL</label><input class="kt-input" id="site_url" value="<?= htmlspecialchars($siteUrl) ?>"></div>
      <div><label class="kt-label">Shop Name</label><input class="kt-input" id="shop_name" value="AK Computer"></div>
      <div><label class="kt-label">Shop Address</label><input class="kt-input" id="shop_address" value="Dwarka, Gujarat"></div>
      <div><label class="kt-label">Shop Phone</label><input class="kt-input" id="shop_phone" placeholder="9978123146"></div>
      <div><label class="kt-label">Shop WhatsApp</label><input class="kt-input" id="shop_whatsapp" placeholder="919978123146"></div>
      <div class="sm:col-span-2"><label class="kt-label">GSTIN <span class="opacity-50">(optional)</span></label><input class="kt-input" id="gstin"></div>
      <hr class="sm:col-span-2 opacity-20">
      <div><label class="kt-label">Admin Full Name</label><input class="kt-input" id="admin_name" value="Admin"></div>
      <div><label class="kt-label">Admin Email (login)</label><input type="email" class="kt-input" id="admin_email"></div>
      <div><label class="kt-label">Admin Password <span class="opacity-50">(min 8)</span></label><input type="password" class="kt-input" id="admin_password" oninput="pwMeter(this.value)"><div class="h-1.5 rounded mt-1" id="pwbar" style="background:rgba(0,0,0,.1)"></div></div>
      <div><label class="kt-label">Confirm Password</label><input type="password" class="kt-input" id="admin_password2"></div>
      <div><label class="kt-label">Default Language</label><select class="kt-select" id="lang"><option value="en">English</option><option value="gu">Gujarati</option></select></div>
      <div><label class="kt-label">Timezone</label><input class="kt-input" id="tz" value="Asia/Kolkata"></div>
    </div>
    <div id="siteMsg" class="mt-3 text-sm"></div>
    <div class="flex gap-2 mt-5">
      <button onclick="goStep(2)" class="btn btn-ghost">← Back</button>
      <button onclick="validSite()&&goStep(4)" class="btn btn-primary">Continue →</button>
    </div>
  </section>

  <!-- STEP 4: Integrations -->
  <section class="kt-card p-5 step-panel hidden" data-step="4">
    <h2 class="section-title text-xl mb-1">4. Integrations <span class="text-sm opacity-60">(all optional)</span></h2>
    <div class="divider-gold mb-4"></div>

    <details class="mb-3" open><summary class="font-semibold cursor-pointer py-2">💳 Razorpay</summary>
      <div class="grid sm:grid-cols-2 gap-3 mt-2">
        <input class="kt-input" id="rzp_key" placeholder="Key ID (rzp_live_...)">
        <input class="kt-input" id="rzp_secret" placeholder="Key Secret">
        <div class="sm:col-span-2"><button onclick="testRzp()" class="btn btn-gold text-sm">Test</button> <span id="rzpMsg" class="text-sm"></span></div>
      </div></details>

    <details class="mb-3"><summary class="font-semibold cursor-pointer py-2">✉️ SMTP Email</summary>
      <div class="grid sm:grid-cols-2 gap-3 mt-2">
        <input class="kt-input" id="smtp_host" placeholder="Host (smtp.gmail.com)">
        <input class="kt-input" id="smtp_port" placeholder="Port (587)" value="587">
        <input class="kt-input" id="smtp_user" placeholder="Username">
        <input type="password" class="kt-input" id="smtp_pass" placeholder="Password">
        <input class="kt-input sm:col-span-2" id="smtp_from" placeholder="From Name" value="Krishna Tools">
        <div class="sm:col-span-2"><button onclick="testSmtp()" class="btn btn-gold text-sm">Test Connection</button> <span id="smtpMsg" class="text-sm"></span></div>
      </div></details>

    <details class="mb-3" open><summary class="font-semibold cursor-pointer py-2">🟢 WhatsApp API (bulk.akdwk.in)</summary>
      <div class="grid sm:grid-cols-2 gap-3 mt-2">
        <input class="kt-input" id="wa_base" value="https://bulk.akdwk.in/api.php">
        <input class="kt-input" id="wa_inbound" value="https://bulk.akdwk.in/api/webhook_inbound.php">
        <input class="kt-input" id="wa_key" value="7016034943" placeholder="API Key">
        <input class="kt-input" id="wa_session" value="9978123146" placeholder="Session ID">
        <input class="kt-input" id="wa_sender" value="919978123146" placeholder="Sender Number">
        <input class="kt-input" id="wa_testnum" placeholder="Test number (919978123146)">
        <div class="sm:col-span-2"><button onclick="testWa()" class="btn btn-wa text-sm">Send WhatsApp Test Message</button> <span id="waMsg" class="text-sm"></span></div>
        <div class="sm:col-span-2 text-xs opacity-60" id="waRaw"></div>
      </div></details>

    <details class="mb-3"><summary class="font-semibold cursor-pointer py-2">📊 Google Analytics</summary>
      <input class="kt-input mt-2" id="ga_id" placeholder="G-XXXXXXXXXX"></details>

    <div class="flex gap-2 mt-5">
      <button onclick="goStep(3)" class="btn btn-ghost">← Back</button>
      <button onclick="goStep(5)" class="btn btn-primary">Continue →</button>
    </div>
  </section>

  <!-- STEP 5: Install -->
  <section class="kt-card p-5 step-panel hidden" data-step="5">
    <h2 class="section-title text-xl mb-1">5. Install</h2>
    <div class="divider-gold mb-4"></div>
    <div id="installLog" class="font-mono text-sm space-y-1 min-h-[200px]"></div>
    <div id="installDone" class="hidden"></div>
    <div class="flex gap-2 mt-5" id="installBtns">
      <button onclick="goStep(4)" class="btn btn-ghost">← Back</button>
      <button onclick="runInstall()" id="startBtn" class="btn btn-cta text-lg">🦚 Start Installation</button>
    </div>
  </section>
</div>

<script>
const AJAX = 'ajax.php';
async function post(data) {
  const fd = new FormData(); Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  const r = await fetch(AJAX, { method: 'POST', body: fd });
  return r.json();
}
function goStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.toggle('hidden', +p.dataset.step !== n));
  document.querySelectorAll('.step-ind').forEach(i => {
    const s = +i.dataset.s, dot = i.querySelector('.dot');
    dot.style.background = s <= n ? 'linear-gradient(135deg,var(--krishna-blue),var(--peacock-teal))' : '';
    dot.style.color = s <= n ? '#fff' : ''; dot.style.borderColor = s <= n ? 'var(--peacock-teal)' : 'var(--border)';
  });
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
goStep(1);

function pwMeter(v) {
  let s = Math.min(100, v.length * 8 + (/[A-Z]/.test(v) ? 15 : 0) + (/[0-9]/.test(v) ? 15 : 0) + (/[^A-Za-z0-9]/.test(v) ? 20 : 0));
  const c = s > 70 ? '#059669' : s > 40 ? '#F59E0B' : '#EA580C';
  document.getElementById('pwbar').style.background = `linear-gradient(90deg,${c} ${s}%,rgba(0,0,0,.1) ${s}%)`;
}
async function testDb() {
  const msg = document.getElementById('dbMsg'); msg.innerHTML = '<span class="opacity-60">⏳ Testing...</span>';
  const d = await post({ action: 'db_test', host: db_host.value, name: db_name.value, user: db_user.value, pass: db_pass.value, prefix: db_prefix.value });
  msg.innerHTML = `<span style="color:${d.ok ? 'var(--morpankh-green)' : '#DC2626'}">${d.ok ? d.msg : d.error}</span>`;
  const nx = document.getElementById('db_next');
  nx.classList.toggle('opacity-40', !d.ok); nx.classList.toggle('pointer-events-none', !d.ok);
}
function validSite() {
  const m = document.getElementById('siteMsg');
  if (!/^[^@]+@[^@]+\.[^@]+$/.test(admin_email.value)) { m.innerHTML = '<span style="color:#DC2626">Enter a valid email</span>'; return false; }
  if (admin_password.value.length < 8) { m.innerHTML = '<span style="color:#DC2626">Password must be at least 8 characters</span>'; return false; }
  if (admin_password.value !== admin_password2.value) { m.innerHTML = '<span style="color:#DC2626">Passwords do not match</span>'; return false; }
  m.innerHTML = ''; return true;
}
async function testWa() {
  const m = document.getElementById('waMsg'); m.textContent = '⏳...';
  const d = await post({ action: 'wa_test', base: wa_base.value, key: wa_key.value, session: wa_session.value, number: wa_testnum.value || wa_sender.value });
  m.innerHTML = d.ok ? '<span style="color:var(--morpankh-green)">✅ Sent!</span>' : `<span style="color:#DC2626">${d.error || '❌ ' + (d.msg || 'Failed')}</span>`;
  document.getElementById('waRaw').textContent = d.raw || d.msg || '';
}
async function testSmtp() {
  const m = document.getElementById('smtpMsg'); m.textContent = '⏳...';
  const d = await post({ action: 'smtp_test', host: smtp_host.value, port: smtp_port.value });
  m.innerHTML = `<span style="color:${d.ok ? 'var(--morpankh-green)' : '#DC2626'}">${d.ok ? '✅ ' + d.msg : d.error}</span>`;
}
async function testRzp() {
  const m = document.getElementById('rzpMsg'); m.textContent = '⏳...';
  const d = await post({ action: 'razorpay_test', key: rzp_key.value, secret: rzp_secret.value });
  m.innerHTML = `<span style="color:${d.ok ? 'var(--morpankh-green)' : '#DC2626'}">${d.ok ? d.msg : (d.error || d.msg)}</span>`;
}

const LABELS = {1:'Creating database tables...',2:'Adding categories...',3:'Registering tools...',4:'Creating plans...',5:'Adding WhatsApp templates...',6:'Creating admin account...',7:'Saving settings...',8:'Writing config/config.php...',9:'Creating .htaccess...',10:'Creating folder structure...',11:'Creating install.lock...'};
function collectWizard() {
  return {
    site_name: site_name.value, site_url: site_url.value, lang: lang.value, tz: tz.value,
    shop_name: shop_name.value, shop_address: shop_address.value, shop_phone: shop_phone.value,
    shop_whatsapp: shop_whatsapp.value, gstin: gstin.value,
    admin_name: admin_name.value, admin_email: admin_email.value, admin_password: admin_password.value, admin_phone: shop_whatsapp.value,
    rzp_key: rzp_key.value, rzp_secret: rzp_secret.value,
    smtp_host: smtp_host.value, smtp_port: smtp_port.value, smtp_user: smtp_user.value, smtp_pass: smtp_pass.value, smtp_from: smtp_from.value,
    wa_base: wa_base.value, wa_inbound: wa_inbound.value, wa_key: wa_key.value, wa_session: wa_session.value, wa_sender: wa_sender.value,
    ga_id: ga_id.value,
  };
}
async function runInstall() {
  document.getElementById('startBtn').disabled = true;
  const log = document.getElementById('installLog');
  const wiz = collectWizard();
  let step = 1;
  while (step > 0) {
    const line = document.createElement('div');
    line.innerHTML = `<span class="chakra-loader inline-block align-middle" style="width:16px;height:16px;border-width:3px"></span> ${LABELS[step] || '...'}`;
    log.appendChild(line);
    const payload = step === 1 ? Object.assign({ action: 'install', step }, wiz) : { action: 'install', step };
    const d = await post(payload);
    if (!d.ok) { line.innerHTML = `❌ ${d.error}`; document.getElementById('startBtn').disabled = false; return; }
    line.innerHTML = `${d.msg}`;
    if (d.done) { finishInstall(d); return; }
    step = d.next;
  }
}
function finishInstall(d) {
  document.getElementById('installBtns').classList.add('hidden');
  const box = document.getElementById('installDone'); box.classList.remove('hidden');
  const cron = `* * * * * php ${'{'}HOME{'}'}/public_html/cron/run.php >/dev/null 2>&1`;
  box.innerHTML = `
    <div class="text-center py-4"><div class="text-6xl">🦚</div>
      <h3 class="brand-title text-2xl mt-2">Installation complete! Hare Krishna</h3></div>
    <div class="kt-card p-4 mt-2 text-sm space-y-2">
      <div>🔐 <b>Admin panel:</b> <a class="text-[color:var(--peacock-teal)] underline" href="${d.admin_url}">${d.admin_url}</a></div>
      <div>📧 <b>Login email:</b> ${d.admin_email}</div>
      <div>🔗 <b>WhatsApp Webhook URL</b> (paste it in the bulk.akdwk.in panel):<br><code class="text-xs break-all">${d.webhook}</code></div>
      <div>⏱️ <b>CRON command</b> (add it in cPanel > Cron Jobs):<pre class="text-xs mt-1 p-2 rounded" style="background:rgba(0,0,0,.05);white-space:pre-wrap">${cron}</pre></div>
    </div>
    <div class="p-3 rounded-lg mt-3 text-sm" style="background:rgba(234,88,12,.1);color:var(--saffron)">
      ⚠️ For security, delete the <b>/install/</b> folder now.</div>
    <div class="flex gap-2 mt-4 flex-wrap">
      <a href="../" class="btn btn-primary">Open Website</a>
      <a href="${d.admin_url}" class="btn btn-gold">Admin Panel</a>
      <button onclick="autoDelete()" class="btn btn-ghost">🗑️ Auto Delete</button>
    </div>`;
  window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}
async function autoDelete() {
  const d = await post({ action: 'auto_delete' });
  alert(d.msg || 'Done'); location.href = '../';
}
</script>
</body>
</html>
