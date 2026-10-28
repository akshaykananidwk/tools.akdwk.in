<?php
/**
 * KRISHNA TOOLS — contact inbox with unread count + mark read.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_admin();
require_once __DIR__ . '/layout.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'read' || $action === 'unread') {
            $mid = (int) ($_POST['msg_id'] ?? 0);
            if ($mid) {
                update('contact_messages', ['is_read' => $action === 'read' ? 1 : 0], ['id' => $mid]);
                $flash = 'Updated.';
            }
        } elseif ($action === 'read_all') {
            q("UPDATE " . tbl('contact_messages') . " SET is_read = 1 WHERE is_read = 0");
            $flash = 'All marked as read.';
        }
    } catch (Throwable $e) {
        kt_error_log('admin contact: ' . $e->getMessage());
        $flash = 'Something went wrong.';
    }
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

$msgs = []; $total = 0; $unread = 0;
try {
    $total  = (int) scalar("SELECT COUNT(*) FROM " . tbl('contact_messages'));
    $unread = (int) scalar("SELECT COUNT(*) FROM " . tbl('contact_messages') . " WHERE is_read = 0");
    $msgs   = all("SELECT * FROM " . tbl('contact_messages') . "
                   ORDER BY is_read ASC, id DESC LIMIT $perPage OFFSET $offset");
} catch (Throwable $e) {
    kt_error_log('admin contact list: ' . $e->getMessage());
}

admin_head('Contact');
?>
<?php if ($flash): ?>
  <div class="kt-card p-3 mb-4 text-sm" style="border-color:var(--gold)"><?= e($flash) ?></div>
<?php endif; ?>

<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
  <div class="flex items-center gap-2">
    <span class="badge-premium">Unread: <?= number_format($unread) ?></span>
    <span class="opacity-60 text-sm">Total: <?= number_format($total) ?></span>
  </div>
  <?php if ($unread > 0): ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read_all">
      <button class="btn btn-ghost text-sm"><i data-lucide="check-check" class="w-4 h-4"></i> Mark all as read</button>
    </form>
  <?php endif; ?>
</div>

<div class="space-y-3">
<?php if (!$msgs): ?>
  <div class="kt-card p-6 text-center opacity-60">No messages.</div>
<?php else: foreach ($msgs as $m):
    $isRead = (int) $m['is_read'] === 1; ?>
  <div class="kt-card p-4 <?= $isRead ? 'opacity-70' : '' ?>" style="<?= $isRead ? '' : 'border-color:var(--gold)' ?>">
    <div class="flex items-start justify-between gap-3 flex-wrap">
      <div>
        <div class="font-semibold flex items-center gap-2">
          <?php if (!$isRead): ?><span class="w-2 h-2 rounded-full" style="background:var(--saffron)"></span><?php endif; ?>
          <?= e($m['name']) ?>
        </div>
        <div class="text-xs opacity-60">
          <?= e($m['email'] ?? '') ?><?php if (!empty($m['phone'])): ?> · <?= e($m['phone']) ?><?php endif; ?> · <?= e($m['created_at'] ?? '') ?>
        </div>
      </div>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="msg_id" value="<?= (int) $m['id'] ?>">
        <input type="hidden" name="action" value="<?= $isRead ? 'unread' : 'read' ?>">
        <button class="btn btn-ghost !py-1 !px-3 text-sm"><?= $isRead ? 'Mark unread' : 'Mark read' ?></button>
      </form>
    </div>
    <p class="mt-2 text-sm whitespace-pre-wrap"><?= e($m['message'] ?? '') ?></p>
    <?php if (!empty($m['phone'])): ?>
      <a href="<?= SITE_URL ?>/admin/whatsapp.php" class="text-xs text-[color:var(--peacock-teal)] hover:underline mt-1 inline-block">Reply via WhatsApp →</a>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>
</div>
<?= admin_pager($page, $total, $perPage) ?>
<?php admin_foot(); ?>
