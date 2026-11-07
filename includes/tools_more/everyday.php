<?php
/** KRISHNA TOOLS — Everyday calculators people search for daily. */

$dow = "['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']";
$isoWeek = '(function(){var d=new Date(v.d);if(isNaN(d))return NaN;d=new Date(Date.UTC(d.getFullYear(),d.getMonth(),d.getDate()));var n=d.getUTCDay()||7;d.setUTCDate(d.getUTCDate()+4-n);var y=new Date(Date.UTC(d.getUTCFullYear(),0,1));return Math.ceil(((d-y)/864e5+1)/7);})()';
$leap = '(v.y%4==0 && v.y%100!=0) || v.y%400==0';
$roman = '(function(s){s=String(s||"").trim().toUpperCase();if(!s)return "—";var M=[[1000,"M"],[900,"CM"],[500,"D"],[400,"CD"],[100,"C"],[90,"XC"],[50,"L"],[40,"XL"],[10,"X"],[9,"IX"],[5,"V"],[4,"IV"],[1,"I"]];'
  . 'if(/^\\d+$/.test(s)){var n=+s;if(n<1||n>3999)return "Enter 1 to 3999";var r="";M.forEach(function(p){while(n>=p[0]){r+=p[1];n-=p[0];}});return r;}'
  . 'if(!/^[MDCLXVI]+$/.test(s))return "Not a valid number or Roman numeral";var V={M:1000,D:500,C:100,L:50,X:10,V:5,I:1},t=0;for(var i=0;i<s.length;i++){var a=V[s[i]],b=V[s[i+1]]||0;t+=a<b?-a:a;}return String(t);})(v.x)';
$N = '(v.n*v.u)';
$avgNums = 'nums(v.t)';
$median = '(function(a){a=a.slice().sort(function(x,y){return x-y});if(!a.length)return NaN;var m=Math.floor(a.length/2);return a.length%2?a[m]:(a[m-1]+a[m])/2;})(nums(v.t))';
$frac = '(function(){var n,d;switch(v.op){case 1:n=v.a*v.d+v.c*v.b;d=v.b*v.d;break;case 2:n=v.a*v.d-v.c*v.b;d=v.b*v.d;break;case 3:n=v.a*v.c;d=v.b*v.d;break;default:n=v.a*v.d;d=v.b*v.c;}if(!d)return null;var g=function(x,y){return y?g(y,x%y):Math.abs(x)};var k=g(Math.round(n),Math.round(d))||1;n=Math.round(n)/k;d=Math.round(d)/k;if(d<0){n=-n;d=-d;}return [n,d];})()';
$sleepTimes = '(function(){var t=mins(v.t);if(isNaN(t))return "—";var out=[];[6,5,4,3].forEach(function(c){out.push(clock(v.m==1? t-c*90-v.f : t+c*90+v.f)+" ("+c+" cycles, "+(c*1.5)+" h)");});return out.join("  •  ");})()';
$dur = '(function(){var a=mins(v.a),b=mins(v.b);if(isNaN(a)||isNaN(b))return NaN;var d=b-a;if(d<0)d+=1440;return d-v.br;})()';
$ws = '(v.mo + v.tu + v.we + v.th + v.fr + v.sa + v.su)';
$petAge = '(function(){var a=v.a;if(a<=0)return 0;var add={1:4,2:5,3:6,4:4}[v.p]||5;if(a<=1)return a*15;if(a<=2)return 15+(a-1)*9;return 24+(a-2)*add;})()';
$pwScore = '(function(p){p=String(p||"");var pool=0;if(/[a-z]/.test(p))pool+=26;if(/[A-Z]/.test(p))pool+=26;if(/[0-9]/.test(p))pool+=10;if(/[^A-Za-z0-9]/.test(p))pool+=33;return p.length&&pool?p.length*Math.log2(pool):0;})(v.p)';
$pwCrack = '(function(b){var s=Math.pow(2,b)/1e10/2;if(s<1)return "Instantly";var u=[[31536000*100,"centuries"],[31536000,"years"],[2592000,"months"],[86400,"days"],[3600,"hours"],[60,"minutes"],[1,"seconds"]];for(var i=0;i<u.length;i++)if(s>=u[i][0]){var n=s/u[i][0];return (n>1e6?"Millions of ":Math.round(n)+" ")+u[i][1];}})(' . $pwScore . ')';
$best5 = '(function(){var a=[v.s1,v.s2,v.s3,v.s4,v.s5,v.s6].sort(function(x,y){return y-x});return a[0]+a[1]+a[2]+a[3]+a[4];})()';
$nw_a = '(v.cash + v.fd + v.mf + v.gold + v.prop + v.veh + v.oa)';
$nw_l = '(v.hl + v.cl + v.pl + v.cc + v.ol)';
$kvpM = '(log(2)/log(1 + v.r/100)*12)';

