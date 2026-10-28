<?php
/**
 * KRISHNA TOOLS — installer AJAX endpoint.
 * Handles: db_test, wa_test, smtp_test, razorpay_test, install, auto_delete.
 * Everything runs in the installer's own session; no config.php required.
 */
session_start();
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');

if (install_is_locked()) {
    echo json_encode(['ok' => false, 'error' => 'Already installed']);
    exit;
}

$action = $_POST['action'] ?? '';
function out($d) { echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

try {
    switch ($action) {

    case 'db_test': {
        $db = [
            'host' => trim($_POST['host'] ?? 'localhost'),
            'name' => trim($_POST['name'] ?? ''),
            'user' => trim($_POST['user'] ?? ''),
            'pass' => $_POST['pass'] ?? '',
            'prefix' => trim($_POST['prefix'] ?? 'kt_') ?: 'kt_',
        ];
        try {
            $pdo = install_pdo($db);
            $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
            $_SESSION['kt_db'] = $db;
            out(['ok' => true, 'msg' => "Connection successful! Found MySQL version $ver ✅"]);
        } catch (PDOException $e) {
            // Friendlier Gujarati hints for common errors.
            $m = $e->getMessage();
            $hint = str_contains($m, 'Access denied') ? 'Wrong username or password.'
                : (str_contains($m, 'Unknown database') ? 'Wrong database name — create it in cPanel > MySQL Databases.'
                : (str_contains($m, 'getaddrinfo') || str_contains($m, 'Connection refused') ? 'Wrong host — usually use localhost.' : $m));
            out(['ok' => false, 'error' => "❌ Connection failed: $hint"]);
        }
    }

    case 'wa_test': {
        $base = trim($_POST['base'] ?? 'https://bulk.akdwk.in/api.php');
        $payload = json_encode([
            'api_key' => trim($_POST['key'] ?? ''),
            'session_id' => trim($_POST['session'] ?? ''),
            'number' => preg_replace('/\D/', '', $_POST['number'] ?? ''),
            'message' => 'Krishna Tools test message 🦚 — installation successful!',
        ], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($base);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
        $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch);
        if ($resp === false) out(['ok' => false, 'error' => "❌ cURL: $err"]);
        out(['ok' => $http >= 200 && $http < 300, 'msg' => "HTTP $http", 'raw' => mb_substr((string) $resp, 0, 500)]);
    }

    case 'smtp_test': {
        // Lightweight SMTP check via fsockopen (PHPMailer send happens post-install).
        $host = trim($_POST['host'] ?? ''); $port = (int) ($_POST['port'] ?? 587);
        if (!$host) out(['ok' => false, 'error' => 'Enter the SMTP host']);
        $fp = @fsockopen(($port == 465 ? 'ssl://' : '') . $host, $port, $e, $es, 8);
        if (!$fp) out(['ok' => false, 'error' => "❌ Could not connect: $es"]);
        $greet = fgets($fp, 512); fclose($fp);
        out(['ok' => str_starts_with(trim($greet), '220'), 'msg' => 'SMTP: ' . trim($greet)]);
    }

    case 'razorpay_test': {
        $key = trim($_POST['key'] ?? ''); $secret = trim($_POST['secret'] ?? '');
        if (!$key || !$secret) out(['ok' => false, 'error' => 'Enter Key ID and Secret']);
        $ch = curl_init('https://api.razorpay.com/v1/orders?count=1');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => "$key:$secret", CURLOPT_TIMEOUT => 15]);
        $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        out(['ok' => $http === 200, 'msg' => $http === 200 ? 'Razorpay keys are valid ✅' : "❌ HTTP $http — check your keys"]);
    }

    case 'install': {
        $db = $_SESSION['kt_db'] ?? null;
        if (!$db) out(['ok' => false, 'error' => 'Test the database first (Step 2)']);
        $step = (int) ($_POST['step'] ?? 0);
        $pdo = install_pdo($db);
        $prefix = $db['prefix'];

        // Rebuild the full config array from posted wizard data (kept in session between steps).
        if ($step === 1) {
            $_SESSION['kt_wizard'] = [
                'site' => [
                    'name' => trim($_POST['site_name'] ?? 'Krishna Tools'),
                    'url'  => rtrim(trim($_POST['site_url'] ?? install_site_url()), '/'),
                    'lang' => ($_POST['lang'] ?? 'en') === 'gu' ? 'gu' : 'en',
                    'tz'   => trim($_POST['tz'] ?? 'Asia/Kolkata'),
                ],
                'shop' => [
                    'name' => trim($_POST['shop_name'] ?? 'AK Computer'),
                    'address' => trim($_POST['shop_address'] ?? 'Dwarka, Gujarat'),
                    'phone' => trim($_POST['shop_phone'] ?? ''),
                    'whatsapp' => trim($_POST['shop_whatsapp'] ?? ''),
                    'gstin' => trim($_POST['gstin'] ?? ''),
                ],
                'admin' => [
                    'name' => trim($_POST['admin_name'] ?? 'Admin'),
                    'email' => trim($_POST['admin_email'] ?? ''),
                    'phone' => trim($_POST['admin_phone'] ?? ($_POST['shop_whatsapp'] ?? '')),
                    'password' => $_POST['admin_password'] ?? '',
                ],
                'razorpay' => ['key' => trim($_POST['rzp_key'] ?? ''), 'secret' => trim($_POST['rzp_secret'] ?? '')],
                'smtp' => ['host' => trim($_POST['smtp_host'] ?? ''), 'port' => trim($_POST['smtp_port'] ?? '587'),
                           'user' => trim($_POST['smtp_user'] ?? ''), 'pass' => $_POST['smtp_pass'] ?? '', 'from' => trim($_POST['smtp_from'] ?? 'Krishna Tools')],
                'wa' => ['base' => trim($_POST['wa_base'] ?? 'https://bulk.akdwk.in/api.php'),
                         'inbound' => trim($_POST['wa_inbound'] ?? 'https://bulk.akdwk.in/api/webhook_inbound.php'),
                         'key' => trim($_POST['wa_key'] ?? ''), 'session' => trim($_POST['wa_session'] ?? ''), 'sender' => trim($_POST['wa_sender'] ?? '')],
                'ga' => trim($_POST['ga_id'] ?? ''),
            ];
            if (!filter_var($_SESSION['kt_wizard']['admin']['email'], FILTER_VALIDATE_EMAIL))
                out(['ok' => false, 'error' => 'Enter a valid admin email']);
            if (strlen($_SESSION['kt_wizard']['admin']['password']) < 8)
                out(['ok' => false, 'error' => 'Admin password must be at least 8 characters']);
        }
        $w = $_SESSION['kt_wizard'] ?? null;
        if (!$w) out(['ok' => false, 'error' => 'Wizard data missing — please start again']);

        switch ($step) {
            case 1: install_schema($pdo, $prefix); out(['ok' => true, 'msg' => 'Database tables created ✅', 'next' => 2]);
            case 2: $c = install_seed_tools($pdo, $prefix); out(['ok' => true, 'msg' => "Categories ({$c['categories']}) added ✅", 'next' => 3]);
            case 3: out(['ok' => true, 'msg' => count(kt_tools()) . ' tools registered ✅', 'next' => 4]); // tools already inserted in step 2
            case 4: $n = install_seed_plans($pdo, $prefix); out(['ok' => true, 'msg' => "Plans ($n) created ✅", 'next' => 5]);
            case 5: $n = install_seed_templates($pdo, $prefix); out(['ok' => true, 'msg' => "WhatsApp templates ($n) added ✅", 'next' => 6]);
            case 6:
                install_admin($pdo, $prefix, $w['admin']);
                install_seed_blog($pdo, $prefix);
                out(['ok' => true, 'msg' => 'Admin account created ✅', 'next' => 7]);
            case 7:
                install_settings($pdo, $prefix, [
                    'site_name' => $w['site']['name'], 'site_url' => $w['site']['url'],
                    'shop_name' => $w['shop']['name'], 'shop_address' => $w['shop']['address'],
                    'shop_phone' => $w['shop']['phone'], 'shop_whatsapp' => $w['shop']['whatsapp'],
                    'gstin' => $w['shop']['gstin'],
                    'wa_base_url' => $w['wa']['base'], 'wa_api_key' => $w['wa']['key'],
                    'wa_session_id' => $w['wa']['session'], 'wa_sender' => $w['wa']['sender'],
                    'wa_inbound_url' => $w['wa']['inbound'], 'wa_ivr_enabled' => '1',
                    'ga_id' => $w['ga'], 'maintenance_mode' => '0',
                    'default_lang' => $w['site']['lang'], 'currency_symbol' => '₹',
                ]);
                out(['ok' => true, 'msg' => 'Settings saved ✅', 'next' => 8]);
            case 8:
                install_write_config([
                    'db' => $db, 'site' => $w['site'], 'razorpay' => $w['razorpay'],
                    'smtp' => $w['smtp'], 'wa' => $w['wa'], 'ga' => $w['ga'],
                ]);
                out(['ok' => true, 'msg' => 'config/config.php written ✅', 'next' => 9]);
            case 9: install_write_htaccess(); out(['ok' => true, 'msg' => '.htaccess created ✅', 'next' => 10]);
            case 10: install_make_dirs(); out(['ok' => true, 'msg' => 'Folder structure ready ✅', 'next' => 11]);
            case 11:
                install_lock();
                $u = $_SERVER['SCRIPT_FILENAME'] ?? '';
                out(['ok' => true, 'msg' => 'install.lock created ✅', 'next' => 0, 'done' => true,
                     'admin_url' => $w['site']['url'] . '/admin/', 'admin_email' => $w['admin']['email'],
                     'webhook' => $w['site']['url'] . '/api/whatsapp_webhook.php']);
        }
        out(['ok' => false, 'error' => 'Unknown step']);
    }

    case 'auto_delete': {
        // Best-effort recursive delete of /install.
        $dir = __DIR__;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
        $gone = @rmdir($dir);
        out(['ok' => true, 'msg' => $gone ? 'Deleted' : 'Please delete it manually']);
    }

    default: out(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (Throwable $e) {
    out(['ok' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
