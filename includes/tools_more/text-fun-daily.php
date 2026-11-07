<?php
/** KRISHNA TOOLS — Text & writing, fun & random, and time & productivity tools. */

$love = '(function(a,b){a=String(a||"").toLowerCase().replace(/[^a-z઀-૿ऀ-ॿ]/g,"");b=String(b||"").toLowerCase().replace(/[^a-z઀-૿ऀ-ॿ]/g,"");if(!a||!b)return NaN;var s=[a,b].sort().join("♥"),h=0;for(var i=0;i<s.length;i++)h=(h*31+s.charCodeAt(i))%100003;return 35+h%66;})(v.a,v.b)';
$flames = '(function(a,b){a=String(a||"").toLowerCase().replace(/[^a-z]/g,"").split("");b=String(b||"").toLowerCase().replace(/[^a-z]/g,"").split("");if(!a.length||!b.length)return "Enter both names (English letters)";a.slice().forEach(function(c){var j=b.indexOf(c);if(j>-1){b.splice(j,1);a.splice(a.indexOf(c),1);}});var n=a.length+b.length;if(!n)return "Same names — try full names";var f=["Friends 🤝","Love ❤️","Affection 🤗","Marriage 💍","Enemies 😠","Siblings 👫"],i=0;while(f.length>1){i=(i+n-1)%f.length;f.splice(i,1);}return f[0];})(v.a,v.b)';
$lifePath = '(function(d){d=String(d||"").replace(/\D/g,"");if(!d)return NaN;var s=d.split("").reduce(function(a,c){return a+(+c)},0);while(s>9&&s!=11&&s!=22&&s!=33)s=String(s).split("").reduce(function(a,c){return a+(+c)},0);return s;})(v.d)';
$nameNum = '(function(n){n=String(n||"").toUpperCase().replace(/[^A-Z]/g,"");if(!n)return NaN;var s=0;for(var i=0;i<n.length;i++)s+=((n.charCodeAt(i)-65)%9)+1;while(s>9&&s!=11&&s!=22)s=String(s).split("").reduce(function(a,c){return a+(+c)},0);return s;})(v.n)';
$lpMean = "{1:'Leader – independent, ambitious, original',2:'Peacemaker – caring, patient, a good partner',3:'Creative – expressive, social, cheerful',4:'Builder – practical, hardworking, reliable',5:'Explorer – adventurous, free, loves change',6:'Nurturer – responsible, loving, family-minded',7:'Thinker – spiritual, analytical, wise',8:'Achiever – business-minded, powerful, successful',9:'Humanitarian – generous, kind, idealistic',11:'Master number 11 – intuitive and inspiring',22:'Master number 22 – the master builder',33:'Master number 33 – the master teacher'}";
$czAnimals = "['Monkey','Rooster','Dog','Pig','Rat','Ox','Tiger','Rabbit','Dragon','Snake','Horse','Goat']";
$czEl = "['Metal','Metal','Water','Water','Wood','Wood','Fire','Fire','Earth','Earth']";
$stones = "['Garnet','Amethyst','Aquamarine','Diamond','Emerald','Pearl / Moonstone','Ruby','Peridot','Blue Sapphire','Opal / Tourmaline','Topaz / Citrine','Turquoise / Tanzanite']";
$flowers = "['Carnation','Violet','Daffodil','Daisy','Lily of the valley','Rose','Larkspur','Gladiolus','Aster','Marigold','Chrysanthemum','Narcissus']";
$colors = "['Dark red','Purple','Light blue','White / clear','Green','Cream','Red','Light green','Deep blue','Pink','Yellow','Sky blue']";
$cc_t = 'String(v.t||"")';


