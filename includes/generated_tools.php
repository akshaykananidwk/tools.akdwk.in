<?php
/**
 * KRISHNA TOOLS — programmatic (generated) converter tools.
 *
 * A single template + a structured dataset produces thousands of real,
 * genuinely-useful converter pages: /convert/<from>-to-<to> and
 * /convert/<value>-<from>-to-<to>. Every page computes a correct answer,
 * shows a conversion table, formula and FAQ, and carries unique SEO metadata
 * — the "quality-at-scale" approach Google rewards (not thin doorway pages).
 *
 * The dataset drives: the router (convert.php), the converters hub, the
 * sitemap index, and internal linking.
 */

/**
 * Unit categories. Each unit: [key => [factor-to-base, name, symbol]].
 * Conversion: value * from.factor / to.factor  (linear categories).
 * Temperature is handled specially (offsets), see gen_convert().
 */
function gen_unit_categories(): array {
    return [
        'length' => ['name' => 'Length', 'icon' => 'ruler', 'base' => 'meter', 'units' => [
            'meter' => [1, 'Meter', 'm'], 'kilometer' => [1000, 'Kilometer', 'km'],
            'centimeter' => [0.01, 'Centimeter', 'cm'], 'millimeter' => [0.001, 'Millimeter', 'mm'],
            'micrometer' => [1e-6, 'Micrometer', 'µm'], 'nanometer' => [1e-9, 'Nanometer', 'nm'],
            'mile' => [1609.344, 'Mile', 'mi'], 'yard' => [0.9144, 'Yard', 'yd'],
            'foot' => [0.3048, 'Foot', 'ft'], 'inch' => [0.0254, 'Inch', 'in'],
            'nautical-mile' => [1852, 'Nautical Mile', 'nmi'], 'furlong' => [201.168, 'Furlong', 'fur'],
            'light-year' => [9.4607e15, 'Light Year', 'ly'], 'decimeter' => [0.1, 'Decimeter', 'dm'],
        ]],
        'weight' => ['name' => 'Weight & Mass', 'icon' => 'scale', 'base' => 'kilogram', 'units' => [
            'kilogram' => [1, 'Kilogram', 'kg'], 'gram' => [0.001, 'Gram', 'g'],
            'milligram' => [1e-6, 'Milligram', 'mg'], 'microgram' => [1e-9, 'Microgram', 'µg'],
            'metric-ton' => [1000, 'Metric Ton', 't'], 'pound' => [0.45359237, 'Pound', 'lb'],
            'ounce' => [0.0283495231, 'Ounce', 'oz'], 'stone' => [6.35029318, 'Stone', 'st'],
            'quintal' => [100, 'Quintal', 'q'], 'carat' => [0.0002, 'Carat', 'ct'],
            'tola' => [0.01166, 'Tola', 'tola'], 'us-ton' => [907.18474, 'US Ton', 'ton'],
            'grain' => [6.479891e-5, 'Grain', 'gr'],
        ]],
        'temperature' => ['name' => 'Temperature', 'icon' => 'thermometer', 'base' => 'celsius', 'special' => 'temp', 'units' => [
            'celsius' => [1, 'Celsius', '°C'], 'fahrenheit' => [1, 'Fahrenheit', '°F'],
            'kelvin' => [1, 'Kelvin', 'K'], 'rankine' => [1, 'Rankine', '°R'],
        ]],
        'area' => ['name' => 'Area', 'icon' => 'square', 'base' => 'square-meter', 'units' => [
            'square-meter' => [1, 'Square Meter', 'm²'], 'square-kilometer' => [1e6, 'Square Kilometer', 'km²'],
            'square-centimeter' => [1e-4, 'Square Centimeter', 'cm²'], 'square-mile' => [2589988.11, 'Square Mile', 'mi²'],
            'square-yard' => [0.83612736, 'Square Yard', 'yd²'], 'square-foot' => [0.09290304, 'Square Foot', 'ft²'],
            'square-inch' => [0.00064516, 'Square Inch', 'in²'], 'acre' => [4046.8564224, 'Acre', 'ac'],
            'hectare' => [10000, 'Hectare', 'ha'], 'bigha' => [1618.7, 'Bigha', 'bigha'],
            'guntha' => [101.17, 'Guntha', 'guntha'],
        ]],
        'volume' => ['name' => 'Volume', 'icon' => 'flask-conical', 'base' => 'liter', 'units' => [
            'liter' => [1, 'Liter', 'L'], 'milliliter' => [0.001, 'Milliliter', 'mL'],
            'cubic-meter' => [1000, 'Cubic Meter', 'm³'], 'cubic-centimeter' => [0.001, 'Cubic Centimeter', 'cm³'],
            'gallon-us' => [3.785411784, 'US Gallon', 'gal'], 'gallon-uk' => [4.54609, 'UK Gallon', 'gal'],
            'quart' => [0.946352946, 'Quart', 'qt'], 'pint' => [0.473176473, 'Pint', 'pt'],
            'cup' => [0.24, 'Cup', 'cup'], 'fluid-ounce' => [0.0295735296, 'Fluid Ounce', 'fl oz'],
            'tablespoon' => [0.0147868, 'Tablespoon', 'tbsp'], 'teaspoon' => [0.00492892, 'Teaspoon', 'tsp'],
            'cubic-foot' => [28.316846592, 'Cubic Foot', 'ft³'], 'cubic-inch' => [0.016387064, 'Cubic Inch', 'in³'],
            'barrel' => [158.987294928, 'Barrel', 'bbl'],
        ]],
        'speed' => ['name' => 'Speed', 'icon' => 'gauge', 'base' => 'meter-per-second', 'units' => [
            'meter-per-second' => [1, 'Meter/second', 'm/s'], 'kilometer-per-hour' => [0.277777778, 'Kilometer/hour', 'km/h'],
            'mile-per-hour' => [0.44704, 'Mile/hour', 'mph'], 'foot-per-second' => [0.3048, 'Foot/second', 'ft/s'],
            'knot' => [0.514444444, 'Knot', 'kn'], 'mach' => [343, 'Mach', 'Ma'],
            'kilometer-per-second' => [1000, 'Kilometer/second', 'km/s'],
        ]],
        'time' => ['name' => 'Time', 'icon' => 'clock', 'base' => 'second', 'units' => [
            'second' => [1, 'Second', 's'], 'millisecond' => [0.001, 'Millisecond', 'ms'],
            'microsecond' => [1e-6, 'Microsecond', 'µs'], 'minute' => [60, 'Minute', 'min'],
            'hour' => [3600, 'Hour', 'h'], 'day' => [86400, 'Day', 'd'],
            'week' => [604800, 'Week', 'wk'], 'month' => [2629746, 'Month', 'mo'],
            'year' => [31556952, 'Year', 'yr'], 'decade' => [315569520, 'Decade', 'dec'],
            'nanosecond' => [1e-9, 'Nanosecond', 'ns'],
        ]],
        'digital' => ['name' => 'Digital Storage', 'icon' => 'hard-drive', 'base' => 'byte', 'units' => [
            'bit' => [0.125, 'Bit', 'b'], 'byte' => [1, 'Byte', 'B'],
            'kilobyte' => [1000, 'Kilobyte', 'KB'], 'megabyte' => [1e6, 'Megabyte', 'MB'],
            'gigabyte' => [1e9, 'Gigabyte', 'GB'], 'terabyte' => [1e12, 'Terabyte', 'TB'],
            'petabyte' => [1e15, 'Petabyte', 'PB'], 'kibibyte' => [1024, 'Kibibyte', 'KiB'],
            'mebibyte' => [1048576, 'Mebibyte', 'MiB'], 'gibibyte' => [1073741824, 'Gibibyte', 'GiB'],
            'tebibyte' => [1099511627776, 'Tebibyte', 'TiB'],
        ]],
        'data-rate' => ['name' => 'Data Transfer Rate', 'icon' => 'wifi', 'base' => 'bit-per-second', 'units' => [
            'bit-per-second' => [1, 'Bit/second', 'bps'], 'kilobit-per-second' => [1000, 'Kilobit/second', 'Kbps'],
            'megabit-per-second' => [1e6, 'Megabit/second', 'Mbps'], 'gigabit-per-second' => [1e9, 'Gigabit/second', 'Gbps'],
            'byte-per-second' => [8, 'Byte/second', 'B/s'], 'kilobyte-per-second' => [8000, 'Kilobyte/second', 'KB/s'],
            'megabyte-per-second' => [8e6, 'Megabyte/second', 'MB/s'], 'gigabyte-per-second' => [8e9, 'Gigabyte/second', 'GB/s'],
        ]],
        'pressure' => ['name' => 'Pressure', 'icon' => 'wind', 'base' => 'pascal', 'units' => [
            'pascal' => [1, 'Pascal', 'Pa'], 'kilopascal' => [1000, 'Kilopascal', 'kPa'],
            'bar' => [100000, 'Bar', 'bar'], 'psi' => [6894.757, 'PSI', 'psi'],
            'atmosphere' => [101325, 'Atmosphere', 'atm'], 'torr' => [133.322, 'Torr', 'Torr'],
            'millibar' => [100, 'Millibar', 'mbar'], 'mmhg' => [133.322, 'mmHg', 'mmHg'],
        ]],
        'energy' => ['name' => 'Energy', 'icon' => 'zap', 'base' => 'joule', 'units' => [
            'joule' => [1, 'Joule', 'J'], 'kilojoule' => [1000, 'Kilojoule', 'kJ'],
            'calorie' => [4.184, 'Calorie', 'cal'], 'kilocalorie' => [4184, 'Kilocalorie', 'kcal'],
            'watt-hour' => [3600, 'Watt-hour', 'Wh'], 'kilowatt-hour' => [3.6e6, 'Kilowatt-hour', 'kWh'],
            'electronvolt' => [1.602176634e-19, 'Electronvolt', 'eV'], 'btu' => [1055.06, 'BTU', 'BTU'],
            'foot-pound' => [1.35582, 'Foot-pound', 'ft·lb'],
        ]],
        'power' => ['name' => 'Power', 'icon' => 'plug', 'base' => 'watt', 'units' => [
            'watt' => [1, 'Watt', 'W'], 'kilowatt' => [1000, 'Kilowatt', 'kW'],
            'megawatt' => [1e6, 'Megawatt', 'MW'], 'horsepower' => [745.699872, 'Horsepower', 'hp'],
            'metric-horsepower' => [735.49875, 'Metric Horsepower', 'PS'], 'btu-per-hour' => [0.29307107, 'BTU/hour', 'BTU/h'],
            'milliwatt' => [0.001, 'Milliwatt', 'mW'],
        ]],
        'angle' => ['name' => 'Angle', 'icon' => 'triangle', 'base' => 'degree', 'units' => [
            'degree' => [1, 'Degree', '°'], 'radian' => [57.2957795, 'Radian', 'rad'],
            'gradian' => [0.9, 'Gradian', 'grad'], 'arcminute' => [0.0166667, 'Arcminute', "'"],
            'arcsecond' => [0.000277778, 'Arcsecond', '"'], 'revolution' => [360, 'Revolution', 'rev'],
        ]],
        'frequency' => ['name' => 'Frequency', 'icon' => 'radio', 'base' => 'hertz', 'units' => [
            'hertz' => [1, 'Hertz', 'Hz'], 'kilohertz' => [1000, 'Kilohertz', 'kHz'],
            'megahertz' => [1e6, 'Megahertz', 'MHz'], 'gigahertz' => [1e9, 'Gigahertz', 'GHz'],
            'rpm' => [0.0166667, 'RPM', 'rpm'],
        ]],
        'fuel' => ['name' => 'Fuel Economy', 'icon' => 'fuel', 'base' => 'km-per-liter', 'special' => 'fuel', 'units' => [
            'km-per-liter' => [1, 'Kilometer/liter', 'km/L'], 'mpg-us' => [0.425144, 'MPG (US)', 'mpg'],
            'mpg-uk' => [0.354006, 'MPG (UK)', 'mpg'], 'liter-per-100km' => [-1, 'Liter/100km', 'L/100km'],
        ]],
    ];
}

