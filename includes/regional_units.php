<?php
/**
 * KRISHNA TOOLS — units whose size changes from region to region (maund, seer, bigha/vigha).
 *
 * The page uses the default region (Gujarat where a Gujarat value is known) and shows a
 * region drop-down that recalculates in the browser. Each value carries its source.
 * The default factor here must equal the factor in gen_unit_categories().
 *
 * Units with ONE value everywhere (guntha, gaj/var = square yard) get a note, not a drop-down.
 */
const KT_SQFT = 0.09290304; // m² per square foot

function gen_regions(): array {
    $s4 = 'BIS SP 4 (1969) "Metric Change in India", Standards of Weights and Measures Act 1956, First Schedule';
    return [
        'maund' => ['default' => 'gujarat', 'list' => [
            'gujarat'  => ['f' => 20, 'en' => 'Gujarat (market yard)', 'gu' => 'ગુજરાત (માર્કેટ યાર્ડ)', 'hi' => 'गुजरात (मंडी)', 'src' => 'Gujarat market-yard practice (1 મણ = 20 કિલો); historical Gujarat maund 17–20 kg (Ahmedabad, Baroda, Surat)'],
            'standard' => ['f' => 37.3242, 'en' => 'Indian standard (1956 Act)', 'gu' => 'ભારતીય ધોરણ (1956 કાયદો)', 'hi' => 'भारतीय मानक (1956 क़ानून)', 'src' => $s4 . ': 1 maund = 40 seer = 37.3242 kg'],
            'rounded'  => ['f' => 40, 'en' => 'Rounded maund (40 kg)', 'gu' => 'ગોળ આંકડો (40 કિલો)', 'hi' => 'गोल आँकड़ा (40 किलो)', 'src' => 'Rounded 40 kg maund still used in parts of North India, Pakistan, Bangladesh and the Nepal Terai'],
        ]],
        'ser' => ['default' => 'standard', 'list' => [
            'standard' => ['f' => 0.9331, 'en' => 'Indian standard (80 tola)', 'gu' => 'ભારતીય ધોરણ (80 તોલા)', 'hi' => 'भारतीय मानक (80 तोला)', 'src' => $s4 . ': 1 seer = 80 tola = 0.93310 kg'],
        ]],
        'bigha' => ['default' => 'gujarat', 'list' => [
            'gujarat'    => ['f' => 17424 * KT_SQFT, 'en' => 'Gujarat (16 guntha)', 'gu' => 'ગુજરાત (16 ગુંઠા)', 'hi' => 'गुजरात (16 गुंठा)', 'src' => '17,424 sq ft = 16 guntha (Gujarat usage; Kotak Mahindra Bank land-unit guide)'],
            'rajasthan'  => ['f' => 27225 * KT_SQFT, 'en' => 'Rajasthan (pucca bigha)', 'gu' => 'રાજસ્થાન (પાકો વીઘો)', 'hi' => 'राजस्थान (पक्का बीघा)', 'src' => '27,225 sq ft = 3,025 sq yd (Wikipedia "Bigha", citing sizes.com)'],
            'rajasthan-kachha' => ['f' => 17424 * KT_SQFT, 'en' => 'Rajasthan (kachha bigha)', 'gu' => 'રાજસ્થાન (કાચો વીઘો)', 'hi' => 'राजस्थान (कच्चा बीघा)', 'src' => '17,424 sq ft (Wikipedia "Bigha")'],
            'up'         => ['f' => 27225 * KT_SQFT, 'en' => 'Uttar Pradesh (east, Lucknow)', 'gu' => 'ઉત્તર પ્રદેશ (પૂર્વ, લખનૌ)', 'hi' => 'उत्तर प्रदेश (पूर्वी, लखनऊ)', 'src' => '27,225 sq ft = 20 biswa (Wikipedia "Bigha"); western UP districts use smaller bighas'],
            'bihar'      => ['f' => 27225 * KT_SQFT, 'en' => 'Bihar (Patna)', 'gu' => 'બિહાર (પટના)', 'hi' => 'बिहार (पटना)', 'src' => '27,225 sq ft = 20 katha (Wikipedia "Bigha"); katha size differs by district'],
            'bengal'     => ['f' => 14400 * KT_SQFT, 'en' => 'West Bengal', 'gu' => 'પશ્ચિમ બંગાળ', 'hi' => 'पश्चिम बंगाल', 'src' => '14,400 sq ft = 20 katha = 33 decimal (Wikipedia "Bigha"; Kotak guide)'],
            'assam'      => ['f' => 14400 * KT_SQFT, 'en' => 'Assam', 'gu' => 'આસામ', 'hi' => 'असम', 'src' => '14,400 sq ft = 5 katha = 100 lessa (Wikipedia "Bigha", citing Assam land revenue; lessa 144 sq ft in BIS SP 4)'],
            'mp'         => ['f' => 12000 * KT_SQFT, 'en' => 'Madhya Pradesh', 'gu' => 'મધ્ય પ્રદેશ', 'hi' => 'मध्य प्रदेश', 'src' => '12,000 sq ft (Wikipedia "Bigha"; Kotak guide)'],
            'himachal'   => ['f' => 8712 * KT_SQFT, 'en' => 'Himachal Pradesh', 'gu' => 'હિમાચલ પ્રદેશ', 'hi' => 'हिमाचल प्रदेश', 'src' => '8,712 sq ft, 5 bigha = 1 acre (Wikipedia "Bigha"; Kotak guide)'],
            'uttarakhand' => ['f' => 6806.25 * KT_SQFT, 'en' => 'Uttarakhand (hills)', 'gu' => 'ઉત્તરાખંડ (પહાડી)', 'hi' => 'उत्तराखंड (पहाड़ी)', 'src' => '6,806.25 sq ft (Wikipedia "Bigha")'],
        ]],
    ];
}

