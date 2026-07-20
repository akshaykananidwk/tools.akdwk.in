<?php
/** KRISHNA TOOLS — English guide + FAQ content (document & CCTV tools). */
return [

    // 1. Excel to CSV
    'excel-to-csv' => [
        'guide' => [
            'Upload or drag your .xlsx or .xls file into the tool.',
            'Pick the worksheet you want to export if the file has multiple sheets.',
            'Choose the delimiter (comma is standard for CSV).',
            'Click Convert and download the generated .csv file.',
        ],
        'faq' => [
            ['How do I convert an Excel file to CSV online?', 'Just upload your .xlsx or .xls file, select the sheet, and click Convert. The tool reads the cells and outputs a plain-text CSV you can download instantly.'],
            ['Will my Excel formulas be kept in the CSV?', 'No. CSV stores only the calculated values, not the formulas, because CSV is a plain-text format with no formula support.'],
            ['Does converting Excel to CSV work with multiple sheets?', 'CSV holds a single table, so you export one worksheet at a time. Select the sheet you need before converting.'],
        ],
    ],

    // 2. CSV to Excel
    'csv-to-excel' => [
        'guide' => [
            'Upload your .csv file or paste the CSV text into the box.',
            'Confirm the delimiter so columns are split correctly.',
            'Preview the parsed rows and columns.',
            'Click Convert and download the .xlsx workbook.',
        ],
        'faq' => [
            ['How can I turn a CSV into an Excel spreadsheet?', 'Upload the CSV, verify the delimiter, and download the result as a ready-to-open .xlsx file with proper columns.'],
            ['Why do my CSV numbers look wrong in Excel?', 'This usually happens with the wrong delimiter or when leading zeros are dropped. Set the correct separator and format sensitive columns as text.'],
            ['Is my CSV data uploaded to a server?', 'The conversion runs in your browser, so your data stays on your device and is not stored after you leave the page.'],
        ],
    ],

    // 3. JSON to Excel
    'json-to-excel' => [
        'guide' => [
            'Paste your JSON array of objects or upload a .json file.',
            'Check that each object becomes a row with keys as column headers.',
            'Review the preview table for the expected fields.',
            'Click Convert and download the .xlsx file.',
        ],
        'faq' => [
            ['How do I convert a JSON array to an Excel table?', 'Paste a JSON array of objects and the tool maps each object to a row and each key to a column header, then exports an .xlsx file.'],
            ['What JSON structure works best for Excel export?', 'A flat array of objects with the same keys converts cleanly. Deeply nested JSON may be flattened or need reshaping first.'],
            ['Can I convert nested JSON to Excel?', 'Nested objects are flattened where possible, but very complex structures are better simplified into a flat array before converting.'],
        ],
    ],

    // 4. Excel to JSON
    'excel-to-json' => [
        'guide' => [
            'Upload your .xlsx or .xls file.',
            'Select the worksheet and confirm the header row.',
            'Choose whether to output an array of objects.',
            'Click Convert and copy or download the JSON.',
        ],
        'faq' => [
            ['How do I convert Excel data to JSON?', 'Upload the workbook, pick the sheet, and the tool turns each row into a JSON object using the header row as keys.'],
            ['Which row is used for the JSON keys?', 'The first (header) row becomes the object keys, and every row below it becomes one JSON object.'],
            ['Can I get pretty-printed JSON from Excel?', 'Yes, you can choose formatted (indented) output for readability or minified output for smaller file size.'],
        ],
    ],

    // 5. JSON Formatter
    'json-formatter' => [
        'guide' => [
            'Paste your JSON into the input box.',
            'Choose Beautify to indent or Minify to compress.',
            'Read any error message if the JSON is invalid, and fix it.',
            'Copy the formatted result to your clipboard.',
        ],
        'faq' => [
            ['How do I beautify messy JSON?', 'Paste the JSON and click Beautify to add clean indentation and line breaks that make the structure easy to read.'],
            ['Why does the JSON formatter show a syntax error?', 'JSON must use double quotes, no trailing commas, and matching brackets. The error usually points to the line where a quote, comma, or bracket is missing.'],
            ['What is the difference between beautify and minify JSON?', 'Beautify adds spacing for human reading, while minify strips whitespace to make the payload as small as possible for transfer.'],
        ],
    ],

    // 6. XML <-> JSON
    'xml-json' => [
        'guide' => [
            'Paste your XML or JSON into the input box.',
            'Select the conversion direction (XML to JSON or JSON to XML).',
            'Click Convert and check the output structure.',
            'Copy the converted result.',
        ],
        'faq' => [
            ['How do I convert XML to JSON online?', 'Paste your XML, choose XML to JSON, and the tool maps elements and attributes into a JSON object you can copy.'],
            ['Are XML attributes preserved when converting to JSON?', 'Yes, attributes are typically represented with a prefix such as @ so no information is lost in the conversion.'],
            ['Can I convert JSON back into XML?', 'Yes, switch the direction to JSON to XML and the tool wraps keys as elements to rebuild a valid XML document.'],
        ],
    ],

    // 7. CSV Merger
    'csv-merger' => [
        'guide' => [
            'Upload two or more CSV files with matching columns.',
            'Confirm the header row so files line up correctly.',
            'Choose to keep or drop repeated headers from later files.',
            'Click Merge and download the combined CSV.',
        ],
        'faq' => [
            ['How do I merge multiple CSV files into one?', 'Upload all the CSV files, confirm they share the same columns, and the tool stacks the rows into a single downloadable CSV.'],
            ['What if my CSV files have different columns?', 'For a clean merge the headers should match. If columns differ, align them first or the extra columns may be left blank.'],
            ['Does the merger remove the repeated header rows?', 'Yes, you can choose to keep only the first header so the merged file does not repeat column names between files.'],
        ],
    ],

    // 8. Word Counter
    'word-counter' => [
        'guide' => [
            'Type or paste your text into the box.',
            'Read the live counts for words, characters, and sentences.',
            'Check the character count with and without spaces.',
            'Use the reading-time estimate to gauge length.',
        ],
        'faq' => [
            ['How do I count words in a block of text?', 'Paste the text and the counter instantly shows the number of words, characters, and sentences as you type.'],
            ['Does the word counter count characters with spaces?', 'Yes, it shows both totals: characters including spaces and characters excluding spaces, so you can meet any limit.'],
            ['How is reading time calculated?', 'Reading time is estimated from the word count using an average speed of about 200 to 250 words per minute.'],
        ],
    ],

    // 9. Case Converter
    'case-converter' => [
        'guide' => [
            'Paste your text into the input area.',
            'Pick a case: UPPERCASE, lowercase, Title Case, or Sentence case.',
            'See the converted text update instantly.',
            'Copy the result to your clipboard.',
        ],
        'faq' => [
            ['How do I change text to Title Case online?', 'Paste your text and select Title Case to capitalise the first letter of each major word automatically.'],
            ['What is the difference between Title Case and Sentence case?', 'Title Case capitalises most words, while Sentence case only capitalises the first letter of each sentence.'],
            ['Can I convert text to all lowercase quickly?', 'Yes, choose the lowercase option and the entire text is converted with a single click, ready to copy.'],
        ],
    ],

    // 10. Find & Replace
    'find-replace' => [
        'guide' => [
            'Paste your text into the box.',
            'Enter the word or phrase to find and its replacement.',
            'Toggle options like match case or whole word if needed.',
            'Click Replace All and copy the updated text.',
        ],
        'faq' => [
            ['How do I find and replace text online?', 'Paste your text, type what to find and what to replace it with, then click Replace All to update every match at once.'],
            ['Can I do case-sensitive find and replace?', 'Yes, enable the match-case option so only text with the exact same capitalisation is replaced.'],
            ['Does the tool support replacing with regular expressions?', 'When the regex option is on you can match patterns; otherwise it does a plain literal text replacement.'],
        ],
    ],

    // 11. Duplicate Line Remover
    'duplicate-remover' => [
        'guide' => [
            'Paste your list or text with one item per line.',
            'Choose whether comparison should ignore case.',
            'Click Remove Duplicates to keep only unique lines.',
            'Copy or download the cleaned list.',
        ],
        'faq' => [
            ['How do I remove duplicate lines from a list?', 'Paste your list with one item per line and click Remove Duplicates to keep only the first occurrence of each line.'],
            ['Can I ignore case when removing duplicate lines?', 'Yes, turn on ignore case so entries like Apple and apple are treated as the same and only one is kept.'],
            ['Does removing duplicates keep the original order?', 'Yes, the tool preserves the first appearance of each line so the remaining order stays the same.'],
        ],
    ],

    // 12. Text Diff Checker
    'text-diff' => [
        'guide' => [
            'Paste the original text into the left box.',
            'Paste the changed text into the right box.',
            'Click Compare to highlight additions and deletions.',
            'Review the colour-coded differences line by line.',
        ],
        'faq' => [
            ['How do I compare two texts for differences?', 'Paste the two versions side by side and click Compare to see added and removed lines highlighted in different colours.'],
            ['What do the colours in a text diff mean?', 'Typically green marks added content and red marks removed content, making it easy to spot exactly what changed.'],
            ['Can the diff checker compare code as well as text?', 'Yes, it works line by line so it is equally useful for comparing code snippets, config files, or plain paragraphs.'],
        ],
    ],

    // 13. Markdown to HTML
    'markdown-to-html' => [
        'guide' => [
            'Paste your Markdown into the input box.',
            'Watch the live HTML preview render on the side.',
            'Adjust the Markdown if a heading or link looks wrong.',
            'Copy the generated HTML code.',
        ],
        'faq' => [
            ['How do I convert Markdown to HTML?', 'Paste your Markdown and the tool instantly generates clean HTML with the headings, lists, and links converted to tags.'],
            ['Does the converter support tables and code blocks?', 'Yes, standard Markdown features like tables, fenced code blocks, and links are converted into their HTML equivalents.'],
            ['Can I preview the HTML before copying it?', 'Yes, a live preview shows the rendered output as you type so you can confirm it looks right before copying.'],
        ],
    ],

    // 14. HTML to Text
    'html-to-text' => [
        'guide' => [
            'Paste your HTML markup into the box.',
            'Click Convert to strip out all the tags.',
            'Review the clean plain text output.',
            'Copy the text for use anywhere.',
        ],
        'faq' => [
            ['How do I strip HTML tags to get plain text?', 'Paste the HTML and click Convert. The tool removes every tag and returns only the readable text content.'],
            ['Will links and images survive the HTML to text conversion?', 'Tags are removed, so images disappear and links usually become plain text; only the visible words remain.'],
            ['Does the tool keep line breaks from the HTML?', 'Block elements such as paragraphs and headings are converted into line breaks so the text stays readable.'],
        ],
    ],

    // 15. CCTV Storage Calculator
    'cctv-storage-calculator' => [
        'guide' => [
            'Enter the number of cameras and their resolution.',
            'Set the bitrate or frame rate and codec (H.264 or H.265).',
            'Enter the retention period in days and recording hours per day.',
            'Read the estimated total HDD size and pick the next standard drive.',
        ],
        'faq' => [
            ['How much hard disk do I need for a CCTV system?', 'It depends on camera count, resolution, bitrate, hours recorded per day, and retention days. Enter those values and the calculator estimates the total HDD size; always round up to the next standard drive.'],
            ['Does H.265 reduce CCTV storage compared to H.264?', 'Yes, H.265 (HEVC) roughly halves the storage of H.264 at the same quality, so choosing it can nearly double your retention on the same disk.'],
            ['Are CCTV storage results exact?', 'No, they are estimates. Real usage varies with scene motion and bitrate mode, so add roughly 10 to 20 percent headroom when buying the drive.'],
        ],
    ],

    // 16. Reverse Storage Calculator
    'reverse-storage-calculator' => [
        'guide' => [
            'Enter the total HDD size you already have installed.',
            'Enter camera count, resolution, and bitrate.',
            'Set the codec and recording hours per day.',
            'Read how many days of footage the disk can hold.',
        ],
        'faq' => [
            ['How many days of CCTV footage will my hard drive store?', 'Enter your HDD size along with camera count, resolution, and bitrate, and the calculator estimates the retention in days before the oldest footage is overwritten.'],
            ['Why did my recorder keep fewer days than expected?', 'Higher motion, higher bitrate, or continuous 24-hour recording all consume more space, so real retention is often shorter than a best-case estimate.'],
            ['Can switching to H.265 give me more recording days?', 'Yes, because H.265 roughly halves the data of H.264 at similar quality, the same disk can typically store close to twice as many days.'],
        ],
    ],

    // 17. CCTV Bandwidth Calculator
    'cctv-bandwidth-calculator' => [
        'guide' => [
            'Enter the number of cameras to be viewed remotely.',
            'Set each camera bitrate or main/sub stream in Mbps.',
            'Choose how many simultaneous remote viewers there will be.',
            'Read the total upload speed required at the site.',
        ],
        'faq' => [
            ['How much upload speed do I need to view CCTV remotely?', 'Add up the bitrate of every stream being sent out at once. The calculator totals the Mbps so you can compare it against your broadband upload speed.'],
            ['Why is upload speed more important than download for CCTV?', 'Remote viewing sends video out of the site, so it relies on upload bandwidth, which is often much lower than download on typical broadband plans.'],
            ['Can I reduce CCTV bandwidth for mobile viewing?', 'Yes, viewing the sub-stream instead of the main stream sends a lower-resolution feed that uses far less bandwidth. These figures are estimates and vary with scene complexity.'],
        ],
    ],

    // 18. Lens / Focal Length Calculator
    'lens-calculator' => [
        'guide' => [
            'Enter the distance from the camera to the target area.',
            'Enter the sensor size and desired scene width.',
            'Read the recommended focal length in millimetres.',
            'Check the resulting horizontal field of view angle.',
        ],
        'faq' => [
            ['How do I choose the right lens mm for a CCTV camera?', 'Enter the distance to the subject and the width you want to cover, and the calculator returns the focal length in mm plus the field of view angle.'],
            ['Does a higher mm lens mean more zoom?', 'Yes, a larger focal length narrows the field of view and magnifies distant subjects, while a smaller mm gives a wider but less detailed view.'],
            ['What lens do I need to read a number plate at a distance?', 'Number plate capture needs high pixel density on target, which usually means a longer focal length; use the PPM calculator together with this tool to confirm.'],
        ],
    ],

    // 19. Camera Coverage / FOV Visualizer
    'fov-visualizer' => [
        'guide' => [
            'Upload or set the dimensions of your floor plan.',
            'Place a camera and set its lens angle and range.',
            'Rotate the camera to aim the coverage cone.',
            'Add more cameras to check for blind spots and overlap.',
        ],
        'faq' => [
            ['How can I plan CCTV camera coverage on a floor plan?', 'Place cameras on your plan and set each lens angle and range. The visualizer draws the coverage cone so you can see exactly what each camera sees.'],
            ['How do I find blind spots in my camera layout?', 'Add all cameras to the plan and look for gaps between the coverage cones; those uncovered areas are the blind spots to fix with extra cameras.'],
            ['Does a wider lens angle always give better coverage?', 'A wider angle covers more area but reduces detail at distance, so balance the field of view against the pixel density you need on target.'],
        ],
    ],

    // 20. IP / Subnet Calculator
    'ip-subnet-calculator' => [
        'guide' => [
            'Enter an IP address and its CIDR prefix or subnet mask.',
            'Click Calculate to compute the network details.',
            'Read the network address, broadcast, and usable range.',
            'Note the total and usable host counts for planning.',
        ],
        'faq' => [
            ['How do I calculate usable IPs for a subnet?', 'Enter the IP and CIDR prefix, and the tool shows the network address, broadcast, and the usable host range between them.'],
            ['What does a /24 subnet mean?', 'A /24 uses a 255.255.255.0 mask, giving 256 addresses with 254 usable hosts after reserving the network and broadcast addresses.'],
            ['Why are two IPs unusable in every subnet?', 'The first address is the network identifier and the last is the broadcast address, so they cannot be assigned to devices like cameras or an NVR.'],
        ],
    ],

    // 21. IP Range Planner for NVR
    'ip-range-planner' => [
        'guide' => [
            'Enter the starting IP for your camera range.',
            'Enter the number of cameras that need static IPs.',
            'Set the NVR and gateway addresses to avoid clashes.',
            'Generate the assignment list and export it for setup.',
        ],
        'faq' => [
            ['How do I assign static IP addresses to CCTV cameras?', 'Set a starting IP and the number of cameras, and the planner auto-assigns sequential static addresses while reserving the NVR and gateway to avoid conflicts.'],
            ['Should cameras and the NVR be on the same subnet?', 'Yes, cameras and the NVR normally share one subnet so they can communicate directly without a router in between.'],
            ['How do I avoid IP conflicts on a camera network?', 'Assign static IPs outside the router DHCP pool, keep one address per device, and never reuse the gateway or NVR address for a camera.'],
        ],
    ],

    // 22. PoE Power Budget Calculator
    'poe-calculator' => [
        'guide' => [
            'Enter your switch total PoE power budget in watts.',
            'Enter the number of cameras and watts per camera.',
            'Include any heaters or PTZ motors that draw extra power.',
            'Read the total draw and the remaining headroom percentage.',
        ],
        'faq' => [
            ['How do I calculate the PoE power budget for a switch?', 'Add the wattage of every powered device and compare it to the switch total PoE budget. The calculator shows the used watts and remaining headroom.'],
            ['How much PoE headroom should I leave on a switch?', 'Leave roughly 20 percent headroom so the switch is not maxed out; PTZ cameras and heaters draw peak power that can exceed their idle rating.'],
            ['What is the difference between PoE, PoE+, and PoE++?', 'PoE (802.3af) supplies up to about 15.4W, PoE+ (802.3at) up to about 30W, and PoE++ (802.3bt) up to about 60 to 90W per port for high-draw cameras.'],
        ],
    ],

    // 23. Cable Length / Voltage Drop
    'cable-length-calculator' => [
        'guide' => [
            'Choose the cable type (Cat6 for IP or RG59 for analog).',
            'Enter the run length and the supply voltage.',
            'Enter the camera current draw for DC power runs.',
            'Read the voltage drop and whether the run is within limits.',
        ],
        'faq' => [
            ['What is the maximum Cat6 cable length for an IP camera?', 'Ethernet including PoE is rated to about 100 metres per run. Beyond that you need a PoE extender or a switch in between to keep the link stable.'],
            ['Why does my 12V camera flicker on a long cable?', 'Long thin cable causes voltage drop, so the camera receives less than 12V. Use thicker gauge wire, a higher supply voltage, or power locally to fix it.'],
            ['How far can RG59 carry an analog CCTV signal?', 'Quality RG59 with a proper power core typically runs well up to around 300 metres for analog, though longer runs may need a signal booster.'],
        ],
    ],

    // 24. DVR/NVR Channel Planner
    'dvr-channel-planner' => [
        'guide' => [
            'Enter how many analog cameras you have.',
            'Enter how many IP cameras you need to add.',
            'Set your growth allowance for future cameras.',
            'Read the recommended recorder channel count and type.',
        ],
        'faq' => [
            ['How do I choose between a DVR and an NVR?', 'A DVR records analog cameras over coax, while an NVR records IP cameras over the network. For a mix, a hybrid recorder supports both in one unit.'],
            ['Can I connect IP cameras to an analog DVR?', 'Only a hybrid or XVR-style recorder accepts IP channels; a pure analog DVR cannot record network cameras without added hardware.'],
            ['How many channels should my recorder have?', 'Add your current cameras plus room to grow, then round up to the next standard size such as 4, 8, 16, or 32 channels.'],
        ],
    ],

    // 25. PPM / Pixel Density Calculator
    'ppm-calculator' => [
        'guide' => [
            'Enter the camera resolution and the scene width covered.',
            'The tool computes pixels per metre (PPM) on target.',
            'Compare the PPM against detect, recognise, and identify thresholds.',
            'Adjust the lens or distance to reach the required PPM.',
        ],
        'faq' => [
            ['What is pixel density (PPM) in CCTV?', 'PPM is pixels per metre on the target. It measures how much detail a camera captures at a given distance, which decides whether you can detect, recognise, or identify a person.'],
            ['How many PPM do I need to identify a face?', 'As a rule of thumb, roughly 25 PPM allows detection, about 125 PPM allows recognition, and around 250 PPM or more is needed to identify an unknown person.'],
            ['Why does my HD camera fail to identify faces far away?', 'PPM drops as distance grows because the same pixels are spread over a wider scene. Use a longer lens or move the camera closer to raise pixel density on target.'],
        ],
    ],

    // 26. CCTV Quotation Generator
    'cctv-quotation' => [
        'guide' => [
            'Add line items with quantity, unit price, and description.',
            'Set the GST rate to calculate tax automatically.',
            'Enter customer details and any labour or installation charges.',
            'Generate the PDF quote on the AK Computer letterhead and download it.',
        ],
        'faq' => [
            ['How do I make a GST CCTV quotation for a customer?', 'Add each item with quantity and price, set the GST rate, and the generator totals everything into a professional PDF on the AK Computer letterhead.'],
            ['Does the quotation calculate GST automatically?', 'Yes, enter the GST percentage and the tool computes the tax and grand total for you so the figures are accurate.'],
            ['Can I add installation and labour charges to the quote?', 'Yes, add them as separate line items so the customer sees a clear breakdown of hardware, labour, and taxes.'],
        ],
    ],

    // 27. CCTV AMC Contract Generator
    'cctv-amc' => [
        'guide' => [
            'Enter the customer name, site address, and contract period.',
            'List the equipment covered under the AMC.',
            'Set the number of visits and the annual charge.',
            'Generate the AMC contract PDF and download it for signing.',
        ],
        'faq' => [
            ['What is a CCTV AMC contract?', 'An AMC (Annual Maintenance Contract) is a yearly agreement covering periodic servicing, checks, and support for a CCTV system for a fixed fee.'],
            ['What should a CCTV AMC include?', 'It should list the covered equipment, the number of preventive visits per year, response time for faults, and whether spare parts are included or charged extra.'],
            ['How many maintenance visits are typical in a CCTV AMC?', 'Most contracts include two to four scheduled visits a year plus breakdown call-outs; set the visit count that suits the site before generating the PDF.'],
        ],
    ],

    // 28. Default Password / Port Reference
    'cctv-password-reference' => [
        'guide' => [
            'Select the camera or recorder brand from the list.',
            'View the default username, port, and web access details.',
            'Note the RTSP and HTTP ports for integration.',
            'Change the default password immediately after first login.',
        ],
        'faq' => [
            ['What are the default ports for CCTV cameras?', 'Common defaults are 80 for HTTP web access, 554 for RTSP streaming, and a brand-specific TCP or server port; check the reference for your exact brand.'],
            ['Why should I change the default CCTV password?', 'Default credentials are publicly known and are the number one way cameras get hacked, so always set a strong unique password on first login.'],
            ['What is the RTSP port used for on a CCTV camera?', 'Port 554 (RTSP) delivers the live video stream and is used to pull feeds into an NVR, VMS, or apps like VLC.'],
        ],
    ],

    // 29. Camera Time Sync Helper
    'camera-time-sync' => [
        'guide' => [
            'Pick an NTP server from the provided list.',
            'Apply the IST offset (UTC +5:30) for correct local time.',
            'Convert between epoch timestamps and readable dates as needed.',
            'Enter the observed drift to estimate the correction interval.',
        ],
        'faq' => [
            ['Why is my CCTV camera showing the wrong time?', 'Cameras drift without a time source. Point them at an NTP server and set the correct IST offset of UTC +5:30 so the timestamp stays accurate.'],
            ['What NTP server should I use for CCTV in India?', 'Public pools such as in.pool.ntp.org or time.google.com work well; the recorder and all cameras should sync to the same server to keep timestamps aligned.'],
            ['What is an epoch timestamp in CCTV logs?', 'Epoch time is the number of seconds since 1 January 1970 UTC. Converting it to a readable IST date helps you match log entries to real events.'],
        ],
    ],

    // 30. CCTV Site Survey Form Generator
    'cctv-site-survey' => [
        'guide' => [
            'Enter the site name, address, and contact person.',
            'List the areas to be covered and proposed camera positions.',
            'Note power points, cable routes, and internet availability.',
            'Generate the printable site survey checklist PDF.',
        ],
        'faq' => [
            ['What is a CCTV site survey?', 'A site survey is a pre-installation checklist that records camera locations, cable routes, power availability, and network details so the job is quoted and installed correctly.'],
            ['Why is a site survey important before installing CCTV?', 'It prevents surprises on installation day by confirming coverage needs, power points, and cable distances, which leads to an accurate quote and a clean install.'],
            ['What should a CCTV site survey checklist cover?', 'It should capture camera positions, lens and coverage needs, cable lengths, power sources, internet for remote viewing, and any lighting or mounting constraints.'],
        ],
    ],

];