/** Number-base conversions (binary/octal/decimal/hex + more). */
function gen_number_bases(): array {
    return [
        'binary' => ['name' => 'Binary', 'base' => 2], 'octal' => ['name' => 'Octal', 'base' => 8],
        'decimal' => ['name' => 'Decimal', 'base' => 10], 'hexadecimal' => ['name' => 'Hexadecimal', 'base' => 16],
    ];
}

/** All directional unit pairs (from != to) across every linear + special category. */
function gen_all_pairs(): array {
    $pairs = [];
    foreach (gen_unit_categories() as $catKey => $cat) {
        $units = array_keys($cat['units']);
        foreach ($units as $from) {
            foreach ($units as $to) {
                if ($from === $to) continue;
                $pairs[] = ['cat' => $catKey, 'from' => $from, 'to' => $to];
            }
        }
    }
    return $pairs;
}

/** Most-searched leading values for value-specific pages (e.g. "5 km to miles"). */
function gen_common_values(): array {
    return [1, 2, 3, 5, 10, 15, 20, 25, 30, 50, 100, 500, 1000];
}

/** Total count of generated pages (pair pages + value pages) for reporting. */
function gen_total_count(): array {
    $pairs = count(gen_all_pairs());
    $values = $pairs * count(gen_common_values());
    return ['pairs' => $pairs, 'value_pages' => $values, 'total' => $pairs + $values];
}