$morse = "{a:'.-',b:'-...',c:'-.-.',d:'-..',e:'.',f:'..-.',g:'--.',h:'....',i:'..',j:'.---',k:'-.-',l:'.-..',m:'--',n:'-.',o:'---',p:'.--.',q:'--.-',r:'.-.',s:'...',t:'-',u:'..-',v:'...-',w:'.--',x:'-..-',y:'-.--',z:'--..',0:'-----',1:'.----',2:'..---',3:'...--',4:'....-',5:'.....',6:'-....',7:'--...',8:'---..',9:'----.','.':'.-.-.-',',':'--..--','?':'..--..','/':'-..-.','@':'.--.-.','!':'-.-.--'}";
$morseOut = '(function(t){var M=' . $morse . ';t=String(t||"").trim();if(!t)return "";if(/^[.\\-\\s\\/]+$/.test(t)){var R={};for(var k in M)R[M[k]]=k;return t.split(/\\s*\\/\\s*|\\s{3,}/).map(function(w){return w.split(/\\s+/).map(function(c){return R[c]||"";}).join("");}).join(" ").toUpperCase();}return t.toLowerCase().split(" ").map(function(w){return w.split("").map(function(c){return M[c]||"";}).filter(Boolean).join(" ");}).join(" / ");})(v.t)';
$digits = '(function(t,to){t=String(t||"");var G="૦૧૨૩૪૫૬૭૮૯",D="०१२३४५६७८९";return t.replace(/[0-9૦-૯०-९]/g,function(c){var n=G.indexOf(c);if(n<0)n=D.indexOf(c);if(n<0)n=+c;return to==1?G[n]:(to==2?D[n]:String(n));});})(v.t,v.to)';

$fun = 'fun';
$txt = 'text';
$pr = 'productivity';

