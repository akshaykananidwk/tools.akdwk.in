<?php
/**
 * KRISHNA TOOLS — exam-specific age calculators (Phase 5).
 * Limits are taken only from the official notification named on each page (see includes/tool_landing.php).
 * Rule used by UPSC and SSC: "attained MIN and not attained MAX on the cut-off date",
 * i.e. completed years >= MIN and completed years < MAX (+ relaxation).
 *
 * GPSC Class 1-2 and Gujarat Police (LRD / PSI) are not added yet: their official
 * advertisements could not be verified, and the owner must confirm the figures first.
 */
return ['tools' => [

    ['slug' => 'upsc-age-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'landmark',
     'en' => 'UPSC Age Calculator 2026 (CSE Age Limit, Relaxation & Attempts)',
     'de' => 'Check your age on 1 August 2026 for UPSC Civil Services (IAS, IPS, IFS): eligibility, OBC/SC/ST/PwBD relaxation and attempts, as per UPSC notice 05/2026-CSE.',
     'kw' => ['upsc age calculator', 'upsc age limit 2026', 'ias age limit', 'upsc age limit for obc', 'upsc attempts limit', 'cse age calculator', 'upsc age relaxation'],
     'steps' => ['Enter your date of birth exactly as in your 10th certificate', 'Choose your category (General/EWS, OBC, SC/ST, PwBD or ex-servicemen)', 'See your exact age on 1 August 2026 and whether you are eligible', 'Check the birth-date range and the number of attempts allowed for you'],
     'in' => [
       ['k' => 'dob', 'en' => 'Date of birth (as in 10th certificate)', 'v' => '2000-08-20', 't' => 'date'],
       ['k' => 'c', 'en' => 'Category', 'v' => '32|6', 'opts' => [
         ['32|6', 'General / EWS (up to 32)'], ['35|9', 'OBC (up to 35)'], ['37|No limit', 'SC / ST (up to 37)'],
         ['42|9', 'PwBD – General / EWS (up to 42)'], ['45|9', 'PwBD – OBC (up to 45)'], ['47|No limit', 'PwBD – SC / ST (up to 47)'],
         ['37|6', 'Ex-servicemen – General / EWS (up to 37)'], ['40|9', 'Ex-servicemen – OBC (up to 40)'], ['42|No limit', 'Ex-servicemen – SC / ST (up to 42)'],
       ]],
     ],
     'out' => [
       ['en' => 'Your age on 1 August 2026', 'f' => "ymd(v.dob, '2026-08-01')", 'big' => 1],
       ['en' => 'Eligible for CSE 2026?', 'f' => "years(v.dob, '2026-08-01') < 21 ? '❌ No — below 21 years on 1 Aug 2026' : (years(v.dob, '2026-08-01') < +String(v.c).split('|')[0] ? '✅ Yes, you are within the age limit' : '❌ No — above the age limit for your category')"],
       ['en' => 'You must be born between', 'f' => "'2 August ' + (2026 - String(v.c).split('|')[0]) + ' and 1 August 2005'"],
       ['en' => 'Attempts allowed', 'f' => "String(v.c).split('|')[1]"],
     ]],

    ['slug' => 'ssc-cgl-age-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'briefcase',
     'en' => 'SSC CGL Age Calculator 2026 (Post-wise Age Limit & Relaxation)',
     'de' => 'Check your age on 1 August 2026 for SSC CGL: post-wise limits 18–27, 18–30, 20–30 and 18–32 with OBC, SC/ST and PwBD relaxation, as per the SSC CGL 2026 notice.',
     'kw' => ['ssc cgl age calculator', 'ssc cgl age limit 2026', 'ssc age calculator', 'ssc cgl age relaxation', 'ssc cgl post wise age limit', 'ssc age limit for obc'],
     'steps' => ['Enter your date of birth exactly as in your 10th certificate', 'Choose the age group of the post you want', 'Choose your category for the relaxation', 'See your age on 1 August 2026 and whether you are eligible'],
     'in' => [
       ['k' => 'dob', 'en' => 'Date of birth (as in 10th certificate)', 'v' => '2000-08-20', 't' => 'date'],
       ['k' => 'p', 'en' => 'Post age group', 'v' => '18|27', 'opts' => [
         ['18|27', '18–27: Auditor, Accountant, Postal Asst, UDC, Tax Assistant, SI (Group C)'],
         ['18|30', '18–30: AAO, Income Tax Inspector, Excise/Preventive Inspector, AEO, ASO (IB, MeitY), Inspector of Posts'],
         ['20|30', '20–30: ASO in CSS, Railways, MEA, AFHQ; SI in CBI'],
         ['18|32', '18–32: Junior Statistical Officer (MoSPI)'],
       ]],
       ['k' => 'rx', 'en' => 'Category relaxation', 'v' => 0, 'opts' => [[0, 'General / EWS (0)'], [3, 'OBC (+3 years)'], [5, 'SC / ST (+5 years)'], [10, 'PwBD – General (+10 years)'], [13, 'PwBD – OBC (+13 years)'], [15, 'PwBD – SC / ST (+15 years)']]],
     ],
     'out' => [
       ['en' => 'Your age on 1 August 2026', 'f' => "ymd(v.dob, '2026-08-01')", 'big' => 1],
       ['en' => 'Eligible for this post?', 'f' => "years(v.dob, '2026-08-01') < +String(v.p).split('|')[0] ? '❌ No — below the minimum age' : (years(v.dob, '2026-08-01') < +String(v.p).split('|')[1] + v.rx ? '✅ Yes, you are within the age limit' : '❌ No — above the age limit for this post')"],
       ['en' => 'Upper age limit for you', 'f' => "+String(v.p).split('|')[1] + v.rx", 'u' => 'years'],
       ['en' => 'You must be born between', 'f' => "'2 August ' + (2026 - String(v.p).split('|')[1] - v.rx) + ' and 1 August ' + (2026 - String(v.p).split('|')[0])"],
     ]],
]];
