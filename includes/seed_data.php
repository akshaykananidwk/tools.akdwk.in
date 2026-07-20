<?php
/**
 * KRISHNA TOOLS — static seed data used by the installer and the app:
 * plans, WhatsApp templates, blog posts, and the daily Gita shlok array.
 */

/** 3 subscription plans. */
function kt_plans(): array {
    return [
        ['name_en' => 'Free', 'name_gu' => 'ફ્રી', 'price' => 0, 'duration_days' => 0,
         'daily_limit' => 3, 'max_file_mb' => 5,
         'features' => "કોઈપણ ટૂલ|દરરોજ 3 વખત|મહત્તમ 5 MB ફાઇલ|આઉટપુટ પર વોટરમાર્ક", 'is_active' => 1],
        ['name_en' => 'Monthly', 'name_gu' => 'માસિક', 'price' => 99, 'duration_days' => 30,
         'daily_limit' => 0, 'max_file_mb' => 100,
         'features' => "અમર્યાદિત ઉપયોગ|100 MB ફાઇલ|બેચ મોડ|વોટરમાર્ક વગર|ઈમેલ સપોર્ટ", 'is_active' => 1],
        ['name_en' => 'Yearly', 'name_gu' => 'વાર્ષિક', 'price' => 499, 'duration_days' => 365,
         'daily_limit' => 0, 'max_file_mb' => 100,
         'features' => "માસિકના બધા લાભ|પ્રાયોરિટી ક્યૂ|API કી|બલ્ક ZIP|સેવ્ડ ફાઇલ્સ", 'is_active' => 1],
    ];
}

/** 13 WhatsApp templates (key_name is stable; body has :placeholders). */
function kt_wa_templates(): array {
    return [
        ['key_name' => 'otp', 'name_gu' => 'OTP', 'variables' => 'otp',
         'body_gu' => "કૃષ્ણા ટૂલ્સ OTP: :otp (5 મિનિટ માન્ય)",
         'body_en' => "Krishna Tools OTP: :otp (valid 5 minutes)"],
        ['key_name' => 'welcome', 'name_gu' => 'સ્વાગત', 'variables' => 'name',
         'body_gu' => "🦚 હરે કૃષ્ણ :name! કૃષ્ણા ટૂલ્સમાં આપનું સ્વાગત છે. 120+ ટૂલ્સ માણો.",
         'body_en' => "Hare Krishna :name! Welcome to Krishna Tools. Enjoy 120+ tools."],
        ['key_name' => 'payment_success', 'name_gu' => 'પેમેન્ટ સફળ', 'variables' => 'plan,amount,expiry',
         'body_gu' => "✅ પેમેન્ટ સફળ! પ્લાન: :plan, રકમ: ₹:amount, માન્યતા: :expiry સુધી. હરે કૃષ્ણ!",
         'body_en' => "Payment successful! Plan: :plan, Amount: ₹:amount, Valid till: :expiry."],
        ['key_name' => 'expiry_reminder', 'name_gu' => 'એક્સપાયરી રિમાઇન્ડર', 'variables' => 'days,link',
         'body_gu' => "⏰ આપનો પ્લાન :days દિવસમાં પૂરો થાય છે. રિન્યુ કરો: :link",
         'body_en' => "Your plan expires in :days days. Renew: :link"],
        ['key_name' => 'plan_expired', 'name_gu' => 'પ્લાન સમાપ્ત', 'variables' => 'link',
         'body_gu' => "આપનો પ્લાન સમાપ્ત થયો છે. ફરી શરૂ કરો: :link",
         'body_en' => "Your plan has expired. Renew: :link"],
        ['key_name' => 'reset_otp', 'name_gu' => 'પાસવર્ડ રીસેટ OTP', 'variables' => 'otp',
         'body_gu' => "પાસવર્ડ રીસેટ OTP: :otp (5 મિનિટ માન્ય)",
         'body_en' => "Password reset OTP: :otp (valid 5 minutes)"],
        ['key_name' => 'login_alert', 'name_gu' => 'લોગિન એલર્ટ', 'variables' => 'ip,time',
         'body_gu' => "નવા ડિવાઇસથી લોગિન: :ip, સમય: :time. તમે ન હો તો પાસવર્ડ બદલો.",
         'body_en' => "New device login: :ip at :time. If not you, change your password."],
        ['key_name' => 'invoice_ready', 'name_gu' => 'ઇન્વોઇસ તૈયાર', 'variables' => 'invoice_no',
         'body_gu' => "🧾 આપનું ઇન્વોઇસ :invoice_no તૈયાર છે. જુઓ 👇",
         'body_en' => "Your invoice :invoice_no is ready. See below."],
        ['key_name' => 'cctv_quote_ready', 'name_gu' => 'CCTV કોટેશન', 'variables' => 'quote_no,amount',
         'body_gu' => "📄 CCTV કોટેશન :quote_no તૈયાર. કુલ ₹:amount (GST સહિત). AK Computer, દ્વારકા.",
         'body_en' => "CCTV quotation :quote_no ready. Total ₹:amount (incl. GST). AK Computer."],
        ['key_name' => 'job_sheet', 'name_gu' => 'જોબ શીટ', 'variables' => 'job_no,date',
         'body_gu' => "🔧 તમારું ટોકન નંબર :job_no, અંદાજિત ડિલિવરી :date. AK Computer, દ્વારકા.",
         'body_en' => "Your token no :job_no, estimated delivery :date. AK Computer."],
        ['key_name' => 'amc_renewal', 'name_gu' => 'AMC રિન્યુઅલ', 'variables' => 'days,client',
         'body_gu' => "🔔 :client, આપનું AMC :days દિવસમાં રિન્યુ કરવાનું છે. AK Computer, દ્વારકા.",
         'body_en' => "AMC renewal due in :days days. AK Computer, Dwarka."],
        ['key_name' => 'warranty_alert', 'name_gu' => 'વોરંટી એલર્ટ', 'variables' => 'item,date',
         'body_gu' => "⚠️ :item ની વોરંટી :date ના રોજ પૂરી થાય છે.",
         'body_en' => "Warranty for :item expires on :date."],
        ['key_name' => 'broadcast', 'name_gu' => 'બ્રોડકાસ્ટ', 'variables' => 'message',
         'body_gu' => ":message",
         'body_en' => ":message"],
    ];
}

