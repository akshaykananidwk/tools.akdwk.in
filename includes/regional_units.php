<?php
/**
 * KRISHNA TOOLS — units whose size changes from region to region (maund, seer, bigha/vigha).
 *
 * The page uses the default region (Gujarat) and shows a region drop-down that recalculates in the
 * browser. Each value carries its source. The default factor here must equal the factor in
 * gen_unit_categories(). 'nm' is the name the unit goes by in that region (Gujarat: "Vigha").
 *
 * Units with ONE value everywhere (guntha, gaj/var = square yard) get a note, not a drop-down.
 * Owner decisions (Nov 2026): seer Gujarat 500 g (colloquial); vigha Gujarat (Saurashtra) 16 guntha only;
 * no Punjab / Haryana / West UP bigha for now; maund Gujarat 20 kg with 40 kg and 37.3242 kg options.
 */
const KT_SQFT = 0.09290304; // m² per square foot

function gen_regions(): array {
    $s4 = 'BIS SP 4 (1969) "Metric Change in India", Standards of Weights and Measures Act 1956, First Schedule';
    return [
        'maund' => ['default' => 'gujarat', 'list' => [
            'gujarat'  => ['f' => 20, 'en' => 'Gujarat (market yard)', 'src' => 'Gujarat market-yard practice (1 man = 20 kg); historical Gujarat maund 17–20 kg (Ahmedabad, Baroda, Surat)'],
            'rounded'  => ['f' => 40, 'en' => 'Rounded maund (40 kg)', 'src' => 'Rounded 40 kg maund used in parts of North India, Pakistan, Bangladesh and the Nepal Terai'],
            'standard' => ['f' => 37.3242, 'en' => 'Indian standard (1956 Act)', 'src' => $s4 . ': 1 maund = 40 seer = 37.3242 kg'],
        ]],
        'ser' => ['default' => 'gujarat', 'list' => [
            'gujarat'  => ['f' => 0.5, 'en' => 'Gujarat (colloquial)', 'src' => 'Colloquial Gujarat measure: 1 maund of 20 kg ÷ 40 seer = 500 g. Not an official standard.'],
            'standard' => ['f' => 0.9331, 'en' => 'Indian standard (80 tola)', 'src' => $s4 . ': 1 seer = 80 tola = 0.93310 kg'],
        ]],
        'bigha' => ['default' => 'gujarat', 'list' => [
            'gujarat'    => ['f' => 17424 * KT_SQFT, 'nm' => 'Vigha', 'en' => 'Gujarat (Saurashtra)', 'src' => '17,424 sq ft = 16 guntha (Gujarat usage; Kotak Mahindra Bank land-unit guide). Other parts of Gujarat may differ — confirm with the 7/12 extract or the talati.'],
            'rajasthan'  => ['f' => 27225 * KT_SQFT, 'en' => 'Rajasthan (pucca bigha)', 'src' => '27,225 sq ft = 3,025 sq yd (Wikipedia "Bigha", citing sizes.com)'],
            'rajasthan-kachha' => ['f' => 17424 * KT_SQFT, 'en' => 'Rajasthan (kachha bigha)', 'src' => '17,424 sq ft (Wikipedia "Bigha")'],
            'up'         => ['f' => 27225 * KT_SQFT, 'en' => 'Uttar Pradesh (east, Lucknow)', 'src' => '27,225 sq ft = 20 biswa (Wikipedia "Bigha"); western UP districts use smaller bighas'],
            'bihar'      => ['f' => 27225 * KT_SQFT, 'en' => 'Bihar (Patna)', 'src' => '27,225 sq ft = 20 katha (Wikipedia "Bigha"); katha size differs by district'],
            'bengal'     => ['f' => 14400 * KT_SQFT, 'en' => 'West Bengal', 'src' => '14,400 sq ft = 20 katha = 33 decimal (Wikipedia "Bigha"; Kotak guide)'],
            'assam'      => ['f' => 14400 * KT_SQFT, 'en' => 'Assam', 'src' => '14,400 sq ft = 5 katha = 100 lessa (Wikipedia "Bigha", citing Assam land revenue; lessa 144 sq ft in BIS SP 4)'],
            'mp'         => ['f' => 12000 * KT_SQFT, 'en' => 'Madhya Pradesh', 'src' => '12,000 sq ft (Wikipedia "Bigha"; Kotak guide)'],
            'himachal'   => ['f' => 8712 * KT_SQFT, 'en' => 'Himachal Pradesh', 'src' => '8,712 sq ft, 5 bigha = 1 acre (Wikipedia "Bigha"; Kotak guide)'],
            'uttarakhand' => ['f' => 6806.25 * KT_SQFT, 'en' => 'Uttarakhand (hills)', 'src' => '6,806.25 sq ft (Wikipedia "Bigha")'],
        ]],
    ];
}

/** Extra line shown under the "value used" note (default region only). */
function gen_region_extra(string $unit): string {
    return [
        'ser' => 'The 500 g Gujarat seer is a spoken, colloquial measure; the official Indian standard seer is 933.1 g.',
        'bigha' => 'The vigha can be a different size in other parts of Gujarat — confirm with the 7/12 extract or the village talati before a land deal.',
    ][$unit] ?? '';
}

/** Units with one fixed size everywhere: note shown on the page instead of a drop-down. */
function gen_fixed_regional_notes(): array {
    return [
        'guntha' => 'A guntha is the same everywhere it is used (Gujarat, Maharashtra, Karnataka): 1 guntha = 1/40 acre = 1,089 sq ft = 121 sq yd = 101.17 m².',
        'square-yard' => 'Gaj (Delhi, UP, Punjab) and var (Gujarat) both mean one square yard: 1 gaj = 1 var = 9 sq ft = 0.8361 m². The size does not change by state.',
    ];
}

/** Region data for the units of one page, for the browser: {unit: {def, list: {key: {f, l, nm}}}}. */
function gen_regions_for(array $units): array {
    $out = [];
    $R = gen_regions();
    foreach ($units as $u) {
        if (!isset($R[$u]) || count($R[$u]['list']) < 2) continue;
        $list = [];
        foreach ($R[$u]['list'] as $k => $r) $list[$k] = ['f' => $r['f'], 'l' => $r['en'], 'nm' => $r['nm'] ?? ''];
        $out[$u] = ['def' => $R[$u]['default'], 'list' => $list];
    }
    return $out;
}

/** The default region row of a unit, or null. */
function gen_region_default(string $unit): ?array {
    $R = gen_regions()[$unit] ?? null;
    return $R ? $R['list'][$R['default']] : null;
}