/** Units with one fixed size everywhere: [en, gu, hi] note shown on the page instead of a drop-down. */
function gen_fixed_regional_notes(): array {
    return [
        'guntha' => [
            'en' => 'A guntha is the same everywhere it is used (Gujarat, Maharashtra, Karnataka): 1 guntha = 1/40 acre = 1,089 sq ft = 121 sq yd = 101.17 m².',
            'gu' => 'ગુંઠો ગુજરાત, મહારાષ્ટ્ર અને કર્ણાટક — બધે એક જ માપનો છે: 1 ગુંઠા = એકરનો 40મો ભાગ = 1,089 ચોરસ ફૂટ = 121 ચોરસ વાર = 101.17 ચોરસ મીટર.',
            'hi' => 'गुंठा गुजरात, महाराष्ट्र और कर्नाटक — हर जगह एक ही माप का है: 1 गुंठा = एकड़ का 40वाँ हिस्सा = 1,089 वर्ग फुट = 121 वर्ग गज = 101.17 वर्ग मीटर।'],
        'square-yard' => [
            'en' => 'Gaj (Delhi, UP, Punjab) and var (Gujarat) both mean one square yard here: 1 gaj = 1 var = 9 sq ft = 0.8361 m². The size does not change by state.',
            'gu' => 'ગુજરાતમાં "વાર" અને દિલ્હી-UP માં "ગજ" — બંને એટલે 1 ચોરસ વાર: 1 વાર = 9 ચોરસ ફૂટ = 0.8361 ચોરસ મીટર. આ માપ રાજ્ય પ્રમાણે બદલાતું નથી.',
            'hi' => 'दिल्ली-UP-पंजाब में "गज" और गुजरात में "वार" — दोनों का मतलब 1 वर्ग गज: 1 गज = 9 वर्ग फुट = 0.8361 वर्ग मीटर। यह माप राज्य के हिसाब से नहीं बदलता।'],
    ];
}

/** Region data for the units of one page, for the browser: {unit: {def, list: {key: {f, label}}}}. */
function gen_regions_for(array $units, string $lang): array {
    $out = [];
    $R = gen_regions();
    foreach ($units as $u) {
        if (!isset($R[$u]) || count($R[$u]['list']) < 2) continue;
        $list = [];
        foreach ($R[$u]['list'] as $k => $r) $list[$k] = ['f' => $r['f'], 'l' => $r[$lang] ?? $r['en']];
        $out[$u] = ['def' => $R[$u]['default'], 'list' => $list];
    }
    return $out;
}

/** The default region row of a unit, or null. */
function gen_region_default(string $unit): ?array {
    $R = gen_regions()[$unit] ?? null;
    return $R ? $R['list'][$R['default']] : null;
}
