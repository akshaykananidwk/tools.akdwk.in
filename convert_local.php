<?php
/**
 * KRISHNA TOOLS — the site is English-only. The old Gujarati / Hindi converter URLs
 * (/gu/convert/<slug>, /hi/convert/<slug>) redirect permanently to the English page.
 * .htaccess does this directly; this file covers servers where that rule is missing.
 */
require_once __DIR__ . '/includes/bootstrap.php';
$slug = preg_replace('/[^A-Za-z0-9.,%\-]/', '', (string) ($_GET['slug'] ?? ''));
header('Location: ' . SITE_URL . '/convert/' . $slug, true, 301);
exit;
