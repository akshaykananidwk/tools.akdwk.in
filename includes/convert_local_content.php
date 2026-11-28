<?php
/**
 * KRISHNA TOOLS — Gujarati and Hindi text for converter pages.
 *
 * Written as people speak in the market yard and at home ("1 ક્વિન્ટલ એટલે 100 કિલો",
 * "1 क्विंटल में 100 किलो"), with the Hinglish search forms ("1 quintal kitna kg hota hai")
 * in the text, never stuffed. Numbers use the same rounding as the English page.
 */
require_once __DIR__ . '/convert_content.php';
require_once __DIR__ . '/local_units.php';
require_once __DIR__ . '/local_units_info.php';
require_once __DIR__ . '/convert_widget.php';

/** Region details for Gujarati/Hindi pages (maund, seer, bigha). */
function gen_local_regional_info(): array {
    return [
        'maund' => [
            'gu' => 'મણ (man) વજનનું જૂનું ભારતીય માપ છે, અને એનું વજન વિસ્તાર પ્રમાણે બદલાય છે. ગુજરાતનાં માર્કેટ યાર્ડમાં 1 મણ = 20 કિલો ગણાય છે — મગફળી, કપાસ, જીરું કે એરંડાનો "મણનો ભાવ" એટલે 20 કિલોનો ભાવ. 1956 ના વજન-માપ કાયદા મુજબનો ભારતીય મણ 40 શેર = 37.3242 કિલો છે, અને ઘણી જગ્યાએ તેને ગોળ કરીને 40 કિલો કહે છે.',
            'hi' => 'मन (man) वज़न का पुराना भारतीय माप है, और इसका वज़न इलाक़े के हिसाब से बदलता है। गुजरात की मंडियों में 1 मन = 20 किलो माना जाता है — मूँगफली, कपास या जीरे का "मन का भाव" यानी 20 किलो का भाव। 1956 के बाट-माप क़ानून के अनुसार भारतीय मन 40 सेर = 37.3242 किलो है, और कई जगह इसे गोल करके 40 किलो कहते हैं।'],
        'ser' => [
            'gu' => 'શેર વજનનું જૂનું ભારતીય માપ છે. 1956 ના વજન-માપ કાયદા મુજબ 1 શેર = 80 તોલા = 0.9331 કિલો (આશરે 933 ગ્રામ), અને 40 શેરનો એક ભારતીય મણ થાય. મેટ્રિક પદ્ધતિ આવ્યા પછી કિલોએ શેરની જગ્યા લીધી, એટલે ઘણા લોકો આજે 1 કિલોને જ "શેર" કહી દે છે.',
            'hi' => 'सेर वज़न का पुराना भारतीय माप है। 1956 के बाट-माप क़ानून के अनुसार 1 सेर = 80 तोला = 0.9331 किलो (लगभग 933 ग्राम), और 40 सेर का एक भारतीय मन होता है। मीट्रिक प्रणाली आने के बाद किलो ने सेर की जगह ले ली, इसलिए कई लोग आज 1 किलो को ही "सेर" कह देते हैं।'],
        'bigha' => [
            'gu' => 'વીઘો જમીનનું જૂનું માપ છે અને દરેક રાજ્યમાં એનું માપ અલગ છે. ગુજરાતમાં 1 વીઘા = 16 ગુંઠા = 17,424 ચોરસ ફૂટ (આશરે 1,618.7 ચોરસ મીટર) ગણાય છે, એટલે 2.5 વીઘા ≈ 1 એકર. રાજસ્થાનનો પાકો વીઘો, ઉત્તર પ્રદેશ અને બિહારનો વીઘો આશરે 27,225 ચોરસ ફૂટ, અને બંગાળ-આસામનો વીઘો 14,400 ચોરસ ફૂટ છે. સોદો કરતાં પહેલાં 7/12 કે ગામના તલાટી પાસેથી સ્થાનિક માપ જાણી લો.',
            'hi' => 'बीघा ज़मीन का पुराना माप है और हर राज्य में इसका नाप अलग है। राजस्थान का पक्का बीघा, पूर्वी उत्तर प्रदेश और बिहार का बीघा लगभग 27,225 वर्ग फुट, बंगाल-असम का बीघा 14,400 वर्ग फुट, मध्य प्रदेश में लगभग 12,000 वर्ग फुट और गुजरात का बीघा (वीघा) 17,424 वर्ग फुट = 16 गुंठा है। सौदे से पहले पटवारी या तहसील से अपने इलाक़े का नाप पक्का कर लें।'],
        'guntha' => [
            'gu' => 'ગુંઠો જમીનનું નાનું માપ છે: 1 ગુંઠા = 1,089 ચોરસ ફૂટ = 121 ચોરસ વાર = આશરે 101.17 ચોરસ મીટર. 40 ગુંઠાનો 1 એકર અને 16 ગુંઠાનો ગુજરાતનો 1 વીઘો થાય. NA પ્લોટ અને નાના ખેતરના સોદા ગુંઠામાં થાય છે.',
            'hi' => 'गुंठा ज़मीन का छोटा माप है: 1 गुंठा = 1,089 वर्ग फुट = 121 वर्ग गज = लगभग 101.17 वर्ग मीटर। 40 गुंठे का 1 एकड़ होता है। महाराष्ट्र, गुजरात और कर्नाटक में प्लॉट और छोटे खेतों के सौदे गुंठे में होते हैं।'],
        'square-yard' => [
            'gu' => 'ચોરસ વાર (વાર, ગજ) પ્લોટ અને મકાનનું જાણીતું માપ છે: 1 વાર = 9 ચોરસ ફૂટ = 0.8361 ચોરસ મીટર. ગુજરાતમાં "200 વારનો પ્લોટ" એટલે 1,800 ચોરસ ફૂટ. આ માપ દરેક રાજ્યમાં સરખું છે.',
            'hi' => 'वर्ग गज (गज) प्लॉट और मकान का सबसे जाना-पहचाना माप है: 1 गज = 9 वर्ग फुट = 0.8361 वर्ग मीटर। "100 गज का प्लॉट" यानी 900 वर्ग फुट। यह माप हर राज्य में एक जैसा है।'],
    ];
}

