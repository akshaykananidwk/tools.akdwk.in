<?php
/**
 * KRISHNA TOOLS — search keywords & SEO copy for every tool.
 *
 * Google ranks a page for the words people actually type. Each tool gets:
 *   - a list of real search phrases (hand-picked for the core tools, `kw` in the
 *     packs, plus phrases built from the tool name and type),
 *   - a search-friendly <title> and meta description,
 *   - a short "About" paragraph and a visible "People also search for" list,
 *     so the page text itself contains those phrases (the meta keywords tag is
 *     ignored by Google; visible text is what counts).
 */

/** Hand-picked search phrases for the original core tools (highest search volume). */
function kt_core_keywords(): array {
    return [
        'jpg-to-png' => ['jpg to png', 'convert jpg to png', 'jpeg to png converter', 'jpg to png transparent', 'image to png'],
        'png-to-jpg' => ['png to jpg', 'png to jpeg', 'convert png to jpg', 'png to jpg converter online'],
        'webp-to-jpg' => ['webp to jpg', 'webp to png', 'convert webp image', 'webp converter'],
        'jpg-to-webp' => ['jpg to webp', 'png to webp', 'convert image to webp', 'webp converter for website'],
        'heic-to-jpg' => ['heic to jpg', 'iphone photo to jpg', 'heic converter', 'heif to jpg'],
        'image-to-pdf' => ['jpg to pdf', 'image to pdf', 'photo to pdf', 'png to pdf', 'convert images to pdf', 'multiple images to pdf'],
        'pdf-to-jpg' => ['pdf to jpg', 'pdf to image', 'pdf to png', 'convert pdf to jpg high quality'],
        'image-compressor' => ['compress image', 'image compressor', 'reduce image size', 'compress jpeg', 'photo size reducer', 'compress image without losing quality'],
        'image-resizer' => ['resize image', 'image resizer', 'photo resizer', 'resize image in cm', 'resize photo pixels', 'change image size'],
        'image-cropper' => ['crop image', 'image cropper', 'crop photo online', 'crop picture'],
        'rotate-flip' => ['rotate image', 'flip image', 'mirror image online', 'rotate photo 90 degrees'],
        'bulk-image-converter' => ['bulk image converter', 'batch convert images', 'convert multiple images'],
        'passport-photo' => ['passport size photo', 'passport photo maker', 'passport size photo online', '35x45 photo', 'passport photo 6 copies', 'visa photo maker'],
        'signature-cleaner' => ['signature background remover', 'transparent signature', 'signature png maker', 'clean signature scan'],
        'photo-kb-resizer' => ['resize image to 20kb', 'photo 50kb', 'reduce photo size to 100kb', 'image size in kb', 'photo resize for government form', 'signature 10kb'],
        'watermark-adder' => ['add watermark to photo', 'watermark image', 'logo on photo', 'text watermark'],
        'image-to-base64' => ['image to base64', 'base64 to image', 'base64 image encoder'],
        'favicon-generator' => ['favicon generator', 'favicon ico', 'png to ico', 'website icon maker'],
        'color-picker' => ['color picker from image', 'get color from image', 'hex color from photo', 'image color picker'],
        'exif-viewer' => ['exif viewer', 'photo metadata', 'remove exif data', 'image location remover'],
        'meme-generator' => ['meme generator', 'meme maker', 'make a meme', 'meme template'],
        'dpi-changer' => ['change dpi of image', '300 dpi converter', 'dpi changer', 'image dpi'],
        'pdf-merge' => ['merge pdf', 'combine pdf', 'join pdf files', 'pdf merger', 'merge pdf free'],
        'pdf-split' => ['split pdf', 'separate pdf pages', 'pdf splitter', 'cut pdf'],
        'pdf-compress' => ['compress pdf', 'reduce pdf size', 'pdf compressor', 'compress pdf to 100kb', 'pdf size reducer', 'compress pdf to 200kb'],
        'pdf-to-word' => ['pdf to word', 'pdf to docx', 'convert pdf to word', 'pdf to word converter free', 'editable pdf to word'],
        'word-to-pdf' => ['word to pdf', 'docx to pdf', 'doc to pdf', 'convert word to pdf'],
        'pdf-to-excel' => ['pdf to excel', 'pdf to xlsx', 'convert pdf table to excel', 'bank statement pdf to excel'],
        'excel-to-pdf' => ['excel to pdf', 'xlsx to pdf', 'convert excel to pdf'],
        'pdf-to-ppt' => ['pdf to ppt', 'pdf to powerpoint', 'convert pdf to pptx'],
        'ppt-to-pdf' => ['ppt to pdf', 'powerpoint to pdf', 'pptx to pdf'],
        'pdf-to-text' => ['pdf to text', 'extract text from pdf', 'copy text from pdf', 'pdf text extractor'],
        'pdf-rotate' => ['rotate pdf', 'rotate pdf pages', 'turn pdf page'],
        'pdf-delete-pages' => ['delete pdf pages', 'remove pages from pdf', 'pdf page remover'],
        'pdf-reorder' => ['reorder pdf pages', 'rearrange pdf pages', 'organize pdf'],
        'pdf-extract-pages' => ['extract pages from pdf', 'save pdf pages', 'pdf page extractor'],
        'pdf-protect' => ['password protect pdf', 'lock pdf', 'encrypt pdf', 'add password to pdf'],
        'pdf-unlock' => ['unlock pdf', 'remove pdf password', 'pdf password remover', 'aadhaar pdf password remove'],
        'pdf-page-numbers' => ['add page numbers to pdf', 'pdf page numbering', 'number pdf pages'],
        'pdf-watermark' => ['add watermark to pdf', 'pdf watermark', 'stamp pdf'],
        'pdf-metadata' => ['pdf metadata editor', 'change pdf title author', 'edit pdf properties'],
        'pdf-esign' => ['sign pdf', 'esign pdf', 'add signature to pdf', 'electronic signature pdf free'],
        'excel-to-csv' => ['excel to csv', 'xlsx to csv', 'convert excel to csv'],
        'csv-to-excel' => ['csv to excel', 'csv to xlsx', 'open csv in excel'],
        'json-to-excel' => ['json to excel', 'json to xlsx', 'json to csv'],
        'excel-to-json' => ['excel to json', 'xlsx to json', 'csv to json'],
        'json-formatter' => ['json formatter', 'json beautifier', 'json validator', 'format json online', 'pretty print json'],
        'xml-json' => ['xml to json', 'json to xml', 'xml converter'],
        'csv-merger' => ['merge csv files', 'combine csv', 'csv merger'],
        'word-counter' => ['word counter', 'word count', 'count words online', 'essay word counter', 'word count tool'],
        'case-converter' => ['case converter', 'uppercase to lowercase', 'lowercase to uppercase', 'title case converter', 'sentence case'],
        'find-replace' => ['find and replace text', 'replace words online', 'text replace tool'],
        'duplicate-remover' => ['remove duplicate lines', 'duplicate remover', 'remove duplicates from list', 'unique lines'],
        'text-diff' => ['text compare', 'diff checker', 'compare two texts', 'text difference checker'],
        'markdown-to-html' => ['markdown to html', 'md to html', 'markdown converter'],
        'html-to-text' => ['html to text', 'strip html tags', 'html to plain text'],
        'cctv-storage-calculator' => ['cctv storage calculator', 'hard disk for cctv calculator', 'cctv recording days calculator', 'nvr storage calculator', 'dvr hdd calculator'],
        'cctv-bandwidth-calculator' => ['cctv bandwidth calculator', 'ip camera bandwidth', 'cctv internet speed required'],
        'lens-calculator' => ['cctv lens calculator', 'focal length calculator', 'camera lens mm calculator'],
        'ip-subnet-calculator' => ['subnet calculator', 'ip subnet calculator', 'cidr calculator', 'subnet mask calculator'],
        'poe-calculator' => ['poe power budget calculator', 'poe switch calculator', 'poe watt calculator'],
        'cctv-quotation' => ['cctv quotation format', 'cctv quotation generator', 'cctv estimate', 'cctv installation quotation pdf'],
        'cctv-amc' => ['cctv amc format', 'cctv amc contract', 'annual maintenance contract format'],
        'cctv-password-reference' => ['hikvision default password', 'dahua default password', 'cp plus default password', 'dvr default password list'],
        'data-storage-converter' => ['gb to mb', 'mb to gb', 'tb to gb', 'data storage converter', 'kb to mb'],
        'ups-backup-time' => ['ups backup time calculator', 'inverter backup calculator', 'battery backup time'],
        'psu-calculator' => ['psu calculator', 'power supply calculator', 'pc wattage calculator'],
        'pc-build-cost' => ['pc build cost', 'gaming pc build price', 'computer build calculator'],
        'screen-ppi-calculator' => ['ppi calculator', 'screen size calculator', 'pixel density calculator'],
        'aspect-ratio-calculator' => ['aspect ratio calculator', '16:9 calculator', 'resolution calculator'],
        'download-time-calculator' => ['download time calculator', 'how long to download', 'file download time'],
        'internet-speed-test' => ['internet speed test', 'speed test', 'wifi speed test', 'net speed check', 'jio speed test', 'broadband speed test'],
        'mbps-converter' => ['mbps to mb/s', 'mbps to mbps', 'internet speed converter'],
        'barcode-generator' => ['barcode generator', 'barcode maker', 'ean 13 barcode', 'code 128 barcode', 'barcode for products'],
        'job-sheet-generator' => ['job sheet format', 'service job card', 'repair job sheet'],
        'gst-calculator' => ['gst calculator', 'gst calculation', 'gst inclusive calculator', 'reverse gst calculator', '18 gst calculator', 'gst amount calculator'],
        'invoice-generator' => ['invoice generator', 'gst invoice format', 'bill format', 'invoice maker free', 'tax invoice pdf', 'bill generator online'],
        'quotation-generator' => ['quotation format', 'quotation maker', 'quotation format in word', 'estimate format pdf'],
        'emi-calculator' => ['emi calculator', 'loan emi calculator', 'home loan emi calculator', 'car loan emi', 'personal loan emi calculator', 'emi calculation formula'],
        'margin-calculator' => ['profit margin calculator', 'markup calculator', 'margin vs markup', 'selling price calculator'],
        'discount-calculator' => ['discount calculator', 'percentage off calculator', 'sale price calculator', '20 percent off'],
        'currency-converter' => ['currency converter', 'usd to inr', 'dollar to rupee', 'aed to inr', 'euro to inr'],
        'warranty-tracker' => ['warranty expiry calculator', 'warranty date calculator', 'warranty tracker'],
        'salary-calculator' => ['salary calculator', 'payroll calculator', 'staff salary calculation'],
        'purchase-order' => ['purchase order format', 'po format', 'purchase order generator'],
        'days-calculator' => ['days between dates', 'day counter', 'date difference calculator', 'how many days until', 'days calculator'],
        'age-calculator' => ['age calculator', 'age calculator by date of birth', 'calculate my age', 'how old am i', 'dob calculator', 'age in years months days'],
        'date-add-subtract' => ['add days to date', 'date calculator', 'subtract days from date', '90 days from today'],
        'working-days-calculator' => ['working days calculator', 'business days calculator', 'days excluding sundays'],
        'epoch-converter' => ['epoch converter', 'unix timestamp converter', 'timestamp to date'],
        'timezone-converter' => ['time zone converter', 'ist to est', 'ist to pst', 'time difference india usa'],
        'countdown-timer' => ['countdown timer', 'stopwatch online', 'online timer', '5 minute timer', '10 minute timer'],
        'unit-converter' => ['unit converter', 'length converter', 'weight converter', 'cm to inches', 'kg to lbs'],
        'percentage-calculator' => ['percentage calculator', 'percent calculator', 'how to calculate percentage', 'marks percentage', 'percentage of a number'],
        'scientific-calculator' => ['scientific calculator', 'online calculator', 'calculator', 'casio calculator online'],
        'qr-generator' => ['qr code generator', 'qr code maker', 'create qr code', 'qr code for link', 'free qr code'],
        'qr-scanner' => ['qr code scanner', 'scan qr code online', 'qr reader', 'scan qr from image'],
        'password-generator' => ['password generator', 'strong password generator', 'random password', 'secure password'],
        'hash-generator' => ['md5 generator', 'sha256 generator', 'hash generator', 'sha1 hash'],
        'base64-encoder' => ['base64 encode', 'base64 decode', 'base64 converter'],
        'url-encoder' => ['url encode', 'url decode', 'urlencoder'],
        'lorem-ipsum' => ['lorem ipsum', 'dummy text generator', 'placeholder text'],
        'wifi-qr-card' => ['wifi qr code', 'wifi password qr', 'wifi qr generator'],
        'my-ip' => ['what is my ip', 'my ip address', 'ip address check', 'public ip'],
        'dns-lookup' => ['dns lookup', 'dns checker', 'mx record lookup', 'nslookup online'],
        'regex-tester' => ['regex tester', 'regular expression tester', 'regex online'],
        'uuid-generator' => ['uuid generator', 'guid generator', 'random uuid'],
        'color-converter' => ['hex to rgb', 'rgb to hex', 'color converter', 'hsl converter'],
        'base-converter' => ['binary to decimal', 'decimal to binary', 'hex to decimal', 'number base converter'],
        'meta-tag-generator' => ['meta tag generator', 'seo meta tags', 'open graph generator'],
        'text-to-binary' => ['text to binary', 'binary to text', 'ascii converter'],
    ];
}