/** 10 seed blog posts (content in Gujarati + English). */
function kt_blog_posts(): array {
    $mk = fn($tg,$te,$slug,$cg,$ce,$md) => compact('tg','te','slug','cg','ce','md');
    return [
        $mk('CCTV સ્ટોરેજ કેવી રીતે ગણવું', 'How to Calculate CCTV Storage', 'cctv-storage-guide',
            "CCTV સ્ટોરેજ ગણવા માટે કેમેરાની સંખ્યા, રિઝોલ્યુશન, FPS, કોડેક (H.264/H.265) અને રીટેન્શન દિવસ જરૂરી છે. H.265+ કોડેક લગભગ અડધી જગ્યા વાપરે છે. અમારું CCTV સ્ટોરેજ કેલ્ક્યુલેટર વાપરો.",
            "To calculate CCTV storage you need camera count, resolution, FPS, codec and retention days. Use our calculator.",
            'CCTV સ્ટોરેજ ગણતરી માર્ગદર્શિકા'),
        $mk('JPG ને PDF કેવી રીતે બનાવવું', 'How to Convert JPG to PDF', 'jpg-to-pdf-guide',
            "JPG ફોટાને PDF માં ફેરવવા માટે અમારું Image to PDF ટૂલ વાપરો. બધું બ્રાઉઝરમાં જ થાય છે, ડેટા સર્વર પર જતો નથી.",
            "Use our Image to PDF tool. Everything runs in your browser.", 'JPG થી PDF'),
        $mk('પરીક્ષા ફોર્મ માટે ફોટો 20KB કેવી રીતે કરવો', 'Resize Photo to 20KB for Exam Forms', 'photo-20kb-guide',
            "સરકારી પરીક્ષા ફોર્મમાં ઘણીવાર 20KB કે 50KB ફોટો જોઈએ. અમારું Photo KB Resizer વાપરીને ચોક્કસ સાઇઝ મેળવો.",
            "Government exam forms often need 20KB/50KB photos. Use our Photo KB Resizer.", 'ફોટો KB રિસાઇઝ'),
        $mk('GST કેલ્ક્યુલેશન સરળ રીતે', 'GST Calculation Made Simple', 'gst-calculation-guide',
            "GST inclusive અને exclusive બંને રીતે ગણી શકાય. CGST + SGST (રાજ્ય અંદર) કે IGST (રાજ્ય બહાર). અમારું GST કેલ્ક્યુલેટર વાપરો.",
            "GST can be inclusive or exclusive. CGST+SGST or IGST. Use our GST calculator.", 'GST ગણતરી'),
        $mk('PDF મર્જ કેવી રીતે કરવું', 'How to Merge PDF Files', 'merge-pdf-guide',
            "ઘણી PDF ને એક ફાઇલમાં જોડવા અમારું Merge PDF ટૂલ વાપરો. ડ્રેગ કરીને ક્રમ ગોઠવો.",
            "Use our Merge PDF tool. Drag to reorder.", 'PDF મર્જ'),
        $mk('CCTV કેમેરા માટે યોગ્ય લેન્સ પસંદગી', 'Choosing the Right CCTV Lens', 'cctv-lens-guide',
            "કેમેરાનું અંતર જાણીને યોગ્ય mm લેન્સ પસંદ કરો. વધુ mm = વધુ ઝૂમ પણ ઓછો FOV. અમારું Lens Calculator વાપરો.",
            "Pick the right mm lens by distance. More mm = more zoom, less FOV.", 'CCTV લેન્સ'),
        $mk('PoE સ્વિચ પાવર બજેટ સમજો', 'Understanding PoE Power Budget', 'poe-budget-guide',
            "PoE સ્વિચનું કુલ વોટ બજેટ કેમેરાના વોટ કરતાં વધુ હોવું જોઈએ. 20% હેડરૂમ રાખો.",
            "PoE switch watt budget must exceed camera watts. Keep 20% headroom.", 'PoE બજેટ'),
        $mk('QR કોડ કેવી રીતે બનાવવો', 'How to Create a QR Code', 'qr-code-guide',
            "URL, WiFi, vCard કે UPI પેમેન્ટ માટે QR કોડ બનાવો. સેન્ટરમાં લોગો પણ ઉમેરી શકાય.",
            "Create QR codes for URL, WiFi, vCard or UPI. Add a center logo.", 'QR કોડ'),
        $mk('ઇમેજ કોમ્પ્રેસ કરીને સાઇઝ ઘટાડો', 'Compress Images to Reduce Size', 'image-compress-guide',
            "ફોટોની ગુણવત્તા જાળવીને સાઇઝ ઘટાડવા અમારું Image Compressor વાપરો. ટાર્ગેટ KB સેટ કરો.",
            "Reduce image size while keeping quality. Set a target KB.", 'ઇમેજ કોમ્પ્રેસ'),
        $mk('કમ્પ્યુટર માટે PSU વોટ કેવી રીતે પસંદ કરવો', 'Choosing a PSU Wattage', 'psu-wattage-guide',
            "CPU, GPU, HDD અને ફેનનું કુલ વોટ ગણી 20% હેડરૂમ ઉમેરો. અમારું PSU Calculator વાપરો.",
            "Sum CPU, GPU, HDD, fan watts and add 20% headroom.", 'PSU વોટેજ'),
    ];
}

