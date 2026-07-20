<?php
/**
 * KRISHNA TOOLS — optional server-side conversion (Ghostscript / LibreOffice /
 * ImageMagick). Returns the converted file, or a JSON message if the required
 * binary is not installed — never a fatal error.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/ratelimit.php';
require_once __DIR__ . '/../install/lib.php'; // for install_has_binary()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
csrf_verify();
if (!rate_limit('convert', 20, 60)) json_out(['ok' => false, 'error' => 'Too many requests'], 429);

if (empty($_FILES['file']['tmp_name'])) json_out(['ok' => false, 'error' => 'No file']);
$f = $_FILES['file'];
$maxMb = (int) (user_plan()['max_file_mb'] ?? 5);
if ($f['size'] > $maxMb * 1024 * 1024) json_out(['ok' => false, 'error' => "ફાઇલ {$maxMb}MB થી નાની હોવી જોઈએ"]);

$op   = preg_replace('/[^a-z_]/', '', $_POST['op'] ?? '');
$from = preg_replace('/[^a-z0-9]/', '', strtolower($_POST['from'] ?? ''));
$to   = preg_replace('/[^a-z0-9]/', '', strtolower($_POST['to'] ?? 'pdf'));

$tmp = KT_UPLOADS . '/temp';
@mkdir($tmp, 0755, true);
$base = $tmp . '/' . rand_token(12);
$inExt = pathinfo($f['name'], PATHINFO_EXTENSION) ?: $from ?: 'bin';
$in = $base . '.' . preg_replace('/[^a-z0-9]/i', '', $inExt);
move_uploaded_file($f['tmp_name'], $in);

function send_file(string $path, string $name): void {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    @unlink($path);
    exit;
}
function no_binary(string $bin): void {
    json_out(['ok' => false, 'error' => "સર્વર પર '$bin' ઇન્સ્ટોલ નથી. હોસ્ટિંગ પર ઇન્સ્ટોલ કરો અથવા બ્રાઉઝર ટૂલ વાપરો."]);
}

try {
    // PDF compress → Ghostscript
    if ($op === 'compress' || ($from === 'pdf' && $to === 'pdf')) {
        if (!install_has_binary('gs')) { @unlink($in); no_binary('Ghostscript'); }
        $out = $base . '_c.pdf';
        $cmd = 'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . escapeshellarg($out) . ' ' . escapeshellarg($in) . ' 2>&1';
        shell_exec($cmd);
        if (is_file($out)) { @unlink($in); send_file($out, 'compressed.pdf'); }
        no_binary('Ghostscript');
    }

    // Office ↔ PDF → LibreOffice (soffice)
    if (in_array($from, ['docx', 'doc', 'pptx', 'ppt', 'xlsx', 'xls', 'odt'], true) || in_array($to, ['docx', 'pptx', 'xlsx'], true)) {
        $soffice = install_has_binary('soffice') ? 'soffice' : (install_has_binary('libreoffice') ? 'libreoffice' : null);
        if (!$soffice) { @unlink($in); no_binary('LibreOffice'); }
        $outDir = $tmp . '/' . rand_token(8); @mkdir($outDir, 0755, true);
        shell_exec($soffice . ' --headless --convert-to ' . escapeshellarg($to) . ' --outdir ' . escapeshellarg($outDir) . ' ' . escapeshellarg($in) . ' 2>&1');
        $outFiles = glob($outDir . '/*.' . $to);
        @unlink($in);
        if ($outFiles) send_file($outFiles[0], 'output.' . $to);
        no_binary('LibreOffice');
    }

    // Advanced image ops → ImageMagick
    if ($op === 'imagick') {
        if (!install_has_binary('convert')) { @unlink($in); no_binary('ImageMagick'); }
        $out = $base . '.' . $to;
        shell_exec('convert ' . escapeshellarg($in) . ' ' . escapeshellarg($out) . ' 2>&1');
        if (is_file($out)) { @unlink($in); send_file($out, 'output.' . $to); }
        no_binary('ImageMagick');
    }

    @unlink($in);
    json_out(['ok' => false, 'error' => 'આ કન્વર્ઝન સપોર્ટેડ નથી']);
} catch (Throwable $e) {
    @unlink($in);
    kt_error_log('convert: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'સર્વર એરર']);
}