/** Category words added to generated phrases (what people append when searching). */
function kt_cat_terms(): array {
    return [
        'image' => 'image', 'pdf' => 'pdf', 'cctv' => 'cctv', 'computer' => 'computer', 'business' => 'business',
        'finance' => 'india', 'tax' => 'india', 'construction' => 'construction', 'electrician' => 'electrical',
        'health' => 'health', 'student' => 'students', 'wishes' => 'whatsapp', 'restaurant' => 'restaurant', 'hotel' => 'hotel',
        'salon' => 'salon', 'printing' => 'printing', 'media' => 'youtube', 'fuel' => 'petrol', 'farming' => 'farmers',
    ];
}

/** Lower-case, remove "(…)", "–" tails and extra spaces: the core name people search. */
function kt_kw_core(string $name): string {
    $n = preg_replace('/\s*[\(\[].*?[\)\]]\s*/u', ' ', $name);
    $n = preg_replace('/\s+[–—|:]\s+.*$/u', '', $n);
    $n = str_replace(['&', '↔', '→', '/'], [' and ', 'to', 'to', ' '], $n);
    return trim(preg_replace('/\s+/', ' ', mb_strtolower($n)));
}

/**
 * Search phrases for a tool (most important first, unique, max $max).
 * Combines hand-picked phrases with ones derived from the name and tool type.
 */
