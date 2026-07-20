<?php
/** KRISHNA TOOLS — single blog post (clean URL /blog/<slug> via .htaccess). */
require_once __DIR__ . '/../includes/bootstrap.php';

$en  = current_lang() === 'en';
$slug = trim($_GET['slug'] ?? '');

$post = $slug !== ''
    ? one("SELECT * FROM " . tbl('blog_posts') . " WHERE slug = :s AND is_published = 1", [':s' => $slug])
    : null;

if (!$post) {
    http_response_code(404);
    $page_title = ($en ? 'Not found' : 'મળ્યું નથી') . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
    require __DIR__ . '/../includes/header.php';
    echo '<div class="max-w-2xl mx-auto my-16 text-center">'
       . '<div class="text-5xl mb-3">🦚</div>'
       . '<h1 class="section-title text-2xl mb-2">' . ($en ? 'Post not found' : 'પોસ્ટ મળી નથી') . '</h1>'
       . '<a href="' . SITE_URL . '/blog/" class="btn btn-primary mt-4">' . t('blog') . '</a></div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$title   = loc($post, 'title');
$content = loc($post, 'content');
$url     = SITE_URL . '/blog/' . $post['slug'];

// Related: up to 3 other published posts.
$related = all("SELECT slug, title_gu, title_en, meta_desc FROM " . tbl('blog_posts') . "
                WHERE is_published = 1 AND id <> :id ORDER BY created_at DESC, id DESC LIMIT 3",
               [':id' => $post['id']]);

$page_title = $title . ' — ' . setting('site_name', 'કૃષ્ણા ટૂલ્સ');
$page_desc  = $post['meta_desc'] ?: mb_substr(strip_tags($content), 0, 155);
if (!empty($post['image'])) $page_og = $post['image'];
$breadcrumb = [
    ['label' => t('home'), 'url' => SITE_URL . '/'],
    ['label' => t('blog'), 'url' => SITE_URL . '/blog/'],
    ['label' => $title],
];

// Structured data: Article + BreadcrumbList JSON-LD.
$published = $post['created_at'] ? date('c', strtotime($post['created_at'])) : date('c');
$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Article',
            'headline' => $title,
            'description' => $page_desc,
            'datePublished' => $published,
            'dateModified' => $published,
            'mainEntityOfPage' => $url,
            'author' => ['@type' => 'Organization', 'name' => setting('shop_name', 'AK Computer')],
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('site_name', 'કૃષ્ણા ટૂલ્સ'),
                'logo' => ['@type' => 'ImageObject', 'url' => SITE_URL . '/assets/img/logo/logo.svg'],
            ],
        ] + (!empty($post['image']) ? ['image' => $post['image']] : []),
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => t('home'), 'item' => SITE_URL . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => t('blog'), 'item' => SITE_URL . '/blog/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $url],
            ],
        ],
    ],
];
$extra_head = '<script type="application/ld+json">' . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';

require __DIR__ . '/../includes/header.php';
?>
<article class="max-w-3xl mx-auto my-6">
  <div class="text-xs opacity-60 mb-2"><?= $post['created_at'] ? e(date('d M Y', strtotime($post['created_at']))) : '' ?></div>
  <h1 class="section-title text-3xl leading-tight mb-2"><?= e($title) ?></h1>
  <div class="divider-gold mb-5"></div>

  <?php if (!empty($post['image'])): ?>
    <img src="<?= e($post['image']) ?>" alt="<?= e($title) ?>" class="w-full rounded-xl mb-5" loading="lazy" onerror="this.style.display='none'">
  <?php endif; ?>

  <?php // Content is plain text — escape then preserve line breaks. ?>
  <div class="kt-card p-6 leading-relaxed text-[15px] opacity-90">
    <?= nl2br(e($content)) ?>
  </div>

  <div class="mt-6">
    <a href="<?= SITE_URL ?>/blog/" class="btn btn-ghost text-sm"><i data-lucide="arrow-left" class="w-4 h-4"></i> <?= t('blog') ?></a>
  </div>

  <?php if ($related): ?>
    <div class="mt-10">
      <h2 class="font-bold text-lg mb-3"><?= $en ? 'Related posts' : 'સંબંધિત પોસ્ટ' ?></h2>
      <div class="grid sm:grid-cols-3 gap-4">
        <?php foreach ($related as $r): ?>
          <a href="<?= SITE_URL ?>/blog/<?= e($r['slug']) ?>" class="kt-card p-4 block hover:shadow-lg transition-shadow">
            <h3 class="font-semibold text-sm leading-snug"><?= e(loc($r, 'title')) ?></h3>
            <p class="text-xs opacity-60 mt-1 line-clamp-2"><?= e($r['meta_desc']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</article>
<?php require __DIR__ . '/../includes/footer.php'; ?>
