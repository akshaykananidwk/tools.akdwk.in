<?php
/** KRISHNA TOOLS — Finance, Loan & Savings tool pack. */

// Reusable JS sub-expressions (kept here so long formulas stay readable).
$sw_i = '(v.r/1200)';
$retN = 'max(0, v.ret - v.age)';
$ret_corpus = '(abs(v.post - v.inf) < 1e-9 ? v.exp*12*pow(1+v.inf/100, ' . $retN . ')*max(0, v.life - v.ret) : v.exp*12*pow(1+v.inf/100, ' . $retN . ')*(1 - pow((1+v.inf/100)/(1+v.post/100), max(0, v.life - v.ret)))/((v.post - v.inf)/100)*(1+v.post/100))';
$ret_have = '(v.sav*pow(1+v.pre/100, ' . $retN . '))';
$su_q = 'pow(1+v.r/1200,12)';
$su_g = '(1+v.s/100)';
$pp_E = 'emi(v.p, v.r, v.n)';
$pp_newN = '(v.r ? -log(1 - max(0, v.p - v.x)*(v.r/1200)/' . $pp_E . ')/log(1+v.r/1200) : max(0, v.p - v.x)/' . $pp_E . ')';
$pp_origInt = '(' . $pp_E . '*v.n - v.p)';
$cc_q = '((1 - v.md/100)*(1 + v.r/100*(1 + v.gst/100)))';
$br_T = '((v.buy + v.sell)*v.q)';
$br_brok = '(v.bt == 1 ? 2*v.bv : ' . $br_T . '*v.bv/100)';
$br_ex = '(' . $br_T . '*v.ex/100)';
$br_sebi = '(' . $br_T . '*v.sebi/1e7)';
$br_total = '(' . $br_brok . ' + ' . $br_T . '*v.stt/100 + ' . $br_ex . ' + ' . $br_sebi . ' + (' . $br_brok . ' + ' . $br_ex . ' + ' . $br_sebi . ')*v.gst/100 + v.buy*v.q*v.stamp/100 + v.dp)';
$sa_q = '(v.q1 + v.q2 + v.q3 + v.q4)';
$sa_c = '(v.q1*v.p1 + v.q2*v.p2 + v.q3*v.p3 + v.q4*v.p4)';
$pt_W = '(v.c1*v.m1 + v.c2*v.m2 + v.c3*v.m3 + v.c4*v.m4)';
$ti_need = '((abs(v.r - v.inf) < 1e-9 ? v.exp*v.n : v.exp*(1 - pow((1+v.inf/100)/(1+v.r/100), v.n))/((v.r - v.inf)/100)*(1+v.r/100)) + v.loans + v.goals)';
$ep_N = 'max(0, v.ret - v.age)';
$ep_C0 = '(v.basic*(v.ee + v.er)/100*12)';
$ep_R = '(1+v.r/100)';
$ep_G = '(1+v.g/100)';
$ep_fv = '((abs(v.r - v.g) < 1e-9 ? ' . $ep_C0 . '*' . $ep_N . '*pow(' . $ep_R . ', ' . $ep_N . '-1) : ' . $ep_C0 . '*(pow(' . $ep_R . ', ' . $ep_N . ') - pow(' . $ep_G . ', ' . $ep_N . '))/(' . $ep_R . ' - ' . $ep_G . ')) + v.bal*pow(' . $ep_R . ', ' . $ep_N . '))';
$fr_rate = '(v.n > 0 && v.p > 0 ? (function(){var E=(v.p + v.p*v.f*v.n/1200)/v.n, lo=0, hi=1; for (var k=0; k<80; k++) { var m=(lo+hi)/2; if (emi(v.p, m*1200, v.n) > E) hi=m; else lo=m; } return lo*1200; })() : 0)';
$er_rate = '(v.n > 0 && v.p - v.fee > 0 ? (function(){var P=v.p - v.fee, lo=0, hi=1; for (var k=0; k<80; k++) { var m=(lo+hi)/2; if (emi(P, m*1200, v.n) > v.emi) hi=m; else lo=m; } return lo*1200; })() : 0)';
$ct_exp = '(v.open + v.sale + v.recv - v.exp - v.paid - v.bank)';
$cd_tot = '(500*v.n500 + 200*v.n200 + 100*v.n100 + 50*v.n50 + 20*v.n20 + 10*v.n10 + v.coins)';


