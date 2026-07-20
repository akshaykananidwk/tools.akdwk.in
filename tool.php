<?php
/** KRISHNA TOOLS — tool page router (/tool/<slug> → tool.php?slug=<slug>). */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/tool_wrapper.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');
render_tool_page($slug);