function kt_tool_keywords(array $t, int $max = 14): array {
    static $core = null;
    $core ??= kt_core_keywords();
    $kw = array_merge($t['kw'] ?? [], $core[$t['slug']] ?? []);
    $name = $t['name_en'] ?? '';
    $c = kt_kw_core($name);
    if ($c !== '') $kw[] = $c;
    // Words inside brackets are often the exact query, e.g. "(Vyaj by Date)".
    if (preg_match_all('/\(([^)]{3,40})\)/u', $name, $m)) foreach ($m[1] as $p) {
        if (!preg_match('/^\d|,|^(with|for|from|incl|excl|per|by|using|in|on|and|or|as|the|a)\b/i', trim($p))) $kw[] = mb_strtolower(trim($p));
    }
    // Tail after a dash, e.g. "Spin the Wheel – Random Name Picker".
    if (preg_match('/\s[–—]\s(.+)$/u', $name, $m)) $kw[] = kt_kw_core($m[1]);

    $engine = $t['engine'] ?? '';
    $isCalc = str_contains($c, 'calculator') || in_array($engine, ['formula', 'calc'], true);
    $isMaker = preg_match('/maker|generator|format|creator|builder/', $c);
    $isText = in_array($engine, ['textgen'], true) || preg_match('/message|wishes|quotes|caption|status/', $c);
    $base = trim(preg_replace('/\b(calculator|generator|maker|converter|checker|format|online|free|tool)\b/', '', $c));
    $base = trim(preg_replace('/\s+/', ' ', $base));

    if ($c !== '') $kw[] = $c . ' online';
    $fun = in_array($t['cat'] ?? '', ['fun', 'wishes'], true);
    if ($isCalc && $base !== '' && !$fun) { $kw[] = $base . ' calculator online free'; $kw[] = 'how to calculate ' . $base; $kw[] = $base . ' formula'; }
    if ($isMaker && $base !== '') { $kw[] = $base . ' format'; $kw[] = $base . ' maker online free'; $kw[] = $base . ' pdf'; }
    if ($isText && $base !== '') { $kw[] = $base . ' for whatsapp'; $kw[] = $base . ' in gujarati'; $kw[] = $base . ' status'; }
    if (str_contains($c, 'converter') && $base !== '') { $kw[] = 'convert ' . $base; }
    if ($engine === 'card') { $kw[] = $base . ' card maker'; $kw[] = $base . ' poster online'; }
    $cat = kt_cat_terms()[$t['cat'] ?? ''] ?? '';
    if ($cat === 'india' && $base !== '' && !str_contains($base, 'india')) $kw[] = $base . ' india';
    $kw[] = 'free ' . ($c ?: $base);

    $out = []; $seen = [];
    foreach ($kw as $k) {
        $k = trim(preg_replace('/\s+/', ' ', mb_strtolower((string) $k)));
        if ($k === '' || mb_strlen($k) < 3 || isset($seen[$k])) continue;
        $seen[$k] = 1; $out[] = $k;
        if (count($out) >= $max) break;
    }
    return $out;
}

