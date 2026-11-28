<?php
/**
 * KRISHNA TOOLS — long guides for the most-searched tool pages (Phase 5).
 * Shown under the tool: who it is for, a worked example, tips and extra FAQ.
 * 'updated' is printed on the page; exam pages also list the official notification they follow.
 */
function kt_tool_landing(string $slug): ?array {
    $L = [
    'cctv-quotation' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['Who uses this CCTV quotation maker?', '<p>CCTV installers, computer and security shops, electricians who take camera work, and society or office admins who want to compare two vendors. Instead of typing a quote in Word or sending a photo of a handwritten estimate on WhatsApp, you fill in the items once and get a clean, itemised quotation with GST that the customer can read and approve.</p>'],
            ['What a good CCTV quotation should contain', '<ul class="list-disc ml-6 space-y-1"><li><b>Customer and site details</b> — name, phone and the address where cameras will be fitted.</li><li><b>Cameras by type</b> — dome or bullet, 2 MP / 4 MP / 5 MP, IP or HD (analog), with or without colour night vision and audio.</li><li><b>Recorder</b> — DVR for HD cameras or NVR for IP cameras, with the number of channels (4, 8, 16).</li><li><b>Hard disk</b> — size in TB, matched to the days of recording the customer wants.</li><li><b>Cabling and accessories</b> — CCTV cable or Cat6 in metres, BNC/DC connectors, PVC pipe or casing, junction boxes, SMPS power supply or PoE switch, rack, and a monitor if needed.</li><li><b>Labour</b> — installation charge per camera or as one amount, and configuration of mobile viewing.</li><li><b>GST, total, validity and warranty</b> — the tax rate, grand total, how many days the rates are valid and the warranty on each item.</li></ul>'],
            ['Example: 4-camera shop quotation', '<p>A small shop wants four 2 MP cameras with mobile viewing and about 15 days of recording. A typical quote lists 4 × 2 MP cameras, 1 × 4-channel DVR, 1 × 1 TB surveillance hard disk, 1 × 4-channel SMPS, about 90 metres of 3+1 CCTV cable, 8 BNC and 4 DC connectors, PVC casing and the installation charge. Enter each line with its quantity and rate, set the GST percentage, and the tool adds up the taxable value, the GST and the grand total, ready as a PDF to send on WhatsApp or print.</p><p>For the hard disk size, use the <a class="underline" href="' . SITE_URL . '/category/cctv">CCTV storage calculators</a>: four 2 MP cameras recording all day usually need about 1 TB for two weeks with H.265.</p>'],
            ['Tips that win more orders', '<ul class="list-disc ml-6 space-y-1"><li>Give two options — a basic HD (analog) set and an IP set — so the customer can choose a budget.</li><li>Write brand and model on each line; customers compare quotes line by line.</li><li>Keep cable as an estimate ("approx. 90 m, actual as used") to avoid disputes after fitting.</li><li>Mention what is not included, such as electrical points or a monitor, in the notes.</li><li>Add a validity date (for example 7 days), because hard disk and camera prices change often.</li></ul>'],
        ],
        'faq' => [
            ['How much does a 4-camera CCTV set cost in India?', 'It depends on brand, camera resolution and cable length. A basic 4-camera 2 MP HD set with DVR, 1 TB disk, cable and fitting is commonly quoted in the ₹12,000–₹20,000 range, and IP sets cost more. Make the quotation with your own current rates.'],
            ['Should I charge GST on installation labour?', 'If you are GST-registered, installation service is also taxable. List labour as a separate line and apply the rate your accountant advises, so the invoice matches the quotation.'],
        ],
    ],
    'pdf-merge' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['When do you need to merge PDF files?', '<p>Government and job forms often allow only one file upload: Aadhaar, PAN, marksheets and certificates must go together as a single PDF. Offices combine invoices, bank statements or scanned pages for a loan file, and students join assignment pages before submitting. This tool joins any number of PDF files into one, in the order you choose, without printing or re-scanning anything.</p>'],
            ['Your files stay on your device', '<p>The merging runs inside your browser. Your PDFs are not uploaded to a server, so personal documents such as ID proofs and bank statements never leave your phone or computer. It also means the merge is fast even on a slow connection, and it works the same on Android, iPhone, Windows and Mac.</p>'],
            ['Example: one PDF for an online form', '<p>Suppose a recruitment form asks for "all documents in one PDF, under 2 MB". Select your 10th marksheet, 12th marksheet, degree certificate and caste certificate, drag them into the right order, and merge. If the result is larger than 2 MB, open the <a class="underline" href="' . SITE_URL . '/tool/pdf-compress">PDF compressor</a> and reduce it. If a document is a photo, first turn it into a PDF with the <a class="underline" href="' . SITE_URL . '/tool/image-to-pdf">image to PDF</a> tool, then merge.</p>'],
            ['Merging PDFs on a mobile phone', '<p>On Android, tap "Select files" and pick the PDFs from Downloads, WhatsApp Documents or Google Drive; on iPhone, choose them from the Files app. Hold and drag a file to move it up or down the list, then tap Merge. The single merged PDF is saved to your Downloads folder, and you can share it straight to WhatsApp, Gmail or the upload page of the form. No app install or login is needed, and the same steps work in Chrome on a computer.</p>'],
            ['Tips for a clean merged PDF', '<ul class="list-disc ml-6 space-y-1"><li>Name files 1, 2, 3… before selecting them; most phones then keep that order.</li><li>Rotate sideways scans before merging so every page reads the right way up.</li><li>Password-protected PDFs must be unlocked first — the tool cannot read encrypted pages.</li><li>Check the page count after merging: it should equal the total of all files.</li></ul>'],
        ],
        'faq' => [
            ['Is there a limit on the number of PDFs I can merge?', 'There is no fixed count. Very large files depend on your device memory; on a phone, merging up to about 100 MB in total works smoothly.'],
            ['Will merging reduce the quality of my PDF?', 'No. Pages are copied as they are, so text stays sharp and scans keep their original quality. Only compression changes quality.'],
        ],
    ],
    'gst-calculator' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['Who uses a GST calculator?', '<p>Shopkeepers checking a bill, small businesses preparing quotations and invoices, accountants splitting tax into CGST and SGST, and buyers who want to know how much of the MRP is tax. The calculator works both ways: add GST to a base price (exclusive) or take GST out of a price that already includes it (inclusive).</p>'],
            ['GST rates after the September 2025 reform', '<p>From 22 September 2025 most goods and services moved to two main rates, 5% and 18%, with a special 40% rate for a short list of luxury and sin goods, and some essentials at nil rate. Older bills may still show 12% or 28%. Because rates depend on the HSN or SAC code of the item, the calculator lets you type any rate — always use the rate printed on your supplier\'s invoice or confirmed by your CA.</p>'],
            ['Example: adding and removing 18% GST', '<p><b>Adding GST:</b> a laptop repair costs ₹2,000 before tax. 18% GST is ₹360, so the customer pays ₹2,360. Inside Gujarat this is shown as CGST 9% = ₹180 and SGST 9% = ₹180; to a buyer in another state it is IGST 18% = ₹360.</p><p><b>Removing GST:</b> a router is sold at ₹1,180 including 18% GST. The base price is 1,180 ÷ 1.18 = ₹1,000 and the GST inside the price is ₹180. The same method works for any rate: divide by 1.05 for 5%, by 1.40 for 40%.</p>'],
            ['Common mistakes to avoid', '<ul class="list-disc ml-6 space-y-1"><li>Taking 18% of an inclusive price gives the wrong tax — divide by 1.18 instead.</li><li>Charging CGST + SGST on an inter-state sale; it should be IGST.</li><li>Rounding each line separately and then adding — round the final tax total to match the invoice.</li></ul><p>To make the bill itself, use the <a class="underline" href="' . SITE_URL . '/tool/invoice-generator">GST invoice generator</a>.</p>'],
        ],
        'faq' => [
            ['What are the current GST slabs in India?', 'After the GST Council reform effective 22 September 2025, the main rates are 5% and 18%, with a 40% rate for selected luxury and sin goods and nil rate for some essentials. The exact rate depends on the item\'s HSN/SAC code.'],
            ['Is GST calculated on the discounted price?', 'Yes, when the discount is shown on the invoice at the time of sale, GST is charged on the price after the discount.'],
        ],
    ],
    'bmi-calculator' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['What BMI tells you', '<p>Body Mass Index (BMI) compares your weight with your height: BMI = weight in kg ÷ (height in metres)². It is a quick screening number used by doctors, gyms, insurance forms and school health checks to see whether weight is in a healthy range. It does not measure body fat directly, so a muscular person can have a high BMI and still be fit.</p>'],
            ['BMI ranges — and the lower cut-offs for Indians', '<p>The WHO adult ranges are: under 18.5 underweight, 18.5–24.9 normal, 25–29.9 overweight and 30 or more obese. For Indians and other Asians, Indian consensus guidelines use lower cut-offs because health risks such as diabetes start at a lower weight: 18.5–22.9 normal, 23–24.9 overweight and 25 or more obese. If you are Indian, treat a BMI of 23–25 as an early warning.</p>'],
            ['Example', '<p>Height 5 feet 6 inches (1.68 m) and weight 72 kg: BMI = 72 ÷ (1.68 × 1.68) = 72 ÷ 2.82 = 25.5. By WHO ranges that is just "overweight"; by Indian cut-offs it is in the obese range, and losing about 7–8 kg would bring BMI near 23. For height in feet and inches, the calculator converts for you.</p>'],
            ['Use BMI with other checks', '<ul class="list-disc ml-6 space-y-1"><li><b>Waist size:</b> above 90 cm for Indian men and 80 cm for Indian women suggests risky belly fat even with a normal BMI.</li><li><b>Children and teens</b> need age-and-sex BMI charts, not adult ranges.</li><li><b>Pregnancy, athletes and older adults</b> — BMI is less reliable; follow your doctor.</li></ul><p>See also the <a class="underline" href="' . SITE_URL . '/category/health">health calculators</a> for ideal weight, calories and water intake.</p>'],
        ],
        'faq' => [
            ['What is a normal BMI for Indians?', 'Indian guidelines treat 18.5 to 22.9 as normal, 23 to 24.9 as overweight and 25 or more as obese — lower than the WHO ranges, because Indians face diabetes and heart risk at lower weights.'],
            ['Is BMI different for men and women?', 'The adult formula and ranges are the same for men and women. Women naturally carry more body fat at the same BMI, which is why waist size is a useful second check.'],
        ],
    ],
    'internet-speed-test' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['What the speed test measures', '<ul class="list-disc ml-6 space-y-1"><li><b>Download (Mbps)</b> — how fast data reaches you: streaming, downloads, web pages.</li><li><b>Upload (Mbps)</b> — how fast you send data: video calls, CCTV live view from a remote site, uploading files.</li><li><b>Ping / latency (ms)</b> — the delay of each request; under 30 ms feels instant, gaming and calls suffer above 100 ms.</li><li><b>Jitter</b> — how much the ping changes; high jitter makes calls break.</li></ul>'],
            ['How much speed do you need?', '<p>HD video on one screen needs about 5 Mbps and 4K about 25 Mbps. A video call needs 2–4 Mbps both ways. For remote CCTV viewing, each 2 MP camera on the sub-stream needs roughly 0.5–1 Mbps of <b>upload</b> at the site, and main-stream full HD about 2–4 Mbps per camera. A home with 3–4 people streaming and working usually feels fine on a 50–100 Mbps plan.</p>'],
            ['Example: is my 100 Mbps plan working?', '<p>Run the test three times — once near the router on Wi-Fi, once on a LAN cable, and once at night. If the cable result is 90+ Mbps but Wi-Fi in the bedroom shows 30 Mbps, the plan is fine and the problem is Wi-Fi coverage. Remember that apps show downloads in MB/s: 100 Mbps ÷ 8 = about 12.5 MB/s. Use the <a class="underline" href="' . SITE_URL . '/convert/megabit-per-second-to-megabyte-per-second">Mbps to MB/s converter</a> to check.</p>'],
            ['How to get a more accurate result', '<ul class="list-disc ml-6 space-y-1"><li>Close downloads, updates and video on other devices first.</li><li>Use the 5 GHz Wi-Fi band near the router, or a cable.</li><li>Older phones and laptops cannot reach high speeds on Wi-Fi even if the line can.</li><li>Test at different times; evening speeds drop when everyone is online.</li></ul>'],
        ],
        'faq' => [
            ['Why is my speed lower than my plan?', 'Wi-Fi distance and walls, an old router or device, many users at the same time and the ISP\'s evening load all reduce speed. Test on a LAN cable first; if it is still far below the plan, complain to the provider with the results.'],
            ['Is 40 Mbps enough for CCTV and home use?', 'For a home with a few phones, one TV and 4 cameras viewed remotely on the sub-stream, 40 Mbps is usually enough, as long as the upload speed is at least 5–10 Mbps.'],
        ],
    ],
    'age-calculator' => [
        'updated' => '2026-11-28',
        'sections' => [
            ['What this age calculator gives you', '<p>Enter your date of birth to get your exact age in years, months and days, plus total months, weeks, days and the days left to your next birthday. You can also change the "age as on" date to find your age on any day in the past or future — which is what forms, schools and recruitment notices ask for.</p>'],
            ['Age for school admission, forms and government jobs', '<p>School admissions in India count age on a fixed date, usually 1 June or 31 March of the admission year. Recruitment exams count it on a cut-off date printed in the notification, and a single day can decide eligibility. For exam-specific checks with category relaxation, use our pages for <a class="underline" href="' . SITE_URL . '/tool/upsc-age-calculator">UPSC Civil Services</a>, <a class="underline" href="' . SITE_URL . '/tool/ssc-cgl-age-calculator">SSC CGL</a>, or the general <a class="underline" href="' . SITE_URL . '/tool/govt-job-age-limit-calculator">government job age calculator</a> where you can type any limit.</p>'],
            ['Example', '<p>Date of birth 15 March 2001, age as on 1 August 2026: from 15 March 2001 to 15 March 2026 is 25 years; from 15 March to 15 July is 4 months; from 15 July to 1 August is 17 days. Exact age: <b>25 years, 4 months, 17 days</b>. Leap years and the different month lengths are handled automatically.</p>'],
            ['Good to know', '<ul class="list-disc ml-6 space-y-1"><li>Someone born on 29 February has a birthday on 28 February or 1 March in non-leap years; legally most offices count 1 March.</li><li>"Age 21 completed" means the 21st birthday has passed; "not attained 32" means the 32nd birthday has not come yet.</li><li>Always use the date of birth exactly as written in your 10th (SSC) certificate for government forms.</li></ul>'],
        ],
        'faq' => [
            ['How do I calculate my age on a specific date?', 'Enter your date of birth and change the "age as on" date to the date you need. The calculator counts complete years, then months, then remaining days up to that date.'],
            ['Which date of birth is used for government jobs?', 'The date of birth recorded in your matriculation (10th) certificate is accepted by UPSC, SSC and state commissions. Changes are generally not allowed after you apply.'],
        ],
    ],
    'upsc-age-calculator' => [
        'updated' => '2026-11-28',
        'source' => ['UPSC Examination Notice No. 05/2026-CSE dated 04.02.2026 (Civil Services Preliminary Examination 2026)', 'https://www.upsc.gov.in/uploads/2026/05/Civil-Services-Preliminary-Examination-2026-Notice-E.pdf'],
        'sections' => [
            ['UPSC CSE 2026 age limit', '<p>As per the official notice, a candidate must have attained the age of <b>21 years</b> and must <b>not have attained 32 years on 1 August 2026</b> — that is, born not earlier than <b>2 August 1994</b> and not later than <b>1 August 2005</b>. The same rule covers IAS, IPS, IFS and the other services filled through the Civil Services Examination.</p>'],
            ['Age relaxation and attempts (CSE 2026)', '<div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left opacity-70"><th class="py-1">Category</th><th>Upper age</th><th>Attempts</th></tr></thead><tbody>'
                . '<tr class="border-t"><td class="py-1">General / EWS</td><td>32</td><td>6</td></tr>'
                . '<tr class="border-t"><td class="py-1">OBC (+3 years)</td><td>35</td><td>9</td></tr>'
                . '<tr class="border-t"><td class="py-1">SC / ST (+5 years)</td><td>37</td><td>No limit</td></tr>'
                . '<tr class="border-t"><td class="py-1">PwBD (+10 years)</td><td>42 / 45 / 47 (Gen-EWS / OBC / SC-ST)</td><td>9 (no limit for SC/ST)</td></tr>'
                . '<tr class="border-t"><td class="py-1">Ex-servicemen, ECOs/SSCOs with 5 years service (+5 years)</td><td>37 (+ category relaxation)</td><td>As per category</td></tr>'
                . '<tr class="border-t"><td class="py-1">Defence personnel disabled in hostilities (+3 years)</td><td>35</td><td>As per category</td></tr>'
                . '</tbody></table></div><p class="mt-2">A candidate who belongs to SC/ST/OBC and is also an ex-serviceman or PwBD gets both relaxations added together, as stated in the notice.</p>'],
            ['How to use this calculator', '<p>Enter your date of birth exactly as in your 10th certificate and choose your category. The calculator shows your exact age on 1 August 2026, whether you are inside the limit, the date range you must be born in, and the attempts allowed for your category. Count your previous attempts yourself: appearing in even one paper of the Preliminary exam counts as an attempt.</p>'],
            ['Example', '<p>Born 10 January 1994, General category: on 1 August 2026 the age is 32 years, 6 months, 22 days. The 32nd birthday has already passed, so the candidate is <b>not eligible</b> for CSE 2026. The same person in the OBC category (limit 35) <b>is eligible</b>, with 9 attempts in all.</p>'],
        ],
        'faq' => [
            ['What is the age limit for UPSC 2026?', 'Minimum 21 and maximum 32 years on 1 August 2026 for General/EWS, i.e. born between 2 August 1994 and 1 August 2005. OBC get 3 years, SC/ST 5 years and PwBD 10 years extra (UPSC notice 05/2026-CSE).'],
            ['How many attempts are allowed in UPSC CSE?', 'General and EWS: 6 attempts; OBC: 9; SC/ST: unlimited up to the age limit; PwBD: 9 for General/EWS/OBC and unlimited for SC/ST.'],
            ['Is there age relaxation for J&K domicile in CSE 2026?', 'The CSE 2026 notice does not list a separate relaxation for J&K domicile. Check the notice for your year, as rules can change.'],
        ],
    ],
    'ssc-cgl-age-calculator' => [
        'updated' => '2026-11-28',
        'source' => ['SSC notice "Combined Graduate Level Examination, 2026", F. No. HQ-C11018/1/2026-C-1 dated 21.05.2026', 'https://ssc.gov.in/api/attachment/uploads/masterData/NoticeBoards/Notice_of_adv_cgl_2026.pdf'],
        'sections' => [
            ['SSC CGL 2026 age limit by post', '<p>The cut-off date for age is <b>1 August 2026</b>. The limit depends on the post you apply for:</p><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left opacity-70"><th class="py-1">Age</th><th>Born between</th><th>Posts (examples)</th></tr></thead><tbody>'
                . '<tr class="border-t"><td class="py-1">18–27</td><td>02.08.1999 – 01.08.2008</td><td>Auditor, Accountant / Junior Accountant, Postal / Sorting Assistant, Senior Secretariat Assistant / UDC, Tax Assistant, Senior Administrative Assistant (MES), Sub-Inspector (Group C)</td></tr>'
                . '<tr class="border-t"><td class="py-1">18–30</td><td>02.08.1996 – 01.08.2008</td><td>Assistant Audit / Accounts Officer, Income Tax Inspector, Inspector (Central Excise / Preventive Officer / Examiner), Assistant Enforcement Officer (ED), Assistant Section Officer (IB, MeitY and others), Inspector of Posts, SI / JIO (NCB)</td></tr>'
                . '<tr class="border-t"><td class="py-1">20–30</td><td>02.08.1996 – 01.08.2006</td><td>Assistant Section Officer in CSS, Railways, MEA, AFHQ; Sub-Inspector in CBI</td></tr>'
                . '<tr class="border-t"><td class="py-1">18–32</td><td>02.08.1994 – 01.08.2008</td><td>Junior Statistical Officer (MoSPI)</td></tr>'
                . '</tbody></table></div>'],
            ['Age relaxation (SSC CGL 2026)', '<ul class="list-disc ml-6 space-y-1"><li>SC / ST: 5 years · OBC: 3 years</li><li>PwBD: 10 years (General), 13 years (OBC), 15 years (SC/ST)</li><li>Ex-servicemen: 3 years after deducting military service from the actual age on 1 August 2026</li><li>Defence personnel disabled in hostilities: 3 years (8 years for SC/ST)</li><li>Central Government civilian employees with 3 years regular service (Group C posts): up to 40 years (45 for SC/ST)</li><li>Widows / divorced / judicially separated women not remarried (Group C posts): up to 35 years (40 for SC/ST)</li></ul><p class="mt-2">There is no limit on the number of attempts in SSC CGL.</p>'],
            ['How to use this calculator', '<p>Enter your date of birth as per your 10th certificate, choose the age group of the post you want, and your category. The calculator shows your age on 1 August 2026 and whether you are within the limit. If you are close to the limit, choose posts in the 18–30 or 18–32 group in your preferences.</p>'],
            ['Example', '<p>Born 15 May 1998, General category, applying for Tax Assistant (18–27): the age on 1 August 2026 is 28 years, 2 months, 17 days — <b>over the limit</b>. For Income Tax Inspector (18–30) the same candidate <b>is eligible</b>, and an OBC candidate could apply for both, because the Tax Assistant limit becomes 30 with relaxation.</p>'],
        ],
        'faq' => [
            ['What is the age limit for SSC CGL 2026?', 'It depends on the post: 18–27, 18–30, 20–30 or 18–32 years on 1 August 2026, with 3 years extra for OBC, 5 for SC/ST and 10–15 for PwBD (SSC CGL 2026 notice).'],
            ['How many attempts are allowed in SSC CGL?', 'There is no limit on attempts. You can appear every year as long as you are within the age limit for the post.'],
            ['Which date is used to count age in SSC CGL 2026?', 'Age is counted as on 1 August 2026, the cut-off date in the official notice.'],
        ],
    ],
    ];
    return $L[$slug] ?? null;
}

/** Print the long guide under a tool. */
function kt_render_tool_landing(array $g): void {
    ?>
    <article class="kt-card p-5 mb-6 leading-relaxed space-y-4">
      <?php foreach ($g['sections'] as [$h, $html]): ?>
        <section><h2 class="section-title text-xl mb-2"><?= e($h) ?></h2><?= $html ?></section>
      <?php endforeach; ?>
      <p class="text-xs opacity-70 border-t pt-3" style="border-color:var(--border)">
        Last updated: <time datetime="<?= e($g['updated']) ?>"><?= e(date('j F Y', strtotime($g['updated']))) ?></time>.
        <?php if (!empty($g['source'])): ?>Source: <a class="underline" rel="nofollow noopener" target="_blank" href="<?= e($g['source'][1]) ?>"><?= e($g['source'][0]) ?></a>. Always confirm with the official notification before applying.<?php endif; ?>
      </p>
    </article>
    <?php
}