/**
 * Convert a value within a category. Returns float (or null on error).
 * Handles temperature offsets and fuel-economy inversion specially.
 */
function gen_convert(string $cat, float $value, string $from, string $to) {
    $cats = gen_unit_categories();
    if (!isset($cats[$cat]['units'][$from], $cats[$cat]['units'][$to])) return null;
    $special = $cats[$cat]['special'] ?? '';

    if ($special === 'temp') {
        // Normalise to Celsius, then to target.
        $c = match ($from) {
            'celsius' => $value,
            'fahrenheit' => ($value - 32) * 5 / 9,
            'kelvin' => $value - 273.15,
            'rankine' => ($value - 491.67) * 5 / 9,
            default => $value,
        };
        return match ($to) {
            'celsius' => $c,
            'fahrenheit' => $c * 9 / 5 + 32,
            'kelvin' => $c + 273.15,
            'rankine' => ($c + 273.15) * 9 / 5,
            default => $c,
        };
    }

    if ($special === 'fuel') {
        // Convert everything through km/L; L/100km is an inverse measure.
        $toKmL = fn($u, $v) => $u === 'liter-per-100km' ? ($v == 0 ? 0 : 100 / $v) : $v * $cats[$cat]['units'][$u][0];
        $kmL = $toKmL($from, $value);
        if ($to === 'liter-per-100km') return $kmL == 0 ? 0 : 100 / $kmL;
        return $kmL / $cats[$cat]['units'][$to][0];
    }

    // Linear categories.
    $base = $value * $cats[$cat]['units'][$from][0];
    return $base / $cats[$cat]['units'][$to][0];
}