/** <title>: the main search phrase first, ≤ ~65 characters where possible. */
function kt_seo_title(array $t): string {
    $name = $t['name_en'];
    $brand = brand_name();
    $isFree = stripos($name, 'free') !== false;
    $suffixes = $isFree ? [' | ' . $brand, ''] : [' – Free Online | ' . $brand, ' – Free Online', ' (Free) | ' . $brand, ' | ' . $brand, ''];
    foreach ($suffixes as $s) {
        if (mb_strlen($name . $s) <= 68) return $name . $s;
    }
    return $name;
}

/** Meta description: tool description + benefits, trimmed to ~158 characters. */
function kt_seo_desc(array $t): string {
    $d = trim($t['desc_en'] ?? '');
    // Very short descriptions (older core tools) get the top search phrases added.
    if (mb_strlen($d) < 80) {
        $kw = array_slice(kt_tool_keywords($t), 1, 3);
        if ($kw) $d = rtrim($d, '.') . '. Popular uses: ' . implode(', ', $kw) . '.';
    }
    $tail = ' Free, instant, no login – works on mobile.';
    $s = $d;
    if (mb_strlen($s . $tail) <= 158) $s .= $tail;
    elseif (mb_strlen($s) > 158) {
        // Cut at the last full stop or comma that fits, so the snippet reads naturally.
        $cut = mb_substr($s, 0, 156);
        $p = max(mb_strrpos($cut, '. ') ?: 0, mb_strrpos($cut, ', ') ?: 0);
        $s = $p > 90 ? rtrim(mb_substr($cut, 0, $p), ' ,') . '.' : rtrim(mb_substr($cut, 0, 154), " ,.;:") . '…';
    }
    return $s;
}

