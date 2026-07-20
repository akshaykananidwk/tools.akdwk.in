<?php
/**
 * KRISHNA TOOLS — create a Razorpay order for a plan and record a pending
 * payment + subscription. The webhook (razorpay_webhook.php) activates it.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_verify();
if (!is_logged_in()) json_out(['ok' => false, 'error' => t('login_required')], 401);
if (!defined('RAZORPAY_KEY_ID') || RAZORPAY_KEY_ID === '') json_out(['ok' => false, 'error' => 'Razorpay સેટ નથી — ફ્રી મોડ']);

$body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$planId = (int) ($body['plan_id'] ?? 0);
$plan = one("SELECT * FROM " . tbl('plans') . " WHERE id = :id AND is_active = 1", [':id' => $planId]);
if (!$plan || (float) $plan['price'] <= 0) json_out(['ok' => false, 'error' => 'અમાન્ય પ્લાન']);

$user = current_user();
$amountPaise = (int) round($plan['price'] * 100);

// Create the order via Razorpay Orders API.
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $amountPaise, 'currency' => 'INR',
        'receipt' => 'kt_' . $user['id'] . '_' . time(),
        'notes' => ['plan_id' => $planId, 'user_id' => $user['id']],
    ]),
    CURLOPT_TIMEOUT => 20,
]);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$order = json_decode($resp, true);
if ($http !== 200 || empty($order['id'])) {
    kt_error_log('Razorpay order failed: ' . $resp);
    json_out(['ok' => false, 'error' => 'ઓર્ડર બનાવવામાં નિષ્ફળ']);
}

// Record pending payment + subscription.
insert('payments', ['user_id' => (int) $user['id'], 'razorpay_order_id' => $order['id'],
    'amount' => $plan['price'], 'currency' => 'INR', 'status' => 'created',
    'raw_response' => mb_substr($resp, 0, 3000), 'created_at' => date('Y-m-d H:i:s')]);
insert('subscriptions', ['user_id' => (int) $user['id'], 'plan_id' => $planId, 'order_id' => $order['id'],
    'amount' => $plan['price'], 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')]);

json_out(['ok' => true, 'order_id' => $order['id'], 'amount' => $amountPaise,
    'key' => RAZORPAY_KEY_ID, 'name' => setting('site_name', 'કૃષ્ણા ટૂલ્સ'),
    'prefill' => ['name' => $user['name'], 'email' => $user['email'], 'contact' => $user['phone']]]);