/** 30 Bhagavad Gita shloks (Sanskrit + Gujarati meaning) for the dashboard. */
function kt_shloks(): array {
    return [
        ['sanskrit' => 'कर्मण्येवाधिकारस्ते मा फलेषु कदाचन।', 'gu' => 'તારો અધિકાર ફક્ત કર્મ પર છે, ફળ પર નહીં.'],
        ['sanskrit' => 'योगः कर्मसु कौशलम्।', 'gu' => 'કર્મમાં કુશળતા એ જ યોગ છે.'],
        ['sanskrit' => 'श्रद्धावान् लभते ज्ञानम्।', 'gu' => 'શ્રદ્ધાવાન વ્યક્તિ જ્ઞાન પ્રાપ્ત કરે છે.'],
        ['sanskrit' => 'सर्वधर्मान्परित्यज्य मामेकं शरणं व्रज।', 'gu' => 'બધા ધર્મ છોડીને મારા એકના શરણે આવ.'],
        ['sanskrit' => 'उद्धरेदात्मनात्मानं नात्मानमवसादयेत्।', 'gu' => 'પોતાનો ઉદ્ધાર પોતાની જાતે કરો, પોતાને પાડો નહીં.'],
        ['sanskrit' => 'मन्मना भव मद्भक्तो मद्याजी मां नमस्कुरु।', 'gu' => 'મન મારામાં લગાવ, મારો ભક્ત બન.'],
        ['sanskrit' => 'समत्वं योग उच्यते।', 'gu' => 'સમતા એ જ યોગ કહેવાય છે.'],
        ['sanskrit' => 'न हि ज्ञानेन सदृशं पवित्रमिह विद्यते।', 'gu' => 'જ્ઞાન સમાન પવિત્ર આ જગતમાં કંઈ નથી.'],
        ['sanskrit' => 'तस्मात्सर्वेषु कालेषु मामनुस्मर युध्य च।', 'gu' => 'તેથી દરેક સમયે મને યાદ કર અને કર્મ કર.'],
        ['sanskrit' => 'वासांसि जीर्णानि यथा विहाय।', 'gu' => 'જૂનાં વસ્ત્રો છોડી નવાં ધારણ કરીએ તેમ આત્મા દેહ બદલે છે.'],
        ['sanskrit' => 'सुखदुःखे समे कृत्वा लाभालाभौ जयाजयौ।', 'gu' => 'સુખ-દુઃખ, લાભ-હાનિ, જય-પરાજયને સમાન ગણ.'],
        ['sanskrit' => 'यद्यदाचरति श्रेष्ठस्तत्तदेवेतरो जनः।', 'gu' => 'શ્રેષ્ઠ પુરુષ જે કરે તેને લોકો અનુસરે છે.'],
        ['sanskrit' => 'क्रोधाद्भवति सम्मोहः।', 'gu' => 'ક્રોધથી મોહ ઉત્પન્ન થાય છે.'],
        ['sanskrit' => 'अभ्यासेन तु कौन्तेय वैराग्येण च गृह्यते।', 'gu' => 'અભ્યાસ અને વૈરાગ્યથી મન વશ થાય છે.'],
        ['sanskrit' => 'सर्वभूतस्थमात्मानं सर्वभूतानि चात्मनि।', 'gu' => 'બધા ભૂતોમાં આત્માને અને આત્મામાં બધાને જુએ છે.'],
        ['sanskrit' => 'यो मां पश्यति सर्वत्र सर्वं च मयि पश्यति।', 'gu' => 'જે મને સર્વત્ર જુએ છે, હું તેનાથી કદી દૂર નથી.'],
        ['sanskrit' => 'श्रेयान्स्वधर्मो विगुणः।', 'gu' => 'બીજાના ધર્મ કરતાં પોતાનો ધર્મ શ્રેષ્ઠ છે.'],
        ['sanskrit' => 'नैनं छिन्दन्ति शस्त्राणि।', 'gu' => 'આત્માને શસ્ત્ર કાપી શકતાં નથી.'],
        ['sanskrit' => 'बुद्धियुक्तो जहातीह उभे सुकृतदुष्कृते।', 'gu' => 'બુદ્ધિયુક્ત વ્યક્તિ સારાં-નરસાં કર્મ બંનેથી મુક્ત થાય છે.'],
        ['sanskrit' => 'प्रसादे सर्वदुःखानां हानिरस्योपजायते।', 'gu' => 'મનની પ્રસન્નતાથી બધાં દુઃખો નષ્ટ થાય છે.'],
        ['sanskrit' => 'यतो योगेश्वरः कृष्णो यत्र पार्थो धनुर्धरः।', 'gu' => 'જ્યાં યોગેશ્વર કૃષ્ણ અને ધનુર્ધર અર્જુન છે, ત્યાં વિજય છે.'],
        ['sanskrit' => 'मां हि पार्थ व्यपाश्रित्य येऽपि स्युः पापयोनयः।', 'gu' => 'મારા શરણે આવનાર સૌ પરમ ગતિ પામે છે.'],
        ['sanskrit' => 'अनन्याश्चिन्तयन्तो मां ये जनाः पर्युपासते।', 'gu' => 'જે અનન્ય ભાવે મને ભજે છે, તેમનો યોગક્ષેમ હું વહન કરું છું.'],
        ['sanskrit' => 'पत्रं पुष्पं फलं तोयं यो मे भक्त्या प्रयच्छति।', 'gu' => 'ભક્તિથી અર્પણ કરેલ પત્ર, પુષ્પ, ફળ, જળ હું સ્વીકારું છું.'],
        ['sanskrit' => 'समोऽहं सर्वभूतेषु न मे द्वेष्योऽस्ति न प्रियः।', 'gu' => 'હું બધા પ્રાણીઓ પ્રત્યે સમાન છું.'],
        ['sanskrit' => 'चतुर्विधा भजन्ते मां जनाः सुकृतिनोऽर्जुन।', 'gu' => 'ચાર પ્રકારના પુણ્યશાળી લોકો મને ભજે છે.'],
        ['sanskrit' => 'ज्ञानं तेऽहं सविज्ञानमिदं वक्ष्याम्यशेषतः।', 'gu' => 'હું તને સંપૂર્ણ જ્ઞાન અને વિજ્ઞાન કહીશ.'],
        ['sanskrit' => 'महात्मानस्तु मां पार्थ दैवीं प्रकृतिमाश्रिताः।', 'gu' => 'મહાત્માઓ દૈવી પ્રકૃતિનો આશ્રય લઈ મને ભજે છે.'],
        ['sanskrit' => 'तेषां सततयुक्तानां भजतां प्रीतिपूर्वकम्।', 'gu' => 'પ્રેમથી ભજનારને હું બુદ્ધિયોગ આપું છું.'],
        ['sanskrit' => 'सर्वस्य चाहं हृदि सन्निविष्टः।', 'gu' => 'હું બધાના હૃદયમાં વસું છું.'],
    ];
}