/** Common unit-abbreviation aliases → canonical unit key (people search these). */
function gen_aliases(): array {
    return [
        // length
        'm' => 'meter', 'km' => 'kilometer', 'cm' => 'centimeter', 'mm' => 'millimeter',
        'mi' => 'mile', 'miles' => 'mile', 'ft' => 'foot', 'feet' => 'foot', 'in' => 'inch',
        'inches' => 'inch', 'yd' => 'yard', 'yards' => 'yard', 'nm' => 'nanometer',
        // weight
        'kg' => 'kilogram', 'kgs' => 'kilogram', 'g' => 'gram', 'mg' => 'milligram',
        'lb' => 'pound', 'lbs' => 'pound', 'pounds' => 'pound', 'oz' => 'ounce',
        'ton' => 'metric-ton', 'tonne' => 'metric-ton', 't' => 'metric-ton',
        // temperature
        'c' => 'celsius', 'f' => 'fahrenheit', 'k' => 'kelvin',
        // speed
        'mph' => 'mile-per-hour', 'kmh' => 'kilometer-per-hour', 'kph' => 'kilometer-per-hour',
        // digital + data rate
        'kb' => 'kilobyte', 'mb' => 'megabyte', 'gb' => 'gigabyte', 'tb' => 'terabyte',
        'mbps' => 'megabit-per-second', 'gbps' => 'gigabit-per-second', 'kbps' => 'kilobit-per-second',
        // volume
        'l' => 'liter', 'ml' => 'milliliter', 'gal' => 'gallon-us',
    ];
}

