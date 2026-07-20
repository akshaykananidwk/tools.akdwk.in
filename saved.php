<?php
/** KRISHNA TOOLS — saved files: list non-expired files for the user. */
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$en = current_lang() === 'en';

// Only files that have not yet expired.
$files = all("SELECT * FROM " . tbl('saved_files') . "
              WHERE user_id = :u AND (expires_at IS NULL OR expires_at > NOW())
              ORDER BY id DESC", [':u' => $user['id']]);

/** Human-friendly file size. */
function kt_fmt_size(int $bytes): string {
    if ($bytes <= 0) return '0 KB';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
}

$page_title = ($en ? 'Saved Files' : 'સેવ્ડ ફાઇલ્સ') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('dashboard'), 'url' => SITE_URL . '/dashboard.php'],
    ['label' => $en ? 'Saved Files' : 'સેવ્ડ ફાઇલ્સ'],
];
require __DIR__ . '/includes/header.php';
?>
<div class="max-w-4xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1"><?= $en ? 'Saved Files' : 'સેવ્ડ ફાઇલ્સ' ?></h1>
  <div class="divider-gold mb-3"></div>
  <p class="text-sm opacity-70 mb-5">
    <i data-lucide="info" class="w-4 h-4 inline"></i>
    <?= $en ? 'Files are automatically deleted after they expire. Download what you need in time.' : 'ફાઇલો એક્સપાયર થયા પછી આપોઆપ ડિલીટ થાય છે. સમયસર ડાઉનલોડ કરી લો.' ?>
  </p>

  <?php if ($files): ?>
    <div class="grid gap-3">
      <?php foreach ($files as $f):
        $secsLeft = $f['expires_at'] ? (strtotime($f['expires_at']) - time()) : null;
        $hoursLeft = $secsLeft !== null ? max(0, (int) floor($secsLeft / 3600)) : null;
        $minsLeft  = $secsLeft !== null ? max(0, (int) floor(($secsLeft % 3600) / 60)) : null;
      ?>
        <div class="kt-card p-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3 min-w-0">
            <i data-lucide="file" class="w-6 h-6 opacity-60 shrink-0"></i>
            <div class="min-w-0">
              <div class="font-semibold truncate"><?= e($f['filename'] ?: basename((string) $f['path'])) ?></div>
              <div class="text-xs opacity-60">
                <?= e(kt_fmt_size((int) $f['size'])) ?>
                <?php if ($secsLeft !== null): ?>
                  · <span style="color:var(--saffron)">
                    <?= $en ? 'Expires in' : 'બાકી' ?>
                    <?= $hoursLeft ?><?= $en ? 'h' : 'ક' ?> <?= $minsLeft ?><?= $en ? 'm' : 'મિ' ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php // path is repo-relative; serve it under SITE_URL. ?>
          <a href="<?= SITE_URL . '/' . e(ltrim((string) $f['path'], '/')) ?>" download
             class="btn btn-primary text-sm shrink-0">
            <i data-lucide="download" class="w-4 h-4"></i> <?= t('download') ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="kt-card p-8 text-center">
      <div class="text-4xl mb-2">🗂️</div>
      <p class="opacity-60"><?= $en ? 'No saved files. Files you save from tools appear here (Yearly plan).' : 'કોઈ સેવ્ડ ફાઇલ નથી. ટૂલ્સમાંથી સેવ કરેલી ફાઇલો અહીં દેખાશે (વાર્ષિક પ્લાન).' ?></p>
      <a href="<?= SITE_URL ?>/" class="btn btn-cta mt-4"><?= t('all_tools') ?></a>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