/** About text for a unit in a language. */
function gen_local_about(string $unit, string $lang): string {
    return gen_local_regional_info()[$unit][$lang] ?? gen_local_units_info()[$unit][$lang] ?? '';
}

/** "1 ક્વિન્ટલ એટલે કેટલા કિલો?" / "1 क्विंटल में कितने किलो होते हैं?" */
function gen_local_q(string $lang, string $v, string $F, string $T): string {
    return $lang === 'gu' ? "$v $F એટલે કેટલા $T?" : "$v $F में कितने $T होते हैं?";
}

/** Hinglish search forms: "1 quintal kitna kg hota hai", "30 quintal me kitne kilo". */
function gen_hinglish(string $lang, ?string $v, string $from, string $to): array {
    $f = gen_lname($from, 'en'); $t = gen_lname($to, 'en');
    $n = $v ?? '1';
    $forms = ["$n $f kitna $t hota hai", "$n $f me kitne $t", strtolower(gen_title_name($from, $p = gen_parse_slug("$from-to-$to")['cat'])) . ' to ' . strtolower(gen_title_name($to, $p))];
    if ($lang === 'gu') array_unshift($forms, "$n $f etle ketla $t");
    return $forms;
}

/** The rule in words: "ક્વિન્ટલના આંકડાને 100 થી ગુણો, એટલે કિલો મળે" / "… 100 से गुणा करें, किलो मिल जाएगा". */
function gen_local_rule(string $cat, string $from, string $to, string $lang): string {
    $F = gen_lname($from, $lang); $T = gen_lname($to, $lang); $fm = gen_formula($cat, $from, $to); $gu = $lang === 'gu';
    if ($fm['op'] === 'multiply') return $gu ? "{$F}ના આંકડાને " . gen_fmt($fm['factor']) . " થી ગુણો, એટલે $T મળે" : "$F की संख्या को " . gen_fmt($fm['factor']) . " से गुणा करें, $T मिल जाएगा";
    if ($fm['op'] === 'divide') return $gu ? "{$F}ના આંકડાને " . gen_fmt($fm['div']) . " થી ભાગો, એટલે $T મળે" : "$F की संख्या को " . gen_fmt($fm['div']) . " से भाग दें, $T मिल जाएगा";
    return $gu ? "આ સૂત્ર વાપરો: {$fm['text']}" : "यह सूत्र लगाएँ: {$fm['text']}";
}