/** Short visible paragraph that naturally uses the main search phrases. */
function kt_tool_about(array $t, array $kw): string {
    if (!empty($t['about'])) return $t['about'];
    $name = $t['name_en'];
    $alt = array_values(array_filter(array_slice($kw, 0, 6), fn($k) => mb_strtolower($k) !== mb_strtolower($name)));
    $also = $alt ? ' People also search for it as ' . implode(', ', array_map(fn($k) => '"' . $k . '"', array_slice($alt, 0, 3))) . '.' : '';
    $kind = match ($t['engine'] ?? '') {
        'formula' => 'Type your numbers and the answer updates instantly.',
        'textgen' => 'Fill in the names or details and pick a ready-made message to copy or send on WhatsApp.',
        'card' => 'Type your text, choose a size and colours, and download a ready image for WhatsApp status or Instagram.',
        'doc' => 'Fill in the form and print it or download a PDF in seconds.',
        default => 'Everything works right in your browser – on mobile or computer.',
    };
    return "{$name} by Krishna Tools is a free online tool. {$kind}{$also} There is no sign-up, no app to install and your data stays in your browser.";
}

/**
 * Related tools for internal linking: tools whose names share real words with
 * this one (any category) first, then neighbours from the same category, so
 * each page links to different, relevant pages instead of the same first five.
 */
function kt_related_smart(string $slug, int $n = 8): array {
    $tool = kt_tool($slug);
    if (!$tool) return [];
    $stop = array_flip(['calculator', 'generator', 'maker', 'converter', 'online', 'free', 'tool', 'tools', 'and', 'for', 'the', 'with', 'to', 'of', 'in', 'by', 'from', 'per', 'your', 'how', 'message', 'card', 'format', 'india', 'a', 'my', 'is', 'on']);
    $words = function (array $t) use ($stop): array {
        $w = preg_split('/[^a-z0-9]+/', mb_strtolower($t['name_en'] . ' ' . str_replace('-', ' ', $t['slug'])));
        return array_values(array_unique(array_filter($w, fn($x) => strlen($x) > 2 && !isset($stop[$x]))));
    };
    $mine = array_flip($words($tool));
    $scored = [];
    foreach (kt_tools() as $t) {
        if ($t['slug'] === $slug) continue;
        $s = 0;
        foreach ($words($t) as $w) if (isset($mine[$w])) $s += 3;
        if ($t['cat'] === $tool['cat']) $s += 1;
        if ($s > 1) $scored[] = [$s, $t];
    }
    usort($scored, fn($a, $b) => $b[0] <=> $a[0]);
    $out = array_map(fn($x) => $x[1], array_slice($scored, 0, $n));
    if (count($out) < $n) {
        // Fill with the tools next to this one in its category (wraps around).
        $sib = array_values(kt_tools_by_cat($tool['cat']));
        $idx = 0;
        foreach ($sib as $i => $t) if ($t['slug'] === $slug) $idx = $i;
        $have = array_flip(array_map(fn($t) => $t['slug'], $out));
        for ($k = 1; $k < count($sib) && count($out) < $n; $k++) {
            $t = $sib[($idx + $k) % count($sib)];
            if ($t['slug'] !== $slug && !isset($have[$t['slug']])) { $out[] = $t; $have[$t['slug']] = 1; }
        }
    }
    return $out;
}
