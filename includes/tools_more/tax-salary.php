<?php
/**
 * KRISHNA TOOLS — Income Tax, Salary & Government Employee tool pack.
 * Tax rules: FY 2025-26 / FY 2026-27 (AY 2026-27 / 2027-28) as per Budget 2025.
 */

// New regime tax on taxable income `ti` (after standard deduction), incl. 87A rebate,
// marginal relief at ₹12 lakh, surcharge (max 25%) and 4% cess.
$taxNew = function (string $ti): string {
    return '(function(ti){ti=Math.max(0,ti);var s=[[400000,0],[800000,.05],[1200000,.10],[1600000,.15],[2000000,.20],[2400000,.25],[1e18,.30]],t=0,p=0;'
        . 'for(var i=0;i<s.length;i++){if(ti>p)t+=(Math.min(ti,s[i][0])-p)*s[i][1];p=s[i][0];}'
        . 'if(ti<=1200000)t=0;else t=Math.min(t,ti-1200000);'
        . 'var sc=ti>2e7?.25:ti>1e7?.15:ti>5e6?.10:0;return Math.round(t*(1+sc)*1.04);})(' . $ti . ')';
};
// Old regime tax on taxable income `ti`, basic exemption by age group `ag` (0 / 60 / 80).
$taxOld = function (string $ti, string $ag): string {
    return '(function(ti,ag){ti=Math.max(0,ti);var ex=ag==80?500000:(ag==60?300000:250000);var s=[[ex,0],[500000,.05],[1000000,.20],[1e18,.30]],t=0,p=0;'
        . 'for(var i=0;i<s.length;i++){if(ti>p&&s[i][0]>p)t+=(Math.min(ti,s[i][0])-p)*s[i][1];p=Math.max(p,s[i][0]);}'
        . 'if(ti<=500000)t=0;var sc=ti>5e7?.37:ti>2e7?.25:ti>1e7?.15:ti>5e6?.10:0;return Math.round(t*(1+sc)*1.04);})(' . $ti . ',' . $ag . ')';
};

$itNewTI = 'v.inc - (v.sal == 1 ? 75000 : 0)';
$itOldTI = 'v.inc - (v.sal == 1 ? 50000 : 0) - min(v.d80c, 150000) - min(v.d80d, 100000) - v.hra - min(v.hl, 200000) - v.oth';
$itNew = $taxNew($itNewTI);
$itOld = $taxOld($itOldTI, 'v.age');

// Salary in hand
$shBasic = '(v.ctc*v.bp/100)';
$shErPF = '(v.pf == 1 ? min(' . $shBasic . '/12, 15000)*0.12*12 : (v.pf == 2 ? ' . $shBasic . '*0.12 : 0))';
$shGrat = '(v.gr == 1 ? ' . $shBasic . '*0.0481 : 0)';
$shGross = '(v.ctc - ' . $shErPF . ' - ' . $shGrat . ')';
$shTax = $taxNew($shGross . ' - 75000');
$shNet = '(' . $shGross . ' - ' . $shErPF . ' - v.pt*12 - ' . $shTax . ')';

// Capital gains on listed shares / equity mutual funds
$cgGain = '(v.sell - v.buy - v.exp)';
$cgTax = '(v.m < 12 ? max(0, ' . $cgGain . ')*0.20 : max(0, ' . $cgGain . ' - v.used)*0.125)*1.04';

// 80C
$c80 = '(v.epf + v.ppf + v.elss + v.lic + v.tui + v.hlp + v.ssy + v.oth)';

$retire = '(function(){var d=new Date(v.dob);if(isNaN(d))return "—";var y=d.getFullYear()+v.ra,m=d.getMonth();var r=d.getDate()==1?new Date(y,m,0):new Date(y,m+1,0);return r;})()';