/** All the Gujarati or Hindi text of a converter page. */
function gen_local_content(array $p, string $lang): array {
    $cat = $p['cat']; $from = $p['from']; $to = $p['to']; $value = $p['value'];
    $gu = $lang === 'gu';
    $v = $value ?? 1.0;
    $F = gen_lname($from, $lang); $T = gen_lname($to, $lang);
    $F1 = gen_lname($from, $lang, true);
    $r = gen_convert($cat, $v, $from, $to);
    $one = gen_convert($cat, 1, $from, $to); $back = gen_convert($cat, 1, $to, $from);
    $fm = gen_formula($cat, $from, $to);
    $vS = gen_fmt($v); $rS = gen_fmt($r); $oneS = gen_fmt($one); $backS = gen_fmt($back);
    $vF = abs($v - 1) < 1e-12 ? $F1 : $F;
    $regionLabel = '';
    foreach ([$from, $to] as $u) {
        $d = gen_region_default($u);
        if ($d && count(gen_regions()[$u]['list']) > 1) $regionLabel = $gu ? explode(' (', $d['gu'])[0] : explode(' (', $d['hi'])[0];
    }
    $rg = $regionLabel !== '' ? " ($regionLabel)" : '';

    // Formula in words.
    if ($fm['op'] === 'multiply') {
        $rule = $gu ? "{$F}ના આંકડાને " . gen_fmt($fm['factor']) . " થી ગુણો, એટલે $T મળે" : "$F की संख्या को " . gen_fmt($fm['factor']) . " से गुणा करें, $T मिल जाएगा";
        $ftext = "$T = $F × " . gen_fmt($fm['factor']);
    } elseif ($fm['op'] === 'divide') {
        $rule = $gu ? "{$F}ના આંકડાને " . gen_fmt($fm['div']) . " થી ભાગો, એટલે $T મળે" : "$F की संख्या को " . gen_fmt($fm['div']) . " से भाग दें, $T मिल जाएगा";
        $ftext = "$T = $F ÷ " . gen_fmt($fm['div']);
    } else {
        $rule = $gu ? "આ સૂત્ર વાપરો: {$fm['text']}" : "यह सूत्र लगाएँ: {$fm['text']}";
        $ftext = $fm['text'];
    }

    // Worked examples (3): the page's own number first on number pages.
    $exVals = $value !== null ? [$v, $v * 2, 0.5] : [1, (gen_units_info()[$from]['ex'] ?? [1, 10, 100])[1], (gen_units_info()[$from]['ex'] ?? [1, 10, 100])[2]];
    $exVals = array_values(array_unique(array_map('floatval', $exVals), SORT_REGULAR));
    if (count($exVals) < 3) $exVals[] = 10.0;
    $examples = [];
    foreach (array_slice($exVals, 0, 3) as $x) {
        $xr = gen_convert($cat, $x, $from, $to); $xS = gen_fmt($x); $xrS = gen_fmt($xr);
        $xF = abs($x - 1) < 1e-12 ? $F1 : $F;
        $st = [$gu ? "આંકડો લો: <b>$xS $xF</b>." : "संख्या लें: <b>$xS $xF</b>।"];
        if ($fm['op'] === 'multiply') {
            $st[] = $gu ? gen_fmt($fm['factor']) . " થી ગુણો (કારણ કે 1 $F1 = " . gen_fmt($fm['factor']) . " $T)." : gen_fmt($fm['factor']) . " से गुणा करें (क्योंकि 1 $F = " . gen_fmt($fm['factor']) . " $T)।";
            $st[] = "$xS × " . gen_fmt($fm['factor']) . " = <b>$xrS $T</b>" . ($gu ? '.' : '।');
        } elseif ($fm['op'] === 'divide') {
            $st[] = $gu ? gen_fmt($fm['div']) . " થી ભાગો (કારણ કે 1 $T = " . gen_fmt($fm['div']) . " $F)." : gen_fmt($fm['div']) . " से भाग दें (क्योंकि 1 $T = " . gen_fmt($fm['div']) . " $F)।";
            $st[] = "$xS ÷ " . gen_fmt($fm['div']) . " = <b>$xrS $T</b>" . ($gu ? '.' : '।');
        } else {
            $st[] = ($gu ? 'સૂત્ર: ' : 'सूत्र: ') . e($fm['text']) . ($gu ? '.' : '।');
            $st[] = ($gu ? 'જવાબ: ' : 'जवाब: ') . "<b>$xrS $T</b>" . ($gu ? '.' : '।');
        }
        $examples[] = ['v' => $x, 'title' => "$xS $xF → $T", 'steps' => $st];
    }

    // Titles, H1, description.
    $tNum = gen_title_num($r); $tOne = gen_title_num($one);
    if ($value !== null) {
        $vS0 = $vS; $vS = gen_plain($v);
        $title = $gu ? "$vS $vF એટલે કેટલા $T? $vS $vF = $tNum $T$rg" : "$vS $vF में कितने $T? $vS $vF = $tNum $T$rg";
        $h1 = $gu ? "$vS $vF એટલે કેટલા $T?" : "$vS $vF में कितने $T?";
    } else {
        $title = $gu ? "$F થી $T — 1 $F1 એટલે $tOne $T$rg | કન્વર્ટર" : "$F से $T — 1 $F में $tOne $T$rg | कन्वर्टर";
        $h1 = $gu ? "$F થી $T કન્વર્ટર (1 $F1 = $oneS $T)" : "$F से $T कन्वर्टर (1 $F = $oneS $T)";
    }
    if (mb_strlen($title) > 66) $title = preg_replace('/ \| (કન્વર્ટર|कन्वर्टर)$/u', '', $title);
    $hing = gen_hinglish($lang, $value !== null ? gen_plain($v) : null, $from, $to);
    $desc = $gu
        ? "$vS $vF = $rS $T$rg. સૂત્ર: $ftext. ગણતરીની રીત, ટેબલ અને સવાલ-જવાબ સાથે — ફ્રી, તરત જવાબ."
        : "$vS $vF = $rS $T$rg. सूत्र: $ftext. हिसाब का तरीका, टेबल और सवाल-जवाब के साथ — फ़्री, तुरंत जवाब.";

    $answer = "$vS $vF = $rS $T";
    $answerSub = $gu ? "એટલે કે " . lcfirst($rule) . '.' : "यानी " . $rule . '।';

    // Intro: what to do, one real-life line for this kind of unit, and the reverse.
    $why = gen_local_category_info()[$cat][$lang] ?? '';
    $intro = $gu
        ? "{$F}માંથી $T કરવું સાવ સહેલું છે: $rule. 1 $F1 = $oneS $T, અને ઊંધું જોઈએ તો 1 $T = $backS $F. $why"
        : "$F से $T बदलना बहुत आसान है: {$rule}। 1 $F = $oneS $T, और उल्टा देखें तो 1 $T = $backS {$F}। $why";
    $hingLine = $gu
        ? 'Google માં ઘણા લોકો “' . $hing[0] . '”, “' . $hing[1] . '” કે “' . $hing[3] . '” લખીને શોધે છે — જવાબ એક જ છે: <b>' . e($answer) . '</b>.'
        : 'Google पर लोग “' . $hing[0] . '”, “' . $hing[1] . '” या “' . $hing[2] . '” लिखकर खोजते हैं — जवाब एक ही है: <b>' . e($answer) . '</b>।';

    // FAQ (6–7), each answer complete on its own.
    $faq = [];
    if ($value !== null) {
        $faq[] = [gen_local_q($lang, $vS, $vF, $T), $gu ? "$vS $vF = $rS $T$rg. ગણતરી: $rule." : "$vS $vF = $rS $T{$rg}। हिसाब: {$rule}।"];
        $rr = gen_fmt(round($r, 4));
        $faq[] = [gen_local_q($lang, $rr, $T, $F), $gu ? "$rr $T = $vS $vF. ઊંધી ગણતરી માટે 1 $T = $backS $F વાપરો." : "$rr $T = $vS {$F}। उल्टे हिसाब के लिए 1 $T = $backS $F लगाएँ।"];
    }
    $faq[] = [gen_local_q($lang, '1', $F1, $T), $gu ? "1 $F1 = $oneS $T$rg." : "1 $F = $oneS $T{$rg}।"];
    $faq[] = [gen_local_q($lang, '1', $T, $F), $gu ? "1 $T = $backS $F. એટલે {$T}માંથી $F કરવા આ આંકડાથી ગુણો." : "1 $T = $backS {$F}। यानी $T से $F करने के लिए इस संख्या से गुणा करें।"];
    $faq[] = [$gu ? "{$F}માંથી $T કરવાનું સૂત્ર શું છે?" : "$F से $T का सूत्र क्या है?", $gu ? "સૂત્ર છે $ftext. સાદી ભાષામાં: $rule." : "सूत्र है {$ftext}। आसान भाषा में: {$rule}।"];
    foreach ([10, 100] as $x) {
        if ($value !== null && abs($x - $v) < 1e-9) continue;
        $faq[] = [gen_local_q($lang, (string) $x, $F, $T), "$x $F = " . gen_fmt(gen_convert($cat, $x, $from, $to)) . " $T" . ($gu ? '.' : '।')];
        if (count($faq) >= 6) break;
    }
    // One question about the unit itself.
    $unitFaq = gen_local_unit_faq($from, $lang) ?? gen_local_unit_faq($to, $lang);
    if ($unitFaq) $faq[] = $unitFaq;

    return [
        'title' => $title, 'desc' => $desc, 'h1' => $h1, 'answer' => $answer, 'answer_sub' => $answerSub,
        'intro' => $intro, 'hing_line' => $hingLine, 'hing' => $hing, 'rule' => $rule, 'formula_text' => $ftext,
        'examples' => $examples, 'about_from' => gen_local_about($from, $lang), 'about_to' => gen_local_about($to, $lang),
        'F' => $F, 'T' => $T, 'F1' => $F1, 'faq' => $faq, 'one' => $oneS, 'back' => $backS,
        'table' => gen_table_values($value, $cat, $from), 'region' => $regionLabel, 'rev_rule' => gen_local_rule($cat, $to, $from, $lang),
    ];
}

