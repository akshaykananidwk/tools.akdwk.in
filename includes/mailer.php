<?php
/**
 * KRISHNA TOOLS — self-contained SMTP mailer.
 * ---------------------------------------------------------------------------
 * A tiny, dependency-free SMTP client written with fsockopen(). It needs NO
 * Composer, NO Node, and NO external libraries — just this single file.
 *
 * Supported features:
 *   - STARTTLS on port 587 (explicit TLS upgrade) and implicit TLS on
 *     port 465 (ssl:// transport).
 *   - AUTH LOGIN (base64 username/password).
 *   - HELO / EHLO negotiation with graceful fallback.
 *   - MAIL FROM / RCPT TO / DATA envelope.
 *   - UTF-8 subject encoded per RFC 2047 (=?UTF-8?B?...?=).
 *   - HTML body with a plain-text alternative (multipart/alternative).
 *   - Correct CRLF line endings and connection/response timeouts.
 *   - Every server reply code is checked (220 / 250 / 334 / 235 / 354).
 *
 * Configuration is read from constants first (SMTP_HOST, SMTP_PORT, SMTP_USER,
 * SMTP_PASS, SMTP_FROM_NAME) and falls back to the settings table
 * (setting('smtp_host') etc.) when a constant is blank.
 *
 * NOTE FOR ADMINS: If you prefer the real PHPMailer, you can drop the official
 * single-file PHPMailer build into includes/ and change the send_mail() helper
 * (or your include) to use it instead. The public helper below —
 *     send_mail(string $to, string $subject, string $htmlBody): array
 * — is intentionally simple so it can be swapped out without touching callers.
 * ---------------------------------------------------------------------------
 */

// Pull in helpers so setting() is available. functions.php is safe to include
// multiple times (its own require_once guards protect against double-loading).
require_once __DIR__ . '/functions.php';

