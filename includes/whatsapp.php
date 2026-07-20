<?php
/**
 * KRISHNA TOOLS — WhatsApp integration (bulk.akdwk.in).
 *
 * A single reusable class. All calls are cURL POST with a JSON body so the
 * API key is never exposed in URLs / server logs. Numbers are normalised to
 * 91XXXXXXXXXX. A WhatsApp failure never breaks the caller — every public
 * method is wrapped in try/catch and logs to whatsapp_logs.
 */
require_once __DIR__ . '/functions.php';

class WhatsApp {
    private string $baseUrl;
    private string $apiKey;
    private string $sessionId;
    private string $sender;

    public function __construct() {
        // Settings table wins over config constants (admin editable).
        $this->baseUrl   = setting('wa_base_url',   defined('WA_BASE_URL') ? WA_BASE_URL : 'https://bulk.akdwk.in/api.php');
        $this->apiKey    = setting('wa_api_key',    defined('WA_API_KEY') ? WA_API_KEY : '');
        $this->sessionId = setting('wa_session_id', defined('WA_SESSION_ID') ? WA_SESSION_ID : '');
        $this->sender    = setting('wa_sender',     defined('WA_SENDER') ? WA_SENDER : '');
    }

    public function isConfigured(): bool {
        return $this->apiKey !== '' && $this->sessionId !== '' && $this->baseUrl !== '';
    }

    /** Send a plain text message. Returns ['ok'=>bool, 'response'=>...]. */
    public function send(string $number, string $message, ?int $userId = null): array {
        return $this->dispatch($number, $message, null, 'text', $userId);
    }

    /** Send a media message (image/pdf) by public URL. */
    public function sendMedia(string $number, string $message, string $mediaUrl, ?int $userId = null): array {
        return $this->dispatch($number, $message, $mediaUrl, 'media', $userId);
    }

    /**
     * Send a stored template by key, filling :placeholders from $vars.
     * Respects a per-template is_active flag.
     */
    public function sendTemplate(string $number, string $keyName, array $vars = [], ?int $userId = null): array {
        try {
            $tpl = one("SELECT * FROM " . tbl('whatsapp_templates') . " WHERE key_name = :k", [':k' => $keyName]);
            if (!$tpl || (int) $tpl['is_active'] !== 1) {
                return ['ok' => false, 'response' => 'template inactive or missing'];
            }
            $body = current_lang() === 'en' ? ($tpl['body_en'] ?: $tpl['body_gu']) : $tpl['body_gu'];
            foreach ($vars as $k => $v) $body = str_replace(':' . $k, (string) $v, $body);
            return $this->dispatch($number, $body, null, 'template:' . $keyName, $userId);
        } catch (Throwable $e) {
            kt_error_log('WA template error: ' . $e->getMessage());
            return ['ok' => false, 'response' => $e->getMessage()];
        }
    }

    /** Low-level dispatch with one retry, always logged. */
    private function dispatch(string $number, string $message, ?string $mediaUrl, string $type, ?int $userId): array {
        $number = normalize_phone($number);
        if (!$this->isConfigured()) {
            $this->log($userId, $number, $type, $message, $mediaUrl, 'not_configured', '');
            return ['ok' => false, 'response' => 'WhatsApp not configured'];
        }

        $payload = [
            'api_key'    => $this->apiKey,
            'session_id' => $this->sessionId,
            'number'     => $number,
            'message'    => $message,
        ];
        if ($mediaUrl) $payload['media_url'] = $mediaUrl;

        $resp = null; $ok = false;
        for ($attempt = 0; $attempt < 2 && !$ok; $attempt++) {
            try {
                $resp = $this->curl($payload);
                // Treat any 2xx-ish response with no explicit error as success.
                $ok = $resp['http'] >= 200 && $resp['http'] < 300;
            } catch (Throwable $e) {
                $resp = ['http' => 0, 'body' => $e->getMessage()];
            }
            if (!$ok && $attempt === 0) usleep(400000); // brief backoff before retry
        }

        $status = $ok ? 'sent' : 'failed';
        $this->log($userId, $number, $type, $message, $mediaUrl, $status, is_array($resp) ? ($resp['body'] ?? '') : '');
        return ['ok' => $ok, 'response' => $resp['body'] ?? ''];
    }

    /** Raw cURL POST JSON. */
    private function curl(array $payload): array {
        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $body = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($body === false) throw new RuntimeException('cURL: ' . $err);
        return ['http' => (int) $http, 'body' => (string) $body];
    }

    /** Persist to whatsapp_logs (best-effort). */
    private function log(?int $userId, string $number, string $type, string $message, ?string $mediaUrl, string $status, string $apiResponse): void {
        try {
            insert('whatsapp_logs', [
                'user_id'      => $userId,
                'number'       => $number,
                'type'         => $type,
                'message'      => mb_substr($message, 0, 2000),
                'media_url'    => $mediaUrl,
                'status'       => $status,
                'api_response' => mb_substr($apiResponse, 0, 4000),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) { /* never break flow */ }
    }
}

/** Convenience singleton. */
function wa(): WhatsApp {
    static $w = null;
    return $w ?? ($w = new WhatsApp());
}
