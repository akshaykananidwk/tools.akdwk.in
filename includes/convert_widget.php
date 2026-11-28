<?php
/**
 * KRISHNA TOOLS — the live converter box used on /convert/… and /gu|hi/convert/… pages.
 *
 * Region-dependent units (maund, bigha…) get a region drop-down. Changing it recalculates the box
 * and every element marked data-cv="<value>" (the answer and the table), and rewrites the
 * "value used" note, all in the browser. The server HTML always shows the default region.
 */
require_once __DIR__ . '/regional_units.php';

/** Labels for the box in each language. */
function kt_cw_labels(string $lang): array {
    return [
        'en' => ['from' => 'From', 'to' => 'To', 'swap' => 'Swap', 'copy' => 'Copy', 'region' => 'Region for %s', 'used' => 'Value used: 1 %s = %s %s — %s.'],
        'gu' => ['from' => 'આમાંથી', 'to' => 'આમાં', 'swap' => 'અદલબદલ', 'copy' => 'કૉપી', 'region' => '%sનો વિસ્તાર', 'used' => 'વાપરેલી કિંમત: 1 %s = %s %s — %s.'],
        'hi' => ['from' => 'इससे', 'to' => 'इसमें', 'swap' => 'अदला-बदली', 'copy' => 'कॉपी', 'region' => '%s का क्षेत्र', 'used' => 'इस्तेमाल की गई कीमत: 1 %s = %s %s — %s।'],
    ][$lang] ?? kt_cw_labels('en');
}

/**
 * The "value used" note for a regional unit in its default region, e.g.
 * "Value used: 1 Maund = 20 kg (Gujarat (market yard))." Empty string for normal units.
 */
function kt_region_note(string $unit, string $cat, string $lang, callable $name): string {
    $d = gen_region_default($unit);
    if (!$d) return '';
    $base = gen_unit_categories()[$cat]['base'];
    $L = kt_cw_labels($lang);
    $bf = gen_unit_categories()[$cat]['units'][$base][0];
    $baseName = $cat === 'area' ? ($lang === 'en' ? 'sq ft' : $name('square-foot')) : $name($base);
    $val = $cat === 'area' ? $d['f'] / KT_SQFT : $d['f'] / $bf;
    return sprintf($L['used'], $name($unit), gen_fmt($val), $baseName, $d[$lang] ?? $d['en']);
}

/**
 * Print the converter box.
 * $o: cat, from, to, val, lang, names (unit key => label shown in the drop-downs), sym (unit key => symbol)
 */
