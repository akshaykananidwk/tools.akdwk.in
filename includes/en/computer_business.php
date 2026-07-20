<?php
/** KRISHNA TOOLS — English guide + FAQ content (computer-shop & business tools). */
return [
  'data-storage-converter' => [
    'guide' => [
      'Enter the value you want to convert in the input box.',
      'Pick the source unit (bit, Byte, KB, MB, GB or TB).',
      'Choose whether to use the 1000 (decimal) or 1024 (binary) base.',
      'Read the converted result across all units instantly.',
    ],
    'faq' => [
      ['What is the difference between 1000 and 1024 based storage units?', 'Drive makers use the decimal system where 1KB = 1000 bytes, while operating systems use the binary system where 1KB = 1024 bytes. That is why a 1TB drive shows around 931GB in Windows.'],
      ['How many bits are there in one Byte?', 'One Byte equals 8 bits. Internet speeds are usually quoted in bits (Mbps) while file sizes are quoted in Bytes (MB), so divide Mbps by 8 to get MB/s.'],
      ['Which unit is bigger, MB or GB?', 'GB is larger. 1GB equals 1024MB in the binary system or 1000MB in the decimal system, so files measured in GB are far bigger than those measured in MB.'],
    ],
  ],
  'ram-calculator' => [
    'guide' => [
      'Select your motherboard DDR type (DDR3, DDR4 or DDR5).',
      'Enter the number of RAM slots available on the board.',
      'Add the size of each installed module in GB.',
      'Check the total capacity and the dual-channel readiness result.',
    ],
    'faq' => [
      ['Why do I need RAM in matched pairs for dual-channel?', 'Dual-channel mode reads two modules at once for higher bandwidth. For it to work you need two sticks of the same size and speed in the correctly coloured slots, for example 2x8GB instead of a single 16GB stick.'],
      ['Can I mix DDR4 and DDR5 memory on one motherboard?', 'No. DDR generations use different physical notches and voltages, so a DDR5 board only accepts DDR5 and a DDR4 board only accepts DDR4. Always match the RAM to the board specification.'],
      ['Is 16GB of RAM enough for everyday work?', 'For office work, browsing and light multitasking 16GB is comfortable in 2026. For heavy editing, virtual machines or modern gaming, 32GB installed as a dual-channel pair is recommended.'],
    ],
  ],
  'hdd-real-capacity' => [
    'guide' => [
      'Enter the advertised drive size printed on the box, such as 1TB.',
      'Select the capacity unit shown by the manufacturer.',
      'Let the tool convert from decimal (1000) to binary (1024) counting.',
      'Read the real usable capacity your operating system will display.',
    ],
    'faq' => [
      ['Why does my 1TB hard drive only show 931GB?', 'The manufacturer counts 1TB as 1,000,000,000,000 bytes (decimal), but Windows divides by 1024 at each step (binary). The result is roughly 931GB of usable space, so nothing is missing or faulty.'],
      ['How much does a 500GB drive really give me?', 'A 500GB drive gives about 465GB usable after the 1000 versus 1024 conversion, and a small amount more is reserved for the file system, so plan for around 460GB of free space.'],
      ['Is the missing space a sign of a defective drive?', 'No. Every brand loses the same percentage because of decimal versus binary counting. It is normal and consistent, and formatting for NTFS or exFAT uses only a tiny extra amount.'],
    ],
  ],
  'partition-planner' => [
    'guide' => [
      'Enter the total usable size of your drive in GB.',
      'Choose how many partitions you want, such as C, D and E.',
      'Set the desired size or percentage for the Windows C drive.',
      'View the suggested split with recommended sizes for each partition.',
    ],
    'faq' => [
      ['How big should the C drive partition be for Windows?', 'For Windows 11 with common software, allow 120GB to 150GB for the C drive. This leaves room for updates, temporary files and installed programs without slowing the system down.'],
      ['Should I keep my data on a separate partition?', 'Yes. Keeping documents, photos and downloads on a separate D drive means you can reinstall Windows on C without losing your personal files, which makes servicing much safer.'],
      ['Does partitioning improve my computer performance?', 'Partitioning mainly improves organisation and backup safety rather than raw speed. On an SSD the speed difference is negligible, but a clean data partition still makes maintenance and recovery easier.'],
    ],
  ],
  'file-transfer-time' => [
    'guide' => [
      'Enter the total file or folder size you want to copy.',
      'Select the size unit (MB, GB or TB).',
      'Enter the transfer speed of your USB port, drive or network.',
      'Read the estimated time needed to complete the transfer.',
    ],
    'faq' => [
      ['Why is my USB copy slower than the rated speed?', 'Rated speeds are peak values. Real transfers are slower because of many small files, drive caching, and USB overhead. A USB 3.0 stick rated at 100MB/s often averages 30-60MB/s with mixed files.'],
      ['How long does it take to copy 10GB over USB 3.0?', 'At a realistic 100MB/s, 10GB (about 10240MB) takes roughly 100 seconds, or under two minutes. Slower USB 2.0 at 30MB/s would take around six minutes for the same data.'],
      ['Does the type of file affect transfer time?', 'Yes. One large 10GB movie copies quickly, while thousands of tiny files of the same total size copy much slower because the drive must open and close each file individually.'],
    ],
  ],
  'backup-estimator' => [
    'guide' => [
      'List the data types you want to back up, such as documents and photos.',
      'Enter the approximate size of each category in GB.',
      'Choose how many backup copies or versions you want to keep.',
      'Review the total storage required and pick a suitable drive size.',
    ],
    'faq' => [
      ['How much backup storage do I actually need?', 'A good rule is to plan for at least 1.5 to 2 times your current data size. This leaves room for growth and for keeping a few older versions of important files.'],
      ['Should I follow the 3-2-1 backup rule?', 'Yes. Keep 3 copies of your data on 2 different types of media with 1 copy stored off-site or in the cloud. This protects you against drive failure, theft and accidental deletion.'],
      ['How often should I run a backup?', 'For business accounts and invoices, a daily backup is ideal. For home photos and documents, a weekly backup is usually enough, with an extra copy before any Windows reinstall.'],
    ],
  ],
  'psu-calculator' => [
    'guide' => [
      'Select your CPU and graphics card from the component list.',
      'Add other parts such as drives, RAM sticks and case fans.',
      'Let the tool total the estimated power draw in watts.',
      'Read the recommended SMPS wattage that includes safe headroom.',
    ],
    'faq' => [
      ['How much extra wattage headroom should a PSU have?', 'Choose a PSU rated 20-30% above your peak load. If your components draw 400W, a 500W to 550W unit keeps the SMPS running cool, efficient and reliable under full load.'],
      ['Does an 80 Plus rating matter for my build?', 'Yes. An 80 Plus Bronze or higher unit wastes less power as heat and runs quieter. For long daily use it saves electricity and protects your components with cleaner, stable voltage.'],
      ['Can a weak or cheap SMPS damage my computer?', 'A low quality or undersized SMPS can cause random restarts, freezes and in bad cases damage the motherboard. Always size the PSU with headroom and choose a reputable branded unit.'],
    ],
  ],
  'pc-build-cost' => [
    'guide' => [
      'Add each component with its purchase cost to the parts list.',
      'Enter your desired profit margin percentage.',
      'Select the applicable GST rate for the build.',
      'View the final quoted price including margin and tax.',
    ],
    'faq' => [
      ['What GST rate applies to an assembled desktop computer?', 'Assembled desktops and most computer parts attract 18% GST in India. For an intra-state sale this splits into 9% CGST and 9% SGST, while an inter-state sale uses 18% IGST.'],
      ['How do I add profit margin to a PC build quote?', 'Total your parts cost, then apply your margin. For a 15% margin on a 40,000 rupee build, sell price before tax is 40000 divided by 0.85, about 47,059 rupees, then add GST on top.'],
      ['Should I quote parts individually or as one package?', 'A single package price is cleaner for the customer and lets you balance margins across parts. Keep an internal parts list for warranty and service records even when quoting one total.'],
    ],
  ],
  'ups-backup-time' => [
    'guide' => [
      'Enter the UPS rating in VA and its power factor.',
      'Add the total connected load in watts.',
      'Enter the battery voltage and capacity in Ah.',
      'Read the estimated backup time in minutes.',
    ],
    'faq' => [
      ['How is UPS backup time calculated?', 'Backup minutes are roughly battery voltage multiplied by Ah multiplied by 60, divided by the load in watts, then reduced by inverter efficiency of around 80%. Higher load means shorter runtime.'],
      ['How long will a 600VA UPS run a desktop?', 'A typical 600VA UPS with a 7Ah battery backs up a 150W desktop for about 5 to 10 minutes, which is enough time to save your work and shut down safely, not for long usage.'],
      ['Can I increase my UPS backup time?', 'Yes, by connecting a larger external battery or reducing the load. Switching to an efficient LED monitor and an SSD-based PC also lowers wattage and stretches the available backup.'],
    ],
  ],
  'screen-ppi-calculator' => [
    'guide' => [
      'Enter the screen diagonal size in inches.',
      'Enter the horizontal resolution in pixels.',
      'Enter the vertical resolution in pixels.',
      'Read the calculated pixel density in PPI.',
    ],
    'faq' => [
      ['What is a good PPI for a computer monitor?', 'Around 90 to 110 PPI is comfortable for a desktop monitor at normal viewing distance. A 24-inch 1080p screen is about 92 PPI, while a 27-inch 4K screen reaches roughly 163 PPI for sharp text.'],
      ['How is PPI calculated from resolution and size?', 'PPI is the diagonal pixel count divided by the screen size in inches. Take the square root of width squared plus height squared in pixels, then divide by the diagonal inches.'],
      ['Does higher PPI always mean a better display?', 'Higher PPI gives sharper text and images, but beyond what the eye resolves at your distance the benefit shrinks. Panel type, brightness and colour accuracy matter just as much as PPI.'],
    ],
  ],
  'aspect-ratio-calculator' => [
    'guide' => [
      'Enter the original width and height in pixels.',
      'Let the tool reduce it to a simple aspect ratio like 16:9.',
      'Enter a new width or height you want to scale to.',
      'Read the matching dimension that keeps the ratio intact.',
    ],
    'faq' => [
      ['What does a 16:9 aspect ratio mean?', 'A 16:9 ratio means the width is 16 units for every 9 units of height. It is the standard for modern monitors, TVs and video, giving a widescreen shape of 1920x1080 and similar resolutions.'],
      ['How do I resize an image without stretching it?', 'Keep the same aspect ratio. If the original is 1600x900 (16:9) and you want 1280 wide, the height must be 720 so the picture scales cleanly without distortion.'],
      ['What is the difference between 16:9 and 4:3?', '4:3 is the older, more square shape used by legacy monitors and documents, while 16:9 is wider and suited to modern video. Mixing them adds black bars on the sides or top.'],
    ],
  ],
  'printer-page-cost' => [
    'guide' => [
      'Enter the price of the ink or toner cartridge.',
      'Enter the rated page yield of that cartridge.',
      'Optionally add paper cost per sheet.',
      'Read the total cost to print one page.',
    ],
    'faq' => [
      ['How do I calculate the cost per printed page?', 'Divide the cartridge price by its rated page yield. A 1,500 rupee cartridge rated for 2,000 pages costs about 0.75 rupees per page in ink, before adding paper cost.'],
      ['Why is my real page cost higher than the rating?', 'Cartridge yields are measured at 5% page coverage. Dark documents, photos and graphics use far more ink, so heavy pages cost more and reduce the number of pages you actually get.'],
      ['Are laser printers cheaper per page than inkjet?', 'For plain text, laser printers usually have a lower cost per page and toner does not dry out. Inkjets are cheaper to buy and better for colour photos but cost more per page over time.'],
    ],
  ],
  'toner-yield-estimator' => [
    'guide' => [
      'Enter your average number of printed pages per month.',
      'Enter the rated page yield of one cartridge.',
      'Let the tool work out cartridges used per month.',
      'Read how many cartridges you will need per year.',
    ],
    'faq' => [
      ['How many toner cartridges will I use in a year?', 'Multiply monthly pages by 12 and divide by the cartridge yield. Printing 1,000 pages a month with a 2,000 page cartridge needs about 6 cartridges per year.'],
      ['Does page coverage change how long a cartridge lasts?', 'Yes. Ratings assume 5% coverage. Invoices and forms with logos or dense text use more toner, so real yield can be lower and you may need cartridges sooner than estimated.'],
      ['Should a busy office buy high-yield cartridges?', 'For heavy monthly printing, high-yield or XL cartridges lower the cost per page and mean fewer changes. For light use, standard cartridges avoid toner sitting unused for long periods.'],
    ],
  ],
  'download-time-calculator' => [
    'guide' => [
      'Enter the file size you want to download.',
      'Select the size unit (MB or GB).',
      'Enter your internet speed in Mbps.',
      'Read the estimated download time.',
    ],
    'faq' => [
      ['How do I convert internet speed to download time?', 'Internet speed is in bits (Mbps), so divide by 8 for MB per second. A 100Mbps line gives about 12.5MB/s, so a 1GB file takes roughly 80 seconds under ideal conditions.'],
      ['Why does my download run slower than my plan speed?', 'Real speed drops due to server limits, Wi-Fi interference, shared connections and network overhead. You typically get 70-90% of the rated speed, so downloads take a little longer than the ideal figure.'],
      ['How long to download a 4GB file on 50Mbps?', 'At 50Mbps you get about 6.25MB/s, so 4GB (roughly 4096MB) takes around 11 minutes in ideal conditions, and a bit longer with normal network overhead.'],
    ],
  ],
  'video-size-estimator' => [
    'guide' => [
      'Enter the video bitrate in Mbps.',
      'Enter the recording or clip duration in minutes.',
      'Let the tool multiply bitrate by time.',
      'Read the estimated video file size.',
    ],
    'faq' => [
      ['How is video file size calculated from bitrate?', 'File size equals bitrate multiplied by duration. A 10Mbps video for 60 seconds is about 10 divided by 8 times 60, roughly 75MB, so higher bitrate and longer clips mean bigger files.'],
      ['What bitrate should I use for 1080p video?', 'For smooth 1080p at 30fps, 8 to 12Mbps gives good quality. 4K needs 35 to 50Mbps. Higher bitrate means sharper detail but larger files and more storage.'],
      ['How much space does one hour of 1080p video take?', 'At around 10Mbps, one hour of 1080p video is roughly 4.5GB. CCTV footage at lower bitrates uses less, while high bitrate editing masters use considerably more.'],
    ],
  ],
  'mbps-converter' => [
    'guide' => [
      'Enter the value you want to convert.',
      'Choose the source unit, Mbps or MB/s.',
      'Let the tool apply the divide-by-8 conversion.',
      'Read the equivalent value in the other unit.',
    ],
    'faq' => [
      ['What is the difference between Mbps and MB/s?', 'Mbps is megabits per second and MB/s is megabytes per second. Since one byte is 8 bits, you divide Mbps by 8 to get MB/s, so 100Mbps equals 12.5MB/s.'],
      ['Why does my 100Mbps plan download at only 12MB/s?', 'That is correct behaviour, not a fault. 100Mbps divided by 8 is 12.5MB/s, which is the true download speed in megabytes. ISPs advertise in bits to show a bigger number.'],
      ['How do I convert MB/s back to Mbps?', 'Multiply MB/s by 8. If a drive or download reports 20MB/s, that is 160Mbps. This helps compare storage speeds with internet plans on the same scale.'],
    ],
  ],
  'barcode-generator' => [
    'guide' => [
      'Choose the barcode symbology, such as Code128 or EAN13.',
      'Enter the product code or number to encode.',
      'Preview the generated barcode image.',
      'Download or print the barcode for your inventory labels.',
    ],
    'faq' => [
      ['What is the difference between Code128 and EAN13?', 'Code128 encodes letters and numbers and suits internal stock codes of any length. EAN13 is a fixed 13-digit retail standard used on packaged products with a registered manufacturer prefix.'],
      ['Can I use these barcodes for my shop inventory?', 'Yes. Code128 is ideal for in-house stock tags because you can encode your own SKU numbers. Print them on label paper and scan with any standard 1D barcode scanner.'],
      ['Does EAN13 need a valid check digit?', 'Yes. EAN13 calculates a check digit from the first 12 numbers to catch scan errors. The tool adds the correct check digit automatically so your barcodes always scan reliably.'],
    ],
  ],
  'job-sheet-generator' => [
    'guide' => [
      'Enter the customer name, phone number and device details.',
      'Describe the reported fault and any accessories received.',
      'Set the estimated cost and expected delivery date.',
      'Generate the job sheet PDF and share it on WhatsApp.',
    ],
    'faq' => [
      ['What details should a repair job sheet include?', 'A good job sheet records customer contact, device model and serial, reported problem, accessories received, estimate, and a unique token number. This protects both the shop and the customer during the repair.'],
      ['Why give the customer a repair token number?', 'A token number makes it easy to track the device, avoid mix-ups and confirm ownership at pickup. It also links neatly to your service records and warranty follow-up.'],
      ['Can I send the job sheet to the customer instantly?', 'Yes. The tool creates a PDF you can send directly on WhatsApp, so the customer has an immediate record of the estimate and expected delivery date on their phone.'],
    ],
  ],
  'gst-calculator' => [
    'guide' => [
      'Enter the amount and choose inclusive or exclusive of GST.',
      'Select the GST rate, such as 5%, 12%, 18% or 28%.',
      'Choose intra-state or inter-state supply.',
      'Read the tax split and the final total amount.',
    ],
    'faq' => [
      ['How is GST split into CGST and SGST?', 'For an intra-state sale the GST rate is divided equally into CGST and SGST. At 18% this means 9% CGST and 9% SGST, both charged on the same taxable value.'],
      ['When do I charge IGST instead of CGST and SGST?', 'IGST applies to inter-state sales where the buyer is in a different state. Instead of splitting the tax, you charge the full rate as IGST, for example 18% IGST on the taxable value.'],
      ['How do I remove GST from an inclusive price?', 'Divide the inclusive amount by 1 plus the rate. For an 1180 rupee price inclusive of 18% GST, the base is 1180 divided by 1.18, which is 1000 rupees, and the GST is 180 rupees.'],
    ],
  ],
  'invoice-generator' => [
    'guide' => [
      'Add your business details, logo and customer information.',
      'Enter each item with HSN code, quantity and rate.',
      'Select the GST rate and supply type for tax calculation.',
      'Generate the GST tax invoice PDF ready to print or share.',
    ],
    'faq' => [
      ['Is an HSN code required on a GST invoice?', 'Yes. Businesses must show the HSN code for goods based on their turnover. Computers and parts commonly fall under HSN 8471 and related codes, and listing them keeps your invoice GST compliant.'],
      ['What must a valid GST tax invoice contain?', 'A tax invoice needs a unique invoice number, date, seller and buyer GSTIN, item description with HSN, taxable value, the CGST-SGST or IGST split, and the total payable amount.'],
      ['Can I add my shop logo to the invoice?', 'Yes. Adding your logo and business address makes the invoice look professional and builds trust. The generated PDF keeps the logo, HSN details and tax breakup in a clean, printable layout.'],
    ],
  ],
  'quotation-generator' => [
    'guide' => [
      'Enter your business and customer details.',
      'Add the items or services with quantity and price.',
      'Set any taxes, terms and validity period.',
      'Generate the quotation PDF to send to the customer.',
    ],
    'faq' => [
      ['What is the difference between a quotation and an invoice?', 'A quotation is an offer of prices before a sale, with no payment due. An invoice is raised after the customer agrees and is a legal demand for payment including GST.'],
      ['How long should a quotation stay valid?', 'Set a validity period, commonly 7 to 15 days, because hardware prices change often. Mentioning validity protects you if component costs rise before the customer confirms the order.'],
      ['Should I include terms and conditions in a quotation?', 'Yes. Clear terms on payment, delivery time, warranty and price validity avoid disputes later and make your quotation look professional and complete to the customer.'],
    ],
  ],
  'emi-calculator' => [
    'guide' => [
      'Enter the loan or product amount to finance.',
      'Enter the annual interest rate.',
      'Set the tenure in months.',
      'View the monthly EMI and the full amortization table.',
    ],
    'faq' => [
      ['How is EMI calculated on a reducing balance basis?', 'EMI uses the formula P x r x (1+r)^n divided by ((1+r)^n minus 1), where r is the monthly rate and n is the number of months. Interest is charged only on the outstanding balance each month.'],
      ['What is the difference between flat and reducing interest?', 'Flat interest is charged on the full amount for the whole tenure, so the effective rate is higher. Reducing balance charges interest only on the remaining principal, which is cheaper and more transparent.'],
      ['Does a longer tenure reduce my EMI?', 'Yes, a longer tenure lowers the monthly EMI but increases the total interest paid. A shorter tenure raises the EMI but saves money overall, so balance affordability against total cost.'],
    ],
  ],
  'margin-calculator' => [
    'guide' => [
      'Enter the cost price of the product.',
      'Choose whether to target margin or markup percentage.',
      'Enter the desired percentage.',
      'Read the recommended selling price or MRP.',
    ],
    'faq' => [
      ['What is the difference between margin and markup?', 'Markup is profit as a percentage of cost, while margin is profit as a percentage of selling price. A 25% markup on 100 rupees cost gives a 125 rupee price, which is only a 20% margin.'],
      ['How do I set a selling price for a target margin?', 'Divide the cost by (1 minus the margin). For a 20% margin on a 4,000 rupee cost, the selling price is 4000 divided by 0.8, which is 5,000 rupees before GST.'],
      ['Why is a healthy margin important for a computer shop?', 'Hardware prices move fast and warranty support has a cost. A sensible margin covers overheads, service, returns and price fluctuations so the shop stays profitable rather than just breaking even.'],
    ],
  ],
  'discount-calculator' => [
    'guide' => [
      'Enter the original price of the item.',
      'Enter the discount percentage or flat amount.',
      'Let the tool subtract the discount.',
      'Read the final price the customer pays.',
    ],
    'faq' => [
      ['How do I calculate a price after a percentage discount?', 'Multiply the price by (1 minus the discount percentage). A 5,000 rupee item at 10% off becomes 5000 times 0.90, which is 4,500 rupees payable.'],
      ['How do I find the discount percentage from two prices?', 'Subtract the sale price from the original, divide by the original, and multiply by 100. From 5,000 down to 4,000 rupees is 1000 divided by 5000, a 20% discount.'],
      ['Should discount be applied before or after GST?', 'Trade discounts are usually applied to the taxable value first, then GST is charged on the reduced amount. This keeps the invoice compliant and reflects the true price paid.'],
    ],
  ],
  'currency-converter' => [
    'guide' => [
      'Enter the amount you want to convert.',
      'Select the source and target currencies.',
      'Use the live exchange rate or enter a manual rate.',
      'Read the converted amount instantly.',
    ],
    'faq' => [
      ['Where does the exchange rate come from?', 'The tool uses a live market rate when available and lets you enter a manual rate as a fallback. This is useful when you want to match a specific supplier or bank rate for a quote.'],
      ['Why does the bank charge a different rate than shown?', 'Live mid-market rates do not include bank margins, forex markup or transfer fees. Banks and cards add a spread, so the actual amount debited is usually a little higher.'],
      ['Can I use a manual rate for imported hardware quotes?', 'Yes. When quoting imported parts you can lock a manual rate that includes your buffer for currency movement and charges, giving the customer a stable price in rupees.'],
    ],
  ],
  'warranty-tracker' => [
    'guide' => [
      'Enter the product purchase date.',
      'Enter the warranty period in months.',
      'Let the tool add the period to the purchase date.',
      'Read the expiry date and days remaining.',
    ],
    'faq' => [
      ['How do I calculate a warranty expiry date?', 'Add the warranty months to the purchase date. A product bought on 20 July 2026 with a 12 month warranty expires on 20 July 2027, and the tool also shows the days remaining.'],
      ['Does warranty start from purchase or from billing?', 'For most brands warranty starts from the invoice date, which is why keeping the original bill is essential. Some products with serial-based warranty may start from the manufacture date instead.'],
      ['Why should a shop track customer warranty dates?', 'Tracking expiry lets you tell customers before their cover ends, offer timely renewals or AMC, and speed up genuine claims, which builds trust and repeat business.'],
    ],
  ],
  'amc-renewal-calculator' => [
    'guide' => [
      'Enter the AMC start date.',
      'Select the AMC duration, such as 12 months.',
      'Let the tool calculate the renewal date.',
      'Read the due date and days left before renewal.',
    ],
    'faq' => [
      ['What is an AMC and how is its renewal date set?', 'An AMC is an Annual Maintenance Contract for ongoing service. The renewal date is the start date plus the contract length, usually one year, after which a fresh agreement is needed.'],
      ['How early should I remind a customer about AMC renewal?', 'Reminding customers 15 to 30 days before expiry avoids a gap in coverage and gives time to arrange payment, so their systems stay under support without interruption.'],
      ['What is the benefit of an AMC for a computer buyer?', 'An AMC covers periodic servicing, priority support and often reduced repair costs. For businesses it means predictable maintenance budgeting and less downtime on critical machines.'],
    ],
  ],
  'salary-calculator' => [
    'guide' => [
      'Enter the gross monthly or annual salary.',
      'Set the basic, HRA and allowance components.',
      'Apply PF and ESI deductions where applicable.',
      'Read the net take-home salary breakdown.',
    ],
    'faq' => [
      ['How much PF is deducted from salary?', 'Employee Provident Fund is normally 12% of basic pay, matched by the employer. The employee 12% is deducted from salary, while the employer share is a separate cost over and above.'],
      ['Who is eligible for ESI deduction?', 'ESI applies to employees earning up to the notified wage limit. The employee contribution is 0.75% and the employer contribution is 3.25% of gross wages, funding medical and insurance benefits.'],
      ['What is the difference between gross and net salary?', 'Gross salary is the total before deductions, including basic, HRA and allowances. Net salary is what reaches the employee after PF, ESI, professional tax and TDS are subtracted.'],
    ],
  ],
  'purchase-order' => [
    'guide' => [
      'Enter your business and supplier details.',
      'Add the items you want to order with quantity and rate.',
      'Set delivery terms, taxes and expected date.',
      'Generate the purchase order PDF to send to the supplier.',
    ],
    'faq' => [
      ['What is a purchase order used for?', 'A purchase order is a formal request to a supplier to deliver specific goods at agreed prices. It confirms the order details and serves as a record for both buyer and seller.'],
      ['What is the difference between a PO and an invoice?', 'A purchase order is issued by the buyer to order goods, while the invoice is issued by the seller to demand payment after delivery. Matching the two prevents billing errors.'],
      ['Should a purchase order include GST details?', 'Yes. Including the applicable GST rate and both parties GSTIN keeps the order clear and helps reconcile the supplier invoice against the PO for accurate input tax credit.'],
    ],
  ],
  'inventory-valuation' => [
    'guide' => [
      'Enter each product in stock with its quantity.',
      'Enter the unit cost for each product.',
      'Let the tool multiply quantity by cost for each line.',
      'Read the total value of your current inventory.',
    ],
    'faq' => [
      ['How is total inventory value calculated?', 'Multiply the quantity of each item by its unit cost, then add all lines together. 10 keyboards at 400 rupees plus 5 mice at 200 rupees gives 4,000 plus 1,000, a total of 5,000 rupees.'],
      ['Should inventory be valued at cost or selling price?', 'For accounting, stock is usually valued at cost price or net realisable value, whichever is lower. This gives a conservative, accurate picture of the money tied up in unsold goods.'],
      ['Why is regular inventory valuation important for a shop?', 'It shows how much capital is locked in stock, highlights slow-moving items, and supports accurate profit and GST reporting, helping you order smartly and avoid dead stock.'],
    ],
  ],
];
