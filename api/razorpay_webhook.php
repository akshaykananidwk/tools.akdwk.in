<?php
/**
 * KRISHNA TOOLS — Razorpay webhook (server-side signature verification).
 * Activates the user's plan on payment.captured / order.paid and sends the
 * WhatsApp + email confirmation. Configure this URL and the webhook secret in
 * the Razorpay dashboard; store the secret in settings key `rzp_webhook_secret`.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp.php';

$payload = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
$secret = setting('rzp_webhook_secret', '');

if ($secret === '') { http_response_code(200); echo 'webhook secret not set'; exit; }
$expected = hash_hmac('sha256', $payload, $secret);
if (!hash_equals($expected, $sig)) {
    kt_error_log('Razorpay webhook: bad signature');
    http_response_code(400); echo 'invalid signature'; exit;
}

$data = json_decode($payload, true);
$event = $data['event'] ?? '';
$entity = $data['payload']['payment']['entity'] ?? ($data['payload']['order']['entity'] ?? []);

if (in_array($event, ['payment.captured', 'order.paid'], true)) {
    $orderId = $entity['order_id'] ?? ($entity['id'] ?? '');
    $paymentId = $entity['id'] ?? '';
    $amount = ($entity['amount'] ?? 0) / 100;

    // Find the pending payment/subscription by order id.
    $pay = one("SELECT * FROM " . tbl('payments') . " WHERE razorpay_order_id = :o", [':o' => $orderId]);
    if ($pay && $pay['status'] !== 'paid') {
        update('payments', ['razorpay_payment_id' => $paymentId, 'signature' => $sig, 'status' => 'paid',
            'raw_response' => mb_substr($payload, 0, 5000)], ['id' => $pay['id']]);

        $sub = one("SELECT * FROM " . tbl('subscriptions') . " WHERE order_id = :o", [':o' => $orderId]);
        if ($sub) {
            $plan = one("SELECT * FROM " . tbl('plans') . " WHERE id = :id", [':id' => $sub['plan_id']]);
            $days = (int) ($plan['duration_days'] ?? 30);
            $expiry = date('Y-m-d H:i:s', time() + $days * 86400);
            update('subscriptions', ['status' => 'active', 'payment_id' => $paymentId,
                'start_date' => date('Y-m-d H:i:s'), 'end_date' => $expiry], ['id' => $sub['id']]);
            update('users', ['plan_id' => $sub['plan_id'], 'plan_expiry' => $expiry], ['id' => $sub['user_id']]);

            $user = one("SELECT * FROM " . tbl('users') . " WHERE id = :id", [':id' => $sub['user_id']]);
            if ($user && $user['phone']) {
                try { wa()->sendTemplate($user['phone'], 'payment_success',
                    ['plan' => loc($plan, 'name'), 'amount' => number_format($amount, 0), 'expiry' => date('d-m-Y', strtotime($expiry))],
                    (int) $user['id']); } catch (Throwable $e) {}
            }
            activity_log((int) $sub['user_id'], 'payment', "Plan {$sub['plan_id']} activated via $paymentId");
        }
    }
}
http_response_code(200);
echo 'ok';