if (!class_exists('Mailer')) {

/**
 * Minimal SMTP client.
 *
 * Typical usage is through the send_mail() helper below, but the class can be
 * used directly for more control:
 *
 *   $m = new Mailer($host, $port, $user, $pass, $fromEmail, $fromName);
 *   $m->send('to@example.com', 'Subject', '<b>Hello</b>');
 */
class Mailer
{
    /** @var string SMTP server hostname. */
    private string $host;
    /** @var int SMTP server port (587 = STARTTLS, 465 = implicit TLS, 25 = plain). */
    private int $port;
    /** @var string SMTP username (usually the full email address). */
    private string $user;
    /** @var string SMTP password / app password. */
    private string $pass;
    /** @var string Envelope + From: header email address. */
    private string $fromEmail;
    /** @var string Human-readable From name. */
    private string $fromName;
    /** @var int Socket timeout in seconds. */
    private int $timeout;

    /** @var resource|null Active socket connection. */
    private $conn = null;

    public function __construct(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $fromEmail,
        string $fromName = '',
        int $timeout = 20
    ) {
        $this->host      = $host;
        $this->port      = $port;
        $this->user      = $user;
        $this->pass      = $pass;
        $this->fromEmail = $fromEmail;
        $this->fromName  = $fromName;
        $this->timeout   = $timeout;
    }

    /**
     * Send one HTML email (with an auto-generated plain-text alternative).
     *
     * @throws RuntimeException on any protocol / connection failure.
     */
    public function send(string $to, string $subject, string $htmlBody): void
    {
        $crlf = "\r\n";

        // ── 1. Open the transport ────────────────────────────────────────
        // Port 465 uses implicit TLS from the first byte (ssl:// transport).
        // Ports 587 / 25 start in plaintext and (for 587) upgrade via STARTTLS.
        $implicitTls = ($this->port === 465);
        $remote      = ($implicitTls ? 'ssl://' : '') . $this->host . ':' . $this->port;

        $errno  = 0;
        $errstr = '';
        $this->conn = @fsockopen($remote, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->conn) {
            throw new RuntimeException("Connection failed: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->conn, $this->timeout);

        try {
            // Server greeting.
            $this->expect(220, 'greeting');

            // ── 2. EHLO / HELO ───────────────────────────────────────────
            $ehloHost = $this->ehloHostname();
            $this->command('EHLO ' . $ehloHost);
            $ehlo = $this->readResponse();
            if ((int) substr($ehlo, 0, 3) !== 250) {
                // Fall back to the older HELO verb.
                $this->command('HELO ' . $ehloHost);
                $this->expect(250, 'HELO');
            }

            // ── 3. STARTTLS upgrade (explicit TLS, e.g. port 587) ────────
            if (!$implicitTls && $this->port !== 25) {
                $this->command('STARTTLS');
                $this->expect(220, 'STARTTLS');

                $ok = @stream_socket_enable_crypto(
                    $this->conn,
                    true,
                    STREAM_CRYPTO_METHOD_TLS_CLIENT
                        | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT
                        | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                );
                if ($ok !== true) {
                    throw new RuntimeException('STARTTLS negotiation failed');
                }
                // RFC 3207: re-issue EHLO after the TLS upgrade.
                $this->command('EHLO ' . $ehloHost);
                $this->expect(250, 'EHLO after STARTTLS');
            }

            // ── 4. AUTH LOGIN ────────────────────────────────────────────
            if ($this->user !== '') {
                $this->command('AUTH LOGIN');
                $this->expect(334, 'AUTH LOGIN');
                $this->command(base64_encode($this->user));
                $this->expect(334, 'AUTH username');
                $this->command(base64_encode($this->pass));
                $this->expect(235, 'AUTH password'); // 235 = authentication successful
            }

            // ── 5. Envelope ──────────────────────────────────────────────
            $this->command('MAIL FROM:<' . $this->fromEmail . '>');
            $this->expect(250, 'MAIL FROM');
            $this->command('RCPT TO:<' . $to . '>');
            // Some servers reply 250, others 251 (will forward) — accept both.
            $rcpt = $this->readResponse();
            $rcptCode = (int) substr($rcpt, 0, 3);
            if ($rcptCode !== 250 && $rcptCode !== 251) {
                throw new RuntimeException('RCPT TO rejected: ' . trim($rcpt));
            }

            // ── 6. DATA (headers + MIME body) ────────────────────────────
            $this->command('DATA');
            $this->expect(354, 'DATA');

            $message = $this->buildMessage($to, $subject, $htmlBody);
            // End of DATA is a single dot on its own line.
            $this->write($message . $crlf . '.' . $crlf);
            $this->expect(250, 'end of DATA');

            // ── 7. Polite goodbye ────────────────────────────────────────
            $this->command('QUIT');
            // We do not strictly require a 221 here; the mail is already queued.
        } finally {
            if (is_resource($this->conn)) {
                @fclose($this->conn);
                $this->conn = null;
            }
        }
    }

    /** Build the full RFC 5322 / MIME multipart/alternative message. */
    private function buildMessage(string $to, string $subject, string $htmlBody): string
    {
        $crlf     = "\r\n";
        $boundary = 'kt_' . bin2hex(random_bytes(16));

        // A readable plain-text fallback derived from the HTML.
        $plain = $this->htmlToText($htmlBody);

        $fromName = $this->fromName !== '' ? $this->fromName : $this->fromEmail;

        $headers   = [];
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'From: ' . $this->encodeHeaderName($fromName) . ' <' . $this->fromEmail . '>';
        $headers[] = 'To: <' . $to . '>';
        $headers[] = 'Subject: ' . $this->encodeSubject($subject);
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $this->messageIdDomain() . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body   = [];
        // Plain-text part.
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/plain; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: base64';
        $body[] = '';
        $body[] = chunk_split(base64_encode($plain), 76, $crlf);
        // HTML part.
        $body[] = '--' . $boundary;
        $body[] = 'Content-Type: text/html; charset=UTF-8';
        $body[] = 'Content-Transfer-Encoding: base64';
        $body[] = '';
        $body[] = chunk_split(base64_encode($htmlBody), 76, $crlf);
        // Closing boundary.
        $body[] = '--' . $boundary . '--';

        // Dot-stuff the body so a line that begins with "." is not mistaken
        // for the end-of-DATA terminator (RFC 5321 §4.5.2).
        $message = implode($crlf, $headers) . $crlf . $crlf . implode($crlf, $body);
        $message = preg_replace('/^\./m', '..', $message);

        return $message;
    }

    /** RFC 2047 encode a UTF-8 subject as =?UTF-8?B?...?= when needed. */
    private function encodeSubject(string $subject): string
    {
        // Pure ASCII subjects can be sent as-is.
        if (preg_match('//u', $subject) && mb_check_encoding($subject, 'ASCII')) {
            return $subject;
        }
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    /** RFC 2047 encode a display name (used in From:) when it is non-ASCII. */
    private function encodeHeaderName(string $name): string
    {
        if (mb_check_encoding($name, 'ASCII')) {
            // Quote if it contains characters that require quoting.
            if (preg_match('/[()<>@,;:\\".\[\]]/', $name)) {
                return '"' . str_replace('"', '\"', $name) . '"';
            }
            return $name;
        }
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }

    /** A best-effort plain-text version of an HTML body. */
    private function htmlToText(string $html): string
    {
        $text = preg_replace('/<(br|\/p|\/div|\/h[1-6]|\/li)[^>]*>/i', "\n", $html);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Collapse excessive blank lines / trailing whitespace.
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    /** Hostname to advertise in EHLO/HELO. */
    private function ehloHostname(): string
    {
        if (!empty($_SERVER['SERVER_NAME'])) return $_SERVER['SERVER_NAME'];
        if (defined('SITE_URL')) {
            $h = parse_url(SITE_URL, PHP_URL_HOST);
            if ($h) return $h;
        }
        return 'localhost';
    }

    /** Domain used in the Message-ID header. */
    private function messageIdDomain(): string
    {
        $at = strrpos($this->fromEmail, '@');
        if ($at !== false) return substr($this->fromEmail, $at + 1);
        return $this->ehloHostname();
    }

    /** Write raw bytes to the socket. */
    private function write(string $data): void
    {
        if (!is_resource($this->conn)) {
            throw new RuntimeException('Connection lost');
        }
        $written = @fwrite($this->conn, $data);
        if ($written === false) {
            throw new RuntimeException('Failed to write to SMTP socket');
        }
    }

    /** Send a single command line terminated with CRLF. */
    private function command(string $line): void
    {
        $this->write($line . "\r\n");
    }

    /**
     * Read a full (possibly multi-line) SMTP response.
     * Multi-line replies use "250-" for continuation and "250 " for the last.
     */
    private function readResponse(): string
    {
        $data = '';
        while (is_resource($this->conn) && !feof($this->conn)) {
            $line = fgets($this->conn, 8192);
            if ($line === false) break;
            $data .= $line;
            // The 4th character is a space on the final line of the reply.
            if (strlen($line) >= 4 && $line[3] === ' ') break;

            $meta = stream_get_meta_data($this->conn);
            if (!empty($meta['timed_out'])) {
                throw new RuntimeException('SMTP response timed out');
            }
        }
        return $data;
    }

    /** Read a response and require a specific leading status code. */
    private function expect(int $code, string $stage): void
    {
        $resp = $this->readResponse();
        $got  = (int) substr($resp, 0, 3);
        if ($got !== $code) {
            throw new RuntimeException(
                "Unexpected SMTP reply at {$stage}: expected {$code}, got " . trim($resp)
            );
        }
    }
}

} // class_exists guard

if (!function_exists('send_mail')) {
    /**
     * Send an HTML email using the configured SMTP settings.
     *
     * Reads SMTP configuration from constants first and falls back to the
     * settings table. NEVER throws — always returns a status array.
     *
     * @return array{ok:bool,error:string}
     */
    function send_mail(string $to, string $subject, string $htmlBody): array
    {
        try {
            // Helper: constant value if non-blank, otherwise settings-table value.
            $cfg = static function (string $const, string $settingKey, string $default = ''): string {
                if (defined($const)) {
                    $v = (string) constant($const);
                    if (trim($v) !== '') return $v;
                }
                $v = (string) setting($settingKey, $default);
                return $v;
            };

            $host = $cfg('SMTP_HOST', 'smtp_host');
            $port = (int) $cfg('SMTP_PORT', 'smtp_port', '587');
            $user = $cfg('SMTP_USER', 'smtp_user');
            $pass = $cfg('SMTP_PASS', 'smtp_pass');

            // From name: constant → setting → SITE_NAME → sensible default.
            $fromName = $cfg('SMTP_FROM_NAME', 'smtp_from_name', '');
            if ($fromName === '') {
                $fromName = defined('SITE_NAME') ? (string) SITE_NAME : 'Krishna Tools';
            }
            // From email: explicit setting → the SMTP username → nothing.
            $fromEmail = (string) setting('smtp_from_email', '');
            if ($fromEmail === '' && $user !== '') {
                $fromEmail = $user;
            }

            // Validate the essentials before attempting a connection.
            if ($host === '' || $port <= 0 || $fromEmail === '') {
                return ['ok' => false, 'error' => 'SMTP not configured'];
            }
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return ['ok' => false, 'error' => 'Invalid recipient address'];
            }

            $mailer = new Mailer($host, $port, $user, $pass, $fromEmail, $fromName);
            $mailer->send($to, $subject, $htmlBody);

            return ['ok' => true, 'error' => ''];
        } catch (Throwable $e) {
            // Log for admins but never surface an exception to the caller.
            if (function_exists('kt_error_log')) {
                kt_error_log('send_mail failed: ' . $e->getMessage());
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