return [
  'cats' => [
    ['slug' => 'everyday', 'name_en' => 'Everyday Calculators', 'name_gu' => 'રોજિંદા કેલ્ક્યુલેટર', 'icon' => 'sun', 'color' => '#0F766E', 'sort_order' => 10],
  ],
  'tools' => [

    ['slug' => 'number-to-words-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'case-sensitive',
     'en' => 'Number to Words Converter (Rupees in Words for Cheque)',
     'de' => 'Convert any amount into words in the Indian system (lakh, crore) for cheques, bills and receipts — e.g. ₹1,25,500 = One Lakh Twenty Five Thousand Five Hundred Rupees Only.',
     'kw' => ['number to words', 'amount in words', 'rupees in words', 'number to words converter indian rupees', 'cheque amount in words', 'convert number to words in english', 'lakh in words'],
     'in' => [['k' => 'n', 'en' => 'Amount / number', 'v' => 125500.50, 'u' => '₹']],
     'out' => [
       ['en' => 'In words (for cheque)', 'f' => "'Rupees ' + words(v.n) + (round((v.n - floor(v.n))*100) ? ' and ' + words(round((v.n - floor(v.n))*100)) + ' Paise' : '') + ' Only'", 'big' => 1],
       ['en' => 'Number in words', 'f' => 'words(v.n)'],
       ['en' => 'Indian format', 'f' => "(+v.n).toLocaleString('en-IN', {maximumFractionDigits: 2})"],
       ['en' => 'International format', 'f' => "(+v.n).toLocaleString('en-US', {maximumFractionDigits: 2})"],
     ],
     'faq' => [['How do I write 1,50,000 in words on a cheque?', 'Write "Rupees One Lakh Fifty Thousand Only". Always start with "Rupees" and end with "Only" so nothing can be added later.']]],

    ['slug' => 'lakh-crore-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'arrow-left-right',
     'en' => 'Lakh Crore to Million Billion Converter',
     'de' => 'Convert between lakh, crore, million and billion instantly — 10 lakh = 1 million, 1 crore = 10 million, 100 crore = 1 billion.',
     'kw' => ['lakh to million converter', 'crore to million', 'million to crore', 'billion to crore', 'lakh crore converter', '1 million in lakhs', '1 billion in crore'],
     'in' => [
       ['k' => 'n', 'en' => 'Number', 'v' => 5],
       ['k' => 'u', 'en' => 'Unit', 'v' => 10000000, 'opts' => [[1, 'Units (plain number)'], [1000, 'Thousand'], [100000, 'Lakh'], [10000000, 'Crore'], [1000000, 'Million'], [1000000000, 'Billion']]],
     ],
     'out' => [
       ['en' => 'In lakh', 'f' => "$N/1e5", 'd' => 4, 'u' => 'lakh', 'big' => 1],
       ['en' => 'In crore', 'f' => "$N/1e7", 'd' => 4, 'u' => 'crore'],
       ['en' => 'In million', 'f' => "$N/1e6", 'd' => 4, 'u' => 'million'],
       ['en' => 'In billion', 'f' => "$N/1e9", 'd' => 6, 'u' => 'billion'],
       ['en' => 'Full number (Indian)', 'f' => "($N).toLocaleString('en-IN')"],
       ['en' => 'Full number (International)', 'f' => "($N).toLocaleString('en-US')"],
     ]],

    ['slug' => 'time-duration-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'timer',
     'en' => 'Time Duration Calculator (Hours Between Two Times)',
     'de' => 'Find the hours and minutes between two times, including overnight shifts and lunch breaks, in hours:minutes and decimal hours.',
     'kw' => ['time calculator', 'hours calculator', 'time duration calculator', 'hours between two times', 'work hours calculator', 'time difference calculator', 'shift hours calculator'],
     'in' => [
       ['k' => 'a', 'en' => 'Start time', 'v' => '09:30', 't' => 'time'],
       ['k' => 'b', 'en' => 'End time', 'v' => '18:45', 't' => 'time'],
       ['k' => 'br', 'en' => 'Break (minutes)', 'v' => 30],
     ],
     'out' => [
       ['en' => 'Duration', 'f' => "hm($dur)", 'big' => 1],
       ['en' => 'Decimal hours', 'f' => "$dur/60", 'd' => 2, 'u' => 'hours'],
       ['en' => 'Total minutes', 'f' => $dur, 'fmt' => 'int', 'u' => 'min'],
     ]],

    ['slug' => 'weekly-timesheet-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'table',
     'en' => 'Weekly Timesheet & Work Hours Pay Calculator',
     'de' => 'Add up hours worked each day of the week and calculate weekly pay with an hourly rate.',
     'kw' => ['timesheet calculator', 'weekly hours calculator', 'time card calculator', 'work hours pay calculator', 'total hours worked calculator'],
     'in' => [
       ['k' => 'mo', 'en' => 'Monday hours', 'v' => 8], ['k' => 'tu', 'en' => 'Tuesday hours', 'v' => 8],
       ['k' => 'we', 'en' => 'Wednesday hours', 'v' => 8], ['k' => 'th', 'en' => 'Thursday hours', 'v' => 8],
       ['k' => 'fr', 'en' => 'Friday hours', 'v' => 8], ['k' => 'sa', 'en' => 'Saturday hours', 'v' => 5],
       ['k' => 'su', 'en' => 'Sunday hours', 'v' => 0], ['k' => 'r', 'en' => 'Hourly rate', 'v' => 150, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Total hours this week', 'f' => $ws, 'd' => 2, 'u' => 'hours', 'big' => 1],
       ['en' => 'Weekly pay', 'f' => "$ws*v.r", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Average per working day', 'f' => "$ws/max(1, [v.mo,v.tu,v.we,v.th,v.fr,v.sa,v.su].filter(function(x){return x>0}).length)", 'd' => 2, 'u' => 'hours'],
       ['en' => 'Monthly estimate (×4.33)', 'f' => "$ws*v.r*4.33", 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'sleep-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'moon',
     'en' => 'Sleep Calculator – Best Time to Sleep & Wake Up',
     'de' => 'Find the best bedtime or wake-up time based on 90-minute sleep cycles, so you wake up fresh instead of tired.',
     'kw' => ['sleep calculator', 'what time should i sleep', 'bedtime calculator', 'wake up time calculator', 'sleep cycle calculator', 'when to sleep to wake up at 6'],
     'in' => [
       ['k' => 'm', 'en' => 'I want to', 'v' => 1, 'opts' => [[1, 'Wake up at this time → when to sleep?'], [2, 'Go to bed at this time → when to wake?']]],
       ['k' => 't', 'en' => 'Time', 'v' => '06:00', 't' => 'time'],
       ['k' => 'f', 'en' => 'Minutes to fall asleep', 'v' => 15],
     ],
     'out' => [
       ['en' => 'Best times (most sleep first)', 'f' => $sleepTimes, 'big' => 1],
       ['en' => 'Tip', 'f' => "'Adults need 7–9 hours: 5 or 6 sleep cycles are ideal.'"],
     ]],

    ['slug' => 'day-of-week-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'calendar-search',
     'en' => 'What Day Is It? – Day of the Week for Any Date',
     'de' => 'Find which day of the week any date falls on — past or future — plus the day of the year and days from today.',
     'kw' => ['what day is it', 'day of the week calculator', 'what day was i born', 'day finder by date', 'which day on date', 'day from date'],
     'in' => [['k' => 'd', 'en' => 'Date', 'v' => 'today', 't' => 'date']],
     'out' => [
       ['en' => 'Day of the week', 'f' => "$dow" . '[new Date(v.d).getDay()]', 'big' => 1],
       ['en' => 'Full date', 'f' => 'fmtDate(v.d)'],
       ['en' => 'Day of the year', 'f' => "days(new Date(v.d).getFullYear() + '-01-01', v.d) + 1", 'fmt' => 'int'],
       ['en' => 'Days from today', 'f' => "days(today(), v.d) === 0 ? 'Today' : (days(today(), v.d) > 0 ? days(today(), v.d) + ' days from now' : -days(today(), v.d) + ' days ago')"],
     ]],

    ['slug' => 'week-number-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'calendar-range',
     'en' => 'Week Number Calculator (What Week Is It Today?)',
     'de' => 'Get the ISO week number, quarter and week of the month for today or any date.',
     'kw' => ['what week is it', 'week number today', 'week number calculator', 'iso week number', 'current week number'],
     'in' => [['k' => 'd', 'en' => 'Date', 'v' => 'today', 't' => 'date']],
     'out' => [
       ['en' => 'ISO week number', 'f' => $isoWeek, 'fmt' => 'int', 'big' => 1],
       ['en' => 'Quarter', 'f' => "'Q' + (Math.floor(new Date(v.d).getMonth()/3) + 1)"],
       ['en' => 'Week of the month', 'f' => 'ceil(new Date(v.d).getDate()/7)', 'fmt' => 'int'],
       ['en' => 'Weeks left in the year', 'f' => "max(0, 52 - $isoWeek)", 'fmt' => 'int'],
     ]],

    ['slug' => 'leap-year-checker', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'calendar-plus',
     'en' => 'Leap Year Checker',
     'de' => 'Check whether a year is a leap year, how many days February has, and the next leap year.',
     'kw' => ['leap year checker', 'is 2028 a leap year', 'leap year calculator', 'next leap year', 'leap year list'],
     'in' => [['k' => 'y', 'en' => 'Year', 'v' => 2028]],
     'out' => [
       ['en' => 'Result', 'f' => "($leap) ? v.y + ' is a leap year (366 days)' : v.y + ' is not a leap year (365 days)'", 'big' => 1],
       ['en' => 'Days in February', 'f' => "($leap) ? 29 : 28", 'fmt' => 'int'],
       ['en' => 'Next leap year', 'f' => '(function(){for(var y=v.y+1;;y++)if((y%4==0&&y%100!=0)||y%400==0)return y;})()', 'fmt' => 'num', 'd' => 0],
     ]],

    ['slug' => 'age-difference-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'users',
     'en' => 'Age Difference Calculator',
     'de' => 'Find the exact age gap between two people in years, months and days — for couples, siblings or friends.',
     'kw' => ['age difference calculator', 'age gap calculator', 'age difference between two dates', 'couple age difference'],
     'in' => [
       ['k' => 'a', 'en' => 'First person\'s date of birth', 'v' => '1995-03-12', 't' => 'date'],
       ['k' => 'b', 'en' => 'Second person\'s date of birth', 'v' => '1998-11-25', 't' => 'date'],
     ],
     'out' => [
       ['en' => 'Age difference', 'f' => 'ymd(v.a, v.b)', 'big' => 1],
       ['en' => 'In days', 'f' => 'abs(days(v.a, v.b))', 'fmt' => 'int', 'u' => 'days'],
       ['en' => 'Older person', 'f' => "days(v.a, v.b) > 0 ? 'First person' : (days(v.a, v.b) < 0 ? 'Second person' : 'Same birthday')"],
     ]],

    ['slug' => 'age-in-days-hours-minutes', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'hourglass',
     'en' => 'Age in Days, Weeks, Hours, Minutes & Seconds',
     'de' => 'See how old you are in months, weeks, days, hours, minutes and seconds, and how many days until your next birthday.',
     'kw' => ['how many days old am i', 'age in days calculator', 'age in seconds', 'age in hours', 'how old am i in weeks', 'days since birth'],
     'in' => [['k' => 'dob', 'en' => 'Date of birth', 'v' => '2000-01-01', 't' => 'date']],
     'out' => [
       ['en' => 'You have lived', 'f' => 'days(v.dob, today())', 'fmt' => 'int', 'u' => 'days', 'big' => 1],
       ['en' => 'Age', 'f' => 'ymd(v.dob, today())'],
       ['en' => 'Months', 'f' => 'days(v.dob, today())/30.4375', 'fmt' => 'int'],
       ['en' => 'Weeks', 'f' => 'days(v.dob, today())/7', 'fmt' => 'int'],
       ['en' => 'Hours', 'f' => 'days(v.dob, today())*24', 'fmt' => 'int'],
       ['en' => 'Minutes', 'f' => 'days(v.dob, today())*1440', 'fmt' => 'int'],
       ['en' => 'Seconds', 'f' => 'days(v.dob, today())*86400', 'fmt' => 'int'],
       ['en' => 'Next birthday in', 'f' => '(function(){var b=new Date(v.dob),t=new Date();t.setHours(0,0,0,0);var n=new Date(t.getFullYear(),b.getMonth(),b.getDate());if(n<t)n.setFullYear(t.getFullYear()+1);var d=Math.round((n-t)/864e5);return d?d+" days":"Today! Happy Birthday 🎉";})()'],
     ]],

    ['slug' => 'birth-year-from-age-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'baby',
     'en' => 'Birth Year from Age Calculator',
     'de' => 'Find the year someone was born from their age today or on any date.',
     'kw' => ['birth year calculator', 'what year was i born if i am', 'year of birth from age', 'born in which year calculator'],
     'in' => [
       ['k' => 'a', 'en' => 'Age (years)', 'v' => 30],
       ['k' => 'd', 'en' => 'Age on date', 'v' => 'today', 't' => 'date'],
     ],
     'out' => [
       ['en' => 'Born in', 'f' => "(new Date(v.d).getFullYear() - v.a - 1) + ' or ' + (new Date(v.d).getFullYear() - v.a)", 'big' => 1],
       ['en' => 'Note', 'f' => "'The later year if the birthday has already passed this year, otherwise the earlier year.'"],
     ]],

    ['slug' => 'roman-numeral-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'columns-3',
     'en' => 'Roman Numeral Converter (Numbers ↔ Roman)',
     'de' => 'Convert numbers to Roman numerals and Roman numerals back to numbers, from 1 to 3999.',
     'kw' => ['roman numerals converter', 'number to roman numerals', 'roman numbers 1 to 100', 'roman to number', 'mmxxvi meaning'],
     'in' => [['k' => 'x', 'en' => 'Number or Roman numeral', 'v' => '2026', 't' => 'text']],
     'out' => [['en' => 'Converted', 'f' => $roman, 'big' => 1]]],

    ['slug' => 'average-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'sigma',
     'en' => 'Average Calculator (Mean, Median, Sum, Min, Max)',
     'de' => 'Paste or type any list of numbers to get the average, sum, count, median, highest and lowest value.',
     'kw' => ['average calculator', 'mean calculator', 'how to calculate average', 'average of numbers', 'sum calculator', 'median calculator'],
     'in' => [['k' => 't', 'en' => 'Numbers (separated by comma, space or new line)', 'v' => '45, 67, 89, 72, 55, 91', 't' => 'textarea', 'full' => 1]],
     'out' => [
       ['en' => 'Average (mean)', 'f' => "$avgNums.length ? $avgNums.reduce(function(a,b){return a+b},0)/$avgNums.length : NaN", 'd' => 4, 'big' => 1],
       ['en' => 'Sum', 'f' => "$avgNums.reduce(function(a,b){return a+b},0)", 'd' => 4],
       ['en' => 'Count', 'f' => "$avgNums.length", 'fmt' => 'int'],
       ['en' => 'Median', 'f' => $median, 'd' => 4],
       ['en' => 'Highest', 'f' => "$avgNums.length ? Math.max.apply(null, $avgNums) : NaN", 'd' => 4],
       ['en' => 'Lowest', 'f' => "$avgNums.length ? Math.min.apply(null, $avgNums) : NaN", 'd' => 4],
     ]],

    ['slug' => 'fraction-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'divide',
     'en' => 'Fraction Calculator (Add, Subtract, Multiply, Divide)',
     'de' => 'Add, subtract, multiply or divide two fractions and get the answer in simplest form, as a mixed number and as a decimal.',
     'kw' => ['fraction calculator', 'adding fractions calculator', 'fraction to decimal', 'simplify fractions', 'mixed fraction calculator'],
     'in' => [
       ['k' => 'a', 'en' => 'First fraction – numerator', 'v' => 3], ['k' => 'b', 'en' => 'First fraction – denominator', 'v' => 4],
       ['k' => 'op', 'en' => 'Operation', 'v' => 1, 'opts' => [[1, '+ Add'], [2, '− Subtract'], [3, '× Multiply'], [4, '÷ Divide']]],
       ['k' => 'c', 'en' => 'Second fraction – numerator', 'v' => 2], ['k' => 'd', 'en' => 'Second fraction – denominator', 'v' => 5],
     ],
     'out' => [
       ['en' => 'Answer (simplest form)', 'f' => "($frac) ? ($frac)[0] + '/' + ($frac)[1] : 'Undefined'", 'big' => 1],
       ['en' => 'Mixed number', 'f' => "($frac) ? (abs(($frac)[0]) >= ($frac)[1] && ($frac)[1] != 1 ? Math.trunc(($frac)[0]/($frac)[1]) + ' ' + abs(($frac)[0] % ($frac)[1]) + '/' + ($frac)[1] : (($frac)[1] == 1 ? String(($frac)[0]) : ($frac)[0] + '/' + ($frac)[1])) : '—'"],
       ['en' => 'Decimal', 'f' => "($frac) ? ($frac)[0]/($frac)[1] : NaN", 'd' => 6],
     ]],

    ['slug' => 'square-root-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'square-radical',
     'en' => 'Square Root, Cube Root & Power Calculator',
     'de' => 'Find the square, cube, square root, cube root, any nth root and power of a number.',
     'kw' => ['square root calculator', 'cube root calculator', 'square calculator', 'power calculator', 'exponent calculator', 'nth root calculator'],
     'in' => [['k' => 'x', 'en' => 'Number', 'v' => 144], ['k' => 'n', 'en' => 'Power / root (n)', 'v' => 4]],
     'out' => [
       ['en' => 'Square root √x', 'f' => 'sqrt(v.x)', 'd' => 6, 'big' => 1],
       ['en' => 'Cube root ∛x', 'f' => 'Math.cbrt(v.x)', 'd' => 6],
       ['en' => 'Square x²', 'f' => 'v.x*v.x', 'd' => 4],
       ['en' => 'Cube x³', 'f' => 'v.x*v.x*v.x', 'd' => 4],
       ['en' => 'x to the power n', 'f' => 'pow(v.x, v.n)', 'd' => 6],
       ['en' => 'nth root of x', 'f' => 'v.n ? (v.x < 0 && v.n % 2 ? -pow(-v.x, 1/v.n) : pow(v.x, 1/v.n)) : NaN', 'd' => 6],
     ]],

    ['slug' => 'percentage-change-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'percent',
     'en' => 'Percentage Increase / Decrease Calculator',
     'de' => 'Find the percentage increase or decrease between an old and new value — for prices, marks, salary or sales.',
     'kw' => ['percentage increase calculator', 'percentage decrease calculator', 'percentage change formula', 'percent difference calculator', 'price increase percentage'],
     'in' => [['k' => 'a', 'en' => 'Old value', 'v' => 800], ['k' => 'b', 'en' => 'New value', 'v' => 1000]],
     'out' => [
       ['en' => 'Percentage change', 'f' => "v.a ? ((v.b - v.a)/abs(v.a)*100 >= 0 ? '▲ ' : '▼ ') + abs((v.b - v.a)/v.a*100).toFixed(2) + '% ' + (v.b >= v.a ? 'increase' : 'decrease') : '—'", 'big' => 1],
       ['en' => 'Difference', 'f' => 'v.b - v.a', 'd' => 2],
       ['en' => 'New value is (% of old)', 'f' => 'v.a ? v.b/v.a*100 : NaN', 'fmt' => 'pct'],
       ['en' => 'Percentage difference (average base)', 'f' => '(v.a + v.b) ? abs(v.a - v.b)/((v.a + v.b)/2)*100 : NaN', 'fmt' => 'pct'],
     ]],

    ['slug' => 'unit-price-comparison-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'scale',
     'en' => 'Price Comparison Calculator – Which Pack Is Cheaper?',
     'de' => 'Compare price per kg, litre or piece of two or three packs to find the best value while shopping.',
     'kw' => ['price comparison calculator', 'unit price calculator', 'which is cheaper calculator', 'price per kg calculator', 'best value calculator grocery'],
     'in' => [
       ['k' => 'p1', 'en' => 'Pack A price', 'v' => 99, 'u' => '₹'], ['k' => 'q1', 'en' => 'Pack A quantity (g / ml / pcs)', 'v' => 500],
       ['k' => 'p2', 'en' => 'Pack B price', 'v' => 185, 'u' => '₹'], ['k' => 'q2', 'en' => 'Pack B quantity', 'v' => 1000],
       ['k' => 'p3', 'en' => 'Pack C price (optional)', 'v' => 0, 'u' => '₹'], ['k' => 'q3', 'en' => 'Pack C quantity', 'v' => 0],
     ],
     'out' => [
       ['en' => 'Best value', 'f' => "(function(){var a=[['Pack A',v.p1/v.q1],['Pack B',v.p2/v.q2],['Pack C',v.q3&&v.p3?v.p3/v.q3:Infinity]].filter(function(x){return isFinite(x[1])}).sort(function(x,y){return x[1]-y[1]});return a.length?a[0][0]+' is cheapest':'—';})()", 'big' => 1],
       ['en' => 'Pack A per 1000 (kg / L)', 'f' => 'v.q1 ? v.p1/v.q1*1000 : NaN', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Pack B per 1000 (kg / L)', 'f' => 'v.q2 ? v.p2/v.q2*1000 : NaN', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Pack C per 1000 (kg / L)', 'f' => 'v.q3 ? v.p3/v.q3*1000 : NaN', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'You save with B vs A', 'f' => 'v.q1 && v.p1 ? (1 - (v.p2/v.q2)/(v.p1/v.q1))*100 : NaN', 'fmt' => 'pct'],
     ]],

    ['slug' => 'grams-price-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'shopping-basket',
     'en' => 'Price per Gram Calculator (Rate per Kg to Grams)',
     'de' => 'Find the price of any grams when you know the rate per kg — and how many grams you get for a given amount. Great for shops and kirana.',
     'kw' => ['price per gram calculator', '250 gram price calculator', 'rate per kg to gram', 'kg to gram price', 'how much for 100 grams', 'kirana calculator'],
     'in' => [
       ['k' => 'r', 'en' => 'Rate per kg', 'v' => 480, 'u' => '₹'],
       ['k' => 'g', 'en' => 'Weight (grams)', 'v' => 250, 'u' => 'g'],
       ['k' => 'm', 'en' => 'Or: amount customer wants to buy for', 'v' => 50, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Price for this weight', 'f' => 'v.r*v.g/1000', 'fmt' => 'inr', 'd' => 2, 'big' => 1],
       ['en' => 'Grams for that amount', 'f' => 'v.r ? v.m/v.r*1000 : NaN', 'd' => 1, 'u' => 'g'],
       ['en' => 'Price per 100 g', 'f' => 'v.r/10', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Price per 50 g', 'f' => 'v.r/20', 'fmt' => 'inr', 'd' => 2],
     ]],

    ['slug' => 'buy-x-get-y-offer-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'tag',
     'en' => 'Buy 1 Get 1 / Buy X Get Y Offer Calculator',
     'de' => 'Find the real discount % and price per item of "Buy 2 Get 1 Free" style offers, and compare them with a flat discount.',
     'kw' => ['buy 1 get 1 calculator', 'buy 2 get 1 free discount', 'bogo calculator', 'offer discount percentage calculator', 'buy 3 get 2 offer'],
     'in' => [
       ['k' => 'p', 'en' => 'Price of one item', 'v' => 499, 'u' => '₹'],
       ['k' => 'x', 'en' => 'Buy', 'v' => 2], ['k' => 'y', 'en' => 'Get free', 'v' => 1],
       ['k' => 'fd', 'en' => 'Compare with flat discount', 'v' => 30, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Real discount', 'f' => '(v.x + v.y) ? v.y/(v.x + v.y)*100 : 0', 'fmt' => 'pct', 'big' => 1],
       ['en' => 'You pay', 'f' => 'v.p*v.x', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Effective price per item', 'f' => '(v.x + v.y) ? v.p*v.x/(v.x + v.y) : 0', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Better deal', 'f' => "v.y/(v.x + v.y)*100 > v.fd ? 'Buy ' + v.x + ' Get ' + v.y + ' offer' : (v.y/(v.x + v.y)*100 < v.fd ? v.fd + '% flat discount' : 'Both are equal')"],
     ]],

    ['slug' => 'lpg-cylinder-days-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'flame',
     'en' => 'LPG Gas Cylinder Usage & Next Booking Calculator',
     'de' => 'Find how much gas you use per day, your monthly cooking gas cost, and when to book the next cylinder.',
     'kw' => ['lpg cylinder calculator', 'gas cylinder how many days', 'lpg usage per day', 'gas cylinder booking date', 'lpg cost per day'],
     'in' => [
       ['k' => 'kg', 'en' => 'Cylinder size', 'v' => 14.2, 'opts' => [[14.2, '14.2 kg (domestic)'], [5, '5 kg'], [19, '19 kg (commercial)']]],
       ['k' => 'd', 'en' => 'Last cylinder lasted (days)', 'v' => 40],
       ['k' => 'p', 'en' => 'Cylinder price', 'v' => 853, 'u' => '₹'],
       ['k' => 'f', 'en' => 'New cylinder started on', 'v' => 'today', 't' => 'date'],
     ],
     'out' => [
       ['en' => 'Book next cylinder around', 'f' => 'addDays(v.f, max(0, v.d - 7))', 'big' => 1],
       ['en' => 'Expected to finish', 'f' => 'addDays(v.f, v.d)'],
       ['en' => 'Gas used per day', 'f' => 'v.d ? v.kg/v.d*1000 : NaN', 'd' => 0, 'u' => 'g'],
       ['en' => 'Cost per day', 'f' => 'v.d ? v.p/v.d : NaN', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Cost per month', 'f' => 'v.d ? v.p/v.d*30 : NaN', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Cylinders per year', 'f' => 'v.d ? 365/v.d : NaN', 'd' => 1],
     ]],

    ['slug' => 'water-tank-filling-time-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'droplet',
     'en' => 'Water Tank Filling Time Calculator',
     'de' => 'Calculate how long your motor or pump will take to fill an overhead or underground water tank.',
     'kw' => ['water tank filling time calculator', 'how long to fill 1000 litre tank', 'motor tank filling time', 'pump flow rate calculator', 'water tank fill time'],
     'in' => [
       ['k' => 'c', 'en' => 'Tank capacity', 'v' => 1000, 'u' => 'litres'],
       ['k' => 'l', 'en' => 'Water already in tank', 'v' => 20, 'u' => '%'],
       ['k' => 'f', 'en' => 'Pump flow (litres per minute)', 'v' => 40, 'u' => 'LPM'],
     ],
     'out' => [
       ['en' => 'Time to fill', 'f' => 'v.f ? hm(v.c*(1 - v.l/100)/v.f) : "—"', 'big' => 1],
       ['en' => 'Water needed', 'f' => 'v.c*(1 - v.l/100)', 'fmt' => 'int', 'u' => 'litres'],
       ['en' => 'Tip', 'f' => "'A 0.5 HP home pump usually gives 20–40 LPM, a 1 HP pump 40–70 LPM, depending on height.'"],
     ]],

    ['slug' => 'cups-to-grams-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'cooking-pot',
     'en' => 'Cups to Grams Converter (Atta, Maida, Sugar, Rice, Oil)',
     'de' => 'Convert cups, tablespoons and teaspoons into grams and ml for common Indian kitchen ingredients.',
     'kw' => ['cups to grams', '1 cup flour in grams', '1 cup sugar in grams', 'cup to gram converter', 'tablespoon to grams', 'baking measurement converter'],
     'in' => [
       ['k' => 'i', 'en' => 'Ingredient', 'v' => 120, 'opts' => [[120, 'Wheat flour / atta'], [125, 'Maida (all-purpose flour)'], [92, 'Besan (gram flour)'], [200, 'Sugar'], [120, 'Powdered sugar'], [185, 'Rice (raw)'], [200, 'Toor / moong dal'], [160, 'Rava / sooji'], [90, 'Poha'], [218, 'Oil'], [227, 'Butter / ghee'], [240, 'Milk / water'], [340, 'Honey'], [288, 'Salt'], [85, 'Grated coconut']]],
       ['k' => 'q', 'en' => 'Quantity', 'v' => 2],
       ['k' => 'u', 'en' => 'Unit', 'v' => 1, 'opts' => [[1, 'Cup (240 ml)'], [0.0625, 'Tablespoon'], [0.0208333, 'Teaspoon']]],
     ],
     'out' => [
       ['en' => 'Weight', 'f' => 'v.i*v.q*v.u', 'd' => 1, 'u' => 'grams', 'big' => 1],
       ['en' => 'Volume', 'f' => '240*v.q*v.u', 'd' => 1, 'u' => 'ml'],
     ]],

    ['slug' => 'shoe-size-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'footprints',
     'en' => 'Shoe Size Converter (India / UK / US / EU)',
     'de' => 'Convert Indian and UK shoe sizes to US and European (EU) sizes for men and women, with approximate foot length.',
     'kw' => ['shoe size converter', 'uk to us shoe size', 'indian shoe size chart', 'eu to uk shoe size', 'shoe size chart india', 'foot length to shoe size'],
     'in' => [
       ['k' => 's', 'en' => 'India / UK size', 'v' => 8],
       ['k' => 'g', 'en' => 'For', 'v' => 1, 'opts' => [[1, 'Men'], [2, 'Women']]],
     ],
     'out' => [
       ['en' => 'US size', 'f' => 'v.g == 1 ? v.s + 1 : v.s + 2', 'd' => 1, 'big' => 1],
       ['en' => 'EU size', 'f' => 'v.g == 1 ? round(v.s + 33.5) : round(v.s + 33)', 'd' => 0],
       ['en' => 'Approx. foot length', 'f' => '((v.s + 25)/3*2.54 - 1.5)', 'd' => 1, 'u' => 'cm'],
     ],
     'how_en' => 'India uses UK sizes. US men = UK + 1, US women = UK + 2. EU sizes are about UK + 33 to 34. Sizes differ slightly between brands, so check the brand\'s own chart for the best fit.'],

    ['slug' => 'ring-size-converter', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'circle-dot',
     'en' => 'Ring Size Calculator (Indian & US Ring Size)',
     'de' => 'Find your Indian and US ring size from the inner diameter of a ring or the circumference of your finger.',
     'kw' => ['ring size calculator', 'ring size chart india', 'how to measure ring size', 'finger size in mm', 'us ring size to indian'],
     'in' => [
       ['k' => 'm', 'en' => 'I measured', 'v' => 1, 'opts' => [[1, 'Inner diameter of a ring (mm)'], [2, 'Finger circumference with thread (mm)']]],
       ['k' => 'x', 'en' => 'Measurement', 'v' => 17, 'u' => 'mm'],
     ],
     'out' => [
       ['en' => 'Indian ring size', 'f' => 'round((v.m == 1 ? v.x*PI : v.x) - 40)', 'fmt' => 'int', 'big' => 1],
       ['en' => 'US ring size', 'f' => 'round(((v.m == 1 ? v.x*PI : v.x) - 36.5)/2.55*4)/4', 'd' => 2],
       ['en' => 'Circumference', 'f' => 'v.m == 1 ? v.x*PI : v.x', 'd' => 1, 'u' => 'mm'],
       ['en' => 'Inner diameter', 'f' => 'v.m == 1 ? v.x : v.x/PI', 'd' => 1, 'u' => 'mm'],
     ]],

    ['slug' => 'height-converter-feet-cm', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'ruler',
     'en' => 'Height Converter – Feet & Inches to cm',
     'de' => 'Convert height in feet and inches to centimetres and metres — e.g. 5 feet 6 inches = 167.6 cm.',
     'kw' => ['height converter', 'feet to cm', '5 feet 6 inches in cm', 'cm to feet and inches', 'height in cm', 'ft to cm height'],
     'in' => [['k' => 'f', 'en' => 'Feet', 'v' => 5], ['k' => 'i', 'en' => 'Inches', 'v' => 6], ['k' => 'c', 'en' => 'Or enter cm (to convert back)', 'v' => 170, 'u' => 'cm']],
     'out' => [
       ['en' => 'Height in cm', 'f' => '(v.f*12 + v.i)*2.54', 'd' => 1, 'u' => 'cm', 'big' => 1],
       ['en' => 'Height in metres', 'f' => '(v.f*12 + v.i)*0.0254', 'd' => 3, 'u' => 'm'],
       ['en' => 'cm to feet & inches', 'f' => "floor(v.c/30.48) + ' ft ' + (v.c/2.54 - floor(v.c/30.48)*12).toFixed(1) + ' in'"],
     ]],

    ['slug' => 'pet-age-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'dog',
     'en' => 'Dog & Cat Age in Human Years Calculator',
     'de' => 'Convert your dog\'s or cat\'s age into human years by size, using the vets\' formula instead of the old "×7" rule.',
     'kw' => ['dog age calculator', 'dog years to human years', 'cat age calculator', 'pet age in human years', 'how old is my dog in human years'],
     'in' => [
       ['k' => 'p', 'en' => 'Pet', 'v' => 2, 'opts' => [[1, 'Small dog (under 10 kg)'], [2, 'Medium dog (10–25 kg)'], [3, 'Large dog (over 25 kg)'], [4, 'Cat']]],
       ['k' => 'a', 'en' => 'Pet\'s age (years)', 'v' => 5],
     ],
     'out' => [
       ['en' => 'Age in human years', 'f' => $petAge, 'd' => 0, 'u' => 'years', 'big' => 1],
       ['en' => 'Life stage', 'f' => "($petAge) < 18 ? 'Puppy / kitten – young' : (($petAge) < 45 ? 'Adult' : (($petAge) < 65 ? 'Mature' : 'Senior'))"],
     ]],

    ['slug' => 'calories-burned-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'flame',
     'en' => 'Calories Burned Calculator (Walking, Running, Yoga, Cycling)',
     'de' => 'Estimate calories burned during walking, running, cycling, yoga, skipping, swimming, dancing or housework from your weight and time.',
     'kw' => ['calories burned calculator', 'walking calories calculator', 'calories burned running', 'how many calories does yoga burn', 'calories burned skipping', 'exercise calorie calculator'],
     'in' => [
       ['k' => 'met', 'en' => 'Activity', 'v' => 4.3, 'opts' => [[3.5, 'Walking – normal pace'], [4.3, 'Walking – brisk'], [7, 'Jogging'], [9.8, 'Running (10 km/h)'], [7.5, 'Cycling – moderate'], [2.5, 'Yoga – hatha'], [4, 'Yoga – power / surya namaskar'], [12.3, 'Skipping rope'], [6, 'Swimming – leisure'], [5.5, 'Dancing / garba'], [6, 'Gym – weight training'], [8, 'Badminton / football'], [5, 'Cricket'], [3.3, 'Housework – cleaning'], [8, 'Climbing stairs']]],
       ['k' => 'w', 'en' => 'Your weight', 'v' => 70, 'u' => 'kg'],
       ['k' => 'm', 'en' => 'Duration', 'v' => 30, 'u' => 'min'],
     ],
     'out' => [
       ['en' => 'Calories burned', 'f' => 'v.met*3.5*v.w/200*v.m', 'fmt' => 'int', 'u' => 'kcal', 'big' => 1],
       ['en' => 'Per hour', 'f' => 'v.met*3.5*v.w/200*60', 'fmt' => 'int', 'u' => 'kcal'],
       ['en' => 'Minutes to burn 500 kcal', 'f' => '500/(v.met*3.5*v.w/200)', 'fmt' => 'int', 'u' => 'min'],
     ],
     'how_en' => 'Calories = MET × 3.5 × body weight (kg) ÷ 200 × minutes. MET values come from the Compendium of Physical Activities. Real burn depends on fitness, intensity and body composition.'],

    ['slug' => 'blood-donation-eligibility-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'heart-pulse',
     'en' => 'Blood Donation Next Date Calculator',
     'de' => 'Find when you can donate blood again — 90 days for men and 120 days for women after whole blood donation, as per Indian guidelines.',
     'kw' => ['blood donation gap', 'when can i donate blood again', 'blood donation eligibility', 'next blood donation date', 'blood donation interval india'],
     'in' => [
       ['k' => 'l', 'en' => 'Last donation date', 'v' => '2026-09-01', 't' => 'date'],
       ['k' => 'g', 'en' => 'Donor', 'v' => 90, 'opts' => [[90, 'Male (90 days)'], [120, 'Female (120 days)'], [3, 'Platelets / plasma (approx. 3 days, as advised)']]],
     ],
     'out' => [
       ['en' => 'You can donate again on', 'f' => 'addDays(v.l, v.g)', 'big' => 1],
       ['en' => 'Status', 'f' => "days(v.l, today()) >= v.g ? '✅ You are eligible now' : '⏳ ' + (v.g - days(v.l, today())) + ' days left'"],
     ],
     'faq' => [['Who can donate blood in India?', 'Healthy people aged 18–65, weighing at least 45–50 kg with haemoglobin 12.5 g/dL or more can usually donate. The blood bank will check you before donation.']]],

    ['slug' => 'password-strength-checker', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'shield-check',
     'en' => 'Password Strength Checker',
     'de' => 'Check how strong your password is and how long it would take to crack — checked inside your browser, never sent anywhere.',
     'kw' => ['password strength checker', 'how strong is my password', 'password checker', 'password security test', 'strong password check'],
     'in' => [['k' => 'p', 'en' => 'Password', 'v' => 'Krishna@2026', 't' => 'text', 'full' => 1]],
     'out' => [
       ['en' => 'Strength', 'f' => "($pwScore) < 28 ? '🔴 Very weak' : (($pwScore) < 36 ? '🟠 Weak' : (($pwScore) < 60 ? '🟡 Medium' : (($pwScore) < 80 ? '🟢 Strong' : '🟢 Very strong')))", 'big' => 1],
       ['en' => 'Time to crack (fast offline attack)', 'f' => $pwCrack],
       ['en' => 'Entropy', 'f' => $pwScore, 'd' => 0, 'u' => 'bits'],
       ['en' => 'Length', 'f' => 'String(v.p).length', 'fmt' => 'int', 'u' => 'characters'],
       ['en' => 'Tip', 'f' => "String(v.p).length < 12 ? 'Use at least 12 characters with upper & lower case, numbers and symbols.' : 'Good length. Avoid names, birthdays and common words.'"],
     ]],

    ['slug' => 'kisan-vikas-patra-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'sprout',
     'en' => 'Kisan Vikas Patra (KVP) Calculator',
     'de' => 'See when your Kisan Vikas Patra investment doubles and the maturity date, at the current post office interest rate.',
     'kw' => ['kvp calculator', 'kisan vikas patra calculator', 'kvp maturity period', 'kvp interest rate 2026', 'post office money double scheme'],
     'in' => [
       ['k' => 'a', 'en' => 'Amount invested', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (p.a., compounded yearly)', 'v' => 7.5, 'u' => '%'],
       ['k' => 'd', 'en' => 'Date of investment', 'v' => 'today', 't' => 'date'],
     ],
     'out' => [
       ['en' => 'Maturity amount', 'f' => 'v.a*2', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Money doubles in', 'f' => "round($kvpM) + ' months (' + floor(round($kvpM)/12) + ' years ' + (round($kvpM) % 12) + ' months)'"],
       ['en' => 'Maturity date', 'f' => "(function(){var d=new Date(v.d);if(isNaN(d))return '—';d.setMonth(d.getMonth()+Math.round($kvpM));return fmtDate(d);})()"],
     ],
     'how_en' => 'KVP doubles your money. Months to double = ln(2) ÷ ln(1 + rate) × 12. At 7.5% that is 115 months. Check the latest post office rate, which the government revises every quarter.'],

    ['slug' => 'nsc-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'landmark',
     'en' => 'NSC Calculator (National Savings Certificate)',
     'de' => 'Calculate the 5-year maturity value and interest of a post office National Savings Certificate.',
     'kw' => ['nsc calculator', 'national savings certificate calculator', 'nsc interest rate', 'nsc maturity value', 'post office nsc'],
     'in' => [
       ['k' => 'a', 'en' => 'Amount invested', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate (compounded yearly)', 'v' => 7.7, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Maturity value after 5 years', 'f' => 'v.a*pow(1 + v.r/100, 5)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total interest', 'f' => 'v.a*pow(1 + v.r/100, 5) - v.a', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Interest in year 1', 'f' => 'v.a*v.r/100', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'net-worth-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'wallet-cards',
     'en' => 'Net Worth Calculator',
     'de' => 'Add up everything you own and owe to find your net worth and debt ratio.',
     'kw' => ['net worth calculator', 'how to calculate net worth', 'personal net worth', 'assets and liabilities calculator'],
     'in' => [
       ['k' => 'cash', 'en' => 'Cash & bank balance', 'v' => 50000, 'u' => '₹'], ['k' => 'fd', 'en' => 'FD / RD / PPF / PF', 'v' => 300000, 'u' => '₹'],
       ['k' => 'mf', 'en' => 'Mutual funds & shares', 'v' => 200000, 'u' => '₹'], ['k' => 'gold', 'en' => 'Gold & silver', 'v' => 250000, 'u' => '₹'],
       ['k' => 'prop', 'en' => 'Property (current value)', 'v' => 2500000, 'u' => '₹'], ['k' => 'veh', 'en' => 'Vehicles', 'v' => 300000, 'u' => '₹'],
       ['k' => 'oa', 'en' => 'Other assets', 'v' => 0, 'u' => '₹'], ['k' => 'hl', 'en' => 'Home loan outstanding', 'v' => 1500000, 'u' => '₹'],
       ['k' => 'cl', 'en' => 'Car / bike loan', 'v' => 150000, 'u' => '₹'], ['k' => 'pl', 'en' => 'Personal / gold loan', 'v' => 0, 'u' => '₹'],
       ['k' => 'cc', 'en' => 'Credit card dues', 'v' => 20000, 'u' => '₹'], ['k' => 'ol', 'en' => 'Other loans', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Net worth', 'f' => "$nw_a - $nw_l", 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total assets', 'f' => $nw_a, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Total liabilities', 'f' => $nw_l, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Debt to assets', 'f' => "$nw_a ? $nw_l/$nw_a*100 : 0", 'fmt' => 'pct'],
     ]],


    ['slug' => 'interest-calculator-between-dates', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'calendar-range',
     'en' => 'Interest Calculator Between Two Dates (Vyaj by Date)',
     'de' => 'Calculate simple interest on money lent or borrowed between two dates, at a monthly rate (like 1% or ₹1 per ₹100 per month) or a yearly rate.',
     'kw' => ['interest calculator between two dates', 'vyaj calculator', 'interest calculation by date', 'byaj calculator', 'monthly interest calculator', 'interest per 100 rupees per month', 'udhar interest calculator'],
     'in' => [
       ['k' => 'p', 'en' => 'Principal amount', 'v' => 100000, 'u' => '₹'],
       ['k' => 'r', 'en' => 'Interest rate', 'v' => 1.5],
       ['k' => 'rt', 'en' => 'Rate is', 'v' => 12, 'opts' => [[12, 'Per month (₹ per ₹100 per month)'], [1, 'Per year (% p.a.)']]],
       ['k' => 'a', 'en' => 'From date', 'v' => '2026-01-01', 't' => 'date'],
       ['k' => 'b', 'en' => 'To date', 'v' => 'today', 't' => 'date'],
     ],
     'out' => [
       ['en' => 'Interest', 'f' => 'v.p*v.r*v.rt/100*days(v.a, v.b)/365', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total to repay', 'f' => 'v.p + v.p*v.r*v.rt/100*days(v.a, v.b)/365', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Time', 'f' => "ymd(v.a, v.b) + ' (' + days(v.a, v.b) + ' days)'"],
       ['en' => 'Interest per month', 'f' => 'v.p*v.r*v.rt/100/12', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Yearly rate', 'f' => 'v.r*v.rt', 'fmt' => 'pct'],
     ],
     'how_en' => 'Interest = principal × yearly rate × days ÷ 365. A monthly rate of 1.5 (₹1.50 per ₹100 per month) equals 18% per year. This is simple interest; for interest on interest use the Compound Interest Calculator.'],

    ['slug' => 'gold-karat-price-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'coins',
     'en' => 'Gold Rate Calculator – 24K to 22K, 18K, 14K Price',
     'de' => 'Convert today\'s 24 carat gold rate into 22K, 18K and 14K rates and find the price of your jewellery weight with making charges and 3% GST.',
     'kw' => ['22 carat gold rate from 24 carat', 'gold rate calculator', 'gold price calculator 22k', '18k gold price', 'gold karat calculator', 'gold price per gram today calculator'],
     'in' => [
       ['k' => 'r', 'en' => '24K gold rate per 10 grams', 'v' => 130000, 'u' => '₹'],
       ['k' => 'k', 'en' => 'Purity', 'v' => 22, 'opts' => [[24, '24K (99.9%)'], [22, '22K (91.6%)'], [18, '18K (75%)'], [14, '14K (58.5%)']]],
       ['k' => 'g', 'en' => 'Weight', 'v' => 10, 'u' => 'g'],
       ['k' => 'mk', 'en' => 'Making charges', 'v' => 10, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'Final price (with making + 3% GST)', 'f' => 'v.r/10*v.k/24*v.g*(1 + v.mk/100)*1.03', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Rate per gram for this purity', 'f' => 'v.r/10*v.k/24', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Gold value', 'f' => 'v.r/10*v.k/24*v.g', 'fmt' => 'inr', 'd' => 0],
       ['en' => '22K rate per 10 g', 'f' => 'v.r*22/24', 'fmt' => 'inr', 'd' => 0],
       ['en' => '18K rate per 10 g', 'f' => 'v.r*18/24', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'percentage-to-cgpa-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'graduation-cap',
     'en' => 'Percentage to CGPA Calculator',
     'de' => 'Convert your percentage into CGPA on a 10-point scale using the CBSE formula (÷ 9.5) or your university\'s factor.',
     'kw' => ['percentage to cgpa', 'percentage to cgpa calculator', 'convert percentage to cgpa', 'percentage to gpa', 'cbse percentage to cgpa'],
     'in' => [['k' => 'p', 'en' => 'Percentage', 'v' => 82, 'u' => '%'], ['k' => 'f', 'en' => 'Conversion factor', 'v' => 9.5, 'opts' => [[9.5, 'CBSE / most universities (÷ 9.5)'], [10, 'Divide by 10'], [9.25, 'Some universities (÷ 9.25)']]]],
     'out' => [['en' => 'CGPA', 'f' => 'min(10, v.p/v.f)', 'd' => 2, 'big' => 1], ['en' => 'On 4.0 GPA scale (approx.)', 'f' => 'min(4, v.p/v.f*0.4)', 'd' => 2]]],

    ['slug' => 'gst-number-validator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'badge-check',
     'en' => 'GST Number (GSTIN) Validator & Format Checker',
     'de' => 'Check whether a 15-digit GSTIN is in the correct format with a valid check digit, and see the state and PAN inside it.',
     'kw' => ['gst number check', 'gstin validator', 'gst number format', 'verify gst number', 'gstin check digit', 'gst number search'],
     'in' => [['k' => 'g', 'en' => 'GSTIN (15 characters)', 'v' => '24AAAAA0000A1Z5', 't' => 'text', 'full' => 1]],
     'out' => [
       ['en' => 'Result', 'f' => "(function(g){g=String(g||'').toUpperCase().replace(/\\s/g,'');if(!/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/.test(g))return '❌ Wrong format (should be like 24ABCDE1234F1Z5)';var C='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',s=0;for(var i=0;i<14;i++){var p=C.indexOf(g[i])*(i%2?2:1);s+=Math.floor(p/36)+p%36;}return C[(36-s%36)%36]===g[14]?'✅ Valid format and check digit':'❌ Check digit does not match – typing mistake?';})(v.g)", 'big' => 1],
       ['en' => 'State', 'f' => "({'01':'Jammu & Kashmir','02':'Himachal Pradesh','03':'Punjab','04':'Chandigarh','05':'Uttarakhand','06':'Haryana','07':'Delhi','08':'Rajasthan','09':'Uttar Pradesh','10':'Bihar','11':'Sikkim','12':'Arunachal Pradesh','13':'Nagaland','14':'Manipur','15':'Mizoram','16':'Tripura','17':'Meghalaya','18':'Assam','19':'West Bengal','20':'Jharkhand','21':'Odisha','22':'Chhattisgarh','23':'Madhya Pradesh','24':'Gujarat','26':'Dadra & Nagar Haveli and Daman & Diu','27':'Maharashtra','29':'Karnataka','30':'Goa','31':'Lakshadweep','32':'Kerala','33':'Tamil Nadu','34':'Puducherry','35':'Andaman & Nicobar','36':'Telangana','37':'Andhra Pradesh','38':'Ladakh','97':'Other territory'})[String(v.g).toUpperCase().slice(0,2)] || '—'"],
       ['en' => 'PAN inside GSTIN', 'f' => "String(v.g).toUpperCase().slice(2,12) || '—'"],
     ],
     'faq' => [['Does a valid format mean the GST number is active?', 'No. This checks the structure and check digit only. To confirm the business name and whether the GSTIN is active, search it on the official GST portal (gst.gov.in → Search Taxpayer).']]],

    ['slug' => 'best-of-five-percentage-calculator', 'cat' => 'everyday', 'type' => 'calc', 'icon' => 'graduation-cap',
     'en' => 'Best of 5 Subjects Percentage Calculator (Board Exams)',
     'de' => 'Calculate your best-five percentage from six subject marks, as used by CBSE and many colleges for admission.',
     'kw' => ['best of five calculator', 'best 5 subjects percentage', 'cbse best of 5 calculator', 'class 12 percentage calculator', 'board exam percentage calculator'],
     'in' => [
       ['k' => 's1', 'en' => 'Subject 1 marks (out of 100)', 'v' => 88], ['k' => 's2', 'en' => 'Subject 2', 'v' => 76],
       ['k' => 's3', 'en' => 'Subject 3', 'v' => 92], ['k' => 's4', 'en' => 'Subject 4', 'v' => 69],
       ['k' => 's5', 'en' => 'Subject 5', 'v' => 81], ['k' => 's6', 'en' => 'Subject 6 (optional)', 'v' => 74],
     ],
     'out' => [
       ['en' => 'Best of 5 percentage', 'f' => "$best5/5", 'fmt' => 'pct', 'big' => 1],
       ['en' => 'Best 5 total (out of 500)', 'f' => $best5, 'fmt' => 'int'],
       ['en' => 'All 6 subjects percentage', 'f' => '(v.s1 + v.s2 + v.s3 + v.s4 + v.s5 + v.s6)/6', 'fmt' => 'pct'],
     ]],
  ],
];
