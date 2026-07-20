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
    echo json_encode(['ok' => false, 'error' => 'પહેલેથી ઇન્સ્ટોલ થયેલ છે / Already installed']);
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
            out(['ok' => true, 'msg' => "કનેક્શન સફળ! MySQL version $ver મળી ✅"]);
        } catch (PDOException $e) {
            // Friendlier Gujarati hints for common errors.
            $m = $e->getMessage();
            $hint = str_contains($m, 'Access denied') ? 'યુઝરનેમ કે પાસવર્ડ ખોટો છે.'
                : (str_contains($m, 'Unknown database') ? 'ડેટાબેઝ નામ ખોટું છે — cPanel MySQL Databases માં બનાવો.'
                : (str_contains($m, 'getaddrinfo') || str_contains($m, 'Connection refused') ? 'હોસ્ટ ખોટો છે — સામાન્ય રીતે localhost વાપરો.' : $m));
            out(['ok' => false, 'error' => "❌ કનેક્શન નિષ્ફળ: $hint"]);
        }
    }

    case 'wa_test': {
        $base = trim($_POST['base'] ?? 'https://bulk.akdwk.in/api.php');
        $payload = json_encode([
            'api_key' => trim($_POST['key'] ?? ''),
            'session_id' => trim($_POST['session'] ?? ''),
            'number' => preg_replace('/\D/', '', $_POST['number'] ?? ''),
            'message' => 'કૃષ્ણા ટૂલ્સ ટેસ્ટ મેસેજ 🦚 — ઇન્સ્ટોલેશન સફળ!',
        ], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($base);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
        $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
        if ($resp === false) out(['ok' => false, 'error' => "❌ cURL: $err"]);
        out(['ok' => $http >= 200 && $http < 300, 'msg' => "HTTP $http", 'raw' => mb_substr((string) $resp, 0, 500)]);
    }

    case 'smtp_test': {
        // Lightweight SMTP check via fsockopen (PHPMailer send happens post-install).
        $host = trim($_POST['host'] ?? ''); $port = (int) ($_POST['port'] ?? 587);
        if (!$host) out(['ok' => false, 'error' => 'SMTP host આપો']);
        $fp = @fsockopen(($port == 465 ? 'ssl://' : '') . $host, $port, $e, $es, 8);
        if (!$fp) out(['ok' => false, 'error' => "❌ કનેક્ટ ન થયું: $es"]);
        $greet = fgets($fp, 512); fclose($fp);
        out(['ok' => str_starts_with(trim($greet), '220'), 'msg' => 'SMTP: ' . trim($greet)]);
    }

    case 'razorpay_test': {
        $key = trim($_POST['key'] ?? ''); $secret = trim($_POST['secret'] ?? '');
        if (!$key || !$secret) out(['ok' => false, 'error' => 'Key ID અને Secret આપો']);
        $ch = curl_init('https://api.razorpay.com/v1/orders?count=1');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_USERPWD => "$key:$secret", CURLOPT_TIMEOUT => 15]);
        $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        out(['ok' => $http === 200, 'msg' => $http === 200 ? 'Razorpay કી માન્ય ✅' : "❌ HTTP $http — કી તપાસો"]);
    }

    case 'install': {
        $db = $_SESSION['kt_db'] ?? null;
        if (!$db) out(['ok' => false, 'error' => 'પહેલા ડેટાબેઝ ટેસ્ટ કરો (Step 2)']);
        $step = (int) ($_POST['step'] ?? 0);
        $pdo = install_pdo($db);
        $prefix = $db['prefix'];

        // Rebuild the full config array from posted wizard data (kept in session between steps).
        if ($step === 1) {
            $_SESSION['kt_wizard'] = [
                'site' => [
                    'name' => trim($_POST['site_name'] ?? 'કૃષ્ણા ટૂલ્સ'),
                    'url'  => rtrim(trim($_POST['site_url'] ?? install_site_url()), '/'),
                    'lang' => ($_POST['lang'] ?? 'gu') === 'en' ? 'en' : 'gu',
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
                out(['ok' => false, 'error' => 'માન્ય એડમિન ઈમેલ આપો']);
            if (strlen($_SESSION['kt_wizard']['admin']['password']) < 8)
                out(['ok' => false, 'error' => 'એડમિન પાસવર્ડ ઓછામાં ઓછો 8 અક્ષર']);
        }
        $w = $_SESSION['kt_wizard'] ?? null;
        if (!$w) out(['ok' => false, 'error' => 'Wizard data ખૂટે છે — ફરી શરૂ કરો']);

        switch ($step) {
            case 1: install_schema($pdo, $prefix); out(['ok' => true, 'msg' => 'ડેટાબેઝ ટેબલ બની ગયા ✅', 'next' => 2]);
            case 2: $c = install_seed_tools($pdo, $prefix); out(['ok' => true, 'msg' => "કેટેગરી ({$c['categories']}) ઉમેરાઈ ✅", 'next' => 3]);
            case 3: out(['ok' => true, 'msg' => count(kt_tools()) . ' ટૂલ્સ રજીસ્ટર થયા ✅', 'next' => 4]); // tools already inserted in step 2
            case 4: $n = install_seed_plans($pdo, $prefix); out(['ok' => true, 'msg' => "પ્લાન ($n) બન્યા ✅", 'next' => 5]);
            case 5: $n = install_seed_templates($pdo, $prefix); out(['ok' => true, 'msg' => "WhatsApp ટેમ્પ્લેટ ($n) ઉમેરાયા ✅", 'next' => 6]);
            case 6:
                install_admin($pdo, $prefix, $w['admin']);
                install_seed_blog($pdo, $prefix);
                out(['ok' => true, 'msg' => 'એડમિન એકાઉન્ટ બન્યું ✅', 'next' => 7]);
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
                out(['ok' => true, 'msg' => 'સેટિંગ્સ સેવ થઈ ✅', 'next' => 8]);
            case 8:
                install_write_config([
                    'db' => $db, 'site' => $w['site'], 'razorpay' => $w['razorpay'],
                    'smtp' => $w['smtp'], 'wa' => $w['wa'], 'ga' => $w['ga'],
                ]);
                out(['ok' => true, 'msg' => 'config/config.php લખાયું ✅', 'next' => 9]);
            case 9: install_write_htaccess(); out(['ok' => true, 'msg' => '.htaccess બન્યું ✅', 'next' => 10]);
            case 10: install_make_dirs(); out(['ok' => true, 'msg' => 'ફોલ્ડર સ્ટ્રક્ચર તૈયાર ✅', 'next' => 11]);
            case 11:
                install_lock();
                $u = $_SERVER['SCRIPT_FILENAME'] ?? '';
                out(['ok' => true, 'msg' => 'install.lock બન્યું ✅', 'next' => 0, 'done' => true,
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
        out(['ok' => true, 'msg' => $gone ? 'ડિલીટ થયું' : 'મેન્યુઅલી ડિલીટ કરો']);
    }

    default: out(['ok' => false, 'error' => 'Unknown action']);
    }
} catch (Throwable $e) {
    out(['ok' => false, 'error' => 'Error: ' . $e->getMessage()]);
}