/** A question people really ask about a unit (gold tola, Gujarat maund, vigha…). */
function gen_local_unit_faq(string $unit, string $lang): ?array {
    $q = [
        'quintal' => [
            'gu' => ['માર્કેટ યાર્ડમાં ભાવ ક્વિન્ટલમાં હોય તો 1 કિલોનો ભાવ કેટલો?', 'ક્વિન્ટલના ભાવને 100 થી ભાગો. દા.ત. કપાસ ₹7,500 પ્રતિ ક્વિન્ટલ હોય તો 1 કિલો = ₹75, અને 20 કિલો (1 મણ) = ₹1,500.'],
            'hi' => ['मंडी में भाव क्विंटल में हो तो 1 किलो का भाव कितना?', 'क्विंटल के भाव को 100 से भाग दें। जैसे गेहूँ ₹2,500 प्रति क्विंटल हो तो 1 किलो = ₹25।']],
        'tola' => [
            'gu' => ['1 તોલો 10 ગ્રામ કે 11.66 ગ્રામ?', 'સરકારી ધોરણ મુજબ 1 તોલો = 11.6638 ગ્રામ. પણ આજે સોનાનો ભાવ મોટે ભાગે 10 ગ્રામ પર બોલાય છે, અને ઘણા લોકો 10 ગ્રામને જ તોલો કહે છે — બિલમાં કયો આંકડો છે તે જોઈ લો.'],
            'hi' => ['1 तोला 10 ग्राम या 11.66 ग्राम?', 'मानक के अनुसार 1 तोला = 11.6638 ग्राम। लेकिन आजकल सोने का भाव ज़्यादातर 10 ग्राम पर बोला जाता है और कई लोग 10 ग्राम को ही तोला कहते हैं — बिल में कौन-सा आँकड़ा है, देख लें।']],
        'maund' => [
            'gu' => ['શું બધે 1 મણ = 20 કિલો જ હોય?', 'ના. ગુજરાતનાં માર્કેટ યાર્ડમાં 1 મણ = 20 કિલો છે, પણ ભારતીય ધોરણનો મણ 37.3242 કિલો છે અને ઘણા લોકો 40 કિલો કહે છે. ઉપરના કન્વર્ટરમાં વિસ્તાર બદલીને જવાબ જુઓ.'],
            'hi' => ['क्या हर जगह 1 मन = 40 किलो होता है?', 'नहीं। भारतीय मानक मन 37.3242 किलो है, कई जगह इसे 40 किलो कहते हैं, और गुजरात की मंडियों में 1 मन = 20 किलो चलता है। ऊपर कन्वर्टर में क्षेत्र बदलकर जवाब देखें।']],
        'bigha' => [
            'gu' => ['1 વીઘા એટલે કેટલા ગુંઠા?', 'ગુજરાતમાં 1 વીઘા = 16 ગુંઠા = 17,424 ચોરસ ફૂટ. બીજાં રાજ્યોમાં વીઘાનું માપ અલગ છે — ઉપરના કન્વર્ટરમાં રાજ્ય પસંદ કરો.'],
            'hi' => ['क्या हर राज्य में बीघा एक जैसा होता है?', 'नहीं। राजस्थान का पक्का बीघा 27,225 वर्ग फुट, बंगाल-असम का 14,400 वर्ग फुट और गुजरात का 17,424 वर्ग फुट है। ऊपर कन्वर्टर में अपना राज्य चुनें।']],
        'guntha' => [
            'gu' => ['1 એકરમાં કેટલા ગુંઠા?', '1 એકર = 40 ગુંઠા. અને ગુજરાતમાં 16 ગુંઠાનો 1 વીઘો, એટલે 1 એકર = 2.5 વીઘા.'],
            'hi' => ['1 एकड़ में कितने गुंठे होते हैं?', '1 एकड़ = 40 गुंठा, और 1 गुंठा = 1,089 वर्ग फुट।']],
        'square-yard' => [
            'gu' => ['100 વારનો પ્લોટ એટલે કેટલા ચોરસ ફૂટ?', '1 વાર = 9 ચોરસ ફૂટ, એટલે 100 વાર = 900 ચોરસ ફૂટ અને 200 વાર = 1,800 ચોરસ ફૂટ.'],
            'hi' => ['100 गज का प्लॉट कितने वर्ग फुट का होता है?', '1 गज = 9 वर्ग फुट, इसलिए 100 गज = 900 वर्ग फुट और 200 गज = 1,800 वर्ग फुट।']],
        'lakh' => [
            'gu' => ['1 લાખમાં કેટલાં શૂન્ય?', '1 લાખ = 1,00,000 — એક પર પાંચ શૂન્ય. અંગ્રેજીમાં એને 100 thousand કહે છે.'],
            'hi' => ['1 लाख में कितने ज़ीरो होते हैं?', '1 लाख = 1,00,000 — एक पर पाँच ज़ीरो। अंग्रेज़ी में इसे 100 thousand कहते हैं।']],
        'crore' => [
            'gu' => ['1 કરોડમાં કેટલાં શૂન્ય?', '1 કરોડ = 1,00,00,000 — એક પર સાત શૂન્ય = 10 મિલિયન.'],
            'hi' => ['1 करोड़ में कितने ज़ीरो होते हैं?', '1 करोड़ = 1,00,00,000 — एक पर सात ज़ीरो = 10 मिलियन।']],
        'megabit-per-second' => [
            'gu' => ['100 Mbps ના પ્લાનમાં ડાઉનલોડ કેટલું ઝડપી થાય?', '100 ÷ 8 = 12.5 MB/s. એટલે 1 GB ની ફાઇલ આશરે દોઢ મિનિટમાં ઊતરે.'],
            'hi' => ['100 Mbps के प्लान में डाउनलोड कितना तेज़ होगा?', '100 ÷ 8 = 12.5 MB/s। यानी 1 GB की फ़ाइल लगभग डेढ़ मिनट में डाउनलोड होगी।']],
        'fahrenheit' => [
            'gu' => ['તાવ કેટલા ફેરનહીટ થી ગણાય?', 'સામાન્ય તાપમાન આશરે 98.6°F (37°C) છે. 100.4°F (38°C) કે વધુ હોય તો તાવ ગણાય.'],
            'hi' => ['बुख़ार कितने फ़ारेनहाइट से माना जाता है?', 'सामान्य तापमान लगभग 98.6°F (37°C) है। 100.4°F (38°C) या ज़्यादा हो तो बुख़ार माना जाता है।']],
    ];
    return $q[$unit][$lang] ?? null;
}