return [
  'cats' => [
    ['slug' => 'finance', 'name_en' => 'Finance, Loan & Savings', 'name_gu' => 'ફાઇનાન્સ, લોન અને બચત', 'icon' => 'landmark', 'color' => '#0369A1', 'sort_order' => 31],
  ],
  'tools' => [

    /* ================= INVESTMENTS & SAVINGS ================= */

    ['slug' => 'sip-return-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'trending-up',
     'en' => 'SIP Return Calculator', 'gu' => 'SIP રિટર્ન કેલ્ક્યુલેટર',
     'de' => 'Estimate how much your monthly mutual fund SIP can grow to, with total invested amount and estimated gains.',
     'dg' => 'દર મહિને કરેલી મ્યુચ્યુઅલ ફંડ SIP કેટલી વધી શકે, કુલ રોકાણ અને અંદાજિત નફા સાથે જાણો.',
     'in' => [
       ['k' => 'm', 'en' => 'Monthly SIP amount', 'gu' => 'માસિક SIP રકમ', 'v' => 5000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Expected annual return', 'gu' => 'અપેક્ષિત વાર્ષિક વળતર', 'v' => 12, 'u' => '%'],
       ['k' => 'y', 'en' => 'Investment period', 'gu' => 'રોકાણનો સમયગાળો', 'v' => 10, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Estimated value', 'gu' => 'અંદાજિત મૂલ્ય', 'f' => 'sip(v.m, v.r, v.y*12)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total invested', 'gu' => 'કુલ રોકાણ', 'f' => 'v.m*v.y*12', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Estimated gains', 'gu' => 'અંદાજિત નફો', 'f' => 'sip(v.m, v.r, v.y*12) - v.m*v.y*12', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Money multiplied', 'gu' => 'રકમ કેટલા ગણી થઈ', 'f' => 'v.m*v.y ? sip(v.m, v.r, v.y*12)/(v.m*v.y*12) : 0', 'fmt' => 'num', 'd' => 2, 'u' => 'x'],
     ],
     'how_en' => 'Future value = P × [((1 + i)^n − 1) ÷ i] × (1 + i), where P = monthly SIP, i = annual return ÷ 12 ÷ 100 and n = number of months. It assumes you invest at the start of every month and the return stays constant.',
     'how_gu' => 'ભવિષ્ય મૂલ્ય = P × [((1 + i)^n − 1) ÷ i] × (1 + i), જ્યાં P = માસિક SIP, i = વાર્ષિક વળતર ÷ 12 ÷ 100 અને n = મહિનાની સંખ્યા. દર મહિનાની શરૂઆતમાં રોકાણ અને સ્થિર વળતર ધારેલું છે.',
     'faq' => [
       ['Is the SIP return guaranteed?', 'No. Mutual fund returns depend on the market. Use 10–12% for equity funds only as an assumption, and check the fund\'s past performance and risk.', 'શું SIP વળતર ગેરંટીવાળું છે?', 'ના. મ્યુચ્યુઅલ ફંડનું વળતર બજાર પર આધાર રાખે છે. 10–12% માત્ર અંદાજ છે; ફંડનો ઇતિહાસ અને જોખમ જરૂર તપાસો.'],
     ]],

    ['slug' => 'lumpsum-investment-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'coins',
     'en' => 'Lumpsum Mutual Fund Return Calculator', 'gu' => 'એકસાથે રોકાણ (લમ્પસમ) રિટર્ન કેલ્ક્યુલેટર',
     'de' => 'Find the future value of a one-time lumpsum investment in a mutual fund or any growth asset.',
     'dg' => 'મ્યુચ્યુઅલ ફંડ કે અન્ય રોકાણમાં એકસાથે મૂકેલી રકમ ભવિષ્યમાં કેટલી થશે તે જાણો.',
     'in' => [
       ['k' => 'p', 'en' => 'Amount invested', 'gu' => 'રોકાણ રકમ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Expected annual return', 'gu' => 'અપેક્ષિત વાર્ષિક વળતર', 'v' => 12, 'u' => '%'],
       ['k' => 'y', 'en' => 'Period', 'gu' => 'સમયગાળો', 'v' => 10, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Estimated value', 'gu' => 'અંદાજિત મૂલ્ય', 'f' => 'v.p*pow(1 + v.r/100, v.y)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Estimated gains', 'gu' => 'અંદાજિત નફો', 'f' => 'v.p*pow(1 + v.r/100, v.y) - v.p', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Absolute return', 'gu' => 'કુલ વળતર', 'f' => '(pow(1 + v.r/100, v.y) - 1)*100', 'fmt' => 'pct'],
     ],
     'how_en' => 'Future value = Amount × (1 + annual return ÷ 100)^years (yearly compounding).',
     'how_gu' => 'ભવિષ્ય મૂલ્ય = રકમ × (1 + વાર્ષિક વળતર ÷ 100)^વર્ષ (વાર્ષિક ચક્રવૃદ્ધિ).',
     'faq' => [
       ['SIP or lumpsum – which is better?', 'Lumpsum gives more if markets rise steadily after you invest; SIP spreads your buying over time and lowers the risk of investing everything at a market peak.', 'SIP સારી કે લમ્પસમ?', 'રોકાણ પછી બજાર સતત વધે તો લમ્પસમ વધુ આપે; SIP ધીમે ધીમે ખરીદી કરે છે એટલે ઊંચા ભાવે બધું રોકાઈ જવાનું જોખમ ઘટે છે.'],
     ]],

    ['slug' => 'step-up-sip-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-line',
     'en' => 'Step-Up SIP Calculator', 'gu' => 'સ્ટેપ-અપ SIP કેલ્ક્યુલેટર',
     'de' => 'See how increasing your SIP by a fixed percentage every year boosts your final mutual fund corpus.',
     'dg' => 'દર વર્ષે SIP રકમ નક્કી ટકા વધારવાથી અંતે કેટલું મોટું ભંડોળ બને છે તે જુઓ.',
     'in' => [
       ['k' => 'm', 'en' => 'Starting monthly SIP', 'gu' => 'શરૂઆતની માસિક SIP', 'v' => 5000, 'u' => '₹'],
       ['k' => 's', 'en' => 'Yearly step-up', 'gu' => 'દર વર્ષે વધારો', 'v' => 10, 'u' => '%'],
       ['k' => 'r', 'en' => 'Expected annual return', 'gu' => 'અપેક્ષિત વાર્ષિક વળતર', 'v' => 12, 'u' => '%'],
       ['k' => 'y', 'en' => 'Period', 'gu' => 'સમયગાળો', 'v' => 15, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Estimated value', 'gu' => 'અંદાજિત મૂલ્ય', 'f' => 'sip(v.m, v.r, 12)*(abs(' . $su_q . ' - ' . $su_g . ') < 1e-9 ? v.y*pow(' . $su_q . ', v.y - 1) : (pow(' . $su_q . ', v.y) - pow(' . $su_g . ', v.y))/(' . $su_q . ' - ' . $su_g . '))', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total invested', 'gu' => 'કુલ રોકાણ', 'f' => 'v.s ? 12*v.m*(pow(' . $su_g . ', v.y) - 1)/(' . $su_g . ' - 1) : 12*v.m*v.y', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Monthly SIP in the last year', 'gu' => 'છેલ્લા વર્ષની માસિક SIP', 'f' => 'v.m*pow(' . $su_g . ', max(0, v.y - 1))', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Value with a flat SIP (no step-up)', 'gu' => 'વધારા વગરની SIP નું મૂલ્ય', 'f' => 'sip(v.m, v.r, v.y*12)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Each year\'s 12 instalments (SIP × (1 + step-up)^year) are grown like a normal SIP for that year, then compounded for the remaining years at the same monthly rate. All years are added together.',
     'how_gu' => 'દરેક વર્ષના 12 હપ્તા (SIP × (1 + વધારો)^વર્ષ) ને તે વર્ષ માટે સામાન્ય SIP ની જેમ ગણી, બાકીના વર્ષો સુધી એ જ માસિક દરે ચક્રવૃદ્ધિ કરી, બધા વર્ષનો સરવાળો કરાય છે.',
     'faq' => [
       ['How much step-up should I choose?', 'Many people match it to their yearly salary hike, typically 5–15%. Even a 10% step-up can almost double the final corpus over 20 years compared with a flat SIP.', 'કેટલો સ્ટેપ-અપ રાખવો?', 'ઘણા લોકો પગાર વધારા જેટલો, એટલે કે 5–15% રાખે છે. 20 વર્ષમાં 10% સ્ટેપ-અપ પણ સાદી SIP કરતાં લગભગ બમણું ભંડોળ બનાવી શકે.'],
     ]],

    ['slug' => 'swp-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'hand-coins',
     'en' => 'SWP Calculator (Systematic Withdrawal Plan)', 'gu' => 'SWP કેલ્ક્યુલેટર (નિયમિત ઉપાડ યોજના)',
     'de' => 'Check how long your mutual fund corpus will last with a fixed monthly withdrawal and what balance remains.',
     'dg' => 'મ્યુચ્યુઅલ ફંડમાંથી દર મહિને નક્કી રકમ ઉપાડો તો ભંડોળ કેટલું ચાલશે અને કેટલું બાકી રહેશે તે જુઓ.',
     'in' => [
       ['k' => 'p', 'en' => 'Total investment (corpus)', 'gu' => 'કુલ રોકાણ (ભંડોળ)', 'v' => 1000000, 'u' => '₹'],
       ['k' => 'w', 'en' => 'Monthly withdrawal', 'gu' => 'માસિક ઉપાડ', 'v' => 8000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Expected annual return', 'gu' => 'અપેક્ષિત વાર્ષિક વળતર', 'v' => 8, 'u' => '%'],
       ['k' => 'y', 'en' => 'Withdrawal period', 'gu' => 'ઉપાડનો સમયગાળો', 'v' => 10, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Balance left at the end', 'gu' => 'અંતે બાકી રકમ', 'f' => 'max(0, v.r ? v.p*pow(1+' . $sw_i . ', v.y*12) - v.w*(pow(1+' . $sw_i . ', v.y*12) - 1)/' . $sw_i . ' : v.p - v.w*v.y*12)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Planned total withdrawal', 'gu' => 'કુલ આયોજિત ઉપાડ', 'f' => 'v.w*v.y*12', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Corpus lasts for', 'gu' => 'ભંડોળ ચાલશે', 'f' => 'v.r ? (v.w > v.p*' . $sw_i . ' ? round(-log(1 - v.p*' . $sw_i . '/v.w)/log(1+' . $sw_i . ')/12, 1) + T(" વર્ષ", " years") : T("ક્યારેય ખૂટશે નહીં (વળતર ≥ ઉપાડ)", "Never runs out (return ≥ withdrawal)")) : (v.w ? round(v.p/v.w/12, 1) + T(" વર્ષ", " years") : "—")', 'fmt' => 'text'],
       ['en' => 'Monthly return on starting corpus', 'gu' => 'શરૂઆતના ભંડોળ પર માસિક વળતર', 'f' => 'v.p*' . $sw_i, 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Balance after n months = P(1 + i)^n − W × ((1 + i)^n − 1) ÷ i, with i = annual return ÷ 12 ÷ 100 and withdrawal W at the end of each month. Months the corpus lasts = −log(1 − P·i ÷ W) ÷ log(1 + i).',
     'how_gu' => 'n મહિના પછી બાકી = P(1 + i)^n − W × ((1 + i)^n − 1) ÷ i, જ્યાં i = વાર્ષિક વળતર ÷ 12 ÷ 100 અને ઉપાડ W દર મહિનાના અંતે. ભંડોળ કેટલા મહિના ચાલે = −log(1 − P·i ÷ W) ÷ log(1 + i).',
     'faq' => [
       ['What is a safe withdrawal rate?', 'If your monthly withdrawal is less than the monthly return on the corpus, the capital is never touched. Keeping yearly withdrawals around 4–6% of the corpus is a common rule of thumb.', 'કેટલો ઉપાડ સુરક્ષિત ગણાય?', 'માસિક ઉપાડ ભંડોળના માસિક વળતર કરતાં ઓછો હોય તો મૂળ રકમ ઘટતી નથી. વર્ષે ભંડોળના 4–6% જેટલો ઉપાડ સામાન્ય નિયમ ગણાય છે.'],
     ]],

    ['slug' => 'fd-maturity-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'vault',
     'en' => 'FD Maturity Calculator (Fixed Deposit)', 'gu' => 'FD પાકતી રકમ કેલ્ક્યુલેટર (ફિક્સ ડિપોઝિટ)',
     'de' => 'Calculate bank fixed deposit maturity amount and interest with quarterly, monthly or yearly compounding.',
     'dg' => 'બેંક ફિક્સ ડિપોઝિટની પાકતી રકમ અને વ્યાજ ત્રિમાસિક, માસિક કે વાર્ષિક ચક્રવૃદ્ધિ મુજબ ગણો.',
     'in' => [
       ['k' => 'p', 'en' => 'Deposit amount', 'gu' => 'જમા રકમ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 7, 'u' => '%'],
       ['k' => 'y', 'en' => 'Years', 'gu' => 'વર્ષ', 'v' => 2],
       ['k' => 'mo', 'en' => 'Extra months', 'gu' => 'વધારાના મહિના', 'v' => 0],
       ['k' => 'n', 'en' => 'Compounding', 'gu' => 'ચક્રવૃદ્ધિ', 'v' => 4, 'opts' => [[4, 'Quarterly (most banks)', 'ત્રિમાસિક (મોટાભાગની બેંક)'], [12, 'Monthly', 'માસિક'], [2, 'Half-yearly', 'છમાસિક'], [1, 'Yearly', 'વાર્ષિક']]],
     ],
     'out' => [
       ['en' => 'Maturity amount', 'gu' => 'પાકતી રકમ', 'f' => 'v.p*pow(1 + v.r/(100*v.n), v.n*(v.y + v.mo/12))', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Interest earned', 'gu' => 'મળેલું વ્યાજ', 'f' => 'v.p*pow(1 + v.r/(100*v.n), v.n*(v.y + v.mo/12)) - v.p', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Effective annual yield', 'gu' => 'અસરકારક વાર્ષિક વળતર', 'f' => '(pow(1 + v.r/(100*v.n), v.n) - 1)*100', 'fmt' => 'pct'],
     ],
     'how_en' => 'Maturity = P × (1 + r ÷ (100 × n))^(n × t), where n = compounding periods per year (4 for quarterly) and t = tenure in years (months ÷ 12 added).',
     'how_gu' => 'પાકતી રકમ = P × (1 + r ÷ (100 × n))^(n × t), જ્યાં n = વર્ષમાં ચક્રવૃદ્ધિ ગાળા (ત્રિમાસિક માટે 4) અને t = વર્ષમાં સમયગાળો (મહિના ÷ 12 ઉમેરીને).',
     'faq' => [
       ['Is FD interest taxable?', 'Yes, FD interest is added to your income and taxed at your slab. Banks may deduct TDS if interest crosses the limit; submit Form 15G/15H if eligible.', 'FD ના વ્યાજ પર ટેક્સ લાગે?', 'હા, FD વ્યાજ આવકમાં ઉમેરાય અને તમારા સ્લેબ મુજબ ટેક્સ લાગે. મર્યાદાથી વધુ વ્યાજ પર બેંક TDS કાપી શકે; લાયક હો તો ફોર્મ 15G/15H આપો.'],
       ['Do senior citizens get a higher rate?', 'Most banks give senior citizens about 0.25–0.50% extra. Enter the senior citizen rate shown by your bank.', 'સિનિયર સિટીઝનને વધુ દર મળે?', 'મોટાભાગની બેંક 0.25–0.50% વધારે આપે છે. તમારી બેંકનો સિનિયર સિટીઝન દર દાખલ કરો.'],
     ]],

    ['slug' => 'rd-maturity-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'piggy-bank',
     'en' => 'RD Maturity Calculator (Recurring Deposit)', 'gu' => 'RD પાકતી રકમ કેલ્ક્યુલેટર (રિકરિંગ ડિપોઝિટ)',
     'de' => 'Find the maturity value of a bank or post office recurring deposit with quarterly compounding.',
     'dg' => 'બેંક કે પોસ્ટ ઓફિસની રિકરિંગ ડિપોઝિટની પાકતી રકમ ત્રિમાસિક ચક્રવૃદ્ધિ સાથે ગણો.',
     'in' => [
       ['k' => 'm', 'en' => 'Monthly deposit', 'gu' => 'માસિક જમા', 'v' => 5000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 6.7, 'u' => '%'],
       ['k' => 'mo', 'en' => 'Tenure', 'gu' => 'સમયગાળો', 'v' => 60, 'u' => 'months', 'u_gu' => 'મહિના'],
     ],
     'out' => [
       ['en' => 'Maturity amount', 'gu' => 'પાકતી રકમ', 'f' => 'v.r ? v.m*pow(1+v.r/400, 1/3)*(pow(1+v.r/400, v.mo/3) - 1)/(pow(1+v.r/400, 1/3) - 1) : v.m*v.mo', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total deposited', 'gu' => 'કુલ જમા', 'f' => 'v.m*v.mo', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest earned', 'gu' => 'મળેલું વ્યાજ', 'f' => '(v.r ? v.m*pow(1+v.r/400, 1/3)*(pow(1+v.r/400, v.mo/3) - 1)/(pow(1+v.r/400, 1/3) - 1) : v.m*v.mo) - v.m*v.mo', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Each monthly deposit earns interest compounded quarterly for the months it stays. Maturity = Σ R × (1 + r/400)^(k/3) for k = months remaining (1 to N), which equals R × a × (a^N − 1) ÷ (a − 1) with a = (1 + r/400)^(1/3).',
     'how_gu' => 'દરેક માસિક હપ્તા પર જેટલા મહિના રહે તેટલું ત્રિમાસિક ચક્રવૃદ્ધિ વ્યાજ મળે. પાકતી રકમ = Σ R × (1 + r/400)^(k/3), k = બાકી મહિના (1 થી N); એટલે R × a × (a^N − 1) ÷ (a − 1), જ્યાં a = (1 + r/400)^(1/3).',
     'faq' => [
       ['What if I miss an RD instalment?', 'Banks and the post office usually charge a small penalty for a late instalment and may close the account after several missed months. Check your bank\'s rules.', 'RD નો હપ્તો ચૂકી જાઉં તો?', 'બેંક અને પોસ્ટ ઓફિસ મોડા હપ્તા પર નાનો દંડ લે છે અને ઘણા મહિના ચૂકો તો ખાતું બંધ થઈ શકે. તમારી બેંકના નિયમ તપાસો.'],
     ]],

    ['slug' => 'ppf-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'shield',
     'en' => 'PPF Calculator (Public Provident Fund)', 'gu' => 'PPF કેલ્ક્યુલેટર (પબ્લિક પ્રોવિડન્ટ ફંડ)',
     'de' => 'Calculate PPF maturity value for yearly deposits over 15 years or more at the current interest rate you enter.',
     'dg' => '15 કે વધુ વર્ષ માટે દર વર્ષે PPF માં જમા કરો તો તમે દાખલ કરેલા દરે પાકતી રકમ કેટલી થાય તે ગણો.',
     'in' => [
       ['k' => 'd', 'en' => 'Yearly deposit', 'gu' => 'વાર્ષિક જમા', 'v' => 150000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'PPF interest rate', 'gu' => 'PPF વ્યાજ દર', 'v' => 7.1, 'u' => '%'],
       ['k' => 'y', 'en' => 'Period (15 or more)', 'gu' => 'સમયગાળો (15 કે વધુ)', 'v' => 15, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Maturity value', 'gu' => 'પાકતી રકમ', 'f' => 'v.r ? v.d*(pow(1 + v.r/100, v.y) - 1)/(v.r/100)*(1 + v.r/100) : v.d*v.y', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total deposited', 'gu' => 'કુલ જમા', 'f' => 'v.d*v.y', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest (tax-free)', 'gu' => 'કુલ વ્યાજ (કરમુક્ત)', 'f' => '(v.r ? v.d*(pow(1 + v.r/100, v.y) - 1)/(v.r/100)*(1 + v.r/100) : v.d*v.y) - v.d*v.y', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Assumes the full yearly deposit is made at the start of each financial year (before 5 April) and interest compounds yearly: Maturity = D × [((1 + r)^n − 1) ÷ r] × (1 + r).',
     'how_gu' => 'દર નાણાકીય વર્ષની શરૂઆતમાં (5 એપ્રિલ પહેલાં) આખી રકમ જમા અને વાર્ષિક ચક્રવૃદ્ધિ ધારીને: પાકતી રકમ = D × [((1 + r)^n − 1) ÷ r] × (1 + r).',
     'faq' => [
       ['What are the PPF deposit limits?', 'Currently the minimum is ₹500 and the maximum ₹1.5 lakh per financial year. The government reviews the interest rate every quarter, so always enter the latest rate.', 'PPF માં કેટલું જમા કરી શકાય?', 'હાલ વર્ષે ઓછામાં ઓછા ₹500 અને વધુમાં વધુ ₹1.5 લાખ. સરકાર દર ત્રિમાસિકે વ્યાજ દર નક્કી કરે છે, એટલે તાજો દર દાખલ કરો.'],
       ['Why deposit before 5 April?', 'PPF interest is calculated on the lowest balance between the 5th and the end of each month, so depositing before the 5th earns interest for that month.', '5 એપ્રિલ પહેલાં કેમ જમા કરવું?', 'PPF વ્યાજ દર મહિનાની 5 તારીખથી મહિનાના અંત સુધીની ઓછામાં ઓછી રકમ પર ગણાય છે, એટલે 5 તારીખ પહેલાં જમા કરવાથી તે મહિનાનું વ્યાજ મળે.'],
     ]],

    ['slug' => 'sukanya-samriddhi-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'baby',
     'en' => 'Sukanya Samriddhi Yojana Calculator', 'gu' => 'સુકન્યા સમૃદ્ધિ યોજના કેલ્ક્યુલેટર',
     'de' => 'Estimate the Sukanya Samriddhi (SSY) maturity amount for your daughter with deposits for 15 years.',
     'dg' => 'દીકરી માટે 15 વર્ષ સુધી જમા કરાવતા સુકન્યા સમૃદ્ધિ ખાતાની પાકતી રકમનો અંદાજ મેળવો.',
     'in' => [
       ['k' => 'd', 'en' => 'Yearly deposit', 'gu' => 'વાર્ષિક જમા', 'v' => 50000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'SSY interest rate', 'gu' => 'SSY વ્યાજ દર', 'v' => 8.2, 'u' => '%'],
       ['k' => 'age', 'en' => 'Daughter\'s age at opening', 'gu' => 'ખાતું ખોલતી વખતે દીકરીની ઉંમર', 'v' => 5, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Maturity amount (after 21 years)', 'gu' => 'પાકતી રકમ (21 વર્ષ પછી)', 'f' => 'v.r ? v.d*(pow(1 + v.r/100, 15) - 1)/(v.r/100)*(1 + v.r/100)*pow(1 + v.r/100, 6) : v.d*15', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total deposited (15 years)', 'gu' => 'કુલ જમા (15 વર્ષ)', 'f' => 'v.d*15', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest', 'gu' => 'કુલ વ્યાજ', 'f' => '(v.r ? v.d*(pow(1 + v.r/100, 15) - 1)/(v.r/100)*(1 + v.r/100)*pow(1 + v.r/100, 6) : v.d*15) - v.d*15', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Daughter\'s age at maturity', 'gu' => 'પાકતી વખતે દીકરીની ઉંમર', 'f' => 'v.age + 21', 'fmt' => 'int', 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'how_en' => 'Deposits are made at the start of each year for 15 years and compound yearly; the balance then keeps earning interest without new deposits until 21 years from opening. Maturity = D × [((1 + r)^15 − 1) ÷ r] × (1 + r) × (1 + r)^6.',
     'how_gu' => '15 વર્ષ સુધી દર વર્ષની શરૂઆતમાં જમા અને વાર્ષિક ચક્રવૃદ્ધિ; પછી ખાતું ખોલ્યાના 21 વર્ષ સુધી નવી જમા વગર વ્યાજ મળતું રહે. પાકતી રકમ = D × [((1 + r)^15 − 1) ÷ r] × (1 + r) × (1 + r)^6.',
     'faq' => [
       ['Who can open a Sukanya Samriddhi account?', 'Parents or guardians can open it for a girl child below 10 years at a post office or authorised bank. Yearly deposit limits and the rate are set by the government and may change.', 'સુકન્યા ખાતું કોણ ખોલી શકે?', '10 વર્ષથી નાની દીકરી માટે માતા-પિતા કે વાલી પોસ્ટ ઓફિસ કે માન્ય બેંકમાં ખોલી શકે. જમા મર્યાદા અને વ્યાજ દર સરકાર નક્કી કરે છે અને બદલાઈ શકે.'],
     ]],

    ['slug' => 'nps-pension-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'sunset',
     'en' => 'NPS Calculator – Corpus & Monthly Pension', 'gu' => 'NPS કેલ્ક્યુલેટર – ભંડોળ અને માસિક પેન્શન',
     'de' => 'Estimate your National Pension System corpus at 60, the lump sum you can withdraw and your monthly pension.',
     'dg' => 'NPS માં 60 વર્ષે કેટલું ભંડોળ થશે, કેટલું એકસાથે ઉપાડી શકાશે અને માસિક પેન્શન કેટલું મળશે તેનો અંદાજ.',
     'in' => [
       ['k' => 'm', 'en' => 'Monthly contribution', 'gu' => 'માસિક ફાળો', 'v' => 5000, 'u' => '₹'],
       ['k' => 'age', 'en' => 'Current age', 'gu' => 'હાલની ઉંમર', 'v' => 30, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'ret', 'en' => 'Retirement age', 'gu' => 'નિવૃત્તિ ઉંમર', 'v' => 60, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'r', 'en' => 'Expected annual return', 'gu' => 'અપેક્ષિત વાર્ષિક વળતર', 'v' => 10, 'u' => '%'],
       ['k' => 'ann', 'en' => 'Share used to buy annuity', 'gu' => 'એન્યુઇટીમાં રોકાતો ભાગ', 'v' => 40, 'u' => '%'],
       ['k' => 'ar', 'en' => 'Annuity rate', 'gu' => 'એન્યુઇટી દર', 'v' => 6, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Total corpus at retirement', 'gu' => 'નિવૃત્તિ સમયે કુલ ભંડોળ', 'f' => 'sip(v.m, v.r, max(0, v.ret - v.age)*12)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Lump sum you can withdraw', 'gu' => 'એકસાથે ઉપાડી શકાય તેવી રકમ', 'f' => 'sip(v.m, v.r, max(0, v.ret - v.age)*12)*(1 - v.ann/100)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Estimated monthly pension', 'gu' => 'અંદાજિત માસિક પેન્શન', 'f' => 'sip(v.m, v.r, max(0, v.ret - v.age)*12)*v.ann/100*v.ar/1200', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total contribution', 'gu' => 'કુલ ફાળો', 'f' => 'v.m*max(0, v.ret - v.age)*12', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Corpus = monthly SIP future value till retirement. Annuity part = corpus × annuity %; monthly pension = annuity part × annuity rate ÷ 12. The rest can be withdrawn as a lump sum.',
     'how_gu' => 'ભંડોળ = નિવૃત્તિ સુધી માસિક ફાળાનું SIP મુજબ ભવિષ્ય મૂલ્ય. એન્યુઇટી ભાગ = ભંડોળ × એન્યુઇટી %; માસિક પેન્શન = એન્યુઇટી ભાગ × એન્યુઇટી દર ÷ 12. બાકીની રકમ એકસાથે ઉપાડી શકાય.',
     'faq' => [
       ['How much must go into an annuity?', 'Under current NPS rules at least 40% of the corpus is used to buy an annuity at retirement. Withdrawal rules are revised from time to time, so check the latest PFRDA rules.', 'કેટલો ભાગ એન્યુઇટીમાં રોકવો પડે?', 'હાલના NPS નિયમ મુજબ નિવૃત્તિ વખતે ઓછામાં ઓછું 40% ભંડોળ એન્યુઇટીમાં રોકવું પડે. નિયમો સમયાંતરે બદલાય છે, એટલે PFRDA ના તાજા નિયમ તપાસો.'],
     ]],

    ['slug' => 'compound-interest-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'layers',
     'en' => 'Compound Interest Calculator', 'gu' => 'ચક્રવૃદ્ધિ વ્યાજ કેલ્ક્યુલેટર',
     'de' => 'Calculate compound interest with yearly, half-yearly, quarterly, monthly or daily compounding and compare with simple interest.',
     'dg' => 'વાર્ષિક, છમાસિક, ત્રિમાસિક, માસિક કે દૈનિક ચક્રવૃદ્ધિ વ્યાજ ગણો અને સાદા વ્યાજ સાથે સરખાવો.',
     'in' => [
       ['k' => 'p', 'en' => 'Principal', 'gu' => 'મુદ્દલ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 10, 'u' => '%'],
       ['k' => 'y', 'en' => 'Time', 'gu' => 'સમય', 'v' => 5, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'n', 'en' => 'Compounding', 'gu' => 'ચક્રવૃદ્ધિ', 'v' => 1, 'opts' => [[1, 'Yearly', 'વાર્ષિક'], [2, 'Half-yearly', 'છમાસિક'], [4, 'Quarterly', 'ત્રિમાસિક'], [12, 'Monthly', 'માસિક'], [365, 'Daily', 'દૈનિક']]],
     ],
     'out' => [
       ['en' => 'Total amount', 'gu' => 'કુલ રકમ', 'f' => 'v.p*pow(1 + v.r/(100*v.n), v.n*v.y)', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Compound interest', 'gu' => 'ચક્રવૃદ્ધિ વ્યાજ', 'f' => 'v.p*pow(1 + v.r/(100*v.n), v.n*v.y) - v.p', 'fmt' => 'inr'],
       ['en' => 'Simple interest for same period', 'gu' => 'એ જ સમયનું સાદું વ્યાજ', 'f' => 'v.p*v.r*v.y/100', 'fmt' => 'inr'],
       ['en' => 'Extra earned by compounding', 'gu' => 'ચક્રવૃદ્ધિથી વધારાનું વ્યાજ', 'f' => 'v.p*pow(1 + v.r/(100*v.n), v.n*v.y) - v.p - v.p*v.r*v.y/100', 'fmt' => 'inr'],
     ],
     'how_en' => 'Amount A = P × (1 + r ÷ (100 × n))^(n × t). Compound interest = A − P. Simple interest = P × r × t ÷ 100.',
     'how_gu' => 'રકમ A = P × (1 + r ÷ (100 × n))^(n × t). ચક્રવૃદ્ધિ વ્યાજ = A − P. સાદું વ્યાજ = P × r × t ÷ 100.',
     'faq' => [
       ['Why does more frequent compounding give more?', 'Interest is added to the principal more often, so the next interest is calculated on a bigger amount. The difference between monthly and daily is very small.', 'વારંવાર ચક્રવૃદ્ધિથી વધારે કેમ મળે?', 'વ્યાજ વહેલું મુદ્દલમાં ઉમેરાય છે, એટલે પછીનું વ્યાજ મોટી રકમ પર ગણાય. માસિક અને દૈનિક વચ્ચે તફાવત બહુ ઓછો હોય છે.'],
     ]],

    ['slug' => 'simple-interest-vyaj-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'percent',
     'en' => 'Simple Interest (Vyaj) Calculator', 'gu' => 'સાદું વ્યાજ કેલ્ક્યુલેટર',
     'de' => 'Calculate simple interest by % per year or the local "rupees per hundred per month" (do taka) rate, in years, months or days.',
     'dg' => 'વાર્ષિક % અથવા "સો રૂપિયે મહિને આટલા રૂપિયા" (બે ટકા) મુજબ, વર્ષ, મહિના કે દિવસનું સાદું વ્યાજ ગણો.',
     'in' => [
       ['k' => 'p', 'en' => 'Principal (amount lent)', 'gu' => 'મુદ્દલ (આપેલી રકમ)', 'v' => 50000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate', 'gu' => 'વ્યાજ દર', 'v' => 2],
       ['k' => 'ru', 'en' => 'Rate type', 'gu' => 'દરનો પ્રકાર', 'v' => 2, 'opts' => [[1, '% per year', 'વાર્ષિક %'], [2, '₹ per ₹100 per month', 'સો રૂપિયે મહિને ₹']]],
       ['k' => 't', 'en' => 'Time', 'gu' => 'સમય', 'v' => 12],
       ['k' => 'tu', 'en' => 'Time unit', 'gu' => 'સમયનું એકમ', 'v' => 2, 'opts' => [[1, 'Years', 'વર્ષ'], [2, 'Months', 'મહિના'], [3, 'Days', 'દિવસ']]],
     ],
     'out' => [
       ['en' => 'Interest', 'gu' => 'વ્યાજ', 'f' => 'v.p*(v.ru == 1 ? v.r : v.r*12)*(v.tu == 1 ? v.t : (v.tu == 2 ? v.t/12 : v.t/365))/100', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Total to be repaid', 'gu' => 'કુલ પરત રકમ', 'f' => 'v.p + v.p*(v.ru == 1 ? v.r : v.r*12)*(v.tu == 1 ? v.t : (v.tu == 2 ? v.t/12 : v.t/365))/100', 'fmt' => 'inr'],
       ['en' => 'Interest per month', 'gu' => 'માસિક વ્યાજ', 'f' => 'v.p*(v.ru == 1 ? v.r : v.r*12)/1200', 'fmt' => 'inr'],
       ['en' => 'Equivalent yearly rate', 'gu' => 'સમકક્ષ વાર્ષિક દર', 'f' => 'v.ru == 1 ? v.r : v.r*12', 'fmt' => 'pct'],
     ],
     'how_en' => 'Simple interest = P × R × T ÷ 100, where R is the yearly rate and T is time in years. "₹2 per ₹100 per month" means 2% per month = 24% per year. Days are converted as days ÷ 365.',
     'how_gu' => 'સાદું વ્યાજ = P × R × T ÷ 100, જ્યાં R = વાર્ષિક દર અને T = વર્ષમાં સમય. "સો રૂપિયે મહિને ₹2" એટલે મહિને 2% = વર્ષે 24%. દિવસ ÷ 365 થી વર્ષ ગણાય.',
     'faq' => [
       ['What does "do taka vyaj" mean?', 'It means ₹2 interest on every ₹100 per month, i.e. 24% per year. On ₹50,000 that is ₹1,000 every month.', '"બે ટકા વ્યાજ" એટલે શું?', 'દર ₹100 પર મહિને ₹2 વ્યાજ, એટલે વર્ષે 24%. ₹50,000 પર દર મહિને ₹1,000 વ્યાજ થાય.'],
     ]],

    ['slug' => 'cagr-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-no-axes-combined',
     'en' => 'CAGR Calculator', 'gu' => 'CAGR કેલ્ક્યુલેટર (ચક્રવૃદ્ધિ વાર્ષિક વૃદ્ધિ દર)',
     'de' => 'Find the compound annual growth rate (CAGR) of an investment, property or business sales between two values.',
     'dg' => 'રોકાણ, મિલકત કે ધંધાના વેચાણનો બે રકમ વચ્ચેનો ચક્રવૃદ્ધિ વાર્ષિક વૃદ્ધિ દર (CAGR) શોધો.',
     'in' => [
       ['k' => 's', 'en' => 'Starting value', 'gu' => 'શરૂઆતનું મૂલ્ય', 'v' => 100000, 'u' => '₹'],
       ['k' => 'e', 'en' => 'Ending value', 'gu' => 'અંતિમ મૂલ્ય', 'v' => 250000, 'u' => '₹'],
       ['k' => 'y', 'en' => 'Number of years', 'gu' => 'વર્ષની સંખ્યા', 'v' => 5, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'CAGR', 'gu' => 'CAGR', 'f' => 'v.s > 0 && v.y > 0 && v.e >= 0 ? (pow(v.e/v.s, 1/v.y) - 1)*100 : 0', 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Absolute return', 'gu' => 'કુલ વળતર', 'f' => 'v.s ? (v.e - v.s)/v.s*100 : 0', 'fmt' => 'pct'],
       ['en' => 'Total gain', 'gu' => 'કુલ નફો', 'f' => 'v.e - v.s', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'CAGR = (Ending value ÷ Starting value)^(1 ÷ years) − 1, shown as a percentage.',
     'how_gu' => 'CAGR = (અંતિમ મૂલ્ય ÷ શરૂઆતનું મૂલ્ય)^(1 ÷ વર્ષ) − 1, ટકામાં.',
     'faq' => [
       ['Why is CAGR lower than average return?', 'CAGR is the smooth yearly rate that turns the start value into the end value. Simple average returns ignore compounding and ups and downs, so they look higher.', 'CAGR સરેરાશ વળતર કરતાં ઓછો કેમ?', 'CAGR એ એવો સ્થિર વાર્ષિક દર છે જે શરૂઆતની રકમને અંતિમ રકમ બનાવે. સાદી સરેરાશ ચક્રવૃદ્ધિ અને ઉતાર-ચઢાવ ગણતી નથી, એટલે વધારે દેખાય.'],
     ]],

    ['slug' => 'inflation-future-cost-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'trending-down',
     'en' => 'Inflation Calculator – Future Cost & Value of Money', 'gu' => 'મોંઘવારી કેલ્ક્યુલેટર – ભવિષ્યની કિંમત અને પૈસાનું મૂલ્ય',
     'de' => 'See what something costing a given amount today will cost in future, and how much today\'s money will really be worth.',
     'dg' => 'આજે જે વસ્તુ આટલામાં મળે છે તે ભવિષ્યમાં કેટલાની થશે અને આજના પૈસાની કિંમત કેટલી ઘટશે તે જુઓ.',
     'in' => [
       ['k' => 'c', 'en' => 'Amount / cost today', 'gu' => 'આજની રકમ / કિંમત', 'v' => 50000, 'u' => '₹'],
       ['k' => 'inf', 'en' => 'Inflation rate (per year)', 'gu' => 'મોંઘવારી દર (વાર્ષિક)', 'v' => 6, 'u' => '%'],
       ['k' => 'y', 'en' => 'Years from now', 'gu' => 'કેટલા વર્ષ પછી', 'v' => 10, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Future cost', 'gu' => 'ભવિષ્યની કિંમત', 'f' => 'v.c*pow(1 + v.inf/100, v.y)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Real value of today\'s amount then', 'gu' => 'ત્યારે આજની રકમનું ખરું મૂલ્ય', 'f' => 'v.c/pow(1 + v.inf/100, v.y)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Loss of purchasing power', 'gu' => 'ખરીદશક્તિમાં ઘટાડો', 'f' => '(1 - 1/pow(1 + v.inf/100, v.y))*100', 'fmt' => 'pct'],
     ],
     'how_en' => 'Future cost = Today\'s cost × (1 + inflation)^years. Real value = Amount ÷ (1 + inflation)^years.',
     'how_gu' => 'ભવિષ્યની કિંમત = આજની કિંમત × (1 + મોંઘવારી)^વર્ષ. ખરું મૂલ્ય = રકમ ÷ (1 + મોંઘવારી)^વર્ષ.',
     'faq' => [
       ['What inflation rate should I use?', 'India\'s retail inflation has mostly been 4–7% in recent years. For school fees and medical costs, 8–10% is safer.', 'કેટલો મોંઘવારી દર રાખવો?', 'ભારતમાં તાજેતરના વર્ષોમાં છૂટક મોંઘવારી મોટેભાગે 4–7% રહી છે. શાળા ફી અને દવાખાના ખર્ચ માટે 8–10% રાખવું સલામત.'],
     ]],

    ['slug' => 'money-doubling-time-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'timer',
     'en' => 'Money Doubling Time Calculator (Rule of 72)', 'gu' => 'પૈસા બમણા થવાનો સમય કેલ્ક્યુલેટર (72 નો નિયમ)',
     'de' => 'Find in how many years your money will double or triple at a given interest rate, with the quick Rule of 72 check.',
     'dg' => 'આપેલા વ્યાજ દરે તમારા પૈસા કેટલા વર્ષમાં બમણા કે ત્રણ ગણા થાય તે 72 ના નિયમ સાથે જાણો.',
     'in' => [
       ['k' => 'r', 'en' => 'Return / interest rate (per year)', 'gu' => 'વળતર / વ્યાજ દર (વાર્ષિક)', 'v' => 8, 'u' => '%'],
       ['k' => 'p', 'en' => 'Amount (optional)', 'gu' => 'રકમ (વૈકલ્પિક)', 'v' => 100000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Years to double', 'gu' => 'બમણા થવાના વર્ષ', 'f' => 'v.r > 0 ? log(2)/log(1 + v.r/100) : 0', 'fmt' => 'num', 'd' => 1, 'u' => 'years', 'u_gu' => 'વર્ષ', 'big' => 1],
       ['en' => 'Rule of 72 estimate', 'gu' => '72 ના નિયમથી અંદાજ', 'f' => 'v.r > 0 ? 72/v.r : 0', 'fmt' => 'num', 'd' => 1, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['en' => 'Years to triple', 'gu' => 'ત્રણ ગણા થવાના વર્ષ', 'f' => 'v.r > 0 ? log(3)/log(1 + v.r/100) : 0', 'fmt' => 'num', 'd' => 1, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['en' => 'Doubled amount', 'gu' => 'બમણી રકમ', 'f' => 'v.p*2', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Exact years to double = log 2 ÷ log(1 + r/100) with yearly compounding. Rule of 72: 72 ÷ rate gives a quick estimate.',
     'how_gu' => 'બમણા થવાના ચોક્કસ વર્ષ = log 2 ÷ log(1 + r/100) (વાર્ષિક ચક્રવૃદ્ધિ). 72 નો નિયમ: 72 ÷ દર = ઝડપી અંદાજ.',
     'faq' => [
       ['Does the Rule of 72 work for all rates?', 'It is quite accurate between about 6% and 10%. For very low or very high rates use the exact result shown above.', '72 નો નિયમ દરેક દરે ચાલે?', 'લગભગ 6% થી 10% વચ્ચે ઘણો સાચો છે. બહુ ઓછા કે વધુ દરે ઉપર બતાવેલો ચોક્કસ જવાબ વાપરો.'],
     ]],

    ['slug' => 'monthly-interest-income-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'calendar-days',
     'en' => 'Monthly Interest Income Calculator (MIS / SCSS / Non-cumulative FD)', 'gu' => 'માસિક વ્યાજ આવક કેલ્ક્યુલેટર (MIS / SCSS / FD)',
     'de' => 'Find the regular monthly, quarterly or yearly interest income from post office MIS, senior citizen scheme or payout FD.',
     'dg' => 'પોસ્ટ ઓફિસ MIS, સિનિયર સિટીઝન યોજના કે વ્યાજ ચૂકવતી FD માંથી દર મહિને, ત્રિમાસિકે કે વર્ષે કેટલું વ્યાજ મળે તે જાણો.',
     'in' => [
       ['k' => 'p', 'en' => 'Deposit amount', 'gu' => 'જમા રકમ', 'v' => 900000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 7.4, 'u' => '%'],
       ['k' => 'f', 'en' => 'Interest paid', 'gu' => 'વ્યાજ ચૂકવણી', 'v' => 12, 'opts' => [[12, 'Monthly', 'માસિક'], [4, 'Quarterly', 'ત્રિમાસિક'], [1, 'Yearly', 'વાર્ષિક']]],
       ['k' => 'y', 'en' => 'Scheme period', 'gu' => 'યોજનાનો સમય', 'v' => 5, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Interest each payout', 'gu' => 'દરેક વખતે મળતું વ્યાજ', 'f' => 'v.p*v.r/100/v.f', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Interest per year', 'gu' => 'વાર્ષિક વ્યાજ', 'f' => 'v.p*v.r/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest in full period', 'gu' => 'પૂરા સમયનું કુલ વ્યાજ', 'f' => 'v.p*v.r/100*v.y', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Interest per payout = Deposit × rate ÷ 100 ÷ payouts per year. The deposit itself is returned at maturity; interest is paid out, not compounded.',
     'how_gu' => 'દરેક ચૂકવણીનું વ્યાજ = જમા × દર ÷ 100 ÷ વર્ષમાં ચૂકવણીની સંખ્યા. મૂળ રકમ પાકતી વખતે પાછી મળે; વ્યાજ ચૂકવાય છે, ચક્રવૃદ્ધિ થતું નથી.',
     'faq' => [
       ['Is this interest taxable?', 'Yes, it is added to your income. Senior citizens may get a higher deduction on interest income; check the current income tax rules.', 'આ વ્યાજ પર ટેક્સ લાગે?', 'હા, તે આવકમાં ઉમેરાય. સિનિયર સિટીઝનને વ્યાજ આવક પર વધુ કપાત મળી શકે; હાલના આવકવેરા નિયમ તપાસો.'],
     ]],

    /* ================= PLANNING ================= */

    ['slug' => 'retirement-corpus-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'armchair',
     'en' => 'Retirement Corpus Calculator', 'gu' => 'નિવૃત્તિ ભંડોળ કેલ્ક્યુલેટર',
     'de' => 'Work out how big a retirement fund you need for your monthly expenses and how much SIP to start today.',
     'dg' => 'માસિક ખર્ચ પ્રમાણે નિવૃત્તિ માટે કેટલું ભંડોળ જોઈએ અને આજથી કેટલી SIP શરૂ કરવી તે ગણો.',
     'in' => [
       ['k' => 'exp', 'en' => 'Current monthly expenses', 'gu' => 'હાલનો માસિક ખર્ચ', 'v' => 30000, 'u' => '₹'],
       ['k' => 'age', 'en' => 'Current age', 'gu' => 'હાલની ઉંમર', 'v' => 35, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'ret', 'en' => 'Retirement age', 'gu' => 'નિવૃત્તિ ઉંમર', 'v' => 60, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'life', 'en' => 'Plan till age', 'gu' => 'કઈ ઉંમર સુધીનું આયોજન', 'v' => 85, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'inf', 'en' => 'Inflation', 'gu' => 'મોંઘવારી', 'v' => 6, 'u' => '%'],
       ['k' => 'pre', 'en' => 'Return before retirement', 'gu' => 'નિવૃત્તિ પહેલાં વળતર', 'v' => 11, 'u' => '%'],
       ['k' => 'post', 'en' => 'Return after retirement', 'gu' => 'નિવૃત્તિ પછી વળતર', 'v' => 7, 'u' => '%'],
       ['k' => 'sav', 'en' => 'Retirement savings you already have', 'gu' => 'હાલની નિવૃત્તિ બચત', 'v' => 500000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Corpus needed at retirement', 'gu' => 'નિવૃત્તિ સમયે જોઈતું ભંડોળ', 'f' => $ret_corpus, 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Monthly expense at retirement', 'gu' => 'નિવૃત્તિ સમયે માસિક ખર્ચ', 'f' => 'v.exp*pow(1 + v.inf/100, ' . $retN . ')', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Your current savings will grow to', 'gu' => 'હાલની બચત વધીને થશે', 'f' => $ret_have, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Monthly SIP needed from now', 'gu' => 'હવેથી જોઈતી માસિક SIP', 'f' => $retN . ' > 0 ? max(0, ' . $ret_corpus . ' - ' . $ret_have . ')/sip(1, v.pre, ' . $retN . '*12) : 0', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Expense at retirement = today\'s expense × (1 + inflation)^years left. Corpus = present value (at retirement) of yearly expenses that rise with inflation, paid at the start of each year till the plan age, discounted at the post-retirement return. Shortfall after your grown savings is divided by the SIP factor to get the monthly SIP.',
     'how_gu' => 'નિવૃત્તિ સમયે ખર્ચ = આજનો ખર્ચ × (1 + મોંઘવારી)^બાકી વર્ષ. ભંડોળ = મોંઘવારી સાથે વધતા વાર્ષિક ખર્ચ (દર વર્ષની શરૂઆતમાં) નું નિવૃત્તિ પછીના વળતરે ગણેલું હાલનું મૂલ્ય. વધેલી બચત બાદ કરતાં ખૂટતી રકમને SIP પરિબળથી ભાગી માસિક SIP મળે.',
     'faq' => [
       ['Is this enough for medical costs?', 'This covers regular expenses only. Keep a separate health insurance policy and an emergency fund for medical needs in old age.', 'શું આમાં દવાખાના ખર્ચ આવી જાય?', 'આ ફક્ત નિયમિત ખર્ચ ગણે છે. ઘડપણમાં દવાખાના ખર્ચ માટે અલગ હેલ્થ ઇન્સ્યોરન્સ અને ઇમરજન્સી ફંડ રાખો.'],
     ]],

    ['slug' => 'emergency-fund-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'umbrella',
     'en' => 'Emergency Fund Calculator', 'gu' => 'ઇમરજન્સી ફંડ કેલ્ક્યુલેટર',
     'de' => 'Find how much emergency fund your family should keep for 3 to 12 months of expenses and how long it will take to build.',
     'dg' => 'પરિવારે 3 થી 12 મહિનાના ખર્ચ જેટલું ઇમરજન્સી ફંડ કેટલું રાખવું અને તે બનાવતાં કેટલો સમય લાગે તે જાણો.',
     'in' => [
       ['k' => 'rent', 'en' => 'Rent / home EMI per month', 'gu' => 'માસિક ભાડું / ઘરનો EMI', 'v' => 10000, 'u' => '₹'],
       ['k' => 'food', 'en' => 'Food & groceries', 'gu' => 'ખાવા-પીવા અને કરિયાણું', 'v' => 12000, 'u' => '₹'],
       ['k' => 'emi', 'en' => 'Other loan EMIs', 'gu' => 'અન્ય લોનના EMI', 'v' => 8000, 'u' => '₹'],
       ['k' => 'oth', 'en' => 'Bills, school, travel, others', 'gu' => 'બિલ, શાળા, મુસાફરી, અન્ય', 'v' => 10000, 'u' => '₹'],
       ['k' => 'mo', 'en' => 'Months to cover', 'gu' => 'કેટલા મહિનાનો ખર્ચ', 'v' => 6, 'opts' => [[3, '3 months', '3 મહિના'], [6, '6 months', '6 મહિના'], [9, '9 months', '9 મહિના'], [12, '12 months', '12 મહિના']]],
       ['k' => 'have', 'en' => 'Emergency savings you have', 'gu' => 'હાલની ઇમરજન્સી બચત', 'v' => 50000, 'u' => '₹'],
       ['k' => 'save', 'en' => 'You can save per month', 'gu' => 'દર મહિને બચત કરી શકો', 'v' => 10000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Emergency fund needed', 'gu' => 'જોઈતું ઇમરજન્સી ફંડ', 'f' => '(v.rent + v.food + v.emi + v.oth)*v.mo', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Monthly essential expenses', 'gu' => 'માસિક જરૂરી ખર્ચ', 'f' => 'v.rent + v.food + v.emi + v.oth', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Shortfall', 'gu' => 'ખૂટતી રકમ', 'f' => 'max(0, (v.rent + v.food + v.emi + v.oth)*v.mo - v.have)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Months to build it', 'gu' => 'બનાવવામાં લાગતા મહિના', 'f' => 'v.save > 0 ? ceil(max(0, (v.rent + v.food + v.emi + v.oth)*v.mo - v.have)/v.save) : 0', 'fmt' => 'int', 'u' => 'months', 'u_gu' => 'મહિના'],
     ],
     'how_en' => 'Emergency fund = total essential monthly expenses × months to cover. Shortfall = fund − existing savings; months to build = shortfall ÷ monthly saving (rounded up).',
     'how_gu' => 'ઇમરજન્સી ફંડ = કુલ જરૂરી માસિક ખર્ચ × મહિના. ખૂટતી રકમ = ફંડ − હાલની બચત; લાગતા મહિના = ખૂટતી રકમ ÷ માસિક બચત (ઉપર રાઉન્ડ).',
     'faq' => [
       ['Where should I keep an emergency fund?', 'Somewhere safe and quick to withdraw: a savings account, sweep-in FD or liquid mutual fund. Do not keep it in shares or long lock-in schemes.', 'ઇમરજન્સી ફંડ ક્યાં રાખવું?', 'સુરક્ષિત અને તરત ઉપાડી શકાય ત્યાં: બચત ખાતું, સ્વીપ-ઇન FD કે લિક્વિડ ફંડ. શેર કે લાંબા લોક-ઇનવાળી યોજનામાં ન રાખો.'],
     ]],

    ['slug' => 'financial-goal-planner-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'target',
     'en' => 'Goal Planner – Monthly Saving Needed', 'gu' => 'લક્ષ્ય આયોજન – દર મહિને કેટલી બચત જોઈએ',
     'de' => 'Plan for a child\'s education, wedding, house or car: find the future cost and the monthly SIP needed to reach it.',
     'dg' => 'બાળકનું ભણતર, લગ્ન, ઘર કે ગાડી માટે ભવિષ્યનો ખર્ચ અને તે મેળવવા દર મહિને કેટલી SIP જોઈએ તે જાણો.',
     'in' => [
       ['k' => 'cost', 'en' => 'Goal cost in today\'s price', 'gu' => 'આજના ભાવે લક્ષ્યનો ખર્ચ', 'v' => 1000000, 'u' => '₹'],
       ['k' => 'y', 'en' => 'Years to goal', 'gu' => 'લક્ષ્ય સુધી વર્ષ', 'v' => 10, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'inf', 'en' => 'Inflation', 'gu' => 'મોંઘવારી', 'v' => 6, 'u' => '%'],
       ['k' => 'r', 'en' => 'Expected return', 'gu' => 'અપેક્ષિત વળતર', 'v' => 12, 'u' => '%'],
       ['k' => 'have', 'en' => 'Savings already set aside', 'gu' => 'આ માટે હાલની બચત', 'v' => 100000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Monthly SIP needed', 'gu' => 'જોઈતી માસિક SIP', 'f' => 'v.y > 0 ? max(0, v.cost*pow(1 + v.inf/100, v.y) - v.have*pow(1 + v.r/100, v.y))/sip(1, v.r, v.y*12) : 0', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Goal cost in future', 'gu' => 'ભવિષ્યમાં લક્ષ્યનો ખર્ચ', 'f' => 'v.cost*pow(1 + v.inf/100, v.y)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Current savings will grow to', 'gu' => 'હાલની બચત વધીને થશે', 'f' => 'v.have*pow(1 + v.r/100, v.y)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Or one-time investment needed today', 'gu' => 'અથવા આજે એકસાથે જોઈતું રોકાણ', 'f' => 'max(0, v.cost*pow(1 + v.inf/100, v.y) - v.have*pow(1 + v.r/100, v.y))/pow(1 + v.r/100, v.y)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Future cost = cost × (1 + inflation)^years. Existing savings grow at the expected return. Monthly SIP = (future cost − grown savings) ÷ SIP factor, where SIP factor = future value of ₹1 per month.',
     'how_gu' => 'ભવિષ્યનો ખર્ચ = ખર્ચ × (1 + મોંઘવારી)^વર્ષ. હાલની બચત અપેક્ષિત વળતરે વધે. માસિક SIP = (ભવિષ્યનો ખર્ચ − વધેલી બચત) ÷ SIP પરિબળ (દર મહિને ₹1 નું ભવિષ્ય મૂલ્ય).',
     'faq' => [
       ['Which investment should I use for the goal?', 'For goals 7+ years away, equity funds are common; for goals under 3 years, prefer FD, RD or debt funds so the money is safe when needed.', 'લક્ષ્ય માટે કયું રોકાણ કરવું?', '7+ વર્ષ દૂરના લક્ષ્ય માટે ઇક્વિટી ફંડ સામાન્ય છે; 3 વર્ષથી ઓછા સમય માટે FD, RD કે ડેટ ફંડ સારા, જેથી જરૂર વખતે પૈસા સુરક્ષિત રહે.'],
     ]],

    ['slug' => 'household-budget-50-30-20-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-pie',
     'en' => '50-30-20 Household Budget Calculator', 'gu' => '50-30-20 ઘર ખર્ચ બજેટ કેલ્ક્યુલેટર',
     'de' => 'Split your monthly income into needs, wants and savings using the 50-30-20 rule or your own percentages.',
     'dg' => 'માસિક આવકને જરૂરિયાત, શોખ અને બચતમાં 50-30-20 નિયમ કે તમારા ટકા મુજબ વહેંચો.',
     'in' => [
       ['k' => 'inc', 'en' => 'Monthly take-home income', 'gu' => 'માસિક હાથમાં આવતી આવક', 'v' => 50000, 'u' => '₹'],
       ['k' => 'n', 'en' => 'Needs (rent, food, bills, EMI)', 'gu' => 'જરૂરિયાત (ભાડું, ખાવાનું, બિલ, EMI)', 'v' => 50, 'u' => '%'],
       ['k' => 'w', 'en' => 'Wants (outing, shopping, hobbies)', 'gu' => 'શોખ (ફરવું, ખરીદી, મનોરંજન)', 'v' => 30, 'u' => '%'],
       ['k' => 's', 'en' => 'Savings & investment', 'gu' => 'બચત અને રોકાણ', 'v' => 20, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Savings & investment per month', 'gu' => 'માસિક બચત અને રોકાણ', 'f' => 'v.inc*v.s/100', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Needs budget', 'gu' => 'જરૂરિયાત માટે બજેટ', 'f' => 'v.inc*v.n/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Wants budget', 'gu' => 'શોખ માટે બજેટ', 'f' => 'v.inc*v.w/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Savings in a year', 'gu' => 'વર્ષની બચત', 'f' => 'v.inc*v.s/100*12', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Check', 'gu' => 'ચકાસણી', 'f' => 'abs(v.n + v.w + v.s - 100) < 0.01 ? T("બરાબર – કુલ 100%", "OK – adds up to 100%") : T("ટકાનો સરવાળો 100 નથી: ", "Percentages add up to ") + round(v.n + v.w + v.s, 1) + "%"', 'fmt' => 'text'],
     ],
     'how_en' => 'Each budget = monthly income × its percentage ÷ 100. The classic rule is 50% needs, 30% wants, 20% savings; change the numbers to suit your family.',
     'how_gu' => 'દરેક બજેટ = માસિક આવક × તેના ટકા ÷ 100. જાણીતો નિયમ 50% જરૂરિયાત, 30% શોખ, 20% બચત; પરિવાર પ્રમાણે ટકા બદલી શકો.',
     'faq' => [
       ['What if my needs are more than 50%?', 'That is common with rent and EMIs. Try to cut wants first and keep at least 10–20% for savings; pay yourself first on salary day.', 'જરૂરિયાત 50% થી વધુ હોય તો?', 'ભાડું અને EMI ને કારણે એવું ઘણી વાર બને. પહેલાં શોખનો ખર્ચ ઘટાડો અને ઓછામાં ઓછું 10–20% બચત રાખો; પગાર આવે ત્યારે પહેલાં બચત અલગ કરો.'],
     ]],

    ['slug' => 'savings-challenge-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'piggy-bank',
     'en' => 'Daily / Weekly Savings Challenge Calculator', 'gu' => 'દૈનિક / સાપ્તાહિક બચત ચેલેન્જ કેલ્ક્યુલેટર',
     'de' => 'Plan a 52-week or 365-day savings challenge where you save a little more each time, and see the total saved.',
     'dg' => '52 અઠવાડિયા કે 365 દિવસની બચત ચેલેન્જ બનાવો, જેમાં દર વખતે થોડું વધુ બચાવો અને કુલ બચત જુઓ.',
     'in' => [
       ['k' => 'fq', 'en' => 'Saving frequency', 'gu' => 'કેટલી વાર બચત', 'v' => 52, 'opts' => [[365, 'Daily (365 days)', 'દરરોજ (365 દિવસ)'], [52, 'Weekly (52 weeks)', 'દર અઠવાડિયે (52)'], [12, 'Monthly (12 months)', 'દર મહિને (12)']]],
       ['k' => 'a', 'en' => 'First saving amount', 'gu' => 'પહેલી બચત રકમ', 'v' => 10, 'u' => '₹'],
       ['k' => 'd', 'en' => 'Increase each time', 'gu' => 'દર વખતે વધારો', 'v' => 10, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Total saved in one year', 'gu' => 'એક વર્ષમાં કુલ બચત', 'f' => 'v.fq*v.a + v.d*v.fq*(v.fq - 1)/2', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Last saving amount', 'gu' => 'છેલ્લી બચત રકમ', 'f' => 'v.a + v.d*(v.fq - 1)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Average per month', 'gu' => 'માસિક સરેરાશ', 'f' => '(v.fq*v.a + v.d*v.fq*(v.fq - 1)/2)/12', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Savings form an arithmetic series: Total = n × first amount + increase × n × (n − 1) ÷ 2, where n is 365, 52 or 12.',
     'how_gu' => 'બચત સમાંતર શ્રેણી છે: કુલ = n × પહેલી રકમ + વધારો × n × (n − 1) ÷ 2, જ્યાં n = 365, 52 કે 12.',
     'faq' => [
       ['Can I do the challenge in reverse?', 'Yes. Start with the biggest amount in the first week when motivation is high; the total stays exactly the same.', 'ચેલેન્જ ઊંધી રીતે કરી શકાય?', 'હા. પહેલા અઠવાડિયે જોશ વધુ હોય ત્યારે સૌથી મોટી રકમથી શરૂ કરો; કુલ બચત એટલી જ રહે.'],
     ]],

    ['slug' => 'term-insurance-cover-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'heart-pulse',
     'en' => 'Term Insurance Cover Calculator', 'gu' => 'ટર્મ ઇન્સ્યોરન્સ કવર કેલ્ક્યુલેટર',
     'de' => 'Estimate how much life cover your family needs, based on expenses, loans, future goals and existing savings.',
     'dg' => 'ખર્ચ, લોન, ભવિષ્યના લક્ષ્ય અને હાલની બચત પ્રમાણે પરિવારને કેટલા જીવન વીમા કવરની જરૂર છે તેનો અંદાજ.',
     'in' => [
       ['k' => 'exp', 'en' => 'Family\'s yearly expenses', 'gu' => 'પરિવારનો વાર્ષિક ખર્ચ', 'v' => 400000, 'u' => '₹'],
       ['k' => 'n', 'en' => 'Years of support needed', 'gu' => 'કેટલા વર્ષ સહારો જોઈએ', 'v' => 20, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'inf', 'en' => 'Inflation', 'gu' => 'મોંઘવારી', 'v' => 6, 'u' => '%'],
       ['k' => 'r', 'en' => 'Return on claim money', 'gu' => 'વીમા રકમ પર વળતર', 'v' => 7, 'u' => '%'],
       ['k' => 'loans', 'en' => 'Outstanding loans', 'gu' => 'બાકી લોન', 'v' => 2000000, 'u' => '₹'],
       ['k' => 'goals', 'en' => 'Future goals (education, marriage)', 'gu' => 'ભવિષ્યના લક્ષ્ય (ભણતર, લગ્ન)', 'v' => 1500000, 'u' => '₹'],
       ['k' => 'sav', 'en' => 'Savings & investments', 'gu' => 'બચત અને રોકાણ', 'v' => 500000, 'u' => '₹'],
       ['k' => 'cover', 'en' => 'Existing life cover', 'gu' => 'હાલનું જીવન વીમા કવર', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Additional cover needed', 'gu' => 'વધારાનું જોઈતું કવર', 'f' => 'max(0, ' . $ti_need . ' - v.sav - v.cover)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total need (expenses + loans + goals)', 'gu' => 'કુલ જરૂર (ખર્ચ + લોન + લક્ષ્ય)', 'f' => $ti_need, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Money needed for expenses alone', 'gu' => 'ફક્ત ખર્ચ માટે જોઈતી રકમ', 'f' => $ti_need . ' - v.loans - v.goals', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Expense need = present value of yearly expenses rising with inflation for the chosen years, invested at the given return (paid at the start of each year). Add loans and goals, then subtract savings and existing cover.',
     'how_gu' => 'ખર્ચની જરૂર = પસંદ કરેલા વર્ષો સુધી મોંઘવારી સાથે વધતા વાર્ષિક ખર્ચનું, આપેલા વળતરે ગણેલું હાલનું મૂલ્ય (દર વર્ષની શરૂઆતમાં). તેમાં લોન અને લક્ષ્ય ઉમેરી, બચત અને હાલનું કવર બાદ કરો.',
     'faq' => [
       ['Is there a quick rule?', 'A common thumb rule is 10–15 times your yearly income. This calculator is more accurate because it uses your own expenses and loans.', 'કોઈ ઝડપી નિયમ છે?', 'સામાન્ય નિયમ વાર્ષિક આવકના 10–15 ગણા કવરનો છે. આ કેલ્ક્યુલેટર તમારા ખર્ચ અને લોન વાપરે છે એટલે વધુ સચોટ છે.'],
     ]],

    /* ================= LOANS ================= */

    ['slug' => 'loan-prepayment-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'badge-indian-rupee',
     'en' => 'Loan Prepayment Calculator', 'gu' => 'લોન પ્રીપેમેન્ટ (વહેલી ચુકવણી) કેલ્ક્યુલેટર',
     'de' => 'See how much interest you save by part-prepaying a home or personal loan, by cutting tenure or reducing EMI.',
     'dg' => 'હોમ કે પર્સનલ લોનમાં વચ્ચે રકમ ભરવાથી હપ્તાના મહિના ઘટાડીને કે EMI ઘટાડીને કેટલું વ્યાજ બચે તે જુઓ.',
     'in' => [
       ['k' => 'p', 'en' => 'Outstanding loan amount', 'gu' => 'બાકી લોન રકમ', 'v' => 3000000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 8.5, 'u' => '%'],
       ['k' => 'n', 'en' => 'Remaining tenure', 'gu' => 'બાકી મુદત', 'v' => 240, 'u' => 'months', 'u_gu' => 'મહિના'],
       ['k' => 'x', 'en' => 'Prepayment amount', 'gu' => 'પ્રીપેમેન્ટ રકમ', 'v' => 300000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Interest saved (tenure reduced)', 'gu' => 'બચતું વ્યાજ (મુદત ઘટાડીને)', 'f' => $pp_origInt . ' - (' . $pp_E . '*' . $pp_newN . ' - max(0, v.p - v.x))', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Tenure reduced by', 'gu' => 'મુદત ઘટશે', 'f' => 'v.n - ' . $pp_newN, 'fmt' => 'int', 'u' => 'months', 'u_gu' => 'મહિના'],
       ['en' => 'Current EMI', 'gu' => 'હાલનો EMI', 'f' => $pp_E, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'New EMI (if tenure kept same)', 'gu' => 'નવો EMI (મુદત એ જ રાખો તો)', 'f' => 'emi(max(0, v.p - v.x), v.r, v.n)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest saved (EMI reduced)', 'gu' => 'બચતું વ્યાજ (EMI ઘટાડીને)', 'f' => $pp_origInt . ' - (emi(max(0, v.p - v.x), v.r, v.n)*v.n - max(0, v.p - v.x))', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'EMI = standard reducing-balance EMI on the outstanding loan. After prepayment, keeping the same EMI, new months = −log(1 − P′·i ÷ EMI) ÷ log(1 + i). Interest saved = old total interest − new total interest.',
     'how_gu' => 'EMI = બાકી લોન પર સામાન્ય ઘટતા બેલેન્સનો EMI. પ્રીપેમેન્ટ પછી એ જ EMI રાખો તો નવા મહિના = −log(1 − P′·i ÷ EMI) ÷ log(1 + i). બચતું વ્યાજ = જૂનું કુલ વ્યાજ − નવું કુલ વ્યાજ.',
     'faq' => [
       ['Reduce EMI or reduce tenure?', 'Reducing tenure saves much more interest. Choose lower EMI only if your monthly budget is tight.', 'EMI ઘટાડવો કે મુદત?', 'મુદત ઘટાડવાથી ઘણું વધારે વ્યાજ બચે. મહિનાનું બજેટ તંગ હોય તો જ EMI ઘટાડવાનું પસંદ કરો.'],
       ['Is there a prepayment charge?', 'Banks generally do not charge prepayment penalty on floating-rate home loans taken by individuals, but fixed-rate and business loans may have charges. Ask your lender.', 'પ્રીપેમેન્ટ ચાર્જ લાગે?', 'વ્યક્તિગત ફ્લોટિંગ રેટ હોમ લોન પર સામાન્ય રીતે ચાર્જ લાગતો નથી, પણ ફિક્સ રેટ અને બિઝનેસ લોન પર લાગી શકે. બેંકને પૂછો.'],
     ]],

    ['slug' => 'home-loan-tax-benefit-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'house',
     'en' => 'Home Loan Tax Benefit Calculator', 'gu' => 'હોમ લોન ટેક્સ લાભ કેલ્ક્યુલેટર',
     'de' => 'Estimate income tax saved on home loan interest and principal repayment, with deduction limits you can edit.',
     'dg' => 'હોમ લોનના વ્યાજ અને મુદ્દલ ચુકવણી પર કેટલો આવકવેરો બચે તેનો અંદાજ, કપાત મર્યાદા બદલી શકાય તે રીતે.',
     'in' => [
       ['k' => 'int', 'en' => 'Interest paid in the year', 'gu' => 'વર્ષમાં ભરેલું વ્યાજ', 'v' => 250000, 'u' => '₹'],
       ['k' => 'prin', 'en' => 'Principal repaid in the year', 'gu' => 'વર્ષમાં ભરેલું મુદ્દલ', 'v' => 120000, 'u' => '₹'],
       ['k' => 'oth', 'en' => 'Other 80C investments (PF, LIC, PPF…)', 'gu' => 'અન્ય 80C રોકાણ (PF, LIC, PPF…)', 'v' => 50000, 'u' => '₹'],
       ['k' => 'l24', 'en' => 'Interest deduction limit (Sec 24b)', 'gu' => 'વ્યાજ કપાત મર્યાદા (કલમ 24b)', 'v' => 200000, 'u' => '₹'],
       ['k' => 'l80', 'en' => 'Sec 80C limit', 'gu' => 'કલમ 80C મર્યાદા', 'v' => 150000, 'u' => '₹'],
       ['k' => 'slab', 'en' => 'Your tax slab rate', 'gu' => 'તમારો ટેક્સ સ્લેબ દર', 'v' => 30, 'u' => '%'],
       ['k' => 'cess', 'en' => 'Health & education cess', 'gu' => 'હેલ્થ અને એજ્યુકેશન સેસ', 'v' => 4, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Estimated tax saved', 'gu' => 'અંદાજિત ટેક્સ બચત', 'f' => '(min(v.int, v.l24) + min(v.prin, max(0, v.l80 - v.oth)))*v.slab/100*(1 + v.cess/100)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Deduction on interest', 'gu' => 'વ્યાજ પર કપાત', 'f' => 'min(v.int, v.l24)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Deduction on principal (80C room left)', 'gu' => 'મુદ્દલ પર કપાત (બાકી 80C જગ્યા)', 'f' => 'min(v.prin, max(0, v.l80 - v.oth))', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest not getting deduction', 'gu' => 'કપાત ન મળતું વ્યાજ', 'f' => 'max(0, v.int - v.l24)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Interest deduction = smaller of interest paid and the 24(b) limit. Principal deduction = smaller of principal repaid and the 80C limit left after other investments. Tax saved = total deduction × slab rate × (1 + cess).',
     'how_gu' => 'વ્યાજ કપાત = ભરેલું વ્યાજ અને 24(b) મર્યાદામાંથી નાની રકમ. મુદ્દલ કપાત = ભરેલું મુદ્દલ અને અન્ય રોકાણ પછી બાકી 80C મર્યાદામાંથી નાની રકમ. ટેક્સ બચત = કુલ કપાત × સ્લેબ દર × (1 + સેસ).',
     'faq' => [
       ['Does this apply in the new tax regime?', 'These deductions are mainly for the old tax regime and a self-occupied house. Tax rules and limits change with every Budget, so verify the current law or ask your CA.', 'નવી ટેક્સ પદ્ધતિમાં આ લાગુ પડે?', 'આ કપાત મુખ્યત્વે જૂની ટેક્સ પદ્ધતિ અને પોતે રહેતા ઘર માટે છે. દરેક બજેટમાં નિયમ અને મર્યાદા બદલાઈ શકે, એટલે હાલનો કાયદો તપાસો કે CA ને પૂછો.'],
     ]],

    ['slug' => 'loan-eligibility-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'scale',
     'en' => 'Loan Eligibility Calculator (by Income)', 'gu' => 'લોન પાત્રતા કેલ્ક્યુલેટર (આવક મુજબ)',
     'de' => 'Find the maximum home or personal loan you may get on your monthly income using the bank\'s FOIR limit.',
     'dg' => 'બેંકની FOIR મર્યાદા મુજબ તમારી માસિક આવક પર વધુમાં વધુ કેટલી હોમ કે પર્સનલ લોન મળી શકે તે જાણો.',
     'in' => [
       ['k' => 'inc', 'en' => 'Net monthly income', 'gu' => 'ચોખ્ખી માસિક આવક', 'v' => 60000, 'u' => '₹'],
       ['k' => 'ex', 'en' => 'Existing EMIs per month', 'gu' => 'હાલના માસિક EMI', 'v' => 5000, 'u' => '₹'],
       ['k' => 'foir', 'en' => 'FOIR allowed by bank', 'gu' => 'બેંકની FOIR મર્યાદા', 'v' => 50, 'u' => '%'],
       ['k' => 'r', 'en' => 'Interest rate', 'gu' => 'વ્યાજ દર', 'v' => 8.5, 'u' => '%'],
       ['k' => 'y', 'en' => 'Loan tenure', 'gu' => 'લોન મુદત', 'v' => 20, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Maximum loan you may get', 'gu' => 'વધુમાં વધુ મળી શકતી લોન', 'f' => 'v.r ? max(0, v.inc*v.foir/100 - v.ex)*(1 - pow(1 + v.r/1200, -v.y*12))/(v.r/1200) : max(0, v.inc*v.foir/100 - v.ex)*v.y*12', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Maximum EMI you can take', 'gu' => 'લઈ શકાય તેટલો મહત્તમ EMI', 'f' => 'max(0, v.inc*v.foir/100 - v.ex)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Max EMI = income × FOIR % − existing EMIs. Loan amount = Max EMI × [1 − (1 + i)^−n] ÷ i, where i = rate ÷ 1200 and n = months.',
     'how_gu' => 'મહત્તમ EMI = આવક × FOIR % − હાલના EMI. લોન રકમ = મહત્તમ EMI × [1 − (1 + i)^−n] ÷ i, જ્યાં i = દર ÷ 1200 અને n = મહિના.',
     'faq' => [
       ['What is FOIR?', 'Fixed Obligation to Income Ratio – the share of your income that can go to EMIs. Banks usually allow 40–60% depending on income and credit score.', 'FOIR એટલે શું?', 'Fixed Obligation to Income Ratio – આવકનો કેટલો ભાગ EMI માં જઈ શકે. આવક અને ક્રેડિટ સ્કોર પ્રમાણે બેંક સામાન્ય રીતે 40–60% સુધી મંજૂર કરે.'],
     ]],

    ['slug' => 'flat-vs-reducing-interest-rate-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'scale',
     'en' => 'Flat vs Reducing Interest Rate Calculator', 'gu' => 'ફ્લેટ અને રિડ્યુસિંગ વ્યાજ દર સરખામણી કેલ્ક્યુલેટર',
     'de' => 'Convert a flat interest rate quoted by a finance company into the real reducing-balance rate and compare the EMI.',
     'dg' => 'ફાઇનાન્સ કંપની કહે તે ફ્લેટ વ્યાજ દરનો ખરો રિડ્યુસિંગ (ઘટતા બેલેન્સ) દર શોધો અને EMI સરખાવો.',
     'in' => [
       ['k' => 'p', 'en' => 'Loan amount', 'gu' => 'લોન રકમ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'f', 'en' => 'Flat interest rate (per year)', 'gu' => 'ફ્લેટ વ્યાજ દર (વાર્ષિક)', 'v' => 10, 'u' => '%'],
       ['k' => 'n', 'en' => 'Tenure', 'gu' => 'મુદત', 'v' => 24, 'u' => 'months', 'u_gu' => 'મહિના'],
     ],
     'out' => [
       ['en' => 'Real reducing-balance rate', 'gu' => 'ખરો રિડ્યુસિંગ દર', 'f' => $fr_rate, 'fmt' => 'pct', 'big' => 1],
       ['en' => 'EMI at flat rate', 'gu' => 'ફ્લેટ દરે EMI', 'f' => 'v.n ? (v.p + v.p*v.f*v.n/1200)/v.n : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest at flat rate', 'gu' => 'ફ્લેટ દરે કુલ વ્યાજ', 'f' => 'v.p*v.f*v.n/1200', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'EMI if same rate were reducing', 'gu' => 'આ જ દર રિડ્યુસિંગ હોત તો EMI', 'f' => 'v.n ? emi(v.p, v.f, v.n) : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Extra interest you pay on flat', 'gu' => 'ફ્લેટ દરે વધારાનું વ્યાજ', 'f' => 'v.n ? v.p*v.f*v.n/1200 - (emi(v.p, v.f, v.n)*v.n - v.p) : 0', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Flat interest = P × flat rate × months ÷ 1200, charged on the full loan throughout; EMI = (P + interest) ÷ months. The real reducing rate is the rate whose normal EMI equals this flat EMI, found by numerical search.',
     'how_gu' => 'ફ્લેટ વ્યાજ = P × ફ્લેટ દર × મહિના ÷ 1200, આખી મુદત પૂરી લોન પર; EMI = (P + વ્યાજ) ÷ મહિના. ખરો રિડ્યુસિંગ દર એ છે જેના સામાન્ય EMI આ ફ્લેટ EMI જેટલો થાય; તે ગણતરીથી શોધાય છે.',
     'faq' => [
       ['Why is a flat rate misleading?', 'With a flat rate you keep paying interest on money you have already repaid, so a 10% flat rate is roughly 18% on a reducing basis for a 2-year loan.', 'ફ્લેટ દર કેમ ભ્રામક છે?', 'ફ્લેટ દરમાં ભરી દીધેલી રકમ પર પણ વ્યાજ ચાલુ રહે છે, એટલે 2 વર્ષની લોનમાં 10% ફ્લેટ લગભગ 18% રિડ્યુસિંગ જેટલો થાય.'],
     ]],

    ['slug' => 'emi-interest-rate-finder', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'search',
     'en' => 'Find Interest Rate from EMI (No Cost EMI Check)', 'gu' => 'EMI પરથી વ્યાજ દર શોધો (નો-કોસ્ટ EMI ચકાસણી)',
     'de' => 'Enter the loan amount, EMI and months to find the real interest rate, including any processing fee you pay.',
     'dg' => 'લોન રકમ, EMI અને મહિના દાખલ કરી, પ્રોસેસિંગ ફી સહિત ખરો વ્યાજ દર શોધો.',
     'in' => [
       ['k' => 'p', 'en' => 'Loan / product price', 'gu' => 'લોન / વસ્તુની કિંમત', 'v' => 50000, 'u' => '₹'],
       ['k' => 'emi', 'en' => 'Monthly EMI', 'gu' => 'માસિક EMI', 'v' => 4500, 'u' => '₹'],
       ['k' => 'n', 'en' => 'Number of EMIs', 'gu' => 'EMI ની સંખ્યા', 'v' => 12],
       ['k' => 'fee', 'en' => 'Processing fee / upfront charges', 'gu' => 'પ્રોસેસિંગ ફી / શરૂઆતના ચાર્જ', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Real interest rate (per year)', 'gu' => 'ખરો વ્યાજ દર (વાર્ષિક)', 'f' => $er_rate, 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Total you pay', 'gu' => 'કુલ ચૂકવણી', 'f' => 'v.emi*v.n + v.fee', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total cost of the loan', 'gu' => 'લોનનો કુલ ખર્ચ', 'f' => 'v.emi*v.n + v.fee - v.p', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Finds the yearly rate at which a normal reducing-balance EMI on (amount − fee) equals your EMI, using numerical search. A fee paid upfront means you effectively borrow less, so it raises the rate.',
     'how_gu' => '(રકમ − ફી) પર સામાન્ય ઘટતા બેલેન્સનો EMI તમારા EMI જેટલો થાય તે વાર્ષિક દર ગણતરીથી શોધાય છે. શરૂઆતમાં ભરેલી ફીથી ખરેખર ઓછી રકમ મળે, એટલે દર વધે.',
     'faq' => [
       ['Is no-cost EMI really free?', 'Often the interest is given back as a discount, but processing fees and GST on interest still add cost. Enter them here to see the real rate.', 'શું નો-કોસ્ટ EMI ખરેખર મફત છે?', 'ઘણી વાર વ્યાજ ડિસ્કાઉન્ટ રૂપે પાછું અપાય છે, પણ પ્રોસેસિંગ ફી અને વ્યાજ પરનો GST ખર્ચ વધારે છે. તે અહીં દાખલ કરી ખરો દર જુઓ.'],
     ]],

    ['slug' => 'credit-card-minimum-due-interest-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'credit-card',
     'en' => 'Credit Card Interest Calculator (Minimum Due Trap)', 'gu' => 'ક્રેડિટ કાર્ડ વ્યાજ કેલ્ક્યુલેટર (મિનિમમ ડ્યુ ની જાળ)',
     'de' => 'See how much interest you pay and how slowly the balance falls if you pay only the minimum due on a credit card.',
     'dg' => 'ક્રેડિટ કાર્ડમાં ફક્ત મિનિમમ ડ્યુ ભરો તો કેટલું વ્યાજ લાગે અને બાકી રકમ કેટલી ધીમે ઘટે તે જુઓ.',
     'in' => [
       ['k' => 'b', 'en' => 'Outstanding balance', 'gu' => 'બાકી રકમ', 'v' => 50000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate per month', 'gu' => 'માસિક વ્યાજ દર', 'v' => 3.5, 'u' => '%'],
       ['k' => 'md', 'en' => 'Minimum due', 'gu' => 'મિનિમમ ડ્યુ', 'v' => 5, 'u' => '%'],
       ['k' => 'gst', 'en' => 'GST on interest', 'gu' => 'વ્યાજ પર GST', 'v' => 18, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Balance after 12 months (paying only minimum)', 'gu' => '12 મહિના પછી બાકી (ફક્ત મિનિમમ ભરો તો)', 'f' => 'v.b*pow(' . $cc_q . ', 12)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Minimum due this month', 'gu' => 'આ મહિનાનો મિનિમમ ડ્યુ', 'f' => 'v.b*v.md/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest + GST next month', 'gu' => 'આવતા મહિને વ્યાજ + GST', 'f' => 'v.b*(1 - v.md/100)*v.r/100*(1 + v.gst/100)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total paid in 12 months', 'gu' => '12 મહિનામાં કુલ ભરેલું', 'f' => 'abs(1 - ' . $cc_q . ') < 1e-12 ? 12*v.b*v.md/100 : v.b*v.md/100*(1 - pow(' . $cc_q . ', 12))/(1 - ' . $cc_q . ')', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Yearly interest rate', 'gu' => 'વાર્ષિક વ્યાજ દર', 'f' => 'v.r*12', 'fmt' => 'pct'],
     ],
     'how_en' => 'Each month you pay minimum due = balance × min %. The rest attracts interest × (1 + GST): next balance = balance × (1 − min %) × (1 + monthly rate × (1 + GST)). Repeated for 12 months. Banks also charge interest on new purchases and from the transaction date, so the real cost can be higher.',
     'how_gu' => 'દર મહિને મિનિમમ ડ્યુ = બાકી × મિનિમમ %. બાકીના પર વ્યાજ × (1 + GST): નવી બાકી = બાકી × (1 − મિનિમમ %) × (1 + માસિક દર × (1 + GST)). 12 મહિના સુધી આ રીતે. બેંક નવી ખરીદી અને ખરીદીની તારીખથી પણ વ્યાજ લે, એટલે ખરો ખર્ચ વધુ હોઈ શકે.',
     'faq' => [
       ['How do I get out of credit card debt?', 'Stop new spending on the card, pay more than the minimum, or convert the balance into a lower-rate EMI or personal loan.', 'ક્રેડિટ કાર્ડના દેવામાંથી કેવી રીતે નીકળવું?', 'કાર્ડથી નવી ખરીદી બંધ કરો, મિનિમમથી વધુ ભરો, અથવા બાકી રકમ ઓછા વ્યાજના EMI કે પર્સનલ લોનમાં ફેરવો.'],
     ]],

    ['slug' => 'gold-loan-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'gem',
     'en' => 'Gold Loan Calculator', 'gu' => 'ગોલ્ડ લોન કેલ્ક્યુલેટર',
     'de' => 'Find how much gold loan you can get on your jewellery by weight, purity, today\'s gold rate and the lender\'s LTV.',
     'dg' => 'દાગીનાના વજન, શુદ્ધતા, આજના સોનાના ભાવ અને લોન આપનારના LTV મુજબ કેટલી ગોલ્ડ લોન મળે તે જાણો.',
     'in' => [
       ['k' => 'w', 'en' => 'Gold weight (net, without stones)', 'gu' => 'સોનાનું વજન (નંગ વગર)', 'v' => 20, 'u' => 'g', 'u_gu' => 'ગ્રામ'],
       ['k' => 'k', 'en' => 'Purity', 'gu' => 'શુદ્ધતા', 'v' => 22, 'opts' => [[24, '24 carat', '24 કેરેટ'], [22, '22 carat', '22 કેરેટ'], [20, '20 carat', '20 કેરેટ'], [18, '18 carat', '18 કેરેટ']]],
       ['k' => 'rate', 'en' => 'Today\'s 24K gold rate per gram', 'gu' => 'આજનો 24 કેરેટ સોનાનો ભાવ (પ્રતિ ગ્રામ)', 'v' => 12000, 'u' => '₹'],
       ['k' => 'ltv', 'en' => 'Loan to value (LTV)', 'gu' => 'લોન ટુ વેલ્યુ (LTV)', 'v' => 75, 'u' => '%'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 12, 'u' => '%'],
       ['k' => 'mo', 'en' => 'Loan period', 'gu' => 'લોન સમય', 'v' => 12, 'u' => 'months', 'u_gu' => 'મહિના'],
     ],
     'out' => [
       ['en' => 'Eligible loan amount', 'gu' => 'મળી શકતી લોન', 'f' => 'v.w*v.rate*v.k/24*v.ltv/100', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Value of your gold', 'gu' => 'તમારા સોનાની કિંમત', 'f' => 'v.w*v.rate*v.k/24', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest per month', 'gu' => 'માસિક વ્યાજ', 'f' => 'v.w*v.rate*v.k/24*v.ltv/100*v.r/1200', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest for the period', 'gu' => 'પૂરા સમયનું કુલ વ્યાજ', 'f' => 'v.w*v.rate*v.k/24*v.ltv/100*v.r/1200*v.mo', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Gold value = weight × 24K rate × (carat ÷ 24). Loan = gold value × LTV %. Interest is shown as simple monthly interest (bullet repayment); some lenders compound or charge differently.',
     'how_gu' => 'સોનાની કિંમત = વજન × 24 કેરેટ ભાવ × (કેરેટ ÷ 24). લોન = સોનાની કિંમત × LTV %. વ્યાજ સાદા માસિક વ્યાજ તરીકે બતાવ્યું છે; કેટલીક કંપની અલગ રીતે ગણે છે.',
     'faq' => [
       ['What is the maximum LTV for a gold loan?', 'RBI sets the maximum LTV for gold loans (commonly 75%) and it can change. Banks and NBFCs may give less depending on their own policy.', 'ગોલ્ડ લોનમાં મહત્તમ LTV કેટલો?', 'ગોલ્ડ લોનનો મહત્તમ LTV RBI નક્કી કરે છે (સામાન્ય રીતે 75%) અને તે બદલાઈ શકે. બેંક અને NBFC પોતાની નીતિ મુજબ ઓછું આપી શકે.'],
     ]],

    ['slug' => 'business-loan-roi-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'briefcase',
     'en' => 'Business Loan EMI vs Return Calculator', 'gu' => 'બિઝનેસ લોન EMI અને વળતર કેલ્ક્યુલેટર',
     'de' => 'Check if a business loan for a machine, stock or shop expansion pays for itself by comparing extra profit with the EMI.',
     'dg' => 'મશીન, માલ કે દુકાન વધારવા લીધેલી લોનથી થતો વધારાનો નફો EMI કરતાં વધુ છે કે નહીં તે ચકાસો.',
     'in' => [
       ['k' => 'loan', 'en' => 'Loan amount', 'gu' => 'લોન રકમ', 'v' => 500000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (per year)', 'gu' => 'વ્યાજ દર (વાર્ષિક)', 'v' => 14, 'u' => '%'],
       ['k' => 'n', 'en' => 'Tenure', 'gu' => 'મુદત', 'v' => 36, 'u' => 'months', 'u_gu' => 'મહિના'],
       ['k' => 'prof', 'en' => 'Extra monthly profit expected', 'gu' => 'અપેક્ષિત વધારાનો માસિક નફો', 'v' => 25000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Monthly surplus after EMI', 'gu' => 'EMI પછી માસિક બચત', 'f' => 'v.prof - emi(v.loan, v.r, v.n)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'EMI', 'gu' => 'EMI', 'f' => 'emi(v.loan, v.r, v.n)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total interest', 'gu' => 'કુલ વ્યાજ', 'f' => 'emi(v.loan, v.r, v.n)*v.n - v.loan', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Yearly return on loan money', 'gu' => 'લોનની રકમ પર વાર્ષિક વળતર', 'f' => 'v.loan ? v.prof*12/v.loan*100 : 0', 'fmt' => 'pct'],
       ['en' => 'Verdict', 'gu' => 'નિષ્કર્ષ', 'f' => 'v.prof >= emi(v.loan, v.r, v.n) ? T("નફો EMI ભરી શકે છે – લોન ફાયદાકારક લાગે છે", "Profit covers the EMI – loan looks worthwhile") : T("નફો EMI કરતાં ઓછો – ખિસ્સામાંથી ભરવું પડશે", "Profit is less than EMI – you will pay from pocket")', 'fmt' => 'text'],
     ],
     'how_en' => 'EMI uses the standard reducing-balance formula. Surplus = expected extra monthly profit − EMI. Yearly return = extra profit × 12 ÷ loan × 100, which should be well above the interest rate.',
     'how_gu' => 'EMI સામાન્ય ઘટતા બેલેન્સ સૂત્રથી. માસિક બચત = અપેક્ષિત વધારાનો નફો − EMI. વાર્ષિક વળતર = વધારાનો નફો × 12 ÷ લોન × 100, જે વ્યાજ દર કરતાં ઘણું વધુ હોવું જોઈએ.',
     'faq' => [
       ['What should I check before taking a business loan?', 'Be realistic about extra sales, keep a margin for slow months, and compare processing fees and prepayment charges across banks and government schemes.', 'બિઝનેસ લોન લેતાં પહેલાં શું તપાસવું?', 'વધારાના વેચાણનો વાસ્તવિક અંદાજ રાખો, ધીમા મહિનાઓ માટે ગાળો રાખો અને બેંક તથા સરકારી યોજનાઓની પ્રોસેસિંગ ફી અને પ્રીપેમેન્ટ ચાર્જ સરખાવો.'],
     ]],

    ['slug' => 'late-payment-interest-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'calendar-clock',
     'en' => 'Late Payment Interest Calculator', 'gu' => 'મોડી ચુકવણી પર વ્યાજ કેલ્ક્યુલેટર',
     'de' => 'Calculate interest and late fee on an overdue bill or udhaar from the due date to the payment date.',
     'dg' => 'બાકી બિલ કે ઉધાર પર નિયત તારીખથી ચુકવણી તારીખ સુધીનું વ્યાજ અને લેટ ફી ગણો.',
     'in' => [
       ['k' => 'amt', 'en' => 'Overdue amount', 'gu' => 'બાકી રકમ', 'v' => 25000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate', 'gu' => 'વ્યાજ દર', 'v' => 18, 'u' => '%'],
       ['k' => 'ru', 'en' => 'Rate is', 'gu' => 'દર', 'v' => 1, 'opts' => [[1, 'Per year', 'વાર્ષિક'], [12, 'Per month', 'માસિક']]],
       ['k' => 'due', 'en' => 'Due date', 'gu' => 'નિયત તારીખ', 't' => 'date', 'v' => '2026-09-01'],
       ['k' => 'paid', 'en' => 'Payment date', 'gu' => 'ચુકવણી તારીખ', 't' => 'date'],
       ['k' => 'fee', 'en' => 'Fixed late fee (if any)', 'gu' => 'ફિક્સ લેટ ફી (હોય તો)', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Total payable', 'gu' => 'કુલ ચૂકવવાની રકમ', 'f' => 'v.amt + v.amt*v.r*v.ru*max(0, days(v.due, v.paid))/36500 + v.fee', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Days late', 'gu' => 'મોડા દિવસ', 'f' => 'max(0, days(v.due, v.paid))', 'fmt' => 'int', 'u' => 'days', 'u_gu' => 'દિવસ'],
       ['en' => 'Interest for late days', 'gu' => 'મોડા દિવસનું વ્યાજ', 'f' => 'v.amt*v.r*v.ru*max(0, days(v.due, v.paid))/36500', 'fmt' => 'inr'],
     ],
     'how_en' => 'Days late = payment date − due date. Interest = amount × yearly rate × days ÷ 36500 (a monthly rate is first multiplied by 12). Fixed late fee is added on top.',
     'how_gu' => 'મોડા દિવસ = ચુકવણી તારીખ − નિયત તારીખ. વ્યાજ = રકમ × વાર્ષિક દર × દિવસ ÷ 36500 (માસિક દર હોય તો પહેલાં × 12). ફિક્સ લેટ ફી ઉપરથી ઉમેરાય.',
     'faq' => [
       ['Can I charge interest on late payment?', 'Only if it was agreed in the bill terms or contract. For MSME suppliers, the MSMED Act also gives a right to interest on delayed payments by buyers.', 'મોડી ચુકવણી પર વ્યાજ લઈ શકાય?', 'બિલની શરતો કે કરારમાં લખ્યું હોય તો જ. MSME સપ્લાયર માટે MSMED કાયદો પણ મોડી ચુકવણી પર વ્યાજનો હક આપે છે.'],
     ]],

    /* ================= SALARY BENEFITS ================= */

    ['slug' => 'hra-exemption-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'building',
     'en' => 'HRA Exemption Calculator', 'gu' => 'HRA છૂટ કેલ્ક્યુલેટર',
     'de' => 'Calculate the tax-exempt part of your House Rent Allowance (HRA) and the taxable HRA for the year.',
     'dg' => 'મકાન ભાડા ભથ્થા (HRA) નો કરમુક્ત ભાગ અને વર્ષનો કરપાત્ર HRA ગણો.',
     'in' => [
       ['k' => 'basic', 'en' => 'Monthly basic + DA', 'gu' => 'માસિક બેઝિક + DA', 'v' => 40000, 'u' => '₹'],
       ['k' => 'hra', 'en' => 'Monthly HRA received', 'gu' => 'મળતું માસિક HRA', 'v' => 16000, 'u' => '₹'],
       ['k' => 'rent', 'en' => 'Monthly rent paid', 'gu' => 'ભરેલું માસિક ભાડું', 'v' => 15000, 'u' => '₹'],
       ['k' => 'metro', 'en' => 'City type', 'gu' => 'શહેરનો પ્રકાર', 'v' => 40, 'opts' => [[50, 'Metro city (50%)', 'મેટ્રો શહેર (50%)'], [40, 'Non-metro (40%)', 'નોન-મેટ્રો (40%)']]],
     ],
     'out' => [
       ['en' => 'HRA exempt per year', 'gu' => 'વાર્ષિક કરમુક્ત HRA', 'f' => 'min(v.hra*12, max(0, (v.rent - 0.1*v.basic)*12), v.basic*12*v.metro/100)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Taxable HRA per year', 'gu' => 'વાર્ષિક કરપાત્ર HRA', 'f' => 'v.hra*12 - min(v.hra*12, max(0, (v.rent - 0.1*v.basic)*12), v.basic*12*v.metro/100)', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Rent paid minus 10% of basic', 'gu' => 'ભાડું − બેઝિકના 10%', 'f' => 'max(0, (v.rent - 0.1*v.basic)*12)', 'fmt' => 'inr', 'd' => 0],
       ['en' => '50% / 40% of basic', 'gu' => 'બેઝિકના 50% / 40%', 'f' => 'v.basic*12*v.metro/100', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'HRA exemption = lowest of (1) actual HRA received, (2) rent paid − 10% of basic + DA, (3) 50% of basic + DA in metro cities or 40% elsewhere. All taken for the year.',
     'how_gu' => 'HRA છૂટ = આ ત્રણમાંથી સૌથી ઓછું: (1) મળેલું HRA, (2) ભરેલું ભાડું − બેઝિક + DA ના 10%, (3) મેટ્રો શહેરમાં બેઝિક + DA ના 50%, બીજે 40%. આખા વર્ષ માટે.',
     'faq' => [
       ['Can I claim HRA in the new tax regime?', 'No, HRA exemption is available only in the old tax regime. Which cities count as metro and other rules can change, so check the current Income Tax rules.', 'નવી ટેક્સ પદ્ધતિમાં HRA છૂટ મળે?', 'ના, HRA છૂટ ફક્ત જૂની ટેક્સ પદ્ધતિમાં મળે. કયા શહેર મેટ્રો ગણાય અને બીજા નિયમ બદલાઈ શકે, એટલે હાલના આવકવેરા નિયમ તપાસો.'],
     ]],

    ['slug' => 'gratuity-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'gift',
     'en' => 'Gratuity Calculator', 'gu' => 'ગ્રેચ્યુઇટી કેલ્ક્યુલેટર',
     'de' => 'Calculate gratuity on leaving a job using the 15/26 formula under the Gratuity Act or 15/30 for others.',
     'dg' => 'નોકરી છોડતી વખતે મળતી ગ્રેચ્યુઇટી કાયદા હેઠળ 15/26 સૂત્ર અથવા અન્ય માટે 15/30 સૂત્રથી ગણો.',
     'in' => [
       ['k' => 'sal', 'en' => 'Last drawn basic + DA (monthly)', 'gu' => 'છેલ્લો માસિક બેઝિક + DA', 'v' => 30000, 'u' => '₹'],
       ['k' => 'yrs', 'en' => 'Completed years of service', 'gu' => 'પૂરા કરેલા નોકરીના વર્ષ', 'v' => 12],
       ['k' => 'mo', 'en' => 'Extra months', 'gu' => 'વધારાના મહિના', 'v' => 7],
       ['k' => 'act', 'en' => 'Employer covered under Gratuity Act?', 'gu' => 'કંપની ગ્રેચ્યુઇટી કાયદા હેઠળ છે?', 'v' => 1, 'opts' => [[1, 'Yes (15/26)', 'હા (15/26)'], [2, 'No (15/30)', 'ના (15/30)']]],
       ['k' => 'cap', 'en' => 'Tax-free / legal limit', 'gu' => 'કરમુક્ત / કાનૂની મર્યાદા', 'v' => 2000000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Gratuity amount', 'gu' => 'ગ્રેચ્યુઇટી રકમ', 'f' => 'min(v.cap, v.act == 1 ? v.sal*15*(v.yrs + (v.mo >= 6 ? 1 : 0))/26 : v.sal*15*v.yrs/30)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Years counted', 'gu' => 'ગણાયેલા વર્ષ', 'f' => 'v.act == 1 ? v.yrs + (v.mo >= 6 ? 1 : 0) : v.yrs', 'fmt' => 'int'],
       ['en' => 'Amount before limit', 'gu' => 'મર્યાદા પહેલાંની રકમ', 'f' => 'v.act == 1 ? v.sal*15*(v.yrs + (v.mo >= 6 ? 1 : 0))/26 : v.sal*15*v.yrs/30', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Eligibility', 'gu' => 'પાત્રતા', 'f' => 'v.yrs >= 5 ? T("5 વર્ષ પૂરાં – પાત્ર", "5 years done – eligible") : T("સામાન્ય રીતે 5 વર્ષ સેવા જરૂરી (મૃત્યુ/અપંગતા સિવાય)", "Usually needs 5 years of service (except death/disability)")', 'fmt' => 'text'],
     ],
     'how_en' => 'Under the Gratuity Act: last basic + DA × 15 × years ÷ 26, where a part year of 6 months or more counts as a full year. Not covered: × 15 × completed years ÷ 30. The result is capped at the limit you enter.',
     'how_gu' => 'ગ્રેચ્યુઇટી કાયદા હેઠળ: છેલ્લો બેઝિક + DA × 15 × વર્ષ ÷ 26; 6 મહિના કે વધુ હોય તો પૂરું વર્ષ ગણાય. કાયદા બહાર: × 15 × પૂરા વર્ષ ÷ 30. પરિણામ તમે દાખલ કરેલી મર્યાદા સુધી.',
     'faq' => [
       ['Why 26 days?', 'The Act treats a month as 26 working days, so 15 days\' wages = monthly salary × 15 ÷ 26 for each year of service.', '26 દિવસ કેમ?', 'કાયદો મહિનાને 26 કામકાજના દિવસ ગણે છે, એટલે દર વર્ષ માટે 15 દિવસનો પગાર = માસિક પગાર × 15 ÷ 26.'],
       ['Will the limit change?', 'The ceiling and the 5-year rule are set by law and new labour codes may change them. Enter the current limit.', 'મર્યાદા બદલાઈ શકે?', 'મહત્તમ મર્યાદા અને 5 વર્ષનો નિયમ કાયદાથી નક્કી થાય છે અને નવા શ્રમ કાયદાથી બદલાઈ શકે. હાલની મર્યાદા દાખલ કરો.'],
     ]],

    ['slug' => 'epf-corpus-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'briefcase',
     'en' => 'EPF Calculator – PF Balance at Retirement', 'gu' => 'EPF કેલ્ક્યુલેટર – નિવૃત્તિ સમયે PF રકમ',
     'de' => 'Estimate your Employees\' Provident Fund (PF) balance at retirement with yearly salary hikes and the EPF rate.',
     'dg' => 'દર વર્ષના પગાર વધારા અને EPF વ્યાજ દર સાથે નિવૃત્તિ સમયે તમારા PF ખાતામાં કેટલી રકમ થશે તેનો અંદાજ.',
     'in' => [
       ['k' => 'basic', 'en' => 'Monthly basic + DA', 'gu' => 'માસિક બેઝિક + DA', 'v' => 25000, 'u' => '₹'],
       ['k' => 'age', 'en' => 'Current age', 'gu' => 'હાલની ઉંમર', 'v' => 30, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'ret', 'en' => 'Retirement age', 'gu' => 'નિવૃત્તિ ઉંમર', 'v' => 58, 'u' => 'years', 'u_gu' => 'વર્ષ'],
       ['k' => 'ee', 'en' => 'Your contribution', 'gu' => 'તમારો ફાળો', 'v' => 12, 'u' => '%'],
       ['k' => 'er', 'en' => 'Employer share going to EPF', 'gu' => 'EPF માં જતો કંપનીનો ફાળો', 'v' => 3.67, 'u' => '%'],
       ['k' => 'r', 'en' => 'EPF interest rate', 'gu' => 'EPF વ્યાજ દર', 'v' => 8.25, 'u' => '%'],
       ['k' => 'g', 'en' => 'Yearly salary increase', 'gu' => 'વાર્ષિક પગાર વધારો', 'v' => 5, 'u' => '%'],
       ['k' => 'bal', 'en' => 'Current PF balance', 'gu' => 'હાલનું PF બેલેન્સ', 'v' => 100000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Estimated PF at retirement', 'gu' => 'નિવૃત્તિ સમયે અંદાજિત PF', 'f' => $ep_fv, 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Monthly contribution now', 'gu' => 'હાલનો માસિક ફાળો', 'f' => 'v.basic*(v.ee + v.er)/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total contributions till retirement', 'gu' => 'નિવૃત્તિ સુધી કુલ ફાળો', 'f' => 'v.g ? ' . $ep_C0 . '*(pow(' . $ep_G . ', ' . $ep_N . ') - 1)/(' . $ep_G . ' - 1) : ' . $ep_C0 . '*' . $ep_N, 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Yearly contribution = basic × (your % + employer EPF %) × 12, rising each year with the salary increase. Each year\'s contribution and the opening balance grow at the EPF rate till retirement (yearly approximation; EPFO calculates interest monthly and credits it yearly).',
     'how_gu' => 'વાર્ષિક ફાળો = બેઝિક × (તમારા % + કંપનીના EPF %) × 12, જે દર વર્ષે પગાર વધારા સાથે વધે. દરેક વર્ષનો ફાળો અને હાલનું બેલેન્સ નિવૃત્તિ સુધી EPF દરે વધે (વાર્ષિક અંદાજ; EPFO વ્યાજ માસિક ગણી વર્ષે જમા કરે છે).',
     'faq' => [
       ['Why does only 3.67% of the employer share go to EPF?', 'Of the employer\'s 12%, 8.33% (up to the wage ceiling) goes to the Employees\' Pension Scheme (EPS) and the rest to your EPF account.', 'કંપનીનો ફક્ત 3.67% EPF માં કેમ?', 'કંપનીના 12% માંથી 8.33% (પગાર મર્યાદા સુધી) પેન્શન યોજના (EPS) માં જાય અને બાકી તમારા EPF ખાતામાં.'],
     ]],

    ['slug' => 'tds-on-invoice-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'receipt',
     'en' => 'TDS Calculator for Bills & Payments', 'gu' => 'બિલ અને ચુકવણી પર TDS કેલ્ક્યુલેટર',
     'de' => 'Work out TDS to deduct on a contractor, professional or rent bill and the net amount to pay, with GST shown separately.',
     'dg' => 'કોન્ટ્રાક્ટર, પ્રોફેશનલ કે ભાડાના બિલ પર કેટલો TDS કાપવો અને GST સાથે ચોખ્ખી કેટલી રકમ ચૂકવવી તે ગણો.',
     'in' => [
       ['k' => 'amt', 'en' => 'Bill amount before GST', 'gu' => 'GST પહેલાંની બિલ રકમ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'gst', 'en' => 'GST on bill', 'gu' => 'બિલ પર GST', 'v' => 18, 'u' => '%'],
       ['k' => 'tds', 'en' => 'TDS rate (as per section)', 'gu' => 'TDS દર (કલમ મુજબ)', 'v' => 2, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Net amount to pay', 'gu' => 'ચૂકવવાની ચોખ્ખી રકમ', 'f' => 'v.amt*(1 + v.gst/100) - v.amt*v.tds/100', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'TDS to deduct', 'gu' => 'કાપવાનો TDS', 'f' => 'v.amt*v.tds/100', 'fmt' => 'inr'],
       ['en' => 'GST amount', 'gu' => 'GST રકમ', 'f' => 'v.amt*v.gst/100', 'fmt' => 'inr'],
       ['en' => 'Invoice total', 'gu' => 'બિલની કુલ રકમ', 'f' => 'v.amt*(1 + v.gst/100)', 'fmt' => 'inr'],
     ],
     'how_en' => 'When GST is shown separately on the bill, TDS is generally calculated on the amount before GST: TDS = amount × TDS rate. Net payment = (amount + GST) − TDS.',
     'how_gu' => 'બિલમાં GST અલગ બતાવ્યો હોય ત્યારે સામાન્ય રીતે TDS GST પહેલાંની રકમ પર ગણાય: TDS = રકમ × TDS દર. ચોખ્ખી ચુકવણી = (રકમ + GST) − TDS.',
     'faq' => [
       ['Which TDS rate applies?', 'It depends on the section (contract, professional fees, rent, commission), the payee type and whether PAN is given. Rates and thresholds change with the Budget, so check the latest TDS chart.', 'કયો TDS દર લાગે?', 'તે કલમ (કોન્ટ્રાક્ટ, પ્રોફેશનલ ફી, ભાડું, કમિશન), લેનારના પ્રકાર અને PAN આપ્યો છે કે નહીં તેના પર આધાર રાખે. દર અને મર્યાદા બજેટ સાથે બદલાય, તાજો TDS ચાર્ટ જુઓ.'],
     ]],

    /* ================= GOLD & SILVER ================= */

    ['slug' => 'gold-jewellery-price-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'gem',
     'en' => 'Gold Jewellery Price Calculator (Making Charge + GST)', 'gu' => 'સોનાના દાગીનાની કિંમત કેલ્ક્યુલેટર (ઘડામણ + GST)',
     'de' => 'Find the final price of gold jewellery from weight, today\'s rate, making charges and GST before you visit the jeweller.',
     'dg' => 'વજન, આજનો ભાવ, ઘડામણ અને GST પરથી સોનાના દાગીનાની અંતિમ કિંમત જ્વેલર પાસે જતાં પહેલાં જાણો.',
     'in' => [
       ['k' => 'w', 'en' => 'Jewellery weight', 'gu' => 'દાગીનાનું વજન', 'v' => 10, 'u' => 'g', 'u_gu' => 'ગ્રામ'],
       ['k' => 'rate', 'en' => 'Gold rate per gram (for this purity)', 'gu' => 'આ શુદ્ધતાનો સોનાનો ભાવ (પ્રતિ ગ્રામ)', 'v' => 11000, 'u' => '₹'],
       ['k' => 'mt', 'en' => 'Making charge type', 'gu' => 'ઘડામણનો પ્રકાર', 'v' => 1, 'opts' => [[1, '% of gold value', 'સોનાની કિંમતના %'], [2, '₹ per gram', 'પ્રતિ ગ્રામ ₹']]],
       ['k' => 'mk', 'en' => 'Making charge', 'gu' => 'ઘડામણ', 'v' => 12],
       ['k' => 'stone', 'en' => 'Stone / other charges', 'gu' => 'નંગ / અન્ય ચાર્જ', 'v' => 0, 'u' => '₹'],
       ['k' => 'gst', 'en' => 'GST', 'gu' => 'GST', 'v' => 3, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Final jewellery price', 'gu' => 'દાગીનાની અંતિમ કિંમત', 'f' => '(v.w*v.rate + (v.mt == 1 ? v.w*v.rate*v.mk/100 : v.w*v.mk) + v.stone)*(1 + v.gst/100)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Gold value', 'gu' => 'સોનાની કિંમત', 'f' => 'v.w*v.rate', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Making charges', 'gu' => 'ઘડામણ', 'f' => 'v.mt == 1 ? v.w*v.rate*v.mk/100 : v.w*v.mk', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'GST amount', 'gu' => 'GST રકમ', 'f' => '(v.w*v.rate + (v.mt == 1 ? v.w*v.rate*v.mk/100 : v.w*v.mk) + v.stone)*v.gst/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Effective price per gram', 'gu' => 'પ્રતિ ગ્રામ અસરકારક ભાવ', 'f' => 'v.w ? (v.w*v.rate + (v.mt == 1 ? v.w*v.rate*v.mk/100 : v.w*v.mk) + v.stone)*(1 + v.gst/100)/v.w : 0', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Gold value = weight × rate. Making = gold value × % or weight × ₹ per gram. Final price = (gold value + making + stone charges) × (1 + GST %).',
     'how_gu' => 'સોનાની કિંમત = વજન × ભાવ. ઘડામણ = સોનાની કિંમત × % અથવા વજન × પ્રતિ ગ્રામ ₹. અંતિમ કિંમત = (સોનાની કિંમત + ઘડામણ + નંગ ચાર્જ) × (1 + GST %).',
     'faq' => [
       ['How do I get the rate for 22 carat?', 'Jewellers display a separate 22K rate. If you only know the 24K rate, 22K is roughly 24K rate × 22 ÷ 24 (about 91.6%). Always ask for a hallmarked (HUID) piece.', '22 કેરેટનો ભાવ કેવી રીતે જાણવો?', 'જ્વેલર 22 કેરેટનો ભાવ અલગ બતાવે છે. ફક્ત 24 કેરેટનો ભાવ ખબર હોય તો 22 કેરેટ ≈ 24 કેરેટ ભાવ × 22 ÷ 24 (લગભગ 91.6%). હંમેશા હોલમાર્ક (HUID) વાળો દાગીનો માંગો.'],
     ]],

    ['slug' => 'silver-price-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'coins',
     'en' => 'Silver Price Calculator', 'gu' => 'ચાંદીની કિંમત કેલ્ક્યુલેટર',
     'de' => 'Calculate the price of silver jewellery, utensils or coins from weight in grams, per-kg rate, purity, making and GST.',
     'dg' => 'ગ્રામમાં વજન, કિલોનો ભાવ, શુદ્ધતા, ઘડામણ અને GST પરથી ચાંદીના દાગીના, વાસણ કે સિક્કાની કિંમત ગણો.',
     'in' => [
       ['k' => 'w', 'en' => 'Weight', 'gu' => 'વજન', 'v' => 100, 'u' => 'g', 'u_gu' => 'ગ્રામ'],
       ['k' => 'rate', 'en' => 'Fine silver rate per kg', 'gu' => 'શુદ્ધ ચાંદીનો ભાવ (પ્રતિ કિલો)', 'v' => 150000, 'u' => '₹'],
       ['k' => 'pur', 'en' => 'Purity', 'gu' => 'શુદ્ધતા', 'v' => 92.5, 'u' => '%'],
       ['k' => 'mk', 'en' => 'Making charge per gram', 'gu' => 'પ્રતિ ગ્રામ ઘડામણ', 'v' => 15, 'u' => '₹'],
       ['k' => 'gst', 'en' => 'GST', 'gu' => 'GST', 'v' => 3, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Final price', 'gu' => 'અંતિમ કિંમત', 'f' => '(v.w*v.rate/1000*v.pur/99.9 + v.w*v.mk)*(1 + v.gst/100)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Silver value', 'gu' => 'ચાંદીની કિંમત', 'f' => 'v.w*v.rate/1000*v.pur/99.9', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Making charges', 'gu' => 'ઘડામણ', 'f' => 'v.w*v.mk', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'GST', 'gu' => 'GST', 'f' => '(v.w*v.rate/1000*v.pur/99.9 + v.w*v.mk)*v.gst/100', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Silver value = weight (g) × rate per kg ÷ 1000 × purity ÷ 99.9 (fine silver). Add making per gram, then GST on the total.',
     'how_gu' => 'ચાંદીની કિંમત = વજન (ગ્રામ) × કિલોનો ભાવ ÷ 1000 × શુદ્ધતા ÷ 99.9 (શુદ્ધ ચાંદી). પ્રતિ ગ્રામ ઘડામણ ઉમેરી, કુલ પર GST.',
     'faq' => [
       ['What purity is silver jewellery?', 'Sterling silver is 92.5% pure. Payal and utensils vary from about 70% to 92.5%; coins and bars are usually 99.9%. Ask the shop for the purity stamp.', 'ચાંદીના દાગીનાની શુદ્ધતા કેટલી હોય?', 'સ્ટર્લિંગ ચાંદી 92.5% શુદ્ધ હોય. પાયલ અને વાસણ લગભગ 70% થી 92.5% સુધી; સિક્કા અને બિસ્કિટ સામાન્ય રીતે 99.9%. દુકાનમાં શુદ્ધતાનો સ્ટેમ્પ પૂછો.'],
     ]],

    /* ================= BUSINESS ================= */

    ['slug' => 'break-even-point-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-column',
     'en' => 'Break-Even Point Calculator', 'gu' => 'બ્રેક-ઇવન પોઇન્ટ (નફો-નુકસાન સમતોલ) કેલ્ક્યુલેટર',
     'de' => 'Find how many units or how much sales a shop or small business needs each month to cover all costs and reach target profit.',
     'dg' => 'દુકાન કે નાના ધંધાને બધો ખર્ચ કાઢવા અને લક્ષ્ય નફો મેળવવા દર મહિને કેટલા નંગ કે કેટલું વેચાણ જોઈએ તે શોધો.',
     'in' => [
       ['k' => 'fixed', 'en' => 'Fixed costs per month (rent, salary, bills)', 'gu' => 'માસિક ફિક્સ ખર્ચ (ભાડું, પગાર, બિલ)', 'v' => 50000, 'u' => '₹'],
       ['k' => 'price', 'en' => 'Selling price per unit', 'gu' => 'પ્રતિ નંગ વેચાણ ભાવ', 'v' => 200, 'u' => '₹'],
       ['k' => 'vc', 'en' => 'Variable cost per unit', 'gu' => 'પ્રતિ નંગ બદલાતો ખર્ચ', 'v' => 120, 'u' => '₹'],
       ['k' => 'tp', 'en' => 'Target profit per month', 'gu' => 'માસિક લક્ષ્ય નફો', 'v' => 20000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Break-even units per month', 'gu' => 'માસિક બ્રેક-ઇવન નંગ', 'f' => 'v.price > v.vc ? ceil(v.fixed/(v.price - v.vc)) : 0', 'fmt' => 'int', 'big' => 1],
       ['en' => 'Break-even sales', 'gu' => 'બ્રેક-ઇવન વેચાણ', 'f' => 'v.price > v.vc ? v.fixed/(v.price - v.vc)*v.price : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Units for target profit', 'gu' => 'લક્ષ્ય નફા માટે નંગ', 'f' => 'v.price > v.vc ? ceil((v.fixed + v.tp)/(v.price - v.vc)) : 0', 'fmt' => 'int'],
       ['en' => 'Contribution per unit', 'gu' => 'પ્રતિ નંગ ફાળો', 'f' => 'v.price - v.vc', 'fmt' => 'inr'],
       ['en' => 'Contribution margin', 'gu' => 'ફાળા માર્જિન', 'f' => 'v.price ? (v.price - v.vc)/v.price*100 : 0', 'fmt' => 'pct'],
     ],
     'how_en' => 'Contribution per unit = selling price − variable cost. Break-even units = fixed costs ÷ contribution. Units for target profit = (fixed costs + target profit) ÷ contribution.',
     'how_gu' => 'પ્રતિ નંગ ફાળો = વેચાણ ભાવ − બદલાતો ખર્ચ. બ્રેક-ઇવન નંગ = ફિક્સ ખર્ચ ÷ ફાળો. લક્ષ્ય નફા માટે નંગ = (ફિક્સ ખર્ચ + લક્ષ્ય નફો) ÷ ફાળો.',
     'faq' => [
       ['What are variable costs?', 'Costs that rise with each unit sold, such as raw material, packing, delivery and sales commission. Rent and staff salary are fixed costs.', 'બદલાતો ખર્ચ એટલે શું?', 'દરેક વેચાતા નંગ સાથે વધતો ખર્ચ, જેમ કે કાચો માલ, પેકિંગ, ડિલિવરી અને કમિશન. ભાડું અને સ્ટાફ પગાર ફિક્સ ખર્ચ છે.'],
     ]],

    ['slug' => 'working-capital-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'store',
     'en' => 'Working Capital & Current Ratio Calculator', 'gu' => 'વર્કિંગ કેપિટલ અને કરંટ રેશિયો કેલ્ક્યુલેટર',
     'de' => 'Calculate working capital, current ratio and quick ratio of a shop or small business from its current assets and dues.',
     'dg' => 'દુકાન કે નાના ધંધાની ચાલુ મિલકત અને ચૂકવવાની રકમ પરથી વર્કિંગ કેપિટલ, કરંટ રેશિયો અને ક્વિક રેશિયો ગણો.',
     'in' => [
       ['k' => 'cash', 'en' => 'Cash & bank balance', 'gu' => 'રોકડ અને બેંક બેલેન્સ', 'v' => 50000, 'u' => '₹'],
       ['k' => 'recv', 'en' => 'Money to receive from customers', 'gu' => 'ગ્રાહકો પાસેથી લેવાના', 'v' => 150000, 'u' => '₹'],
       ['k' => 'inv', 'en' => 'Stock / inventory value', 'gu' => 'માલ સ્ટોકની કિંમત', 'v' => 200000, 'u' => '₹'],
       ['k' => 'pay', 'en' => 'Money to pay suppliers', 'gu' => 'સપ્લાયરને આપવાના', 'v' => 120000, 'u' => '₹'],
       ['k' => 'stl', 'en' => 'Short-term loans / OD / other dues', 'gu' => 'ટૂંકી લોન / OD / અન્ય બાકી', 'v' => 80000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Working capital', 'gu' => 'વર્કિંગ કેપિટલ', 'f' => 'v.cash + v.recv + v.inv - v.pay - v.stl', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Current ratio', 'gu' => 'કરંટ રેશિયો', 'f' => 'v.pay + v.stl ? (v.cash + v.recv + v.inv)/(v.pay + v.stl) : 0', 'fmt' => 'num'],
       ['en' => 'Quick ratio (without stock)', 'gu' => 'ક્વિક રેશિયો (સ્ટોક વગર)', 'f' => 'v.pay + v.stl ? (v.cash + v.recv)/(v.pay + v.stl) : 0', 'fmt' => 'num'],
       ['en' => 'Health', 'gu' => 'સ્થિતિ', 'f' => '(v.pay + v.stl) == 0 ? T("કોઈ બાકી ચુકવણી નથી", "No current dues") : ((v.cash + v.recv + v.inv)/(v.pay + v.stl) >= 1.5 ? T("સારી – પૂરતી ચાલુ મૂડી", "Healthy – enough working capital") : ((v.cash + v.recv + v.inv)/(v.pay + v.stl) >= 1 ? T("ઠીક – થોડું ધ્યાન રાખો", "Okay – watch cash flow") : T("તંગ – ચુકવણી કરતાં મિલકત ઓછી", "Tight – dues exceed current assets")))', 'fmt' => 'text'],
     ],
     'how_en' => 'Working capital = current assets (cash + receivables + stock) − current liabilities (payables + short-term loans). Current ratio = assets ÷ liabilities; quick ratio leaves out stock.',
     'how_gu' => 'વર્કિંગ કેપિટલ = ચાલુ મિલકત (રોકડ + લેવાના + સ્ટોક) − ચાલુ જવાબદારી (આપવાના + ટૂંકી લોન). કરંટ રેશિયો = મિલકત ÷ જવાબદારી; ક્વિક રેશિયોમાં સ્ટોક ગણાતો નથી.',
     'faq' => [
       ['What is a good current ratio?', 'Around 1.5 to 2 is usually comfortable for a trading business. Below 1 means you may struggle to pay suppliers on time.', 'સારો કરંટ રેશિયો કેટલો?', 'વેપારી ધંધા માટે સામાન્ય રીતે 1.5 થી 2 આરામદાયક ગણાય. 1 થી ઓછો હોય તો સપ્લાયરને સમયસર ચૂકવવામાં તકલીફ પડી શકે.'],
     ]],

    ['slug' => 'partnership-profit-sharing-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'handshake',
     'en' => 'Partnership Profit Sharing Calculator', 'gu' => 'ભાગીદારી નફા વહેંચણી કેલ્ક્યુલેટર',
     'de' => 'Divide business profit among up to four partners in proportion to the capital each put in and for how many months.',
     'dg' => 'ધંધાનો નફો ચાર સુધીના ભાગીદારોમાં દરેકે કેટલી મૂડી કેટલા મહિના રાખી તે પ્રમાણે વહેંચો.',
     'in' => [
       ['k' => 'profit', 'en' => 'Total profit to share', 'gu' => 'વહેંચવાનો કુલ નફો', 'v' => 500000, 'u' => '₹'],
       ['k' => 'c1', 'en' => 'Partner 1 capital', 'gu' => 'ભાગીદાર 1 મૂડી', 'v' => 500000, 'u' => '₹'],
       ['k' => 'm1', 'en' => 'Partner 1 months', 'gu' => 'ભાગીદાર 1 મહિના', 'v' => 12],
       ['k' => 'c2', 'en' => 'Partner 2 capital', 'gu' => 'ભાગીદાર 2 મૂડી', 'v' => 300000, 'u' => '₹'],
       ['k' => 'm2', 'en' => 'Partner 2 months', 'gu' => 'ભાગીદાર 2 મહિના', 'v' => 12],
       ['k' => 'c3', 'en' => 'Partner 3 capital', 'gu' => 'ભાગીદાર 3 મૂડી', 'v' => 200000, 'u' => '₹'],
       ['k' => 'm3', 'en' => 'Partner 3 months', 'gu' => 'ભાગીદાર 3 મહિના', 'v' => 6],
       ['k' => 'c4', 'en' => 'Partner 4 capital', 'gu' => 'ભાગીદાર 4 મૂડી', 'v' => 0, 'u' => '₹'],
       ['k' => 'm4', 'en' => 'Partner 4 months', 'gu' => 'ભાગીદાર 4 મહિના', 'v' => 0],
     ],
     'out' => [
       ['en' => 'Partner 1 share', 'gu' => 'ભાગીદાર 1 નો ભાગ', 'f' => $pt_W . ' ? v.profit*v.c1*v.m1/' . $pt_W . ' : 0', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Partner 2 share', 'gu' => 'ભાગીદાર 2 નો ભાગ', 'f' => $pt_W . ' ? v.profit*v.c2*v.m2/' . $pt_W . ' : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Partner 3 share', 'gu' => 'ભાગીદાર 3 નો ભાગ', 'f' => $pt_W . ' ? v.profit*v.c3*v.m3/' . $pt_W . ' : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Partner 4 share', 'gu' => 'ભાગીદાર 4 નો ભાગ', 'f' => $pt_W . ' ? v.profit*v.c4*v.m4/' . $pt_W . ' : 0', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Each partner\'s weight = capital × months invested. Share = total profit × own weight ÷ sum of all weights. Leave unused partners at 0.',
     'how_gu' => 'દરેક ભાગીદારનું વજન = મૂડી × રોકાણના મહિના. ભાગ = કુલ નફો × પોતાનું વજન ÷ બધાના વજનનો સરવાળો. ન હોય તે ભાગીદાર 0 રાખો.',
     'faq' => [
       ['What if the partnership deed says a different ratio?', 'The deed always wins. If it fixes a ratio, salary or interest on capital for partners, follow that; this tool is for capital-and-time based sharing.', 'ભાગીદારી ખતમાં અલગ પ્રમાણ હોય તો?', 'ભાગીદારી ખત જ માન્ય ગણાય. તેમાં પ્રમાણ, પગાર કે મૂડી પર વ્યાજ નક્કી હોય તો તે મુજબ કરો; આ સાધન મૂડી અને સમય આધારિત વહેંચણી માટે છે.'],
     ]],

    ['slug' => 'agent-commission-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'users',
     'en' => 'Commission Calculator for Agents & Brokers', 'gu' => 'એજન્ટ અને દલાલ કમિશન કેલ્ક્યુલેટર',
     'de' => 'Calculate commission for property dealers, insurance or sales agents with GST, TDS and the net amount received.',
     'dg' => 'મકાન દલાલ, વીમા કે સેલ્સ એજન્ટનું કમિશન GST, TDS અને હાથમાં આવતી ચોખ્ખી રકમ સાથે ગણો.',
     'in' => [
       ['k' => 'sale', 'en' => 'Deal / sale value', 'gu' => 'સોદા / વેચાણની રકમ', 'v' => 1000000, 'u' => '₹'],
       ['k' => 'rate', 'en' => 'Commission rate', 'gu' => 'કમિશન દર', 'v' => 2, 'u' => '%'],
       ['k' => 'bonus', 'en' => 'Fixed bonus / incentive', 'gu' => 'ફિક્સ બોનસ / ઇન્સેન્ટિવ', 'v' => 0, 'u' => '₹'],
       ['k' => 'gst', 'en' => 'GST charged (0 if not registered)', 'gu' => 'લાગતો GST (નોંધણી ન હોય તો 0)', 'v' => 0, 'u' => '%'],
       ['k' => 'tds', 'en' => 'TDS deducted by payer', 'gu' => 'ચૂકવનાર દ્વારા કપાતો TDS', 'v' => 2, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Net amount received', 'gu' => 'હાથમાં આવતી ચોખ્ખી રકમ', 'f' => '(v.sale*v.rate/100 + v.bonus)*(1 + v.gst/100) - (v.sale*v.rate/100 + v.bonus)*v.tds/100', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Gross commission', 'gu' => 'કુલ કમિશન', 'f' => 'v.sale*v.rate/100 + v.bonus', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'GST to collect', 'gu' => 'લેવાનો GST', 'f' => '(v.sale*v.rate/100 + v.bonus)*v.gst/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'TDS deducted', 'gu' => 'કપાયેલો TDS', 'f' => '(v.sale*v.rate/100 + v.bonus)*v.tds/100', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Commission = deal value × rate + bonus. GST (if registered) is added on the commission; TDS is deducted on the commission before GST. Net = commission + GST − TDS.',
     'how_gu' => 'કમિશન = સોદાની રકમ × દર + બોનસ. GST (નોંધણી હોય તો) કમિશન પર ઉમેરાય; TDS GST વગરના કમિશન પર કપાય. ચોખ્ખું = કમિશન + GST − TDS.',
     'faq' => [
       ['Can I claim back the TDS?', 'Yes. TDS deducted from your commission shows in Form 26AS / AIS and is adjusted against your income tax when you file your return.', 'કપાયેલો TDS પાછો મળે?', 'હા. કમિશન પર કપાયેલો TDS ફોર્મ 26AS / AIS માં દેખાય અને રિટર્ન ભરતી વખતે તમારા આવકવેરા સામે સરભર થાય.'],
     ]],

    ['slug' => 'chit-fund-bhishi-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'users',
     'en' => 'Chit Fund / Bhishi Dividend Calculator', 'gu' => 'ચિટ ફંડ / ભીસી ડિવિડન્ડ કેલ્ક્યુલેટર',
     'de' => 'Work out the prize amount, organiser commission, dividend per member and net instalment in a chit fund or bhishi auction.',
     'dg' => 'ચિટ ફંડ કે ભીસીની હરાજીમાં ઇનામ રકમ, આયોજક કમિશન, દરેક સભ્યનું ડિવિડન્ડ અને ચોખ્ખો હપ્તો ગણો.',
     'in' => [
       ['k' => 'n', 'en' => 'Number of members', 'gu' => 'સભ્યોની સંખ્યા', 'v' => 20],
       ['k' => 'c', 'en' => 'Monthly instalment per member', 'gu' => 'દરેક સભ્યનો માસિક હપ્તો', 'v' => 5000, 'u' => '₹'],
       ['k' => 'bid', 'en' => 'Winning bid discount this month', 'gu' => 'આ મહિને બોલીની છૂટ (ડિસ્કાઉન્ટ)', 'v' => 20000, 'u' => '₹'],
       ['k' => 'fee', 'en' => 'Organiser / foreman commission', 'gu' => 'આયોજક / ફોરમેન કમિશન', 'v' => 5, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Prize money to the winner', 'gu' => 'જીતનારને મળતી રકમ', 'f' => 'v.n*v.c - v.bid', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Chit value (total pool)', 'gu' => 'ચિટની કુલ રકમ', 'f' => 'v.n*v.c', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Organiser commission', 'gu' => 'આયોજક કમિશન', 'f' => 'v.n*v.c*v.fee/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Dividend per member', 'gu' => 'દરેક સભ્યનું ડિવિડન્ડ', 'f' => 'v.n ? max(0, v.bid - v.n*v.c*v.fee/100)/v.n : 0', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Net instalment payable this month', 'gu' => 'આ મહિને ભરવાનો ચોખ્ખો હપ્તો', 'f' => 'v.c - (v.n ? max(0, v.bid - v.n*v.c*v.fee/100)/v.n : 0)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Pool = members × instalment. Winner gets pool − bid discount. Organiser commission = pool × commission %, taken from the discount; the remaining discount is shared equally as dividend, which reduces everyone\'s instalment.',
     'how_gu' => 'કુલ રકમ = સભ્યો × હપ્તો. જીતનારને = કુલ રકમ − બોલીની છૂટ. આયોજક કમિશન = કુલ રકમ × કમિશન %, જે છૂટમાંથી કપાય; બાકી છૂટ બધા સભ્યોમાં સરખી વહેંચાય અને દરેકનો હપ્તો ઘટે.',
     'faq' => [
       ['Is a chit fund safe?', 'Use only chit funds registered under the Chit Funds Act. Informal bhishi among known people depends entirely on trust, so keep written records of every month.', 'શું ચિટ ફંડ સુરક્ષિત છે?', 'ફક્ત ચિટ ફંડ કાયદા હેઠળ નોંધાયેલા ચિટ ફંડ જ વાપરો. ઓળખીતા લોકોની ભીસી સંપૂર્ણ વિશ્વાસ પર ચાલે છે, એટલે દર મહિનાનો લખિત હિસાબ રાખો.'],
     ]],

    ['slug' => 'cash-discount-annual-cost-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'percent',
     'en' => 'Early Payment Cash Discount Calculator', 'gu' => 'વહેલી ચુકવણી રોકડ વટાવ કેલ્ક્યુલેટર',
     'de' => 'Find the real yearly return of taking a supplier\'s early payment cash discount, like 2% in 10 days or net 30.',
     'dg' => 'સપ્લાયર "10 દિવસમાં ભરો તો 2% વટાવ, નહીં તો 30 દિવસ" આપે ત્યારે વટાવ લેવાનો ખરો વાર્ષિક ફાયદો શોધો.',
     'in' => [
       ['k' => 'amt', 'en' => 'Invoice amount', 'gu' => 'બિલ રકમ', 'v' => 100000, 'u' => '₹'],
       ['k' => 'd', 'en' => 'Cash discount', 'gu' => 'રોકડ વટાવ', 'v' => 2, 'u' => '%'],
       ['k' => 'dd', 'en' => 'Pay within (discount days)', 'gu' => 'કેટલા દિવસમાં ભરો તો વટાવ', 'v' => 10, 'u' => 'days', 'u_gu' => 'દિવસ'],
       ['k' => 'nd', 'en' => 'Normal credit days', 'gu' => 'સામાન્ય ઉધાર દિવસ', 'v' => 30, 'u' => 'days', 'u_gu' => 'દિવસ'],
     ],
     'out' => [
       ['en' => 'Yearly return of taking the discount', 'gu' => 'વટાવ લેવાનો વાર્ષિક ફાયદો', 'f' => 'v.nd > v.dd && v.d < 100 ? v.d/(100 - v.d)*365/(v.nd - v.dd)*100 : 0', 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Discount amount', 'gu' => 'વટાવ રકમ', 'f' => 'v.amt*v.d/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Pay early', 'gu' => 'વહેલા ભરવાની રકમ', 'f' => 'v.amt*(1 - v.d/100)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Yearly cost of skipping the discount = discount ÷ (100 − discount) × 365 ÷ (credit days − discount days) × 100. If this is higher than your loan or OD rate, borrowing to pay early is profitable.',
     'how_gu' => 'વટાવ ન લેવાનો વાર્ષિક ખર્ચ = વટાવ ÷ (100 − વટાવ) × 365 ÷ (ઉધાર દિવસ − વટાવ દિવસ) × 100. આ તમારા લોન કે OD દર કરતાં વધુ હોય તો ઉધાર લઈને પણ વહેલું ભરવું ફાયદાકારક.',
     'faq' => [
       ['Is 2% in 10 days a good deal?', 'Yes – with 30-day credit it works out to about 37% a year, far more than any bank interest. Pay early whenever you have the cash.', '10 દિવસમાં 2% સારો સોદો છે?', 'હા – 30 દિવસના ઉધાર સાથે તે વર્ષે લગભગ 37% થાય, જે કોઈ પણ બેંક વ્યાજ કરતાં ઘણું વધારે છે. રોકડ હોય ત્યારે વહેલું ભરો.'],
     ]],

    ['slug' => 'daily-cash-tally-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'wallet',
     'en' => 'Shop Daily Cash Tally Calculator (Galla Hisab)', 'gu' => 'દુકાન રોજનો ગલ્લા હિસાબ કેલ્ક્યુલેટર',
     'de' => 'Tally your shop\'s cash drawer at closing: expected cash from sales and expenses versus the cash actually counted.',
     'dg' => 'દુકાન બંધ કરતી વખતે ગલ્લાનો હિસાબ: વેચાણ અને ખર્ચ પરથી કેટલી રોકડ હોવી જોઈએ અને ખરેખર ગણેલી રોકડ સરખાવો.',
     'in' => [
       ['k' => 'open', 'en' => 'Opening cash', 'gu' => 'શરૂઆતની રોકડ', 'v' => 5000, 'u' => '₹'],
       ['k' => 'sale', 'en' => 'Cash sales today', 'gu' => 'આજનું રોકડ વેચાણ', 'v' => 25000, 'u' => '₹'],
       ['k' => 'recv', 'en' => 'Cash received from credit customers', 'gu' => 'ઉધારવાળા ગ્રાહકો પાસેથી આવેલી રોકડ', 'v' => 8000, 'u' => '₹'],
       ['k' => 'exp', 'en' => 'Shop expenses paid in cash', 'gu' => 'રોકડમાં ભરેલો દુકાન ખર્ચ', 'v' => 3000, 'u' => '₹'],
       ['k' => 'paid', 'en' => 'Paid to suppliers in cash', 'gu' => 'સપ્લાયરને રોકડ ચૂકવણી', 'v' => 5000, 'u' => '₹'],
       ['k' => 'bank', 'en' => 'Deposited in bank / taken out', 'gu' => 'બેંકમાં જમા / ઘરે લઈ ગયા', 'v' => 15000, 'u' => '₹'],
       ['k' => 'count', 'en' => 'Cash actually counted', 'gu' => 'ખરેખર ગણેલી રોકડ', 'v' => 14800, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Difference (counted − expected)', 'gu' => 'તફાવત (ગણેલી − હોવી જોઈએ)', 'f' => 'v.count - ' . $ct_exp, 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Expected closing cash', 'gu' => 'હોવી જોઈએ તેટલી રોકડ', 'f' => $ct_exp, 'fmt' => 'inr'],
       ['en' => 'Status', 'gu' => 'સ્થિતિ', 'f' => 'abs(v.count - ' . $ct_exp . ') < 0.5 ? T("હિસાબ બરાબર મળે છે ✔", "Cash tallies ✔") : (v.count > ' . $ct_exp . ' ? T("રોકડ વધારે છે – કોઈ એન્ટ્રી રહી ગઈ?", "Cash excess – an entry missed?") : T("રોકડ ઓછી છે – ખર્ચ કે ખોટો છૂટો તપાસો", "Cash short – check expenses or wrong change"))', 'fmt' => 'text'],
     ],
     'how_en' => 'Expected cash = opening + cash sales + collections − expenses − supplier payments − bank deposit. Difference = counted cash − expected cash.',
     'how_gu' => 'હોવી જોઈએ તેટલી રોકડ = શરૂઆતની + રોકડ વેચાણ + ઉઘરાણી − ખર્ચ − સપ્લાયર ચુકવણી − બેંક જમા. તફાવત = ગણેલી રોકડ − હોવી જોઈએ તેટલી રોકડ.',
     'faq' => [
       ['Should UPI payments be included?', 'No. Enter only cash. UPI and card sales go directly to the bank, so tally them separately with your bank or payment app statement.', 'UPI ચુકવણી ઉમેરવી?', 'ના. ફક્ત રોકડ દાખલ કરો. UPI અને કાર્ડનું વેચાણ સીધું બેંકમાં જાય છે, તેનો હિસાબ બેંક કે પેમેન્ટ એપના સ્ટેટમેન્ટથી અલગ મેળવો.'],
     ]],

    ['slug' => 'cash-denomination-counter', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'banknote',
     'en' => 'Cash Denomination Counter (Note Counting)', 'gu' => 'રોકડ નોટ ગણતરી કેલ્ક્યુલેટર',
     'de' => 'Count cash quickly by entering the number of ₹500, ₹200, ₹100 and smaller notes and coins to get the total.',
     'dg' => '₹500, ₹200, ₹100 અને નાની નોટો તથા સિક્કાની સંખ્યા દાખલ કરી કુલ રોકડ ઝડપથી ગણો.',
     'in' => [
       ['k' => 'n500', 'en' => '₹500 notes', 'gu' => '₹500 ની નોટ', 'v' => 20],
       ['k' => 'n200', 'en' => '₹200 notes', 'gu' => '₹200 ની નોટ', 'v' => 10],
       ['k' => 'n100', 'en' => '₹100 notes', 'gu' => '₹100 ની નોટ', 'v' => 25],
       ['k' => 'n50', 'en' => '₹50 notes', 'gu' => '₹50 ની નોટ', 'v' => 10],
       ['k' => 'n20', 'en' => '₹20 notes', 'gu' => '₹20 ની નોટ', 'v' => 15],
       ['k' => 'n10', 'en' => '₹10 notes', 'gu' => '₹10 ની નોટ', 'v' => 20],
       ['k' => 'coins', 'en' => 'Coins total', 'gu' => 'સિક્કાની કુલ રકમ', 'v' => 150, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Total cash', 'gu' => 'કુલ રોકડ', 'f' => $cd_tot, 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Number of notes', 'gu' => 'નોટોની સંખ્યા', 'f' => 'v.n500 + v.n200 + v.n100 + v.n50 + v.n20 + v.n10', 'fmt' => 'int'],
       ['en' => 'In ₹500 notes', 'gu' => '₹500 ની નોટમાં', 'f' => '500*v.n500', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'In ₹200 & ₹100 notes', 'gu' => '₹200 અને ₹100 ની નોટમાં', 'f' => '200*v.n200 + 100*v.n100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'In smaller notes & coins', 'gu' => 'નાની નોટ અને સિક્કામાં', 'f' => '50*v.n50 + 20*v.n20 + 10*v.n10 + v.coins', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Total = 500 × count + 200 × count + 100 × count + 50 × count + 20 × count + 10 × count + coins.',
     'how_gu' => 'કુલ = 500 × સંખ્યા + 200 × સંખ્યા + 100 × સંખ્યા + 50 × સંખ્યા + 20 × સંખ્યા + 10 × સંખ્યા + સિક્કા.',
     'faq' => [
       ['Why count by denomination?', 'Banks ask for a denomination-wise slip when you deposit cash, and counting this way catches mistakes in the day\'s cash.', 'નોટ મુજબ ગણતરી કેમ?', 'બેંકમાં રોકડ જમા કરતી વખતે નોટ મુજબ સ્લિપ ભરવી પડે છે, અને આ રીતે ગણવાથી દિવસની રોકડમાં ભૂલ પકડાય છે.'],
     ]],

    ['slug' => 'rent-increase-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'house-plus',
     'en' => 'Rent Increase Calculator', 'gu' => 'ભાડા વધારો કેલ્ક્યુલેટર',
     'de' => 'See how a house or shop rent grows with a yearly percentage increase, and the total rent paid over the years.',
     'dg' => 'ઘર કે દુકાનનું ભાડું દર વર્ષે નક્કી ટકા વધે તો કેટલું થશે અને વર્ષોમાં કુલ કેટલું ભાડું ભરાશે તે જુઓ.',
     'in' => [
       ['k' => 'rent', 'en' => 'Current monthly rent', 'gu' => 'હાલનું માસિક ભાડું', 'v' => 10000, 'u' => '₹'],
       ['k' => 'inc', 'en' => 'Increase every year', 'gu' => 'દર વર્ષે વધારો', 'v' => 5, 'u' => '%'],
       ['k' => 'y', 'en' => 'Number of years', 'gu' => 'વર્ષની સંખ્યા', 'v' => 5, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'out' => [
       ['en' => 'Monthly rent in the last year', 'gu' => 'છેલ્લા વર્ષનું માસિક ભાડું', 'f' => 'v.rent*pow(1 + v.inc/100, max(0, v.y - 1))', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total rent paid in all years', 'gu' => 'બધા વર્ષમાં કુલ ભાડું', 'f' => 'v.inc ? 12*v.rent*(pow(1 + v.inc/100, v.y) - 1)/(v.inc/100) : 12*v.rent*v.y', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Rent after the period', 'gu' => 'સમયગાળા પછીનું ભાડું', 'f' => 'v.rent*pow(1 + v.inc/100, v.y)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Rent in year k = current rent × (1 + increase)^(k − 1). Total = 12 × rent × ((1 + increase)^years − 1) ÷ increase.',
     'how_gu' => 'k મા વર્ષનું ભાડું = હાલનું ભાડું × (1 + વધારો)^(k − 1). કુલ = 12 × ભાડું × ((1 + વધારો)^વર્ષ − 1) ÷ વધારો.',
     'faq' => [
       ['How much rent increase is normal?', 'Most rent agreements add 5–10% at each renewal (often every 11 months). Write the agreed increase clearly in the agreement.', 'ભાડામાં કેટલો વધારો સામાન્ય છે?', 'મોટાભાગના ભાડા કરારમાં દરેક રિન્યુઅલે (ઘણી વાર દર 11 મહિને) 5–10% વધારો હોય. નક્કી થયેલો વધારો કરારમાં સ્પષ્ટ લખો.'],
     ]],

    ['slug' => 'rental-yield-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'building',
     'en' => 'Rental Yield Calculator (Property)', 'gu' => 'મિલકત ભાડા વળતર (રેન્ટલ યીલ્ડ) કેલ્ક્યુલેટર',
     'de' => 'Calculate gross and net rental yield of a flat or shop to see if buying it for rent income makes sense.',
     'dg' => 'ફ્લેટ કે દુકાન ભાડે આપવા ખરીદવી યોગ્ય છે કે નહીં તે જાણવા ગ્રોસ અને નેટ ભાડા વળતર ગણો.',
     'in' => [
       ['k' => 'price', 'en' => 'Property price (with registration)', 'gu' => 'મિલકતની કિંમત (દસ્તાવેજ સાથે)', 'v' => 5000000, 'u' => '₹'],
       ['k' => 'rent', 'en' => 'Monthly rent', 'gu' => 'માસિક ભાડું', 'v' => 15000, 'u' => '₹'],
       ['k' => 'exp', 'en' => 'Yearly costs (maintenance, tax, repairs)', 'gu' => 'વાર્ષિક ખર્ચ (મેન્ટેનન્સ, વેરો, રિપેર)', 'v' => 20000, 'u' => '₹'],
       ['k' => 'vac', 'en' => 'Vacant months per year', 'gu' => 'વર્ષમાં ખાલી રહેતા મહિના', 'v' => 1],
     ],
     'out' => [
       ['en' => 'Net rental yield', 'gu' => 'નેટ ભાડા વળતર', 'f' => 'v.price ? (v.rent*max(0, 12 - v.vac) - v.exp)/v.price*100 : 0', 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Gross rental yield', 'gu' => 'ગ્રોસ ભાડા વળતર', 'f' => 'v.price ? v.rent*12/v.price*100 : 0', 'fmt' => 'pct'],
       ['en' => 'Net rent per year', 'gu' => 'વાર્ષિક ચોખ્ખું ભાડું', 'f' => 'v.rent*max(0, 12 - v.vac) - v.exp', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Years of rent to recover price', 'gu' => 'કિંમત વસૂલ થતાં વર્ષ', 'f' => 'v.rent*max(0, 12 - v.vac) - v.exp > 0 ? v.price/(v.rent*max(0, 12 - v.vac) - v.exp) : 0', 'fmt' => 'num', 'd' => 1, 'u' => 'years', 'u_gu' => 'વર્ષ'],
     ],
     'how_en' => 'Gross yield = yearly rent ÷ price × 100. Net yield = (rent for occupied months − yearly costs) ÷ price × 100.',
     'how_gu' => 'ગ્રોસ વળતર = વાર્ષિક ભાડું ÷ કિંમત × 100. નેટ વળતર = (ભાડે રહેલા મહિનાનું ભાડું − વાર્ષિક ખર્ચ) ÷ કિંમત × 100.',
     'faq' => [
       ['What is a good rental yield in India?', 'Residential flats usually give only 2–4% gross; shops and offices often 5–8%. Compare with FD rates and expected price growth.', 'ભારતમાં સારું ભાડા વળતર કેટલું?', 'રહેણાંક ફ્લેટ સામાન્ય રીતે ફક્ત 2–4% ગ્રોસ આપે; દુકાન અને ઓફિસ ઘણી વાર 5–8%. FD દર અને ભાવ વધારાની અપેક્ષા સાથે સરખાવો.'],
     ]],

    /* ================= SHARES ================= */

    ['slug' => 'share-brokerage-charges-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-line',
     'en' => 'Share Brokerage & Charges Calculator (Delivery)', 'gu' => 'શેર બ્રોકરેજ અને ચાર્જ કેલ્ક્યુલેટર (ડિલિવરી)',
     'de' => 'Calculate brokerage, STT, exchange fees, GST, stamp duty and net profit on a share buy and sell, with all rates editable.',
     'dg' => 'શેર ખરીદ-વેચાણ પર બ્રોકરેજ, STT, એક્સચેન્જ ચાર્જ, GST, સ્ટેમ્પ ડ્યુટી અને ચોખ્ખો નફો ગણો; બધા દર બદલી શકાય.',
     'in' => [
       ['k' => 'buy', 'en' => 'Buy price', 'gu' => 'ખરીદ ભાવ', 'v' => 500, 'u' => '₹'],
       ['k' => 'sell', 'en' => 'Sell price', 'gu' => 'વેચાણ ભાવ', 'v' => 550, 'u' => '₹'],
       ['k' => 'q', 'en' => 'Quantity', 'gu' => 'જથ્થો', 'v' => 100],
       ['k' => 'bt', 'en' => 'Brokerage type', 'gu' => 'બ્રોકરેજ પ્રકાર', 'v' => 1, 'opts' => [[1, 'Flat ₹ per order', 'દરેક ઓર્ડર પર ફિક્સ ₹'], [2, '% of turnover', 'ટર્નઓવરના %']]],
       ['k' => 'bv', 'en' => 'Brokerage (₹ per order or %)', 'gu' => 'બ્રોકરેજ (ઓર્ડર દીઠ ₹ અથવા %)', 'v' => 20],
       ['k' => 'stt', 'en' => 'STT (on buy + sell)', 'gu' => 'STT (ખરીદ + વેચાણ પર)', 'v' => 0.1, 'u' => '%'],
       ['k' => 'ex', 'en' => 'Exchange transaction charge', 'gu' => 'એક્સચેન્જ ટ્રાન્ઝેક્શન ચાર્જ', 'v' => 0.00297, 'u' => '%'],
       ['k' => 'sebi', 'en' => 'SEBI fee per crore', 'gu' => 'SEBI ફી (પ્રતિ કરોડ)', 'v' => 10, 'u' => '₹'],
       ['k' => 'stamp', 'en' => 'Stamp duty (on buy)', 'gu' => 'સ્ટેમ્પ ડ્યુટી (ખરીદ પર)', 'v' => 0.015, 'u' => '%'],
       ['k' => 'gst', 'en' => 'GST on brokerage & fees', 'gu' => 'બ્રોકરેજ અને ફી પર GST', 'v' => 18, 'u' => '%'],
       ['k' => 'dp', 'en' => 'DP charge on sell', 'gu' => 'વેચાણ પર DP ચાર્જ', 'v' => 15, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Net profit after charges', 'gu' => 'ચાર્જ પછી ચોખ્ખો નફો', 'f' => '(v.sell - v.buy)*v.q - ' . $br_total, 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Total charges', 'gu' => 'કુલ ચાર્જ', 'f' => $br_total, 'fmt' => 'inr'],
       ['en' => 'Brokerage', 'gu' => 'બ્રોકરેજ', 'f' => $br_brok, 'fmt' => 'inr'],
       ['en' => 'STT', 'gu' => 'STT', 'f' => $br_T . '*v.stt/100', 'fmt' => 'inr'],
       ['en' => 'GST', 'gu' => 'GST', 'f' => '(' . $br_brok . ' + ' . $br_ex . ' + ' . $br_sebi . ')*v.gst/100', 'fmt' => 'inr'],
       ['en' => 'Break-even sell price', 'gu' => 'નફો-નુકસાન વગરનો વેચાણ ભાવ', 'f' => 'v.q ? v.buy + ' . $br_total . '/v.q : 0', 'fmt' => 'inr'],
     ],
     'how_en' => 'Turnover = (buy + sell) × qty. Charges = brokerage + STT% × turnover + exchange% × turnover + SEBI fee + GST on (brokerage + exchange + SEBI) + stamp duty% × buy value + DP charge. Net profit = (sell − buy) × qty − charges.',
     'how_gu' => 'ટર્નઓવર = (ખરીદ + વેચાણ) × જથ્થો. ચાર્જ = બ્રોકરેજ + STT% × ટર્નઓવર + એક્સચેન્જ% × ટર્નઓવર + SEBI ફી + (બ્રોકરેજ + એક્સચેન્જ + SEBI) પર GST + સ્ટેમ્પ% × ખરીદ રકમ + DP ચાર્જ. ચોખ્ખો નફો = (વેચાણ − ખરીદ) × જથ્થો − ચાર્જ.',
     'faq' => [
       ['Are these charge rates fixed?', 'No. STT, exchange fees, stamp duty and broker plans change from time to time. Check your broker\'s contract note and update the rates here.', 'શું આ ચાર્જ દર કાયમી છે?', 'ના. STT, એક્સચેન્જ ફી, સ્ટેમ્પ ડ્યુટી અને બ્રોકરના પ્લાન સમયાંતરે બદલાય. તમારા બ્રોકરની કોન્ટ્રાક્ટ નોટ જોઈ અહીં દર સુધારો.'],
     ]],

    ['slug' => 'stock-average-price-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'chart-column',
     'en' => 'Stock Average Price Calculator', 'gu' => 'શેર સરેરાશ ભાવ કેલ્ક્યુલેટર',
     'de' => 'Find the average buying price of shares bought at different prices and your profit or loss at the current price.',
     'dg' => 'અલગ અલગ ભાવે ખરીદેલા શેરનો સરેરાશ ખરીદ ભાવ અને હાલના ભાવે નફો કે નુકસાન શોધો.',
     'in' => [
       ['k' => 'q1', 'en' => 'Buy 1 quantity', 'gu' => 'ખરીદી 1 જથ્થો', 'v' => 50],
       ['k' => 'p1', 'en' => 'Buy 1 price', 'gu' => 'ખરીદી 1 ભાવ', 'v' => 600, 'u' => '₹'],
       ['k' => 'q2', 'en' => 'Buy 2 quantity', 'gu' => 'ખરીદી 2 જથ્થો', 'v' => 30],
       ['k' => 'p2', 'en' => 'Buy 2 price', 'gu' => 'ખરીદી 2 ભાવ', 'v' => 540, 'u' => '₹'],
       ['k' => 'q3', 'en' => 'Buy 3 quantity', 'gu' => 'ખરીદી 3 જથ્થો', 'v' => 20],
       ['k' => 'p3', 'en' => 'Buy 3 price', 'gu' => 'ખરીદી 3 ભાવ', 'v' => 500, 'u' => '₹'],
       ['k' => 'q4', 'en' => 'Buy 4 quantity', 'gu' => 'ખરીદી 4 જથ્થો', 'v' => 0],
       ['k' => 'p4', 'en' => 'Buy 4 price', 'gu' => 'ખરીદી 4 ભાવ', 'v' => 0, 'u' => '₹'],
       ['k' => 'cmp', 'en' => 'Current market price', 'gu' => 'હાલનો બજાર ભાવ', 'v' => 560, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Average buy price', 'gu' => 'સરેરાશ ખરીદ ભાવ', 'f' => $sa_q . ' ? ' . $sa_c . '/' . $sa_q . ' : 0', 'fmt' => 'inr', 'big' => 1],
       ['en' => 'Total shares', 'gu' => 'કુલ શેર', 'f' => $sa_q, 'fmt' => 'int'],
       ['en' => 'Total invested', 'gu' => 'કુલ રોકાણ', 'f' => $sa_c, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Profit / loss now', 'gu' => 'હાલ નફો / નુકસાન', 'f' => 'v.cmp*' . $sa_q . ' - ' . $sa_c, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Profit / loss %', 'gu' => 'નફો / નુકસાન %', 'f' => $sa_c . ' ? (v.cmp*' . $sa_q . ' - ' . $sa_c . ')/' . $sa_c . '*100 : 0', 'fmt' => 'pct'],
     ],
     'how_en' => 'Average price = Σ(quantity × price) ÷ Σ quantity. Profit/loss = current price × total shares − total invested.',
     'how_gu' => 'સરેરાશ ભાવ = Σ(જથ્થો × ભાવ) ÷ Σ જથ્થો. નફો/નુકસાન = હાલનો ભાવ × કુલ શેર − કુલ રોકાણ.',
     'faq' => [
       ['Is averaging down a good idea?', 'Only if the company is still fundamentally strong. Buying more of a falling share just to lower the average can increase losses.', 'ભાવ ઘટે ત્યારે એવરેજ કરવું સારું?', 'કંપની હજુ મજબૂત હોય તો જ. ફક્ત સરેરાશ ઘટાડવા ઘટતો શેર વધુ ખરીદવાથી નુકસાન વધી શકે.'],
     ]],

    ['slug' => 'dividend-yield-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'hand-coins',
     'en' => 'Dividend Yield Calculator', 'gu' => 'ડિવિડન્ડ યીલ્ડ કેલ્ક્યુલેટર',
     'de' => 'Find a share\'s dividend yield, your yearly dividend income and the payout ratio from dividend, price and EPS.',
     'dg' => 'ડિવિડન્ડ, ભાવ અને EPS પરથી શેરનું ડિવિડન્ડ યીલ્ડ, તમારી વાર્ષિક ડિવિડન્ડ આવક અને પેઆઉટ રેશિયો શોધો.',
     'in' => [
       ['k' => 'dps', 'en' => 'Yearly dividend per share', 'gu' => 'પ્રતિ શેર વાર્ષિક ડિવિડન્ડ', 'v' => 15, 'u' => '₹'],
       ['k' => 'price', 'en' => 'Share price', 'gu' => 'શેરનો ભાવ', 'v' => 600, 'u' => '₹'],
       ['k' => 'sh', 'en' => 'Shares you hold', 'gu' => 'તમારી પાસેના શેર', 'v' => 100],
       ['k' => 'eps', 'en' => 'Earnings per share (EPS)', 'gu' => 'પ્રતિ શેર કમાણી (EPS)', 'v' => 40, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Dividend yield', 'gu' => 'ડિવિડન્ડ યીલ્ડ', 'f' => 'v.price ? v.dps/v.price*100 : 0', 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Your yearly dividend income', 'gu' => 'તમારી વાર્ષિક ડિવિડન્ડ આવક', 'f' => 'v.dps*v.sh', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Payout ratio', 'gu' => 'પેઆઉટ રેશિયો', 'f' => 'v.eps ? v.dps/v.eps*100 : 0', 'fmt' => 'pct'],
     ],
     'how_en' => 'Dividend yield = yearly dividend per share ÷ share price × 100. Payout ratio = dividend per share ÷ EPS × 100.',
     'how_gu' => 'ડિવિડન્ડ યીલ્ડ = પ્રતિ શેર વાર્ષિક ડિવિડન્ડ ÷ શેરનો ભાવ × 100. પેઆઉટ રેશિયો = પ્રતિ શેર ડિવિડન્ડ ÷ EPS × 100.',
     'faq' => [
       ['Is dividend income taxable?', 'Yes, dividends are taxed in your hands at your slab rate and companies may deduct TDS above a limit. Check current rules.', 'ડિવિડન્ડ આવક પર ટેક્સ લાગે?', 'હા, ડિવિડન્ડ તમારા સ્લેબ દરે કરપાત્ર છે અને મર્યાદાથી વધુ પર કંપની TDS કાપી શકે. હાલના નિયમ તપાસો.'],
     ]],

    ['slug' => 'pe-ratio-calculator', 'cat' => 'finance', 'type' => 'calc', 'icon' => 'calculator',
     'en' => 'P/E Ratio & EPS Calculator', 'gu' => 'P/E રેશિયો અને EPS કેલ્ક્યુલેટર',
     'de' => 'Calculate a company\'s EPS, price-to-earnings (P/E) ratio, earnings yield and market cap from profit, shares and price.',
     'dg' => 'નફો, શેરની સંખ્યા અને ભાવ પરથી કંપનીનો EPS, P/E રેશિયો, અર્નિંગ યીલ્ડ અને માર્કેટ કેપ ગણો.',
     'in' => [
       ['k' => 'price', 'en' => 'Share price', 'gu' => 'શેરનો ભાવ', 'v' => 1500, 'u' => '₹'],
       ['k' => 'np', 'en' => 'Yearly net profit', 'gu' => 'વાર્ષિક ચોખ્ખો નફો', 'v' => 2000, 'u' => '₹ crore', 'u_gu' => '₹ કરોડ'],
       ['k' => 'sh', 'en' => 'Total shares', 'gu' => 'કુલ શેર', 'v' => 50, 'u' => 'crore', 'u_gu' => 'કરોડ'],
     ],
     'out' => [
       ['en' => 'P/E ratio', 'gu' => 'P/E રેશિયો', 'f' => 'v.np && v.sh ? v.price/(v.np/v.sh) : 0', 'fmt' => 'num', 'big' => 1],
       ['en' => 'EPS (earnings per share)', 'gu' => 'EPS (પ્રતિ શેર કમાણી)', 'f' => 'v.sh ? v.np/v.sh : 0', 'fmt' => 'inr'],
       ['en' => 'Earnings yield', 'gu' => 'અર્નિંગ યીલ્ડ', 'f' => 'v.price && v.sh ? v.np/v.sh/v.price*100 : 0', 'fmt' => 'pct'],
       ['en' => 'Market capitalisation', 'gu' => 'માર્કેટ કેપ', 'f' => 'v.price*v.sh', 'fmt' => 'num', 'd' => 0, 'u' => '₹ crore', 'u_gu' => '₹ કરોડ'],
     ],
     'how_en' => 'EPS = net profit ÷ number of shares. P/E = share price ÷ EPS. Earnings yield = EPS ÷ price × 100. Market cap = price × shares.',
     'how_gu' => 'EPS = ચોખ્ખો નફો ÷ શેરની સંખ્યા. P/E = શેરનો ભાવ ÷ EPS. અર્નિંગ યીલ્ડ = EPS ÷ ભાવ × 100. માર્કેટ કેપ = ભાવ × શેર.',
     'faq' => [
       ['What is a good P/E?', 'There is no single number. Compare a company\'s P/E with others in the same industry and with its own past; a high P/E means the market expects fast growth.', 'સારો P/E કેટલો?', 'કોઈ એક આંકડો નથી. કંપનીનો P/E તે જ ઉદ્યોગની બીજી કંપનીઓ અને તેના પોતાના ઇતિહાસ સાથે સરખાવો; ઊંચો P/E એટલે બજાર ઝડપી વૃદ્ધિની આશા રાખે છે.'],
     ]],

    /* ================= MESSAGES ================= */

    ['slug' => 'payment-reminder-message', 'cat' => 'finance', 'type' => 'text', 'icon' => 'bell',
     'en' => 'Payment Reminder Message for Customers', 'gu' => 'ગ્રાહક માટે બાકી ચુકવણી યાદ સંદેશ',
     'de' => 'Polite ready-made WhatsApp and SMS messages for shopkeepers and businesses to remind customers about pending dues.',
     'dg' => 'દુકાનદાર અને વેપારીઓ માટે ગ્રાહકને બાકી રકમ યાદ કરાવવા વિવેકી, તૈયાર WhatsApp અને SMS સંદેશ.',
     'in' => [
       ['k' => 'name', 'en' => 'Customer name', 'gu' => 'ગ્રાહકનું નામ', 'v' => 'Rameshbhai', 't' => 'text'],
       ['k' => 'amt', 'en' => 'Pending amount (₹)', 'gu' => 'બાકી રકમ (₹)', 'v' => '12,500', 't' => 'text'],
       ['k' => 'bill', 'en' => 'Bill no. / details', 'gu' => 'બિલ નં. / વિગત', 'v' => '245', 't' => 'text'],
       ['k' => 'due', 'en' => 'Due date', 'gu' => 'નિયત તારીખ', 'v' => '30 October', 't' => 'text'],
       ['k' => 'shop', 'en' => 'Your shop / business name', 'gu' => 'તમારી દુકાન / પેઢીનું નામ', 'v' => 'Krishna Traders', 't' => 'text'],
       ['k' => 'upi', 'en' => 'UPI ID / phone for payment', 'gu' => 'ચુકવણી માટે UPI ID / ફોન', 'v' => '98XXXXXXXX@upi', 't' => 'text'],
     ],
     'tpl_en' => [
       "Dear {name},\nThis is a friendly reminder that ₹{amt} against bill no. {bill} is due on {due}. You can pay by UPI: {upi}.\nThank you,\n{shop}",
       "Namaste {name} 🙏\nYour payment of ₹{amt} (bill {bill}) is pending. Kindly clear it by {due}.\nUPI: {upi}\n– {shop}",
       "Hello {name}, hope you are well. Just a gentle reminder about the pending amount of ₹{amt} for bill {bill}. Please pay at your convenience before {due}. – {shop}",
       "Dear {name}, our records show ₹{amt} outstanding for bill {bill}, which was due on {due}. Please make the payment at the earliest. If already paid, kindly share the receipt and ignore this message. – {shop}",
       "Reminder: ₹{amt} pending | Bill {bill} | Due {due}\nPay via UPI: {upi}\nThank you for your business! – {shop}",
       "Dear {name}, this is our final reminder regarding ₹{amt} pending since {due} (bill {bill}). Please clear the dues within 3 days to avoid any inconvenience. – {shop}",
     ],
     'tpl_gu' => [
       "આદરણીય {name},\nબિલ નં. {bill} ના ₹{amt} ની ચુકવણી {due} સુધીમાં કરવાની છે, તે યાદ અપાવવા આ સંદેશ છે. UPI થી ચૂકવી શકો: {upi}\nઆભાર,\n{shop}",
       "જય શ્રી કૃષ્ણ {name} 🙏\nઆપના ₹{amt} (બિલ {bill}) બાકી છે. મહેરબાની કરી {due} સુધીમાં ચૂકવી દેશો.\nUPI: {upi}\n– {shop}",
       "નમસ્તે {name}, આશા છે આપ મજામાં હશો. બિલ {bill} ના ₹{amt} બાકી છે, આપની અનુકૂળતાએ {due} પહેલાં ભરી દેશો તો આભારી રહીશું. – {shop}",
       "{name}, અમારા ચોપડે બિલ {bill} ના ₹{amt} બાકી બતાવે છે, જેની તારીખ {due} હતી. વહેલી તકે ચુકવણી કરવા વિનંતી. જો ભરી દીધા હોય તો રસીદ મોકલશો અને આ સંદેશ અવગણશો. – {shop}",
       "યાદી: ₹{amt} બાકી | બિલ {bill} | તારીખ {due}\nUPI થી ચૂકવો: {upi}\nઆપના સહકાર બદલ આભાર! – {shop}",
       "{name}, {due} થી બાકી ₹{amt} (બિલ {bill}) અંગે આ છેલ્લી યાદી છે. કૃપા કરી 3 દિવસમાં ચુકવણી કરી દેશો, જેથી કોઈ તકલીફ ન થાય. – {shop}",
     ],
     'faq' => [
       ['How often should I send a payment reminder?', 'Send a polite reminder a few days before the due date, one on the due date and a firmer one a week later. Always keep the tone respectful.', 'કેટલી વાર યાદ સંદેશ મોકલવો?', 'તારીખના થોડા દિવસ પહેલાં એક વિવેકી સંદેશ, તારીખે એક અને એક અઠવાડિયા પછી થોડો કડક સંદેશ. ભાષા હંમેશા માનભરી રાખો.'],
     ]],

    ['slug' => 'loan-emi-reminder-message', 'cat' => 'finance', 'type' => 'text', 'icon' => 'message-square',
     'en' => 'Loan EMI Reminder Message', 'gu' => 'લોન EMI યાદ સંદેશ',
     'de' => 'Ready EMI due-date reminder messages for finance companies, credit societies and lenders to send to borrowers.',
     'dg' => 'ફાઇનાન્સ કંપની, ક્રેડિટ સોસાયટી અને ધિરાણકર્તા માટે લોન લેનારને મોકલવાના EMI તારીખના તૈયાર યાદ સંદેશ.',
     'in' => [
       ['k' => 'name', 'en' => 'Borrower name', 'gu' => 'લોન લેનારનું નામ', 'v' => 'Sureshbhai', 't' => 'text'],
       ['k' => 'emi', 'en' => 'EMI amount (₹)', 'gu' => 'EMI રકમ (₹)', 'v' => '4,500', 't' => 'text'],
       ['k' => 'loan', 'en' => 'Loan / account no.', 'gu' => 'લોન / ખાતા નં.', 'v' => 'GL-1023', 't' => 'text'],
       ['k' => 'due', 'en' => 'EMI due date', 'gu' => 'EMI તારીખ', 'v' => '5 November', 't' => 'text'],
       ['k' => 'no', 'en' => 'Instalment number', 'gu' => 'હપ્તા નંબર', 'v' => '7 of 24', 't' => 'text'],
       ['k' => 'org', 'en' => 'Your organisation name', 'gu' => 'તમારી સંસ્થાનું નામ', 'v' => 'Shree Credit Co-op Society', 't' => 'text'],
     ],
     'tpl_en' => [
       "Dear {name}, your EMI of ₹{emi} for loan {loan} (instalment {no}) is due on {due}. Please keep sufficient balance or pay at our office. – {org}",
       "Reminder 🔔 EMI ₹{emi} | Loan {loan} | Due {due}\nTimely payment keeps your credit record clean. – {org}",
       "Namaste {name}, a gentle reminder that instalment {no} of ₹{emi} on loan {loan} falls due on {due}. Thank you for paying on time. – {org}",
       "Dear {name}, we have not yet received your EMI of ₹{emi} due on {due} for loan {loan}. Please pay immediately to avoid late charges. If already paid, please ignore. – {org}",
       "Dear {name}, your EMI {no} of ₹{emi} (loan {loan}) is overdue. Kindly contact us today to clear it and avoid penalty and impact on your credit score. – {org}",
     ],
     'tpl_gu' => [
       "{name}, આપની લોન {loan} નો ₹{emi} નો હપ્તો ({no}) {due} ના રોજ ભરવાનો છે. ખાતામાં પૂરતું બેલેન્સ રાખશો અથવા ઓફિસે ભરી જશો. – {org}",
       "યાદી 🔔 હપ્તો ₹{emi} | લોન {loan} | તારીખ {due}\nસમયસર હપ્તો ભરવાથી આપનો ક્રેડિટ રેકોર્ડ સારો રહે છે. – {org}",
       "નમસ્તે {name}, લોન {loan} નો હપ્તો નં. {no}, ₹{emi}, {due} ના રોજ ભરવાનો છે તેની યાદી. સમયસર ચુકવણી બદલ આભાર. – {org}",
       "{name}, લોન {loan} નો {due} નો ₹{emi} નો હપ્તો હજુ મળ્યો નથી. લેટ ચાર્જથી બચવા તરત ભરી દેશો. જો ભરી દીધો હોય તો આ સંદેશ અવગણશો. – {org}",
       "{name}, લોન {loan} નો હપ્તો {no} (₹{emi}) મુદત વીતી ગયો છે. દંડ અને ક્રેડિટ સ્કોર પર અસરથી બચવા આજે જ અમારો સંપર્ક કરશો. – {org}",
     ],
     'faq' => [
       ['What should an EMI reminder contain?', 'Borrower name, loan number, EMI amount, due date and how to pay. Never ask for OTP or passwords in a reminder message.', 'EMI યાદ સંદેશમાં શું હોવું જોઈએ?', 'લોન લેનારનું નામ, લોન નંબર, હપ્તાની રકમ, તારીખ અને કેવી રીતે ભરવું. યાદ સંદેશમાં ક્યારેય OTP કે પાસવર્ડ ન માંગો.'],
     ]],

    ['slug' => 'payment-received-thank-you-message', 'cat' => 'finance', 'type' => 'text', 'icon' => 'message-circle',
     'en' => 'Payment Received Confirmation Message', 'gu' => 'ચુકવણી મળ્યાની પુષ્ટિ સંદેશ',
     'de' => 'Thank-you messages for shops and businesses to confirm a customer\'s payment received by cash, UPI or cheque.',
     'dg' => 'રોકડ, UPI કે ચેકથી ગ્રાહકની ચુકવણી મળ્યાની પુષ્ટિ કરવા દુકાન અને પેઢી માટે આભાર સંદેશ.',
     'in' => [
       ['k' => 'name', 'en' => 'Customer name', 'gu' => 'ગ્રાહકનું નામ', 'v' => 'Rameshbhai', 't' => 'text'],
       ['k' => 'amt', 'en' => 'Amount received (₹)', 'gu' => 'મળેલી રકમ (₹)', 'v' => '12,500', 't' => 'text'],
       ['k' => 'mode', 'en' => 'Payment mode', 'gu' => 'ચુકવણીની રીત', 'v' => 'UPI', 't' => 'text'],
       ['k' => 'bill', 'en' => 'Against bill no.', 'gu' => 'બિલ નં.', 'v' => '245', 't' => 'text'],
       ['k' => 'bal', 'en' => 'Balance still due (₹)', 'gu' => 'હજુ બાકી રકમ (₹)', 'v' => '0', 't' => 'text'],
       ['k' => 'shop', 'en' => 'Your shop / business name', 'gu' => 'તમારી દુકાન / પેઢીનું નામ', 'v' => 'Krishna Traders', 't' => 'text'],
     ],
     'tpl_en' => [
       "Dear {name}, we have received ₹{amt} by {mode} against bill {bill}. Thank you! Balance due: ₹{bal}. – {shop}",
       "Payment received ✅\nAmount: ₹{amt}\nMode: {mode}\nBill: {bill}\nBalance: ₹{bal}\nThank you for your trust. – {shop}",
       "Thank you {name} 🙏 Your payment of ₹{amt} has been credited to your account with us. We look forward to serving you again. – {shop}",
       "Hello {name}, this confirms receipt of ₹{amt} ({mode}) for bill {bill}. Please keep this message for your records. – {shop}",
       "Dear {name}, thanks for the prompt payment of ₹{amt}! Your account is updated and the remaining balance is ₹{bal}. – {shop}",
     ],
     'tpl_gu' => [
       "{name}, બિલ {bill} પેટે {mode} થી ₹{amt} મળી ગયા છે. આભાર! હવે બાકી: ₹{bal}. – {shop}",
       "ચુકવણી મળી ✅\nરકમ: ₹{amt}\nરીત: {mode}\nબિલ: {bill}\nબાકી: ₹{bal}\nઆપના વિશ્વાસ બદલ આભાર. – {shop}",
       "આભાર {name} 🙏 આપના ₹{amt} અમારા ચોપડે આપના ખાતે જમા થઈ ગયા છે. ફરી સેવાની તક આપશો. – {shop}",
       "નમસ્તે {name}, બિલ {bill} માટે {mode} થી ₹{amt} મળ્યાની આ પુષ્ટિ છે. આપના રેકોર્ડ માટે આ સંદેશ સાચવી રાખશો. – {shop}",
       "{name}, ₹{amt} ની સમયસર ચુકવણી બદલ દિલથી આભાર! આપનું ખાતું સુધારી દીધું છે, બાકી રકમ ₹{bal}. – {shop}",
     ]],

    /* ================= DOCUMENTS ================= */

    ['slug' => 'cash-receipt-voucher-maker', 'cat' => 'finance', 'type' => 'doc', 'icon' => 'receipt',
     'en' => 'Cash Receipt Voucher Maker', 'gu' => 'રોકડ પહોંચ (રસીદ વાઉચર) મેકર',
     'de' => 'Make a printable money receipt for payments your shop or business receives from customers by cash, UPI or cheque.',
     'dg' => 'દુકાન કે પેઢીને ગ્રાહક પાસેથી રોકડ, UPI કે ચેકથી મળેલી રકમની છાપી શકાય તેવી પહોંચ બનાવો.',
     'in' => [
       ['k' => 'no', 'en' => 'Receipt no.', 'gu' => 'પહોંચ નં.', 'v' => 'R-101', 't' => 'text'],
       ['k' => 'date', 'en' => 'Date', 'gu' => 'તારીખ', 't' => 'date'],
       ['k' => 'from', 'en' => 'Received from', 'gu' => 'કોની પાસેથી', 'v' => 'Rameshbhai Patel', 't' => 'text'],
       ['k' => 'amt', 'en' => 'Amount (₹)', 'gu' => 'રકમ (₹)', 'v' => '12,500', 't' => 'text'],
       ['k' => 'words', 'en' => 'Amount in words', 'gu' => 'રકમ શબ્દોમાં', 'v' => 'Twelve thousand five hundred only', 't' => 'text'],
       ['k' => 'mode', 'en' => 'Payment mode / cheque no.', 'gu' => 'ચુકવણીની રીત / ચેક નં.', 'v' => 'Cash', 't' => 'text'],
       ['k' => 'for', 'en' => 'Towards (purpose / bill no.)', 'gu' => 'શેના પેટે (હેતુ / બિલ નં.)', 'v' => 'Bill no. 245', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Cash Receipt', 'title_gu' => 'પહોંચ',
       'body_en' => "Received with thanks from {from} the sum of ₹{amt} (Rupees {words}) by {mode} towards {for}.",
       'body_gu' => "{from} પાસેથી {for} પેટે {mode} દ્વારા ₹{amt} (અંકે રૂપિયા {words}) આભાર સહ મળ્યા છે.",
       'foot_en' => 'Cheque payments are subject to realisation.', 'foot_gu' => 'ચેક દ્વારા ચુકવણી ચેક પાસ થયા પછી માન્ય ગણાશે.', 'sign' => true],
     'faq' => [
       ['Can I use this as a rent receipt?', 'No. This is for a business recording money it actually received from its customers. Rent receipts must be issued by the landlord.', 'શું આ ભાડા રસીદ તરીકે વાપરી શકાય?', 'ના. આ પેઢીને ગ્રાહક પાસેથી ખરેખર મળેલી રકમની નોંધ માટે છે. ભાડા રસીદ મકાનમાલિકે જ આપવી જોઈએ.'],
     ]],

    ['slug' => 'cash-payment-voucher-maker', 'cat' => 'finance', 'type' => 'doc', 'icon' => 'file-text',
     'en' => 'Cash Payment Voucher (Expense Voucher) Maker', 'gu' => 'રોકડ ચુકવણી વાઉચર (ખર્ચ વાઉચર) મેકર',
     'de' => 'Create a printable payment voucher to record cash or UPI paid by your business for expenses, with the payee\'s signature.',
     'dg' => 'પેઢીએ ખર્ચ માટે રોકડ કે UPI થી ચૂકવેલી રકમની નોંધ માટે, લેનારની સહી સાથે છાપી શકાય તેવું ચુકવણી વાઉચર બનાવો.',
     'in' => [
       ['k' => 'no', 'en' => 'Voucher no.', 'gu' => 'વાઉચર નં.', 'v' => 'PV-56', 't' => 'text'],
       ['k' => 'date', 'en' => 'Date', 'gu' => 'તારીખ', 't' => 'date'],
       ['k' => 'to', 'en' => 'Paid to', 'gu' => 'કોને ચૂકવ્યા', 'v' => 'Mahesh Transport', 't' => 'text'],
       ['k' => 'head', 'en' => 'Expense head', 'gu' => 'ખર્ચનું ખાતું', 'v' => 'Freight / Transport', 't' => 'text'],
       ['k' => 'mode', 'en' => 'Payment mode', 'gu' => 'ચુકવણીની રીત', 'v' => 'Cash', 't' => 'text'],
       ['k' => 'words', 'en' => 'Total in words', 'gu' => 'કુલ રકમ શબ્દોમાં', 'v' => 'One thousand eight hundred only', 't' => 'text'],
       ['k' => 'by', 'en' => 'Approved by', 'gu' => 'મંજૂર કરનાર', 'v' => 'Owner', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Payment Voucher', 'title_gu' => 'ચુકવણી વાઉચર',
       'items' => ['desc_en' => 'Particulars', 'desc_gu' => 'વિગત', 'qty_en' => 'Qty', 'qty_gu' => 'જથ્થો', 'rate_en' => 'Amount', 'rate_gu' => 'રકમ',
         'rows' => [['Transport charges – 2 trips', 2, 750, 'ટ્રાન્સપોર્ટ ભાડું – 2 ફેરા'], ['Loading / unloading', 1, 300, 'ચઢાવ-ઉતાર મજૂરી']]],
       'foot_en' => 'Receiver\'s signature: ____________', 'foot_gu' => 'લેનારની સહી: ____________', 'sign' => true]],

    ['slug' => 'payment-reminder-letter-maker', 'cat' => 'finance', 'type' => 'doc', 'icon' => 'file-pen',
     'en' => 'Payment Reminder Letter Maker', 'gu' => 'બાકી ચુકવણી યાદી પત્ર મેકર',
     'de' => 'Make a formal printable letter to remind a customer or dealer about outstanding payment against your bills.',
     'dg' => 'ગ્રાહક કે ડીલરને આપના બિલની બાકી રકમ યાદ કરાવવા માટે ઔપચારિક, છાપી શકાય તેવો પત્ર બનાવો.',
     'in' => [
       ['k' => 'date', 'en' => 'Date', 'gu' => 'તારીખ', 't' => 'date'],
       ['k' => 'to', 'en' => 'To (customer name & address)', 'gu' => 'પ્રતિ (ગ્રાહકનું નામ અને સરનામું)', 'v' => 'M/s Shiv Hardware, Main Bazaar', 't' => 'text'],
       ['k' => 'bills', 'en' => 'Bill numbers & dates', 'gu' => 'બિલ નંબર અને તારીખ', 'v' => 'No. 245 (05-08-2026), No. 268 (20-08-2026)', 't' => 'text'],
       ['k' => 'amt', 'en' => 'Total outstanding (₹)', 'gu' => 'કુલ બાકી રકમ (₹)', 'v' => '48,600', 't' => 'text'],
       ['k' => 'by', 'en' => 'Pay by date', 'gu' => 'ચૂકવવાની છેલ્લી તારીખ', 'v' => '15-11-2026', 't' => 'text'],
       ['k' => 'bank', 'en' => 'Bank / UPI details', 'gu' => 'બેંક / UPI વિગત', 'v' => 'A/c 1234567890, IFSC XXXX0001234', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Payment Reminder', 'title_gu' => 'બાકી ચુકવણી યાદી',
       'body_en' => "To,\n{to}\n\nSubject: Reminder for outstanding payment of ₹{amt}\n\nDear Sir/Madam,\n\nAs per our records, an amount of ₹{amt} is outstanding against our bill(s) {bills}. We request you to kindly clear the payment on or before {by}.\n\nPayment can be made to: {bank}.\n\nIf you have already made the payment, please share the details so that we can update our records, and ignore this letter.\n\nWe value our business relationship and thank you for your prompt co-operation.",
       'body_gu' => "પ્રતિ,\n{to}\n\nવિષય: ₹{amt} ની બાકી ચુકવણી અંગે યાદી\n\nમાનનીય સાહેબશ્રી,\n\nઅમારા ચોપડા મુજબ અમારા બિલ {bills} પેટે ₹{amt} આપની પાસે બાકી છે. આ રકમ {by} સુધીમાં ચૂકવી આપવા નમ્ર વિનંતી છે.\n\nચુકવણી માટે વિગત: {bank}.\n\nજો આપે ચુકવણી કરી દીધી હોય તો તેની વિગત મોકલશો જેથી અમે હિસાબ સુધારી શકીએ, અને આ પત્ર અવગણશો.\n\nઆપની સાથેના વ્યાપારી સંબંધની અમે કદર કરીએ છીએ. આપના ઝડપી સહકાર બદલ આભાર.",
       'sign' => true],
     'faq' => [
       ['When should I send a formal reminder letter?', 'After WhatsApp reminders fail, or when the dues are large or old. Keep a copy, as it helps if you later need to send a legal notice.', 'ઔપચારિક યાદી પત્ર ક્યારે મોકલવો?', 'WhatsApp યાદી પછી પણ ચુકવણી ન આવે, કે રકમ મોટી કે જૂની હોય ત્યારે. નકલ સાચવો, પછી કાનૂની નોટિસ મોકલવી પડે તો ઉપયોગી થાય.'],
     ]],

    ['slug' => 'loan-repayment-schedule-maker', 'cat' => 'finance', 'type' => 'doc', 'icon' => 'clipboard-list',
     'en' => 'Loan Repayment Schedule Maker', 'gu' => 'લોન હપ્તા પત્રક (રિપેમેન્ટ શેડ્યૂલ) મેકર',
     'de' => 'Prepare a printable instalment schedule for a loan or credit sale, listing each EMI due date and amount for the borrower.',
     'dg' => 'લોન કે હપ્તેથી વેચાણ માટે દરેક હપ્તાની તારીખ અને રકમ સાથે લોન લેનારને આપવાનું છાપી શકાય તેવું હપ્તા પત્રક બનાવો.',
     'in' => [
       ['k' => 'name', 'en' => 'Borrower / customer name', 'gu' => 'લોન લેનાર / ગ્રાહકનું નામ', 'v' => 'Sureshbhai Joshi', 't' => 'text'],
       ['k' => 'acc', 'en' => 'Loan / account no.', 'gu' => 'લોન / ખાતા નં.', 'v' => 'HL-2026-07', 't' => 'text'],
       ['k' => 'loan', 'en' => 'Loan amount (₹)', 'gu' => 'લોન રકમ (₹)', 'v' => '60,000', 't' => 'text'],
       ['k' => 'rate', 'en' => 'Interest rate', 'gu' => 'વ્યાજ દર', 'v' => '12% p.a. reducing', 't' => 'text'],
       ['k' => 'start', 'en' => 'Loan date', 'gu' => 'લોન તારીખ', 't' => 'date'],
       ['k' => 'note', 'en' => 'Terms / late charges', 'gu' => 'શરતો / લેટ ચાર્જ', 't' => 'textarea', 'v' => 'EMI to be paid on or before the 5th of every month. Late payment attracts ₹100 per instalment.'],
     ],
     'doc' => ['title_en' => 'Loan Repayment Schedule', 'title_gu' => 'લોન હપ્તા પત્રક',
       'items' => ['desc_en' => 'Instalment no. & due date', 'desc_gu' => 'હપ્તા નં. અને તારીખ', 'qty_en' => 'Qty', 'qty_gu' => 'જથ્થો', 'rate_en' => 'EMI amount', 'rate_gu' => 'હપ્તાની રકમ',
         'rows' => [['EMI 1 – 05 Dec 2026', 1, 10353, 'હપ્તો 1 – 05 ડિસે 2026'], ['EMI 2 – 05 Jan 2027', 1, 10353, 'હપ્તો 2 – 05 જાન્યુ 2027'], ['EMI 3 – 05 Feb 2027', 1, 10353, 'હપ્તો 3 – 05 ફેબ્રુ 2027'], ['EMI 4 – 05 Mar 2027', 1, 10353, 'હપ્તો 4 – 05 માર્ચ 2027'], ['EMI 5 – 05 Apr 2027', 1, 10353, 'હપ્તો 5 – 05 એપ્રિલ 2027'], ['EMI 6 – 05 May 2027', 1, 10353, 'હપ્તો 6 – 05 મે 2027']]],
       'foot_en' => 'Total above = total amount repayable. Please keep this schedule and the payment receipts safely.', 'foot_gu' => 'ઉપરનો કુલ = ચૂકવવાની કુલ રકમ. આ પત્રક અને ચુકવણીની પહોંચ સાચવીને રાખો.', 'sign' => true],
     'faq' => [
       ['How do I get the EMI amount for each row?', 'Use our EMI calculator for the loan amount, rate and months, then enter that EMI and each due date in the rows here.', 'દરેક હારમાં હપ્તાની રકમ કેવી રીતે જાણવી?', 'લોન રકમ, દર અને મહિના માટે અમારા EMI કેલ્ક્યુલેટરથી હપ્તો શોધો, પછી દરેક તારીખ સાથે તે રકમ અહીં હારમાં લખો.'],
     ]],
  ],
];