function kt_convert_widget(array $o): void {
    $cat = $o['cat']; $catMeta = gen_unit_categories()[$cat]; $lang = $o['lang'] ?? 'en';
    $L = kt_cw_labels($lang);
    $names = $o['names']; $sym = $o['sym'];
    $units = [];
    foreach ($catMeta['units'] as $k => $u) $units[$k] = ['f' => $u[0], 'n' => $names[$k] ?? $u[1], 's' => $sym[$k] ?? $u[2]];
    $regions = gen_regions_for(array_unique([$o['from'], $o['to']]), $lang);
    $baseLabel = $cat === 'area' ? ['f' => KT_SQFT, 'n' => $sym['square-foot'] ?? 'sq ft'] : ['f' => $catMeta['units'][$catMeta['base']][0], 'n' => $sym[$catMeta['base']] ?? $catMeta['units'][$catMeta['base']][2]];
    $answer = gen_convert($cat, (float) $o['val'], $o['from'], $o['to']);
    ?>
    <div class="kt-card p-5 mb-6" id="genConvert"
         data-special="<?= e($catMeta['special'] ?? '') ?>" data-from="<?= e($o['from']) ?>" data-to="<?= e($o['to']) ?>"
         data-units='<?= e(json_encode($units, JSON_UNESCAPED_UNICODE)) ?>'
         data-regions='<?= e(json_encode($regions, JSON_UNESCAPED_UNICODE)) ?>'
         data-base='<?= e(json_encode($baseLabel, JSON_UNESCAPED_UNICODE)) ?>'
         data-used="<?= e($L['used']) ?>">
      <?php foreach ($regions as $u => $R): ?>
        <label class="kt-label"><?= e(sprintf($L['region'], $units[$u]['n'])) ?></label>
        <select class="kt-select gc-region mb-1" data-unit="<?= e($u) ?>"><?php foreach ($R['list'] as $rk => $r): ?><option value="<?= e($rk) ?>" <?= $rk === $R['def'] ? 'selected' : '' ?>><?= e($r['l']) ?></option><?php endforeach; ?></select>
        <p class="text-xs opacity-75 mb-3 gc-used" data-unit="<?= e($u) ?>"><?= e(kt_region_note($u, $cat, $lang, fn($k) => $lang === 'en' ? ($k === $u ? $units[$k]['n'] : $units[$k]['s']) : $units[$k]['n'])) ?></p>
      <?php endforeach; ?>
      <div class="grid sm:grid-cols-[1fr,auto,1fr] gap-3 items-end">
        <div>
          <label class="kt-label"><?= e($L['from']) ?></label>
          <input type="number" class="kt-input gc-val" value="<?= e($o['val']) ?>" step="any" aria-label="<?= e($L['from']) ?>">
          <select class="kt-select gc-from mt-2" aria-label="<?= e($L['from']) ?>"><?php foreach ($units as $k => $u): ?><option value="<?= e($k) ?>" <?= $k === $o['from'] ? 'selected' : '' ?>><?= e($u['n']) ?><?= $u['s'] !== $u['n'] ? ' (' . e($u['s']) . ')' : '' ?></option><?php endforeach; ?></select>
        </div>
        <button class="btn btn-ghost gc-swap mb-1" title="<?= e($L['swap']) ?>" aria-label="<?= e($L['swap']) ?>"><i data-lucide="arrow-left-right" class="w-4 h-4"></i></button>
        <div>
          <label class="kt-label"><?= e($L['to']) ?></label>
          <input type="text" class="kt-input gc-out" readonly aria-label="<?= e($L['to']) ?>">
          <select class="kt-select gc-to mt-2" aria-label="<?= e($L['to']) ?>"><?php foreach ($units as $k => $u): ?><option value="<?= e($k) ?>" <?= $k === $o['to'] ? 'selected' : '' ?>><?= e($u['n']) ?><?= $u['s'] !== $u['n'] ? ' (' . e($u['s']) . ')' : '' ?></option><?php endforeach; ?></select>
        </div>
      </div>
      <div class="mt-4 p-4 rounded-xl text-center" style="background:linear-gradient(135deg,rgba(30,58,138,.08),rgba(5,150,105,.08))">
        <div class="text-2xl font-extrabold gc-result" style="color:var(--peacock-teal)"><?= gen_fmt((float) $o['val']) ?> <?= e($units[$o['from']]['s']) ?> = <?= gen_fmt($answer) ?> <?= e($units[$o['to']]['s']) ?></div>
      </div>
      <div class="flex flex-wrap gap-2 mt-4">
        <button class="btn btn-ghost text-sm gc-copy"><i data-lucide="copy" class="w-4 h-4"></i> <?= e($L['copy']) ?></button>
        <button class="btn btn-wa text-sm gc-wa"><i data-lucide="send" class="w-4 h-4"></i> WhatsApp</button>
      </div>
    </div>
<script>
(function () {
  const box = document.getElementById('genConvert');
  const units = JSON.parse(box.dataset.units), regions = JSON.parse(box.dataset.regions), base = JSON.parse(box.dataset.base);
  const special = box.dataset.special, pageFrom = box.dataset.from, pageTo = box.dataset.to;
  const valI = box.querySelector('.gc-val'), fromS = box.querySelector('.gc-from'),
        toS = box.querySelector('.gc-to'), outI = box.querySelector('.gc-out'),
        res = box.querySelector('.gc-result');
  function convert(v, from, to) {
    if (special === 'temp') {
      let c = from === 'celsius' ? v : from === 'fahrenheit' ? (v - 32) * 5 / 9 : from === 'kelvin' ? v - 273.15 : (v - 491.67) * 5 / 9;
      return to === 'celsius' ? c : to === 'fahrenheit' ? c * 9 / 5 + 32 : to === 'kelvin' ? c + 273.15 : (c + 273.15) * 9 / 5;
    }
    if (special === 'fuel') {
      const toKmL = (u, x) => u === 'liter-per-100km' ? (x === 0 ? 0 : 100 / x) : x * units[u].f;
      const kmL = toKmL(from, v);
      return to === 'liter-per-100km' ? (kmL === 0 ? 0 : 100 / kmL) : kmL / units[to].f;
    }
    return v * units[from].f / units[to].f;
  }
  function fmt(n) {
    if (!isFinite(n)) return '—';
    if (n === 0) return '0';
    const a = Math.abs(n);
    if (a >= 1e15 || (a < 1e-6)) return n.toExponential(6).replace(/\.?0+e/, 'e');
    const d = a >= 100 ? 2 : a >= 1 ? 4 : 8;
    return parseFloat(n.toFixed(d)).toLocaleString('en-US', { maximumFractionDigits: d });
  }
  let last = '';
  function run() {
    const v = parseFloat(valI.value) || 0, f = fromS.value, t = toS.value;
    const r = convert(v, f, t);
    outI.value = fmt(r);
    last = `${fmt(v)} ${units[f].s} = ${fmt(r)} ${units[t].s}`;
    res.textContent = last;
  }
  // Region drop-downs: change the unit's size, then refresh the box, the answer and the table.
  box.querySelectorAll('.gc-region').forEach(sel => sel.addEventListener('change', () => {
    const u = sel.dataset.unit, r = regions[u].list[sel.value];
    units[u].f = r.f;
    const note = box.querySelector('.gc-used[data-unit="' + u + '"]');
    if (note) note.textContent = box.dataset.used.replace('%s', units[u].n).replace('%s', fmt(r.f / base.f)).replace('%s', base.n).replace('%s', r.l);
    document.querySelectorAll('[data-cv]').forEach(el => { el.textContent = fmt(convert(parseFloat(el.dataset.cv), pageFrom, pageTo)); });
    run();
  }));
  valI.addEventListener('input', run); fromS.addEventListener('change', run); toS.addEventListener('change', run);
  box.querySelector('.gc-swap').addEventListener('click', () => { const a = fromS.value; fromS.value = toS.value; toS.value = a; run(); });
  box.querySelector('.gc-copy').addEventListener('click', () => window.ktCopy && window.ktCopy(last));
  box.querySelector('.gc-wa').addEventListener('click', () => window.ktWaShareText && window.ktWaShareText(last + '\n' + location.href));
  run();
})();
</script>
<?php
}

/** "English · ગુજરાતી · हिन्दी" links between the language versions of a converter page. */
function kt_lang_switch(string $slug, string $current): string {
    if (!function_exists('gen_local_exists') || !gen_local_exists($slug)) return '';
    $links = ['en' => ['English', SITE_URL . '/convert/' . $slug], 'gu' => ['ગુજરાતી', SITE_URL . '/gu/convert/' . $slug], 'hi' => ['हिन्दी', SITE_URL . '/hi/convert/' . $slug]];
    $out = [];
    foreach ($links as $l => [$label, $url]) {
        $out[] = $l === $current ? '<b aria-current="page">' . e($label) . '</b>'
            : '<a hreflang="' . $l . '" lang="' . $l . '" class="underline hover:text-[color:var(--saffron)]" href="' . e($url) . '">' . e($label) . '</a>';
    }
    return '<p class="text-sm mb-4 flex flex-wrap gap-x-3 gap-y-1 items-center"><span aria-hidden="true">🌐</span>' . implode(' <span class="opacity-40">·</span> ', $out) . '</p>';
}