return [
  'cats' => [
    ['slug' => 'tax', 'name_en' => 'Income Tax & Salary', 'name_gu' => 'ઇન્કમ ટેક્સ અને પગાર', 'icon' => 'landmark', 'color' => '#9333EA', 'sort_order' => 11],
  ],
  'tools' => [

    ['slug' => 'income-tax-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'landmark',
     'en' => 'Income Tax Calculator FY 2026-27 (New vs Old Regime)',
     'de' => 'Calculate your income tax for FY 2026-27 under the new and old regime side by side, with the ₹12 lakh rebate, standard deduction, 80C, 80D, HRA and home loan interest, and see which regime saves you more.',
     'kw' => ['income tax calculator', 'income tax calculator 2026-27', 'new vs old tax regime calculator', 'tax calculator india', 'salary tax calculator', 'income tax calculator ay 2027-28', 'how much tax on 12 lakh salary', 'tax on 15 lakh salary new regime'],
     'in' => [
       ['k' => 'inc', 'en' => 'Total annual income (gross)', 'v' => 1500000, 'u' => '₹'],
       ['k' => 'sal', 'en' => 'Income type', 'v' => 1, 'opts' => [[1, 'Salary / pension (standard deduction)'], [0, 'Business / other income']]],
       ['k' => 'age', 'en' => 'Age group (old regime)', 'v' => 0, 'opts' => [[0, 'Below 60 years'], [60, '60 to 79 years (senior)'], [80, '80+ years (super senior)']]],
       ['k' => 'd80c', 'en' => '80C investments (PF, PPF, LIC, ELSS…) – old regime', 'v' => 150000, 'u' => '₹'],
       ['k' => 'd80d', 'en' => '80D health insurance – old regime', 'v' => 25000, 'u' => '₹'],
       ['k' => 'hra', 'en' => 'HRA exemption – old regime', 'v' => 0, 'u' => '₹'],
       ['k' => 'hl', 'en' => 'Home loan interest 24(b) – old regime', 'v' => 0, 'u' => '₹'],
       ['k' => 'oth', 'en' => 'Other deductions (80CCD(1B), 80E, 80G…) – old regime', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Tax under NEW regime', 'f' => $itNew, 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Tax under OLD regime', 'f' => $itOld, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Better for you', 'f' => "($itNew) <= ($itOld) ? 'New regime' : 'Old regime'"],
       ['en' => 'You save', 'f' => "abs(($itNew) - ($itOld))", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Monthly tax (TDS) – new regime', 'f' => "($itNew)/12", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Taxable income – new regime', 'f' => "max(0, $itNewTI)", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Taxable income – old regime', 'f' => "max(0, $itOldTI)", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Effective tax rate (new)', 'f' => "v.inc ? ($itNew)/v.inc*100 : 0", 'fmt' => 'pct', 'd' => 2],
     ],
     'how_en' => 'New regime slabs: up to ₹4 lakh nil, ₹4–8 lakh 5%, ₹8–12 lakh 10%, ₹12–16 lakh 15%, ₹16–20 lakh 20%, ₹20–24 lakh 25%, above ₹24 lakh 30%. Salaried people get a ₹75,000 standard deduction, and the Section 87A rebate makes tax nil up to ₹12 lakh taxable income (₹12.75 lakh salary), with marginal relief just above it. Old regime: ₹2.5 lakh nil (₹3 lakh for seniors, ₹5 lakh for 80+), 5%, 20% and 30% slabs, ₹50,000 standard deduction and rebate up to ₹5 lakh. 4% health & education cess and surcharge above ₹50 lakh are added.',
     'faq' => [
       ['Is income up to ₹12 lakh tax free?', 'Yes, in the new regime. Taxable income up to ₹12 lakh gets a full rebate under Section 87A. A salaried person also gets the ₹75,000 standard deduction, so a salary up to ₹12.75 lakh pays zero tax.'],
       ['Which regime is better for me?', 'If your deductions (80C, 80D, HRA, home loan interest) are small, the new regime is almost always better. The old regime wins only when your total deductions are large — usually above ₹4–5 lakh at higher incomes. Enter your numbers above to compare.'],
       ['Does this include special-rate income like capital gains?', 'No. Capital gains on shares, lottery winnings etc. are taxed at special rates. Use our Capital Gains Tax Calculator for shares and mutual funds.'],
     ]],

    ['slug' => 'salary-in-hand-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'wallet',
     'en' => 'Salary Calculator – CTC to In-Hand (Take Home) Salary',
     'de' => 'Convert your annual CTC into monthly in-hand salary after PF, gratuity, professional tax and income tax (new regime, FY 2026-27).',
     'kw' => ['ctc to in hand salary calculator', 'take home salary calculator', 'in hand salary calculator', 'salary calculator india', 'net salary calculator', 'monthly salary from ctc', '10 lpa in hand salary', 'ctc calculator'],
     'in' => [
       ['k' => 'ctc', 'en' => 'Annual CTC', 'v' => 1000000, 'u' => '₹'],
       ['k' => 'bp', 'en' => 'Basic salary as % of CTC', 'v' => 50, 'u' => '%'],
       ['k' => 'pf', 'en' => 'Provident fund (PF)', 'v' => 1, 'opts' => [[1, '12% of basic, capped at ₹15,000 basic (₹1,800/month)'], [2, '12% of full basic'], [0, 'No PF']]],
       ['k' => 'gr', 'en' => 'Gratuity included in CTC?', 'v' => 1, 'opts' => [[1, 'Yes (4.81% of basic)'], [0, 'No']]],
       ['k' => 'pt', 'en' => 'Professional tax per month', 'v' => 200, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Monthly in-hand salary', 'f' => "$shNet/12", 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Yearly in-hand salary', 'f' => $shNet, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Gross salary (yearly)', 'f' => $shGross, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Your PF deduction (monthly)', 'f' => "$shErPF/12", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Income tax (monthly TDS)', 'f' => "$shTax/12", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Employer PF + gratuity (yearly)', 'f' => "$shErPF + $shGrat", 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'Gross salary = CTC − employer PF − gratuity. In-hand = gross − your own PF (same as employer PF) − professional tax − income tax. Tax uses the new regime with the ₹75,000 standard deduction and the ₹12 lakh rebate. Real payslips vary with allowances, bonus and company policy.',
     'faq' => [
       ['What is the in-hand salary for 10 LPA?', 'With a 50% basic, capped PF and gratuity in CTC, ₹10 lakh CTC gives roughly ₹77,000–₹78,000 per month in hand, and no income tax in the new regime. Put your own numbers above for an exact estimate.'],
       ['Why is in-hand much lower than CTC ÷ 12?', 'CTC includes the employer\'s PF and gratuity, which you don\'t get monthly, plus your own PF, professional tax and income tax are deducted from gross salary.'],
     ]],

    ['slug' => 'salary-hike-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'trending-up',
     'en' => 'Salary Hike / Increment Calculator',
     'de' => 'Find your new salary after a percentage hike, or the hike percentage between your current and offered salary.',
     'kw' => ['salary hike calculator', 'increment calculator', 'salary increase percentage calculator', 'hike percentage calculator', 'appraisal hike calculator', 'new salary after increment'],
     'in' => [
       ['k' => 'cur', 'en' => 'Current salary (monthly or yearly)', 'v' => 40000, 'u' => '₹'],
       ['k' => 'p', 'en' => 'Hike percentage', 'v' => 10, 'u' => '%'],
       ['k' => 'nw', 'en' => 'Offered new salary (optional, to find hike %)', 'v' => 50000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'New salary after hike', 'f' => 'v.cur*(1 + v.p/100)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Increase amount', 'f' => 'v.cur*v.p/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Hike % for offered salary', 'f' => 'v.cur ? (v.nw - v.cur)/v.cur*100 : 0', 'fmt' => 'pct', 'd' => 2],
       ['en' => 'Extra per year (if monthly figures)', 'f' => 'v.cur*v.p/100*12', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'per-day-salary-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'calendar-days',
     'en' => 'Per Day Salary & Attendance Salary Calculator',
     'de' => 'Work out per-day pay and the salary to pay for days present, half days and leave — for shops, offices and staff.',
     'kw' => ['per day salary calculator', 'salary calculator by days', 'attendance salary calculator', 'salary for days worked', 'one day salary calculation', 'salary deduction for leave calculator'],
     'in' => [
       ['k' => 's', 'en' => 'Monthly salary', 'v' => 15000, 'u' => '₹'],
       ['k' => 'dm', 'en' => 'Count salary on', 'v' => 30, 'opts' => [[30, '30 days'], [31, '31 days'], [28, '28 days'], [26, '26 working days']]],
       ['k' => 'p', 'en' => 'Days present (incl. paid holidays)', 'v' => 27],
       ['k' => 'h', 'en' => 'Half days', 'v' => 1],
       ['k' => 'adv', 'en' => 'Advance / deduction', 'v' => 0, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Salary payable', 'f' => 'v.s/v.dm*(v.p + v.h/2) - v.adv', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Per day salary', 'f' => 'v.s/v.dm', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Days paid', 'f' => 'v.p + v.h/2', 'd' => 1],
       ['en' => 'Cut for absent days', 'f' => 'max(0, v.s - v.s/v.dm*(v.p + v.h/2))', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'overtime-pay-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'clock',
     'en' => 'Overtime (OT) Pay Calculator',
     'de' => 'Calculate hourly wage and overtime payment at double rate (Factories Act) or any rate you choose.',
     'kw' => ['overtime calculator', 'ot calculation formula', 'overtime pay calculator india', 'double overtime calculator', 'ot salary calculator'],
     'in' => [
       ['k' => 's', 'en' => 'Monthly salary (basic + DA)', 'v' => 18000, 'u' => '₹'],
       ['k' => 'd', 'en' => 'Working days per month', 'v' => 26],
       ['k' => 'h', 'en' => 'Normal hours per day', 'v' => 8],
       ['k' => 'ot', 'en' => 'Overtime hours this month', 'v' => 20],
       ['k' => 'x', 'en' => 'OT rate', 'v' => 2, 'opts' => [[2, '2× (Factories Act)'], [1.5, '1.5×'], [1, '1× (same rate)']]],
     ],
     'out' => [
       ['en' => 'Overtime pay', 'f' => 'v.s/(v.d*v.h)*v.x*v.ot', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Normal hourly wage', 'f' => 'v.s/(v.d*v.h)', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'OT hourly rate', 'f' => 'v.s/(v.d*v.h)*v.x', 'fmt' => 'inr', 'd' => 2],
       ['en' => 'Total salary with OT', 'f' => 'v.s + v.s/(v.d*v.h)*v.x*v.ot', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'hourly-to-monthly-salary-converter', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'repeat',
     'en' => 'Hourly to Monthly & Yearly Salary Converter',
     'de' => 'Convert an hourly rate into daily, weekly, monthly and yearly income — useful for freelancers and part-time jobs.',
     'kw' => ['hourly to monthly salary', 'hourly rate calculator', 'hourly to annual salary', 'freelance rate calculator', 'per hour salary calculator'],
     'in' => [
       ['k' => 'r', 'en' => 'Hourly rate', 'v' => 300, 'u' => '₹'],
       ['k' => 'h', 'en' => 'Hours per day', 'v' => 8],
       ['k' => 'd', 'en' => 'Days per week', 'v' => 6],
     ],
     'out' => [
       ['en' => 'Monthly income', 'f' => 'v.r*v.h*v.d*52/12', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Daily', 'f' => 'v.r*v.h', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Weekly', 'f' => 'v.r*v.h*v.d', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Yearly', 'f' => 'v.r*v.h*v.d*52', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'leave-encashment-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'calendar-x',
     'en' => 'Leave Encashment Calculator',
     'de' => 'Calculate the amount you get for unused earned leave at resignation or retirement, and the tax-free limit.',
     'kw' => ['leave encashment calculator', 'earned leave encashment formula', 'leave encashment calculation', 'el encashment', 'leave encashment tax exemption'],
     'in' => [
       ['k' => 's', 'en' => 'Last drawn basic + DA (monthly)', 'v' => 30000, 'u' => '₹'],
       ['k' => 'l', 'en' => 'Leave balance (days)', 'v' => 45],
       ['k' => 'dv', 'en' => 'Divide monthly salary by', 'v' => 30, 'opts' => [[30, '30 days (government / most companies)'], [26, '26 working days']]],
     ],
     'out' => [
       ['en' => 'Leave encashment amount', 'f' => 'v.s/v.dv*v.l', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Per day salary', 'f' => 'v.s/v.dv', 'fmt' => 'inr', 'd' => 2],
     ],
     'how_en' => 'Leave encashment = (basic + DA) ÷ 30 (or 26) × leave days. At retirement, government employees get it fully tax free; private employees get up to ₹25 lakh tax free in their lifetime (Section 10(10AA)). Encashment while still in service is fully taxable.'],

    ['slug' => 'notice-period-buyout-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'log-out',
     'en' => 'Notice Period Buyout Calculator',
     'de' => 'Find how much you must pay (or will be deducted) to leave a job before completing your notice period.',
     'kw' => ['notice period buyout calculator', 'notice period recovery calculation', 'notice pay calculator', 'notice period amount'],
     'in' => [
       ['k' => 's', 'en' => 'Monthly salary used for recovery (basic or gross)', 'v' => 50000, 'u' => '₹'],
       ['k' => 'n', 'en' => 'Notice period (days)', 'v' => 60],
       ['k' => 'w', 'en' => 'Days you will serve', 'v' => 30],
     ],
     'out' => [
       ['en' => 'Buyout amount', 'f' => 'max(0, v.n - v.w)*v.s/30', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Days short', 'f' => 'max(0, v.n - v.w)', 'fmt' => 'int'],
       ['en' => 'Per day salary', 'f' => 'v.s/30', 'fmt' => 'inr', 'd' => 2],
     ]],

    ['slug' => 'bonus-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'gift',
     'en' => 'Bonus Calculator (Payment of Bonus Act / Diwali Bonus)',
     'de' => 'Calculate statutory bonus at 8.33% to 20% under the Payment of Bonus Act, with wage ceiling and months worked.',
     'kw' => ['bonus calculator', 'diwali bonus calculation', 'payment of bonus act calculator', '8.33 bonus calculation', 'statutory bonus calculator'],
     'in' => [
       ['k' => 'w', 'en' => 'Monthly basic + DA', 'v' => 15000, 'u' => '₹'],
       ['k' => 'c', 'en' => 'Wage ceiling (₹7,000 or state minimum wage, whichever is higher)', 'v' => 7000, 'u' => '₹'],
       ['k' => 'p', 'en' => 'Bonus %', 'v' => 8.33, 'u' => '%'],
       ['k' => 'm', 'en' => 'Months worked in the year', 'v' => 12],
     ],
     'out' => [
       ['en' => 'Bonus payable', 'f' => 'min(v.w, v.c)*v.m*v.p/100', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Eligible under the Act?', 'f' => "v.w <= 21000 ? 'Yes (wage up to ₹21,000)' : 'No — wage above ₹21,000 (company may still pay)'"],
       ['en' => 'Bonus on full wage (if company pays)', 'f' => 'v.w*v.m*v.p/100', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'capital-gains-tax-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'chart-line',
     'en' => 'Capital Gains Tax Calculator for Shares & Mutual Funds (STCG / LTCG)',
     'de' => 'Calculate short-term (20%) and long-term (12.5% above ₹1.25 lakh) capital gains tax on listed shares and equity mutual funds.',
     'kw' => ['capital gains tax calculator', 'ltcg calculator', 'stcg tax calculator', 'mutual fund tax calculator', 'share profit tax calculator', 'ltcg on shares 12.5'],
     'in' => [
       ['k' => 'buy', 'en' => 'Total buy value', 'v' => 200000, 'u' => '₹'],
       ['k' => 'sell', 'en' => 'Total sell value', 'v' => 350000, 'u' => '₹'],
       ['k' => 'exp', 'en' => 'Brokerage & expenses', 'v' => 0, 'u' => '₹'],
       ['k' => 'm', 'en' => 'Holding period (months)', 'v' => 18],
       ['k' => 'used', 'en' => 'LTCG exemption available this year', 'v' => 125000, 'u' => '₹'],
     ],
     'out' => [
       ['en' => 'Estimated tax (incl. 4% cess)', 'f' => $cgTax, 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Capital gain', 'f' => $cgGain, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Type', 'f' => "v.m < 12 ? 'Short term (STCG @ 20%)' : 'Long term (LTCG @ 12.5%)'"],
       ['en' => 'Profit after tax', 'f' => "$cgGain - $cgTax", 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'For listed equity shares and equity mutual funds sold on or after 23 July 2024: held under 12 months = STCG taxed at 20%; held 12 months or more = LTCG taxed at 12.5% on gains above ₹1.25 lakh a year. 4% cess is added; surcharge is not included.'],

    ['slug' => 'advance-tax-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'calendar-clock',
     'en' => 'Advance Tax Calculator & Due Dates',
     'de' => 'Split your estimated tax into the four advance tax instalments due on 15 June, 15 September, 15 December and 15 March.',
     'kw' => ['advance tax calculator', 'advance tax due dates', 'advance tax installments', 'advance tax for freelancers'],
     'in' => [
       ['k' => 't', 'en' => 'Estimated total tax for the year', 'v' => 120000, 'u' => '₹'],
       ['k' => 'tds', 'en' => 'TDS / TCS already deducted', 'v' => 20000, 'u' => '₹'],
       ['k' => 'pg', 'en' => 'Presumptive income (44AD / 44ADA)?', 'v' => 0, 'opts' => [[0, 'No – pay in 4 instalments'], [1, 'Yes – pay all by 15 March']]],
     ],
     'out' => [
       ['en' => 'Advance tax payable', 'f' => 'max(0, v.t - v.tds)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Is advance tax needed?', 'f' => "max(0, v.t - v.tds) >= 10000 ? 'Yes (₹10,000 or more)' : 'No (below ₹10,000)'"],
       ['en' => 'By 15 June (15%)', 'f' => 'v.pg ? 0 : max(0, v.t - v.tds)*0.15', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'By 15 September (45% total)', 'f' => 'v.pg ? 0 : max(0, v.t - v.tds)*0.30', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'By 15 December (75% total)', 'f' => 'v.pg ? 0 : max(0, v.t - v.tds)*0.30', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'By 15 March (100%)', 'f' => 'v.pg ? max(0, v.t - v.tds) : max(0, v.t - v.tds)*0.25', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => '80c-deduction-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'piggy-bank',
     'en' => 'Section 80C Deduction Calculator & Tax Saving Planner',
     'de' => 'Add up your 80C investments, see how much of the ₹1.5 lakh limit is left and how much tax you save in the old regime.',
     'kw' => ['80c calculator', '80c deduction limit', 'tax saving calculator', 'how to save tax under 80c', '80c investment planner'],
     'in' => [
       ['k' => 'epf', 'en' => 'EPF / VPF (your share)', 'v' => 21600, 'u' => '₹'],
       ['k' => 'ppf', 'en' => 'PPF', 'v' => 50000, 'u' => '₹'],
       ['k' => 'elss', 'en' => 'ELSS mutual funds', 'v' => 0, 'u' => '₹'],
       ['k' => 'lic', 'en' => 'Life insurance premium', 'v' => 20000, 'u' => '₹'],
       ['k' => 'tui', 'en' => 'Children\'s tuition fees', 'v' => 0, 'u' => '₹'],
       ['k' => 'hlp', 'en' => 'Home loan principal', 'v' => 0, 'u' => '₹'],
       ['k' => 'ssy', 'en' => 'Sukanya Samriddhi / NSC / 5-yr FD', 'v' => 0, 'u' => '₹'],
       ['k' => 'oth', 'en' => 'Other 80C', 'v' => 0, 'u' => '₹'],
       ['k' => 'sl', 'en' => 'Your tax slab (old regime)', 'v' => 20, 'opts' => [[5, '5%'], [20, '20%'], [30, '30%']]],
     ],
     'out' => [
       ['en' => 'Deduction allowed', 'f' => "min($c80, 150000)", 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Total invested', 'f' => $c80, 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Limit still left', 'f' => "max(0, 150000 - $c80)", 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Tax saved (incl. cess)', 'f' => "min($c80, 150000)*v.sl/100*1.04", 'fmt' => 'inr', 'd' => 0],
     ],
     'faq' => [['Does 80C work in the new tax regime?', 'No. Section 80C, 80D and most deductions are available only in the old regime. The new regime instead has lower slab rates and a rebate up to ₹12 lakh.']]],

    ['slug' => 'professional-tax-calculator-gujarat', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'building',
     'en' => 'Professional Tax Calculator Gujarat',
     'de' => 'Check professional tax payable on salary in Gujarat per month and per year.',
     'kw' => ['professional tax gujarat', 'professional tax slab gujarat', 'pt deduction calculator', 'professional tax on salary'],
     'in' => [['k' => 's', 'en' => 'Monthly gross salary', 'v' => 25000, 'u' => '₹']],
     'out' => [
       ['en' => 'Professional tax per month', 'f' => 'v.s >= 12000 ? 200 : 0', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Per year', 'f' => 'v.s >= 12000 ? 2400 : 0', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'In Gujarat, salary below ₹12,000 a month pays no professional tax; ₹12,000 and above pays ₹200 a month (₹2,400 a year). Professional tax is deductible from salary income under the old regime.'],

    ['slug' => '8th-pay-commission-salary-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'badge-indian-rupee',
     'en' => '8th Pay Commission Salary Calculator (Fitment Factor)',
     'de' => 'Estimate your new basic pay and salary under the 8th Pay Commission for different fitment factors.',
     'kw' => ['8th pay commission calculator', '8th cpc salary calculator', 'fitment factor calculator', '8th pay commission fitment factor', 'new basic pay 8th pay commission'],
     'in' => [
       ['k' => 'b', 'en' => 'Current basic pay (7th CPC)', 'v' => 35400, 'u' => '₹'],
       ['k' => 'f', 'en' => 'Fitment factor', 'v' => 2.28, 'opts' => [[1.92, '1.92'], [2.08, '2.08'], [2.28, '2.28'], [2.57, '2.57'], [2.86, '2.86']]],
       ['k' => 'hra', 'en' => 'HRA city', 'v' => 18, 'opts' => [[27, 'X city (27%)'], [18, 'Y city (18%)'], [9, 'Z city (9%)']]],
       ['k' => 'da', 'en' => 'DA on new basic (starts near 0%)', 'v' => 0, 'u' => '%'],
     ],
     'out' => [
       ['en' => 'New basic pay (estimate)', 'f' => 'round(v.b*v.f)', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Increase in basic', 'f' => 'v.b*v.f - v.b', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'HRA', 'f' => 'v.b*v.f*v.hra/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Basic + DA + HRA', 'f' => 'v.b*v.f*(1 + v.da/100 + v.hra/100)', 'fmt' => 'inr', 'd' => 0],
     ],
     'how_en' => 'New basic = current basic × fitment factor. The 8th Pay Commission has not announced its final fitment factor; values from 1.92 to 2.86 are the ranges discussed publicly. Treat the result as an estimate only.'],

    ['slug' => 'da-hike-arrears-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'arrow-up-right',
     'en' => 'DA Hike & Arrears Calculator',
     'de' => 'Calculate the monthly increase and arrears from a Dearness Allowance (DA) hike for central and state government employees and pensioners.',
     'kw' => ['da hike calculator', 'da arrears calculator', 'dearness allowance calculator', 'da increase salary calculator', 'dr arrears pensioners'],
     'in' => [
       ['k' => 'b', 'en' => 'Basic pay / basic pension', 'v' => 35400, 'u' => '₹'],
       ['k' => 'o', 'en' => 'Old DA %', 'v' => 55, 'u' => '%'],
       ['k' => 'n', 'en' => 'New DA %', 'v' => 58, 'u' => '%'],
       ['k' => 'm', 'en' => 'Months of arrears', 'v' => 3],
     ],
     'out' => [
       ['en' => 'Monthly increase', 'f' => 'v.b*(v.n - v.o)/100', 'fmt' => 'inr', 'd' => 0, 'big' => 1],
       ['en' => 'Arrears', 'f' => 'v.b*(v.n - v.o)/100*v.m', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'New DA amount per month', 'f' => 'v.b*v.n/100', 'fmt' => 'inr', 'd' => 0],
       ['en' => 'Yearly increase', 'f' => 'v.b*(v.n - v.o)/100*12', 'fmt' => 'inr', 'd' => 0],
     ]],

    ['slug' => 'retirement-date-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'calendar-check',
     'en' => 'Retirement Date Calculator (Government Employees)',
     'de' => 'Find your exact retirement date and remaining service from your date of birth — retirement falls on the last day of the month you reach the age.',
     'kw' => ['retirement date calculator', 'date of retirement calculator', 'superannuation date calculator', 'government employee retirement date', 'remaining service calculator'],
     'in' => [
       ['k' => 'dob', 'en' => 'Date of birth', 'v' => '1970-06-15', 't' => 'date'],
       ['k' => 'ra', 'en' => 'Retirement age', 'v' => 60, 'opts' => [[58, '58'], [60, '60'], [62, '62'], [65, '65']]],
     ],
     'out' => [
       ['en' => 'Retirement date', 'f' => "fmtDate($retire)", 'big' => 1],
       ['en' => 'Service left', 'f' => "($retire) > new Date() ? ymd(today(), $retire) : 'Already retired'"],
       ['en' => 'Days left', 'f' => "max(0, days(today(), ($retire) instanceof Date ? ($retire).toISOString().slice(0,10) : ''))", 'fmt' => 'int'],
     ],
     'how_en' => 'Under central government rules, an employee retires on the afternoon of the last day of the month in which they reach the retirement age. If the birthday is on the 1st of a month, retirement is on the last day of the previous month.'],

    ['slug' => 'govt-job-age-limit-calculator', 'cat' => 'tax', 'type' => 'calc', 'icon' => 'user-check',
     'en' => 'Age Calculator for Government Jobs (Age Limit & Relaxation)',
     'de' => 'Check your exact age on the exam cut-off date and whether you are eligible, including OBC, SC/ST, PwD and ex-servicemen age relaxation.',
     'kw' => ['age calculator for government exam', 'age limit calculator', 'age as on date calculator', 'ssc age calculator', 'upsc age limit calculator', 'age relaxation calculator'],
     'in' => [
       ['k' => 'dob', 'en' => 'Your date of birth', 'v' => '2000-08-20', 't' => 'date'],
       ['k' => 'cut', 'en' => 'Age as on (cut-off date in notification)', 'v' => 'today', 't' => 'date'],
       ['k' => 'mn', 'en' => 'Minimum age', 'v' => 18],
       ['k' => 'mx', 'en' => 'Maximum age (general)', 'v' => 27],
       ['k' => 'rx', 'en' => 'Category relaxation', 'v' => 0, 'opts' => [[0, 'General / EWS (0)'], [3, 'OBC (+3 years)'], [5, 'SC / ST (+5 years)'], [10, 'PwD General (+10 years)'], [13, 'PwD OBC (+13 years)'], [15, 'PwD SC/ST (+15 years)']]],
     ],
     'out' => [
       ['en' => 'Your age on cut-off date', 'f' => 'ymd(v.dob, v.cut)', 'big' => 1],
       ['en' => 'Eligible?', 'f' => "years(v.dob, v.cut) >= v.mn && years(v.dob, v.cut) <= v.mx + v.rx ? '✅ Yes, you are within the age limit' : (years(v.dob, v.cut) < v.mn ? '❌ No — below minimum age' : '❌ No — above maximum age')"],
       ['en' => 'Upper age limit for you', 'f' => 'v.mx + v.rx', 'u' => 'years'],
     ]],

    ['slug' => 'salary-slip-generator', 'cat' => 'tax', 'type' => 'app', 'kind' => 'salaryslip', 'icon' => 'file-spreadsheet',
     'en' => 'Salary Slip Generator (Payslip Format with PDF)',
     'de' => 'Make a professional monthly salary slip with earnings, deductions, net pay in words, and download it as PDF or print it.',
     'kw' => ['salary slip generator', 'salary slip format', 'payslip generator', 'salary slip format in word', 'salary slip pdf', 'pay slip maker online free'],
     'steps' => ['Enter the company and employee details (company details are saved in your browser)', 'Fill in earnings like basic, HRA and allowances, and deductions like PF, PT and TDS', 'Check the live payslip preview with net pay in words', 'Print it or download the PDF'],
     'faq' => [['Is a salary slip made here valid?', 'It is a correctly formatted payslip, but it is only valid when issued by the real employer with genuine salary figures. Never use it to show false income.']]],

    ['slug' => 'rent-receipt-generator', 'cat' => 'tax', 'type' => 'app', 'kind' => 'rentreceipt', 'icon' => 'receipt',
     'en' => 'Rent Receipt Generator for HRA (Monthly Receipts PDF)',
     'de' => 'Generate monthly house rent receipts for the whole year in one click to claim HRA, with landlord PAN, revenue stamp space and amount in words.',
     'kw' => ['rent receipt generator', 'rent receipt format', 'hra rent receipt', 'house rent receipt pdf', 'rent receipt for hra claim', 'rent slip format'],
     'steps' => ['Enter tenant, landlord, house address and monthly rent', 'Choose the period (from month and to month)', 'All monthly receipts appear with the rent in words', 'Print them or download one PDF, then get them signed by the landlord'],
     'faq' => [['When is landlord PAN needed?', 'If the yearly rent is more than ₹1 lakh, your employer will ask for the landlord\'s PAN to allow the HRA exemption.'], ['Is a revenue stamp needed?', 'For cash rent above ₹5,000 per receipt, a ₹1 revenue stamp signed by the landlord is commonly used. Rent paid by bank transfer or UPI does not need a stamp.']]],

    ['slug' => 'experience-certificate-format', 'cat' => 'tax', 'type' => 'doc', 'icon' => 'award',
     'en' => 'Experience Certificate Format (Work Experience Letter)',
     'de' => 'Create a printable experience certificate for an employee with joining date, last working day, designation and conduct.',
     'kw' => ['experience certificate format', 'experience letter format', 'work experience certificate', 'experience certificate in word format', 'employee experience letter'],
     'in' => [
       ['k' => 'date', 'en' => 'Date', 't' => 'date'],
       ['k' => 'name', 'en' => 'Employee name', 'v' => 'Mr. Rahul Patel', 't' => 'text'],
       ['k' => 'des', 'en' => 'Designation', 'v' => 'Computer Operator', 't' => 'text'],
       ['k' => 'from', 'en' => 'Joining date', 'v' => '1 June 2022', 't' => 'text'],
       ['k' => 'to', 'en' => 'Last working day', 'v' => '31 October 2026', 't' => 'text'],
       ['k' => 'work', 'en' => 'Work done (short)', 'v' => 'data entry, billing and customer support', 't' => 'text'],
       ['k' => 'by', 'en' => 'Signing authority (name & designation)', 'v' => 'Proprietor', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Experience Certificate', 'sign' => true,
       'body_en' => "TO WHOMSOEVER IT MAY CONCERN\n\nThis is to certify that {name} was employed with us as {des} from {from} to {to}.\n\nDuring this period, the employee handled {work}. We found the employee sincere, hardworking and honest, and the employee's conduct was good.\n\nWe wish them all the best for their future.\n\nFor the organisation,\n{by}"]],

    ['slug' => 'resignation-letter-format', 'cat' => 'tax', 'type' => 'doc', 'icon' => 'mail',
     'en' => 'Resignation Letter Format (Simple & Professional)',
     'de' => 'Write a polite resignation letter with notice period and last working day, ready to print or send.',
     'kw' => ['resignation letter format', 'resignation letter', 'simple resignation letter', 'resignation letter for personal reason', 'resignation email format', 'resign letter sample'],
     'in' => [
       ['k' => 'date', 'en' => 'Date', 't' => 'date'],
       ['k' => 'to', 'en' => 'To (manager name & designation)', 'v' => 'The HR Manager', 't' => 'text'],
       ['k' => 'co', 'en' => 'Company name', 'v' => 'ABC Pvt. Ltd.', 't' => 'text'],
       ['k' => 'des', 'en' => 'Your designation', 'v' => 'Sales Executive', 't' => 'text'],
       ['k' => 'lwd', 'en' => 'Last working day', 'v' => '30 November 2026', 't' => 'text'],
       ['k' => 'why', 'en' => 'Reason', 'v' => 'personal reasons', 'opts' => [['personal reasons', 'Personal reasons'], ['a better career opportunity', 'Better career opportunity'], ['higher studies', 'Higher studies'], ['health reasons', 'Health reasons'], ['family reasons', 'Family reasons']]],
       ['k' => 'name', 'en' => 'Your name', 'v' => 'Amit Shah', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Resignation Letter', 'nobiz' => true, 'sign' => true,
       'body_en' => "To,\n{to}\n{co}\n\nSubject: Resignation from the post of {des}\n\nDear Sir / Madam,\n\nPlease accept this letter as formal notice of my resignation from the post of {des} due to {why}. As per my notice period, my last working day will be {lwd}.\n\nI am thankful for the opportunities, support and guidance I received here. I will complete all pending work and help with a smooth handover before I leave.\n\nKindly accept my resignation and relieve me on the above date.\n\nThank you.\n\nYours sincerely,\n{name}"]],

    ['slug' => 'leave-application-for-office', 'cat' => 'tax', 'type' => 'doc', 'icon' => 'calendar-minus',
     'en' => 'Leave Application for Office (Sick, Casual & Urgent Leave)',
     'de' => 'Write a leave application to your manager or boss for sick leave, casual leave, family function or urgent work.',
     'kw' => ['leave application for office', 'leave application format', 'sick leave application', 'leave letter to boss', 'casual leave application', 'leave application for family function'],
     'in' => [
       ['k' => 'date', 'en' => 'Date', 't' => 'date'],
       ['k' => 'to', 'en' => 'To', 'v' => 'The Manager', 't' => 'text'],
       ['k' => 'co', 'en' => 'Company / office', 'v' => 'ABC Pvt. Ltd.', 't' => 'text'],
       ['k' => 'type', 'en' => 'Reason', 'v' => 'I am not well and the doctor has advised rest', 'opts' => [['I am not well and the doctor has advised rest', 'Sick leave'], ['I have to attend a family function', 'Family function / marriage'], ['I have some urgent personal work', 'Urgent personal work'], ['of a medical emergency in my family', 'Family medical emergency'], ['I have to go out of town', 'Out of town']]],
       ['k' => 'n', 'en' => 'Number of days', 'v' => '2', 't' => 'text'],
       ['k' => 'from', 'en' => 'From', 'v' => '10 November 2026', 't' => 'text'],
       ['k' => 'till', 'en' => 'To', 'v' => '11 November 2026', 't' => 'text'],
       ['k' => 'name', 'en' => 'Your name & designation', 'v' => 'Neha Joshi, Accountant', 't' => 'text'],
     ],
     'doc' => ['title_en' => 'Leave Application', 'nobiz' => true, 'sign' => true,
       'body_en' => "To,\n{to}\n{co}\n\nSubject: Application for {n} days leave\n\nRespected Sir / Madam,\n\nI respectfully request leave for {n} days from {from} to {till}, as {type}.\n\nI will make sure my work is handed over and will be available on phone for anything urgent. Kindly grant me leave for the above period.\n\nThank you.\n\nYours faithfully,\n{name}"]],
  ],
];
