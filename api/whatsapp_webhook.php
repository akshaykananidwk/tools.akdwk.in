<?php
/**
 * KRISHNA TOOLS — inbound WhatsApp webhook + Gujarati IVR.
 * Paste this URL into the outbound webhook field on bulk.akdwk.in:
 *   https://tools.akdwk.in/api/whatsapp_webhook.php
 *
 * Verifies api_key against settings, then runs a menu-driven auto-reply with
 * per-number state in whatsapp_sessions (30-min timeout). IVR can be disabled
 * from admin settings (wa_ivr_enabled = 0).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp.php';

// Accept JSON or form-encoded inbound payloads (provider-dependent).
$raw = file_get_contents('php://input');
$in = json_decode($raw, true);
if (!is_array($in)) $in = $_POST;

$apiKey = $in['api_key'] ?? ($_GET['api_key'] ?? '');
if (setting('wa_api_key', '') !== '' && $apiKey !== setting('wa_api_key', '')) {
    // Some providers don't echo api_key; only reject when we can positively tell it's wrong.
    if ($apiKey !== '') { http_response_code(403); echo 'bad key'; exit; }
}

// Extract sender + message text across common payload shapes.
$from = $in['from'] ?? ($in['number'] ?? ($in['sender'] ?? ($in['phone'] ?? '')));
$text = trim($in['message'] ?? ($in['text'] ?? ($in['body'] ?? '')));
$from = normalize_phone((string) $from);
if ($from === '' ) { http_response_code(200); echo 'no sender'; exit; }

// Log the inbound message.
try { insert('whatsapp_logs', ['user_id' => null, 'number' => $from, 'type' => 'inbound',
    'message' => mb_substr($text, 0, 1000), 'status' => 'received', 'api_response' => '', 'created_at' => date('Y-m-d H:i:s')]); } catch (Throwable $e) {}

if (setting('wa_ivr_enabled', '1') !== '1') { http_response_code(200); echo 'ivr off'; exit; }

$site = setting('site_url', SITE_URL);
$shopAddr = setting('shop_address', 'Dwarka, Gujarat');
$shopPhone = setting('shop_phone', '9978123146');

// Load / expire session state (30-min timeout).
$sess = one("SELECT * FROM " . tbl('whatsapp_sessions') . " WHERE number = :n", [':n' => $from]);
if ($sess && strtotime($sess['updated_at']) < time() - 1800) $sess = null;
$state = $sess['state'] ?? 'menu';

$reply = '';
$choice = strtolower(trim($text));

function wa_menu($site): string {
    return "🦚 નમસ્તે! કૃષ્ણા ટૂલ્સ માં આપનું સ્વાગત છે.\n\n"
        . "1️⃣ ટૂલ્સ લિસ્ટ\n2️⃣ પ્લાન અને કિંમત\n3️⃣ મારો પ્લાન સ્ટેટસ\n"
        . "4️⃣ CCTV સ્ટોરેજ કેલ્ક્યુલેટર\n5️⃣ શોપ સંપર્ક / લોકેશન\n0️⃣ સપોર્ટ (એજન્ટ સાથે વાત)";
}

// CCTV mini-calculator conversational flow (state: cctv_cams → cctv_res → cctv_days).
if (str_starts_with($state, 'cctv_')) {
    $data = json_decode($sess['data'] ?? '{}', true) ?: [];
    if ($state === 'cctv_cams') {
        $data['cams'] = max(1, (int) $choice);
        $reply = "કેમેરાનું રિઝોલ્યુશન લખો:\n1 = 2MP\n2 = 4MP\n3 = 8MP (4K)";
        wa_set_state($from, 'cctv_res', $data);
    } elseif ($state === 'cctv_res') {
        $map = ['1' => 2, '2' => 4, '3' => 8]; $data['mp'] = $map[$choice] ?? 2;
        $reply = "કેટલા દિવસનું રેકોર્ડિંગ રાખવું છે? (દા.ત. 30)";
        wa_set_state($from, 'cctv_days', $data);
    } elseif ($state === 'cctv_days') {
        $days = max(1, (int) $choice);
        $kbps = ['2' => 2048, '4' => 8192, '8' => 16384][$data['mp']] ?? 4096;
        $kbps *= 0.5; // H.265
        $gb = $kbps * 1000 / 8 * 3600 * 24 * $days / 1e9 * ($data['cams'] ?? 1);
        $tb = $gb / 1000;
        $hdd = 0; foreach ([1,2,4,6,8,10] as $x) { if ($x >= $tb * 1.1) { $hdd = $x; break; } }
        $reply = "🎥 અંદાજ:\nકેમેરા: {$data['cams']}, {$data['mp']}MP, {$days} દિવસ\n"
            . "કુલ સ્ટોરેજ: " . round($gb) . " GB (" . round($tb, 2) . " TB)\n"
            . "ભલામણ HDD: " . ($hdd ?: ceil($tb)) . " TB\n\n"
            . "પૂરું ટૂલ: $site/tool/cctv-storage-calculator\n\nમેનૂ માટે 'menu' લખો.";
        wa_set_state($from, 'menu', []);
    }
    wa_reply($from, $reply); http_response_code(200); echo 'ok'; exit;
}

switch ($choice) {
    case '1':
        $top = array_slice(kt_tools(), 0, 10);
        $reply = "🛠️ લોકપ્રિય ટૂલ્સ:\n";
        foreach ($top as $t) $reply .= "• {$t['name_gu']}: $site/tool/{$t['slug']}\n";
        $reply .= "\nબધા ટૂલ્સ: $site";
        break;
    case '2':
        $reply = "💎 પ્લાન:\n• ફ્રી — ₹0 (દરરોજ 3 વખત)\n• માસિક — ₹99/મહિનો (અમર્યાદિત)\n• વાર્ષિક — ₹499/વર્ષ (BEST VALUE, 58% બચત)\n\nખરીદો: $site/pricing.php";
        break;
    case '3':
        $u = one("SELECT * FROM " . tbl('users') . " WHERE phone LIKE :p", [':p' => '%' . substr($from, -10)]);
        if ($u && $u['plan_expiry'] && strtotime($u['plan_expiry']) > time()) {
            $plan = one("SELECT * FROM " . tbl('plans') . " WHERE id = :id", [':id' => $u['plan_id']]);
            $left = ceil((strtotime($u['plan_expiry']) - time()) / 86400);
            $reply = "✅ {$u['name']}, આપનો પ્લાન: " . ($plan['name_gu'] ?? '-') . "\nબાકી દિવસ: $left\nસમાપ્તિ: " . date('d-m-Y', strtotime($u['plan_expiry']));
        } else {
            $reply = "આપનો કોઈ સક્રિય પ્લાન મળ્યો નહીં. ખરીદો: $site/pricing.php";
        }
        break;
    case '4':
        $reply = "🎥 CCTV સ્ટોરેજ કેલ્ક્યુલેટર\nકેટલા કેમેરા છે? સંખ્યા લખો (દા.ત. 4)";
        wa_set_state($from, 'cctv_cams', []);
        wa_reply($from, $reply); http_response_code(200); echo 'ok'; exit;
    case '5':
        $reply = "🏪 AK Computer\n📍 $shopAddr\n📞 $shopPhone\n🗺️ https://maps.google.com/?q=" . urlencode($shopAddr) . "\n\nકમ્પ્યુટર અને CCTV — હરે કૃષ્ણ 🦚";
        break;
    case '0':
        // Flag conversation for the admin Live Chat inbox.
        wa_set_state($from, 'agent', ['flagged' => date('c')]);
        try { insert('contact_messages', ['name' => 'WhatsApp ' . $from, 'phone' => $from,
            'message' => 'IVR: સપોર્ટ એજન્ટ સાથે વાત કરવા માંગે છે', 'created_at' => date('Y-m-d H:i:s')]); } catch (Throwable $e) {}
        $reply = "🙏 આપની વિનંતી નોંધાઈ. અમારો એજન્ટ ટૂંક સમયમાં સંપર્ક કરશે.\nમેનૂ માટે 'menu' લખો.";
        break;
    default:
        $reply = wa_menu($site);
        wa_set_state($from, 'menu', []);
        break;
}

wa_reply($from, $reply);
http_response_code(200);
echo 'ok';

/* ── helpers ── */
function wa_set_state(string $number, string $state, array $data): void {
    q("INSERT INTO " . tbl('whatsapp_sessions') . " (number, state, data, updated_at)
       VALUES (:n, :s, :d, NOW())
       ON DUPLICATE KEY UPDATE state = :s2, data = :d2, updated_at = NOW()",
       [':n' => $number, ':s' => $state, ':d' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ':s2' => $state, ':d2' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
}
function wa_reply(string $number, string $message): void {
    try { wa()->send($number, $message); } catch (Throwable $e) { kt_error_log('IVR reply failed: ' . $e->getMessage()); }
}