/** Resolve a single unit token through the alias map. */
function gen_resolve_unit(string $token): string {
    $a = gen_aliases();
    return $a[$token] ?? $token;
}

/** Build the canonical slug for a (value, from, to) using full unit keys. */
function gen_canonical_slug(?float $value, string $from, string $to): string {
    return ($value !== null ? gen_fmt($value) . '-' : '') . $from . '-to-' . $to;
}

/**
 * Parse a converter slug into its parts, applying abbreviation aliases.
 * Accepts:  <from>-to-<to>          (pair page)
 *           <value>-<from>-to-<to>  (value page, value may be a decimal)
 * Returns ['cat','from','to','value'|null,'canonical'=>bool] or null.
 */
function gen_parse_slug(string $slug): ?array {
    $orig = $slug = strtolower(trim($slug));
    if (!str_contains($slug, '-to-')) return null;

    // Optional leading numeric value.
    $value = null;
    if (preg_match('/^(\d+(?:\.\d+)?)-(.+)$/', $slug, $m)) {
        $rest = $m[2];
        if (str_contains($rest, '-to-')) { $value = (float) $m[1]; $slug = $rest; }
    }

    [$from, $to] = explode('-to-', $slug, 2);
    $from = gen_resolve_unit(trim($from, '-'));
    $to   = gen_resolve_unit(trim($to, '-'));

    foreach (gen_unit_categories() as $catKey => $cat) {
        if (isset($cat['units'][$from], $cat['units'][$to])) {
            $canonicalSlug = gen_canonical_slug($value, $from, $to);
            return ['cat' => $catKey, 'from' => $from, 'to' => $to, 'value' => $value,
                    'is_canonical' => ($orig === $canonicalSlug), 'canonical_slug' => $canonicalSlug];
        }
    }
    return null;
}

/** Human labels for a unit within a category. */
function gen_unit_label(string $cat, string $unit): array {
    $u = gen_unit_categories()[$cat]['units'][$unit] ?? [1, ucfirst($unit), $unit];
    return ['name' => $u[1], 'symbol' => $u[2]];
}

/** Pretty-format a converted number. */
function gen_fmt(float $n): string {
    if ($n == 0) return '0';
    $abs = abs($n);
    if ($abs >= 1e15 || ($abs < 1e-6 && $abs > 0)) return rtrim(rtrim(sprintf('%.6e', $n), '0'), '.');
    $decimals = $abs >= 100 ? 2 : ($abs >= 1 ? 4 : 8);
    return rtrim(rtrim(number_format($n, $decimals, '.', ','), '0'), '.');
}
