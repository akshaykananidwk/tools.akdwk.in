<?php
/** KRISHNA TOOLS — English guide + FAQ content (date/time/math & developer tools). */
return [

    // 1. Total Days Calculator
    'days-calculator' => [
        'guide' => [
            'Pick the start date in the "From" field.',
            'Pick the end date in the "To" field.',
            'Choose whether to count all days or only working days.',
            'Read the result showing total days, weeks and remaining days.',
        ],
        'faq' => [
            ['How do I calculate the number of days between two dates?', 'Select a start date and an end date, and the calculator instantly shows the total days, weeks and working days between them.'],
            ['Does the days calculator include both the start and end date?', 'By default it counts the difference between the two dates; you can toggle inclusive counting to add the final day to the total.'],
            ['Can I find how many working days are between two dates?', 'Yes, switch to the working-days option to exclude Saturdays and Sundays from the count.'],
        ],
    ],

    // 2. Age Calculator
    'age-calculator' => [
        'guide' => [
            'Enter your date of birth.',
            'Set the "age at" date, or leave it as today.',
            'Press Calculate to see your exact age.',
            'View your age in years, months and days plus days to your next birthday.',
        ],
        'faq' => [
            ['How do I calculate my exact age in years, months and days?', 'Enter your birth date and the tool breaks down your age into completed years, months and days automatically.'],
            ['How many days until my next birthday?', 'The age calculator shows the countdown to your next birthday along with the weekday it falls on.'],
            ['Can I calculate age on a specific past or future date?', 'Yes, change the "age at" date to any day and the result updates for that date instead of today.'],
        ],
    ],

    // 3. Date Add/Subtract
    'date-add-subtract' => [
        'guide' => [
            'Choose a starting date.',
            'Select whether to add or subtract time.',
            'Enter the number of days, months or years.',
            'Read the resulting date and its weekday.',
        ],
        'faq' => [
            ['How do I add days to a date?', 'Pick a start date, choose "add", enter the number of days, and the new date appears instantly.'],
            ['How do I calculate a date 90 days from today?', 'Set today as the start date, add 90 days, and the tool returns the exact future date and weekday.'],
            ['Can I subtract months or years from a date?', 'Yes, select "subtract" and enter the months or years to move the date backward accurately, accounting for month lengths.'],
        ],
    ],

    // 4. Working Days Calculator
    'working-days-calculator' => [
        'guide' => [
            'Select the start and end dates.',
            'Optionally add public holidays to exclude.',
            'Choose which weekdays count as working days.',
            'See the total working days and total weekends skipped.',
        ],
        'faq' => [
            ['How do I count working days between two dates?', 'Enter a date range and the calculator counts only Monday to Friday, skipping weekends automatically.'],
            ['Can I exclude public holidays from the count?', 'Yes, add your holiday dates and they will be removed from the working-day total.'],
            ['Does the working days calculator include the start date?', 'The start date is counted as a working day if it falls on a weekday and is not listed as a holiday.'],
        ],
    ],

    // 5. Timestamp/Epoch Converter
    'epoch-converter' => [
        'guide' => [
            'Paste a Unix timestamp to convert it to a readable date.',
            'Or enter a date and time to get its epoch value.',
            'Pick your preferred time zone for the output.',
            'Copy the converted timestamp or date with one click.',
        ],
        'faq' => [
            ['What is a Unix timestamp?', 'A Unix timestamp is the number of seconds elapsed since 1 January 1970 UTC, used widely in programming and databases.'],
            ['How do I convert an epoch timestamp to a normal date?', 'Paste the timestamp into the converter and it instantly shows the matching date and time in your chosen time zone.'],
            ['Does this converter support milliseconds?', 'Yes, it detects millisecond timestamps and converts them correctly; everything runs in your browser so no data is sent to a server.'],
        ],
    ],

    // 6. Time Zone Converter
    'timezone-converter' => [
        'guide' => [
            'Choose a source time zone and time.',
            'Add one or more target time zones to compare.',
            'View the converted times side by side.',
            'Use the IST reference to plan calls and meetings.',
        ],
        'faq' => [
            ['How do I convert a time from one time zone to another?', 'Set the source time and zone, add a target zone, and the equivalent local time is shown instantly.'],
            ['What time is it in IST compared to other countries?', 'The converter displays Indian Standard Time alongside any zones you add so you can compare at a glance.'],
            ['Does the time zone converter handle daylight saving time?', 'Yes, it applies each region\'s daylight saving rules automatically for accurate conversions.'],
        ],
    ],

    // 7. Countdown Timer / Stopwatch
    'countdown-timer' => [
        'guide' => [
            'Switch between countdown and stopwatch modes.',
            'For countdown, set hours, minutes and seconds or a target date.',
            'Press Start to begin and Pause to hold.',
            'Reset anytime and get an alert when the countdown ends.',
        ],
        'faq' => [
            ['How do I set an online countdown timer?', 'Enter the hours, minutes and seconds or pick a target date, then press Start to begin the countdown.'],
            ['Can I use this as a stopwatch?', 'Yes, switch to stopwatch mode to measure elapsed time with start, pause and lap controls.'],
            ['Will the timer keep running if I switch tabs?', 'The timer keeps counting accurately using your device clock, and it plays an alert sound when it reaches zero.'],
        ],
    ],

    // 8. Unit Converter
    'unit-converter' => [
        'guide' => [
            'Select a category such as length, weight or temperature.',
            'Choose the unit you are converting from.',
            'Enter the value to convert.',
            'Read the result in your chosen target unit.',
        ],
        'faq' => [
            ['How do I convert between metric and imperial units?', 'Pick the category, choose the from and to units, and enter a value to see the converted result instantly.'],
            ['Which units does this converter support?', 'It covers length, weight, temperature, area and volume with common metric and imperial units.'],
            ['How do I convert Celsius to Fahrenheit?', 'Select temperature, set the from unit to Celsius and the to unit to Fahrenheit, then enter the value for an instant conversion.'],
        ],
    ],

    // 9. Percentage Calculator
    'percentage-calculator' => [
        'guide' => [
            'Choose the type of percentage calculation you need.',
            'Enter the known numbers into the fields.',
            'Press Calculate to get the answer.',
            'Copy or clear the result and try another calculation.',
        ],
        'faq' => [
            ['How do I calculate the percentage of a number?', 'Enter the percentage and the total, and the tool returns the exact value, for example 15% of 200 equals 30.'],
            ['How do I find what percentage one number is of another?', 'Type both numbers and the calculator shows what percent the first is of the second.'],
            ['How do I calculate percentage increase or decrease?', 'Enter the original and new values and the tool displays the percentage change between them.'],
        ],
    ],

    // 10. Scientific Calculator
    'scientific-calculator' => [
        'guide' => [
            'Type your expression using the on-screen buttons.',
            'Use trig, log, power and root functions as needed.',
            'Switch between degrees and radians for angles.',
            'Press equals to evaluate and view the result.',
        ],
        'faq' => [
            ['What can a scientific calculator do?', 'It handles trigonometry, logarithms, exponents, roots and constants like pi in addition to basic arithmetic.'],
            ['How do I switch between degrees and radians?', 'Use the DEG/RAD toggle so trigonometric functions interpret angles in your preferred unit.'],
            ['Does this calculator work offline?', 'Yes, all calculations run directly in your browser, so it works without an internet connection once the page has loaded.'],
        ],
    ],

    // 11. QR Code Generator
    'qr-generator' => [
        'guide' => [
            'Select the QR type: URL, WiFi, vCard or UPI.',
            'Fill in the required details for that type.',
            'Optionally upload a logo to place in the center.',
            'Download the generated QR code as a PNG image.',
        ],
        'faq' => [
            ['How do I create a QR code for a link?', 'Choose the URL type, paste your link, and the QR code is generated instantly for download.'],
            ['Can I add a logo in the middle of the QR code?', 'Yes, upload an image and it is placed at the center while keeping the code scannable.'],
            ['Is my data safe when generating a QR code here?', 'Yes, the QR code is created entirely in your browser and nothing you enter is uploaded to a server.'],
        ],
    ],

    // 12. QR Code Scanner
    'qr-scanner' => [
        'guide' => [
            'Allow camera access or upload a QR image.',
            'Point the camera at the QR code to scan it.',
            'Read the decoded text or link that appears.',
            'Copy the result or open the link directly.',
        ],
        'faq' => [
            ['How do I scan a QR code on my computer?', 'Grant camera permission and hold the code up to your webcam, or upload a saved QR image to decode it.'],
            ['Can I scan a QR code from a saved image?', 'Yes, upload the image file and the scanner extracts the encoded content instantly.'],
            ['Is scanning a QR code here private?', 'Yes, scanning happens locally in your browser, so the camera feed and images are never sent to any server.'],
        ],
    ],

    // 13. Password Generator
    'password-generator' => [
        'guide' => [
            'Choose the password length with the slider.',
            'Select which character types to include.',
            'Click Generate to create a random password.',
            'Check the strength meter and copy your password.',
        ],
        'faq' => [
            ['How do I create a strong random password?', 'Set a length of at least 12 characters and include uppercase, lowercase, numbers and symbols for maximum strength.'],
            ['Are the generated passwords safe to use?', 'Yes, passwords are generated locally using the browser\'s built-in crypto API and are never sent to or stored on a server.'],
            ['What makes a password strong?', 'Length and variety matter most; the built-in strength meter rates each password so you can pick a secure one.'],
        ],
    ],

    // 14. Hash Generator
    'hash-generator' => [
        'guide' => [
            'Type or paste the text you want to hash.',
            'Choose an algorithm such as MD5, SHA-1 or SHA-256.',
            'View the generated hash value instantly.',
            'Copy the hash for verification or storage.',
        ],
        'faq' => [
            ['How do I generate an MD5 or SHA-256 hash of text?', 'Enter your text and pick the algorithm, and the hash is calculated instantly in the browser.'],
            ['Is my text sent to a server when hashing?', 'No, hashing runs entirely on your device, so the text you enter never leaves your browser.'],
            ['What is a hash used for?', 'A hash is a fixed-length fingerprint of data used to verify integrity, store passwords and detect changes in files.'],
        ],
    ],

    // 15. Base64 Encoder/Decoder
    'base64-encoder' => [
        'guide' => [
            'Paste the text you want to encode or decode.',
            'Choose Encode to convert text to Base64.',
            'Choose Decode to convert Base64 back to text.',
            'Copy the converted output with one click.',
        ],
        'faq' => [
            ['How do I encode text to Base64?', 'Paste your text, click Encode, and the Base64 string appears ready to copy.'],
            ['How do I decode a Base64 string?', 'Paste the Base64 value, click Decode, and the original text is restored instantly.'],
            ['Is Base64 encoding secure or private here?', 'Base64 is an encoding, not encryption, and all conversion happens locally in your browser with nothing sent to a server.'],
        ],
    ],

    // 16. URL Encoder/Decoder
    'url-encoder' => [
        'guide' => [
            'Paste the URL or text to process.',
            'Select Encode to make it URL-safe.',
            'Select Decode to reverse an encoded string.',
            'Copy the result for use in links or code.',
        ],
        'faq' => [
            ['How do I encode special characters in a URL?', 'Paste your text and click Encode to convert spaces and symbols into percent-encoded, URL-safe characters.'],
            ['How do I decode a percent-encoded URL?', 'Paste the encoded URL and click Decode to turn characters like %20 back into readable text.'],
            ['Does URL encoding happen on a server?', 'No, encoding and decoding run entirely in your browser, so your links and text stay private.'],
        ],
    ],

    // 17. Lorem Ipsum Generator
    'lorem-ipsum' => [
        'guide' => [
            'Choose how many paragraphs, sentences or words you need.',
            'Pick English or Gujarati placeholder text.',
            'Click Generate to create the dummy text.',
            'Copy the generated text into your design or document.',
        ],
        'faq' => [
            ['What is Lorem Ipsum used for?', 'Lorem Ipsum is placeholder text used by designers and developers to fill layouts before real content is ready.'],
            ['How much Lorem Ipsum text can I generate?', 'You can generate any number of words, sentences or paragraphs to fit your design needs.'],
            ['Can I generate placeholder text in Gujarati?', 'Yes, switch the language option to Gujarati to produce dummy text in the Gujarati script.'],
        ],
    ],

    // 18. WiFi QR Card Generator
    'wifi-qr-card' => [
        'guide' => [
            'Enter your WiFi network name (SSID).',
            'Type the password and select the security type.',
            'Preview the printable card with the QR code.',
            'Download or print the card to share your WiFi easily.',
        ],
        'faq' => [
            ['How do I make a WiFi QR code guests can scan?', 'Enter your network name and password, and a scannable QR code is created that connects phones automatically.'],
            ['Can I print the WiFi QR code as a card?', 'Yes, the tool builds a printable card you can download and place near your router for guests.'],
            ['Is my WiFi password safe in this tool?', 'Yes, the QR card is generated locally in your browser and your network details are never uploaded to any server.'],
        ],
    ],

];