return [
  'cats' => [
    ['slug' => 'text', 'name_en' => 'Text & Writing Tools', 'name_gu' => 'ટેક્સ્ટ અને લેખન', 'icon' => 'type', 'color' => '#2563EB', 'sort_order' => 12],
    ['slug' => 'fun', 'name_en' => 'Fun & Random Tools', 'name_gu' => 'મજા અને રેન્ડમ', 'icon' => 'party-popper', 'color' => '#E11D48', 'sort_order' => 13],
    ['slug' => 'productivity', 'name_en' => 'Time & Productivity', 'name_gu' => 'સમય અને પ્રોડક્ટિવિટી', 'icon' => 'alarm-clock', 'color' => '#4F46E5', 'sort_order' => 14],
  ],
  'tools' => [

    /* ======================= TEXT & WRITING ======================= */

    ['slug' => 'image-to-text-converter', 'cat' => $txt, 'type' => 'app', 'kind' => 'ocr', 'icon' => 'scan-text',
     'en' => 'Image to Text Converter (OCR) – English, Hindi & Gujarati',
     'de' => 'Extract text from photos, screenshots and scanned documents in English, Hindi and Gujarati — free OCR that runs in your browser.',
     'kw' => ['image to text', 'image to text converter', 'ocr online', 'photo to text', 'extract text from image', 'gujarati ocr', 'hindi image to text', 'jpg to text', 'screenshot to text'],
     'steps' => ['Choose a photo, screenshot or scanned page (JPG, PNG or WEBP)', 'Pick the language of the text – English, Hindi or Gujarati', 'Wait a few seconds while the text is read in your browser', 'Copy the text or download it as a .txt file'],
     'faq' => [['Does it work with Gujarati and Hindi?', 'Yes. Choose Gujarati or Hindi before reading. Clear, well-lit photos with printed text give the best results; handwriting is not reliable.'], ['Is my image uploaded?', 'No. The OCR engine runs inside your browser, so the image never reaches our server. The first use downloads the language data (a few MB).']]],

    ['slug' => 'speech-to-text', 'cat' => $txt, 'type' => 'app', 'kind' => 'stt', 'icon' => 'mic',
     'en' => 'Voice Typing – Speech to Text (Gujarati, Hindi, English)',
     'de' => 'Speak and get text instantly — free voice typing in Gujarati, Hindi, English and other Indian languages. Copy it to WhatsApp, email or documents.',
     'kw' => ['voice typing', 'speech to text', 'gujarati voice typing', 'hindi voice typing', 'voice to text', 'bolkar type kare', 'speech to text online free', 'talk to type'],
     'steps' => ['Choose your language (Gujarati, Hindi, English and more)', 'Tap Start and allow microphone access', 'Speak clearly – the text appears as you talk', 'Copy, download or share the text on WhatsApp'],
     'faq' => [['Which browsers support voice typing?', 'Google Chrome on Android and computers, and Microsoft Edge, work best. Safari on iPhone supports it on newer iOS versions. Firefox does not support it yet.'], ['Where does my voice go?', 'The browser\'s own speech service (for example Google in Chrome) turns speech into text. Nothing is stored on our server.']]],

    ['slug' => 'text-to-speech', 'cat' => $txt, 'type' => 'app', 'kind' => 'tts', 'icon' => 'volume-2',
     'en' => 'Text to Speech – Read Text Aloud (Hindi, English, Gujarati)',
     'de' => 'Paste any text and listen to it read aloud in natural voices. Choose language, voice, speed and pitch.',
     'kw' => ['text to speech', 'text to voice', 'read text aloud', 'hindi text to speech', 'gujarati text to speech', 'text to audio', 'tts online free'],
     'steps' => ['Type or paste your text', 'Choose a voice and language', 'Set the speed and pitch you like', 'Press Play to listen, Pause or Stop anytime']],

    ['slug' => 'character-counter', 'cat' => $txt, 'type' => 'calc', 'icon' => 'letter-text',
     'en' => 'Character Counter (Letters, Words & Limits for Instagram, X, SMS)',
     'de' => 'Count characters with and without spaces, words, sentences and lines, and check limits for Instagram, X (Twitter), SMS and Google meta tags.',
     'kw' => ['character counter', 'letter counter', 'character count', 'count characters online', 'instagram caption character limit', 'twitter character counter', 'sms character counter'],
     'in' => [['k' => 't', 'en' => 'Your text', 'v' => "Type or paste your text here.\nKrishna Tools counts every character instantly.", 't' => 'textarea', 'full' => 1]],
     'out' => [
       ['en' => 'Characters (with spaces)', 'f' => "Array.from($cc_t).length", 'fmt' => 'int', 'big' => 1],
       ['en' => 'Characters (without spaces)', 'f' => "Array.from($cc_t.replace(/\\s/g, '')).length", 'fmt' => 'int'],
       ['en' => 'Words', 'f' => "($cc_t.trim().match(/\\S+/g) || []).length", 'fmt' => 'int'],
       ['en' => 'Sentences', 'f' => "($cc_t.match(/[^.!?।]+[.!?।]+/g) || []).length", 'fmt' => 'int'],
       ['en' => 'Lines', 'f' => "$cc_t ? $cc_t.split(/\\n/).length : 0", 'fmt' => 'int'],
       ['en' => 'X (Twitter) – left of 280', 'f' => "280 - Array.from($cc_t).length", 'fmt' => 'int'],
       ['en' => 'Instagram caption – left of 2,200', 'f' => "2200 - Array.from($cc_t).length", 'fmt' => 'int'],
       ['en' => 'SMS parts needed', 'f' => "/[^\\x00-\\x7F]/.test($cc_t) ? ceil(Array.from($cc_t).length/70) : ceil($cc_t.length/160)", 'fmt' => 'int'],
       ['en' => 'Meta description (ideal 150–160)', 'f' => "Array.from($cc_t).length <= 160 ? 'OK – ' + Array.from($cc_t).length + ' chars' : 'Too long by ' + (Array.from($cc_t).length - 160)"],
     ]],

    ['slug' => 'reverse-text-generator', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'reverse'], 'icon' => 'undo-2',
     'en' => 'Reverse Text Generator (Backwards Text)',
     'de' => 'Write text backwards, reverse the word order or flip each word — fun for puzzles and social media.',
     'kw' => ['reverse text', 'backwards text generator', 'reverse words', 'mirror text', 'text reverser']],

    ['slug' => 'remove-line-breaks', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'clean'], 'icon' => 'wrap-text',
     'en' => 'Remove Line Breaks & Extra Spaces',
     'de' => 'Clean up text copied from PDFs or emails: remove line breaks, extra spaces, empty lines and tabs in one click.',
     'kw' => ['remove line breaks', 'remove extra spaces', 'remove empty lines', 'text cleaner', 'pdf text line break remover', 'join lines']],

    ['slug' => 'text-sorter', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'sort'], 'icon' => 'arrow-down-a-z',
     'en' => 'Sort Lines Alphabetically (A–Z, Z–A, Numbers, Shuffle)',
     'de' => 'Sort a list of names or lines alphabetically, by length or by number, reverse it or shuffle it randomly.',
     'kw' => ['sort alphabetically', 'alphabetical order tool', 'sort lines online', 'sort names a to z', 'list sorter', 'shuffle list']],

    ['slug' => 'add-prefix-suffix-to-lines', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'prefix'], 'icon' => 'list-plus',
     'en' => 'Add Prefix / Suffix to Each Line',
     'de' => 'Add text, numbers, bullets or country codes like +91 to the start or end of every line in a list.',
     'kw' => ['add prefix to each line', 'add suffix to each line', 'add +91 to numbers', 'add text to every line', 'bulk add text']],

    ['slug' => 'add-line-numbers', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'number'], 'icon' => 'list-ordered',
     'en' => 'Add Line Numbers to Text',
     'de' => 'Number every line of a list or text automatically (1. 2. 3. or 1) 2) 3)), with an option to skip empty lines.',
     'kw' => ['add line numbers', 'number lines online', 'numbered list maker', 'auto number list']],

    ['slug' => 'text-repeater', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'repeat'], 'icon' => 'repeat-2',
     'en' => 'Text Repeater (Repeat Text for WhatsApp)',
     'de' => 'Repeat any word, emoji or message 10, 100 or 1000 times, on new lines or in one line, and copy it for WhatsApp.',
     'kw' => ['text repeater', 'repeat text', 'whatsapp text repeater', 'emoji repeater', 'repeat word 100 times', 'message repeater']],

    ['slug' => 'blank-text-generator', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'blank'], 'icon' => 'square-dashed',
     'en' => 'Blank Text Generator (Empty Message for WhatsApp)',
     'de' => 'Copy invisible blank characters to send an empty WhatsApp message, blank name or empty Instagram bio line.',
     'kw' => ['blank text', 'empty message whatsapp', 'invisible character', 'blank space copy paste', 'empty text copy', 'invisible name']],

    ['slug' => 'email-phone-extractor', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'extract'], 'icon' => 'at-sign',
     'en' => 'Email, Phone Number & Link Extractor from Text',
     'de' => 'Pull out all email addresses, mobile numbers, links, hashtags or numbers from any block of text, without duplicates.',
     'kw' => ['email extractor', 'extract emails from text', 'phone number extractor', 'url extractor', 'extract mobile numbers', 'link extractor']],

    ['slug' => 'whatsapp-text-formatter', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'wa'], 'icon' => 'bold',
     'en' => 'WhatsApp Text Formatter (Bold, Italic, Strikethrough, Mono)',
     'de' => 'Make your WhatsApp messages bold, italic, strikethrough or monospace, or add bullets and quotes — copy and paste.',
     'kw' => ['whatsapp bold text', 'whatsapp italic', 'whatsapp strikethrough', 'whatsapp font style', 'whatsapp text format', 'how to write bold in whatsapp']],

    ['slug' => 'remove-emojis-from-text', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'emoji'], 'icon' => 'smile-plus',
     'en' => 'Remove Emojis from Text',
     'de' => 'Strip all emojis and symbols from a message or caption and keep only the plain text.',
     'kw' => ['remove emoji from text', 'emoji remover', 'delete emojis', 'clean text emojis']],

    ['slug' => 'word-frequency-counter', 'cat' => $txt, 'type' => 'app', 'kind' => 'textop', 'cfg' => ['op' => 'freq'], 'icon' => 'bar-chart-3',
     'en' => 'Word Frequency Counter & Keyword Density Checker',
     'de' => 'See which words appear most often in your text, with count and keyword density % — useful for SEO and essays.',
     'kw' => ['word frequency counter', 'keyword density checker', 'most used words', 'word count by frequency', 'seo keyword density']],

    ['slug' => 'fancy-text-generator', 'cat' => $txt, 'type' => 'app', 'kind' => 'fancy', 'icon' => 'sparkles',
     'en' => 'Fancy Text Generator (Stylish Fonts for Instagram & WhatsApp)',
     'de' => 'Turn normal text into 30+ stylish fonts — bold, italic, cursive, bubble, small caps, upside down — to copy into Instagram bio, WhatsApp, Free Fire and BGMI names.',
     'kw' => ['fancy text generator', 'stylish name', 'instagram fonts', 'stylish text', 'fancy font', 'cool fonts copy paste', 'free fire name style', 'stylish name for bgmi', 'bio fonts'],
     'steps' => ['Type your name or text', 'Scroll through 30+ ready styles', 'Tap Copy next to the style you like', 'Paste it into Instagram, WhatsApp or your game name']],

    ['slug' => 'online-notepad', 'cat' => $txt, 'type' => 'app', 'kind' => 'notepad', 'icon' => 'notebook-pen',
     'en' => 'Online Notepad (Auto Save, Download as TXT)',
     'de' => 'A simple online notepad that saves automatically in your browser. Write notes, lists or drafts and download them as a text file.',
     'kw' => ['online notepad', 'notepad', 'online notes', 'write notes online', 'notepad online save', 'text editor online'],
     'steps' => ['Start typing – your note saves automatically in this browser', 'Use several notes with the note picker', 'See words and characters as you type', 'Download as .txt, copy, or share on WhatsApp']],

    ['slug' => 'typing-test', 'cat' => $txt, 'type' => 'app', 'kind' => 'typing', 'icon' => 'keyboard',
     'en' => 'Typing Speed Test (WPM) – 1, 2 & 5 Minute Test',
     'de' => 'Test your English typing speed in words per minute with accuracy — practice for SSC, government jobs and data entry tests.',
     'kw' => ['typing test', 'typing speed test', 'wpm test', 'typing test 1 minute', 'english typing test', 'typing practice', 'ssc typing test'],
     'steps' => ['Choose the test time – 1, 2 or 5 minutes', 'Start typing the passage shown; the timer starts with your first key', 'Mistakes are marked in red as you type', 'See your WPM, accuracy and errors at the end']],

    ['slug' => 'whatsapp-direct-message-link', 'cat' => $txt, 'type' => 'app', 'kind' => 'walink', 'icon' => 'message-circle',
     'en' => 'WhatsApp Without Saving Number – Direct Chat Link Maker',
     'de' => 'Send a WhatsApp message to any number without saving the contact, or create a wa.me link and QR code with a pre-filled message for your business.',
     'kw' => ['whatsapp without saving number', 'wa.me link generator', 'whatsapp direct message', 'whatsapp link generator', 'click to chat whatsapp', 'whatsapp qr code for business'],
     'steps' => ['Enter the mobile number with country code (91 for India)', 'Type an optional ready message', 'Tap Open chat to message without saving the number', 'Or copy the link / QR code for your shop or visiting card']],

    ['slug' => 'upi-qr-code-generator', 'cat' => $txt, 'type' => 'app', 'kind' => 'upiqr', 'icon' => 'qr-code',
     'en' => 'UPI QR Code Generator (GPay, PhonePe, Paytm)',
     'de' => 'Make a free UPI payment QR code for your shop with your UPI ID, name and optional fixed amount — print it as a standee or share it on WhatsApp.',
     'kw' => ['upi qr code generator', 'upi qr code', 'phonepe qr code', 'google pay qr code', 'paytm qr code generator', 'payment qr code for shop', 'upi payment link'],
     'steps' => ['Enter your UPI ID (like name@okaxis) and the name to show', 'Add an amount and note if you want a fixed payment', 'The QR code updates instantly – scan it once to test', 'Download the PNG, print the standee or share on WhatsApp'],
     'faq' => [['Is this QR code safe?', 'The QR only contains your UPI ID, name and optional amount in the standard UPI format. Money goes directly to your bank account through your UPI app. Always scan once with your own phone to verify the name before printing.']]],


    ['slug' => 'morse-code-translator', 'cat' => $txt, 'type' => 'calc', 'icon' => 'radio',
     'en' => 'Morse Code Translator (Text ↔ Morse)',
     'de' => 'Translate English text to Morse code and Morse code (dots and dashes) back to text.',
     'kw' => ['morse code translator', 'text to morse code', 'morse code to text', 'morse code converter', 'sos in morse code'],
     'in' => [['k' => 't', 'en' => 'Text or Morse code (separate letters with spaces, words with /)', 'v' => 'SOS help', 't' => 'textarea', 'full' => 1]],
     'out' => [['en' => 'Translation', 'f' => $morseOut, 'big' => 1]]],

    ['slug' => 'gujarati-hindi-number-converter', 'cat' => $txt, 'type' => 'calc', 'icon' => 'languages',
     'en' => 'Gujarati & Hindi Numbers Converter (૧૨૩ ↔ 123 ↔ १२३)',
     'de' => 'Convert English digits to Gujarati or Hindi (Devanagari) numerals and back — for invitations, bills and documents.',
     'kw' => ['gujarati numbers', 'gujarati number converter', 'english to gujarati numbers', 'hindi numbers converter', 'gujarati ank', '1 to 100 in gujarati'],
     'in' => [['k' => 't', 'en' => 'Text with numbers', 'v' => 'Mobile 9876543210, Date 15-08-2026', 't' => 'textarea', 'full' => 1],
              ['k' => 'to', 'en' => 'Convert digits to', 'v' => 1, 'opts' => [[1, 'Gujarati (૦૧૨૩)'], [2, 'Hindi / Devanagari (०१२३)'], [3, 'English (0123)']]]],
     'out' => [['en' => 'Converted text', 'f' => $digits, 'big' => 1]]],

    ['slug' => 'emoji-copy-paste', 'cat' => $txt, 'type' => 'app', 'kind' => 'emoji', 'icon' => 'smile',
     'en' => 'Emoji Copy & Paste Keyboard (All Emojis)',
     'de' => 'Tap any emoji to copy it — smileys, hearts, hands, festivals, food, flags and symbols. Build a string of emojis and copy them all together.',
     'kw' => ['emoji copy paste', 'emoji keyboard', 'copy emoji', 'heart emoji copy', 'emoji list', 'whatsapp emoji copy']],

    ['slug' => 'vcard-qr-code-generator', 'cat' => $txt, 'type' => 'app', 'kind' => 'vcardqr', 'icon' => 'contact',
     'en' => 'Contact QR Code Generator (vCard for Visiting Card)',
     'de' => 'Make a QR code that saves your name, phone, email, business and address straight into anyone\'s contacts when scanned — perfect for visiting cards.',
     'kw' => ['vcard qr code generator', 'contact qr code', 'visiting card qr code', 'business card qr', 'save contact qr code', 'phone number qr code']],

    /* ======================= FUN & RANDOM ======================= */

    ['slug' => 'love-calculator', 'cat' => $fun, 'type' => 'calc', 'icon' => 'heart',
     'en' => 'Love Calculator – Check Your Love Percentage',
     'de' => 'Enter two names to get a fun love compatibility percentage — just for fun, share it with friends on WhatsApp.',
     'kw' => ['love calculator', 'love percentage', 'love test', 'name love calculator', 'love calculator by name', 'true love calculator', 'love meter'],
     'in' => [['k' => 'a', 'en' => 'Your name', 'v' => 'Krishna', 't' => 'text'], ['k' => 'b', 'en' => 'Partner\'s / crush\'s name', 'v' => 'Radha', 't' => 'text']],
     'out' => [
       ['en' => 'Love percentage', 'f' => "isNaN($love) ? 'Enter both names' : '❤️ ' + $love + '%'", 'big' => 1],
       ['en' => 'Result', 'f' => "($love) >= 90 ? 'A perfect match made in heaven! 💞' : (($love) >= 75 ? 'Strong love and great understanding 😍' : (($love) >= 55 ? 'Good chemistry – keep the spark alive 😊' : 'Friendship first, love may grow 🌱'))"],
     ],
     'faq' => [['Is the love calculator real?', 'No — it is just for fun. The same two names always give the same score, but it has nothing to do with real feelings.']]],

    ['slug' => 'flames-calculator', 'cat' => $fun, 'type' => 'calc', 'icon' => 'flame-kindling',
     'en' => 'FLAMES Calculator – Friends, Love, Affection, Marriage, Enemies, Siblings',
     'de' => 'Play the classic school FLAMES game online: enter two names and find out your relationship.',
     'kw' => ['flames calculator', 'flames game', 'flames love calculator', 'flames test', 'flames meaning', 'flames online'],
     'in' => [['k' => 'a', 'en' => 'Your name', 'v' => 'Rahul', 't' => 'text'], ['k' => 'b', 'en' => 'Other person\'s name', 'v' => 'Priya', 't' => 'text']],
     'out' => [['en' => 'FLAMES result', 'f' => $flames, 'big' => 1]],
     'how_en' => 'Common letters in both names are struck out once each. The count of remaining letters is used to cancel letters from F-L-A-M-E-S in a circle until one is left: Friends, Love, Affection, Marriage, Enemies or Siblings.'],

    ['slug' => 'numerology-calculator', 'cat' => $fun, 'type' => 'calc', 'icon' => 'hash',
     'en' => 'Numerology Calculator – Life Path & Name Number (Lucky Number)',
     'de' => 'Find your life path number from your date of birth and your name number, with a short meaning — for fun.',
     'kw' => ['numerology calculator', 'lucky number calculator', 'life path number', 'name numerology', 'numerology by date of birth', 'mulank calculator'],
     'in' => [['k' => 'd', 'en' => 'Date of birth', 'v' => '1995-08-15', 't' => 'date'], ['k' => 'n', 'en' => 'Your name', 'v' => 'Krishna Patel', 't' => 'text']],
     'out' => [
       ['en' => 'Life path number', 'f' => $lifePath, 'fmt' => 'int', 'big' => 1],
       ['en' => 'Meaning', 'f' => "$lpMean" . "[$lifePath] || '—'"],
       ['en' => 'Mulank (birth day number)', 'f' => '(function(){var x=new Date(v.d).getDate();while(x>9)x=String(x).split("").reduce(function(a,c){return a+(+c)},0);return x;})()', 'fmt' => 'int'],
       ['en' => 'Name number', 'f' => $nameNum, 'fmt' => 'int'],
     ]],

    ['slug' => 'chinese-zodiac-calculator', 'cat' => $fun, 'type' => 'calc', 'icon' => 'rabbit',
     'en' => 'Chinese Zodiac Calculator (Animal Year)',
     'de' => 'Find your Chinese zodiac animal and element from your birth year.',
     'kw' => ['chinese zodiac calculator', 'chinese zodiac by year', 'what is my chinese zodiac', 'chinese animal year', 'year of the horse 2026'],
     'in' => [['k' => 'y', 'en' => 'Birth year', 'v' => 2000]],
     'out' => [
       ['en' => 'Your animal', 'f' => "$czAnimals" . '[v.y % 12]', 'big' => 1],
       ['en' => 'Element', 'f' => "$czEl" . '[v.y % 10]'],
       ['en' => 'Note', 'f' => "'If you were born in January or early February, use the previous year (Chinese New Year starts late Jan / Feb).'"],
     ]],

    ['slug' => 'birthstone-by-month', 'cat' => $fun, 'type' => 'calc', 'icon' => 'gem',
     'en' => 'Birthstone, Birth Flower & Lucky Colour by Month',
     'de' => 'Find the birthstone, birth flower and colour for any birth month — gift ideas for birthdays.',
     'kw' => ['birthstone by month', 'birth stone', 'birth flower by month', 'which stone is for my birth month', 'birthstone chart'],
     'in' => [['k' => 'm', 'en' => 'Birth month', 'v' => 0, 'opts' => [[0, 'January'], [1, 'February'], [2, 'March'], [3, 'April'], [4, 'May'], [5, 'June'], [6, 'July'], [7, 'August'], [8, 'September'], [9, 'October'], [10, 'November'], [11, 'December']]]],
     'out' => [
       ['en' => 'Birthstone', 'f' => "$stones" . '[v.m]', 'big' => 1],
       ['en' => 'Birth flower', 'f' => "$flowers" . '[v.m]'],
       ['en' => 'Colour', 'f' => "$colors" . '[v.m]'],
     ]],

    ['slug' => 'random-number-generator', 'cat' => $fun, 'type' => 'app', 'kind' => 'random', 'icon' => 'dices',
     'en' => 'Random Number Generator (1–10, 1–100, Lucky Draw)',
     'de' => 'Pick one or many random numbers between any range, with or without repeats — for lucky draws, games and raffles.',
     'kw' => ['random number generator', 'random number 1 to 100', 'number picker', 'lucky draw number', 'random number 1 to 10', 'rng']],

    ['slug' => 'coin-flip', 'cat' => $fun, 'type' => 'app', 'kind' => 'coin', 'icon' => 'circle-dollar-sign',
     'en' => 'Coin Flip – Heads or Tails (Toss a Coin Online)',
     'de' => 'Toss a coin online for heads or tails with a flip animation and keep a count — perfect for cricket toss and quick decisions.',
     'kw' => ['coin flip', 'heads or tails', 'toss a coin', 'coin toss online', 'flip a coin', 'cricket toss']],

    ['slug' => 'dice-roller', 'cat' => $fun, 'type' => 'app', 'kind' => 'dice', 'icon' => 'dice-5',
     'en' => 'Dice Roller – Roll 1 to 6 Dice Online (Ludo Dice)',
     'de' => 'Roll one to six dice online with a tap, see the total — use it for Ludo, Snakes & Ladders and board games.',
     'kw' => ['roll a dice', 'dice roller', 'online dice', 'ludo dice', 'virtual dice', 'roll dice 1-6']],

    ['slug' => 'spin-the-wheel', 'cat' => $fun, 'type' => 'app', 'kind' => 'wheel', 'icon' => 'loader-pinwheel',
     'en' => 'Spin the Wheel – Random Name Picker',
     'de' => 'Add names or options and spin the wheel to pick a random winner — for classrooms, giveaways, lucky draws and decisions.',
     'kw' => ['spin the wheel', 'wheel of names', 'random name picker', 'spin wheel online', 'lucky draw wheel', 'random picker wheel', 'decision wheel'],
     'steps' => ['Type the names or options, one per line', 'Tap Spin and watch the wheel turn', 'The winner is shown when the wheel stops', 'Remove the winner and spin again if you need more winners']],

    ['slug' => 'random-team-generator', 'cat' => $fun, 'type' => 'app', 'kind' => 'teams', 'icon' => 'users-round',
     'en' => 'Random Team Generator (Split Names into Groups)',
     'de' => 'Split a list of names into random, fair teams or groups — for cricket, school projects and office games.',
     'kw' => ['random team generator', 'team picker', 'group generator', 'split names into groups', 'random group maker', 'cricket team maker']],

    /* ======================= TIME & PRODUCTIVITY ======================= */

    ['slug' => 'pomodoro-timer', 'cat' => $pr, 'type' => 'app', 'kind' => 'pomodoro', 'icon' => 'timer-reset',
     'en' => 'Pomodoro Timer – 25 Minute Study & Focus Timer',
     'de' => 'Study or work in focused 25-minute sessions with 5-minute breaks and a long break, with sound alerts — free Pomodoro timer.',
     'kw' => ['pomodoro timer', 'study timer', 'focus timer', '25 minute timer', 'study with me timer', 'productivity timer']],

    ['slug' => 'online-alarm-clock', 'cat' => $pr, 'type' => 'app', 'kind' => 'alarm', 'icon' => 'alarm-clock',
     'en' => 'Online Alarm Clock',
     'de' => 'Set an alarm in your browser with a loud beep and a label — keep this tab open and the alarm rings on time.',
     'kw' => ['online alarm clock', 'alarm clock', 'set alarm', 'alarm online', 'wake me up alarm', 'alarm in browser']],

    ['slug' => 'to-do-list', 'cat' => $pr, 'type' => 'app', 'kind' => 'todo', 'icon' => 'list-checks',
     'en' => 'To-Do List Online (Saves in Your Browser)',
     'de' => 'A simple, private to-do list: add tasks, tick them off, set priority, and share the list on WhatsApp. Saves automatically.',
     'kw' => ['to do list', 'todo list online', 'task list', 'checklist maker', 'daily to do list', 'online checklist']],

    ['slug' => 'world-clock', 'cat' => $pr, 'type' => 'app', 'kind' => 'worldclock', 'icon' => 'globe',
     'en' => 'World Clock – Current Time in India, USA, UK, Dubai & More',
     'de' => 'See the live current time in India and major cities worldwide — New York, London, Dubai, Sydney, Toronto, Singapore and more.',
     'kw' => ['world clock', 'current time in usa', 'india time now', 'time in dubai now', 'london time now', 'usa time to ist', 'canada time now']],

    ['slug' => 'tally-counter', 'cat' => $pr, 'type' => 'app', 'kind' => 'tally', 'icon' => 'plus-circle',
     'en' => 'Tally Counter / Naam Jap Mala Counter (108)',
     'de' => 'A big-button tap counter for counting people, items, laps or naam jap — with a 108 mala round count and vibration. Saves automatically.',
     'kw' => ['tally counter', 'jap counter', 'naam jap counter', 'mala counter 108', 'click counter', 'tasbih counter', 'counter online']],

    ['slug' => 'expense-splitter', 'cat' => $pr, 'type' => 'app', 'kind' => 'splitter', 'icon' => 'split',
     'en' => 'Split Expenses with Friends – Trip & Room Expense Calculator',
     'de' => 'Add who paid what on a trip, party or shared room, and see exactly who owes whom with the fewest payments.',
     'kw' => ['split expenses', 'expense splitter', 'trip expense calculator', 'split bill with friends', 'who owes whom', 'roommate expense calculator'],
     'steps' => ['Add each friend\'s name and how much they paid', 'The total and each person\'s share are calculated', 'See who should pay whom, with the fewest payments', 'Copy or share the settlement on WhatsApp']],

    ['slug' => 'grocery-list-maker', 'cat' => $pr, 'type' => 'app', 'kind' => 'grocery', 'icon' => 'shopping-cart',
     'en' => 'Grocery List Maker (Kirana List with Total)',
     'de' => 'Make a monthly grocery or kirana list with quantity and price, get the total, tick items while shopping and share the list on WhatsApp.',
     'kw' => ['grocery list', 'kirana list', 'monthly grocery list', 'shopping list maker', 'grocery list with price', 'ration list']],

    ['slug' => 'breathing-exercise-timer', 'cat' => $pr, 'type' => 'app', 'kind' => 'breathe', 'icon' => 'wind',
     'en' => 'Breathing Exercise Timer (Box Breathing, 4-7-8, Pranayama)',
     'de' => 'Calm down with guided breathing: box breathing, 4-7-8 relaxing breath and simple pranayama with an animated guide.',
     'kw' => ['breathing exercise', 'box breathing', '4 7 8 breathing', 'pranayama timer', 'breathing timer', 'calm breathing app']],
  ],
];
