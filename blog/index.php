<?php
/** KRISHNA TOOLS — blog index: published posts, newest first. */
require_once __DIR__ . '/../includes/bootstrap.php';

$en = current_lang() === 'en';
$posts = all("SELECT * FROM " . tbl('blog_posts') . " WHERE is_published = 1 ORDER BY created_at DESC, id DESC");

$page_title = t('blog') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$page_desc  = $en ? 'Guides and tips on tools, CCTV, PDF, images and more.' : 'ટૂલ્સ, CCTV, PDF, ઇમેજ અને વધુ પર માર્ગદર્શિકા અને ટિપ્સ.';
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('blog')],
];
require __DIR__ . '/../includes/header.php';
?>
<div class="max-w-5xl mx-auto my-6">
  <h1 class="section-title text-2xl mb-1"><?= t('blog') ?></h1>
  <div class="divider-gold mb-5"></div>

  <?php if ($posts): ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($posts as $post): ?>
        <a href="<?= SITE_URL ?>/blog/<?= e($post['slug']) ?>" class="kt-card p-5 block hover:shadow-lg transition-shadow">
          <?php if (!empty($post['image'])): ?>
            <img src="<?= e($post['image']) ?>" alt="" class="w-full h-36 object-cover rounded-lg mb-3" loading="lazy" onerror="this.style.display='none'">
          <?php endif; ?>
          <div class="text-xs opacity-60 mb-1"><?= $post['created_at'] ? e(date('d M Y', strtotime($post['created_at']))) : '' ?></div>
          <h2 class="font-bold text-lg leading-snug mb-2"><?= e(loc($post, 'title')) ?></h2>
          <p class="text-sm opacity-70 line-clamp-3"><?= e($post['meta_desc']) ?></p>
          <span class="text-sm text-[color:var(--peacock-teal)] mt-3 inline-flex items-center gap-1">
            <?= $en ? 'Read more' : 'વધુ વાંચો' ?> <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="kt-card p-8 text-center">
      <div class="text-4xl mb-2">📝</div>
      <p class="opacity-60"><?= $en ? 'No posts yet. Check back soon.' : 'હજુ કોઈ પોસ્ટ નથી. જલદી પાછા આવો.' ?></p>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
