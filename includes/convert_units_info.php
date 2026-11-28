<?php
/**
 * KRISHNA TOOLS — what every converter unit is, where it is used, and typical example values.
 * Used by includes/convert_content.php to write unique, useful text for every /convert/ page.
 *   about : one or two sentences — what the unit is and how it is defined
 *   use   : where people actually meet this unit (India-first where it matters)
 *   ex    : realistic values for worked examples (small, medium, large)
 *   faq   : optional unit-specific question [q, a]
 */
function gen_units_info(): array {
    return [
        /* ---------------- length ---------------- */
        'meter' => ['about' => 'The meter (m) is the base unit of length in the metric (SI) system, defined by the distance light travels in 1/299,792,458 of a second.', 'use' => 'room sizes, cloth, plot sides, sports tracks and almost every measurement on drawings', 'ex' => [2.5, 12, 150]],
        'kilometer' => ['about' => 'A kilometer (km) is 1,000 meters. It is the standard unit for road distances in India and most of the world.', 'use' => 'road signs, Google Maps distances, train and bus routes, vehicle mileage and running races', 'ex' => [5, 42.195, 350]],
        'centimeter' => ['about' => 'A centimeter (cm) is one hundredth of a meter (0.01 m), the everyday metric unit for small lengths.', 'use' => 'height of a person, school rulers, clothing and tailoring, screen and paper sizes', 'ex' => [30, 165, 250]],
        'millimeter' => ['about' => 'A millimeter (mm) is one thousandth of a meter, ten millimeters make one centimeter.', 'use' => 'engineering drawings, steel bar (sariya) diameter, rainfall, tile thickness and CCTV lens focal length', 'ex' => [12, 100, 2500]],
        'micrometer' => ['about' => 'A micrometer (µm), also called a micron, is one millionth of a meter.', 'use' => 'paper and plastic film thickness, dust and PM2.5 particle size, machining tolerances and biology', 'ex' => [2.5, 50, 750]],
        'nanometer' => ['about' => 'A nanometer (nm) is one billionth of a meter, small enough to describe atoms and light waves.', 'use' => 'computer chip process nodes (e.g. 5 nm), the wavelength of light and nanotechnology', 'ex' => [5, 550, 1500]],
        'mile' => ['about' => 'A mile (mi) is an imperial unit equal to exactly 1,609.344 meters (5,280 feet).', 'use' => 'road distances and speed limits in the USA and UK, and international running events', 'ex' => [3, 26.2, 500]],
        'yard' => ['about' => 'A yard (yd) is 3 feet or exactly 0.9144 meters. In India a square yard is called a “gaj”.', 'use' => 'cloth shops, cricket pitch length (22 yards), American football and plot measurement', 'ex' => [5, 22, 100]],
        'foot' => ['about' => 'A foot (ft) is 12 inches or exactly 0.3048 meters, still the favourite unit for building and height in India.', 'use' => 'house construction, room dimensions, a person’s height (5 ft 6 in) and aircraft altitude', 'ex' => [6, 12.5, 1000]],
        'inch' => ['about' => 'An inch (in) is exactly 2.54 centimeters; 12 inches make a foot.', 'use' => 'TV, mobile and monitor screen sizes, pipe sizes, tyre rims and photo prints', 'ex' => [6, 32, 55]],
        'nautical-mile' => ['about' => 'A nautical mile (nmi) is exactly 1,852 meters, based on one minute of latitude on the Earth.', 'use' => 'shipping, navy, aviation charts and sea-border limits', 'ex' => [1, 12, 200]],
        'furlong' => ['about' => 'A furlong is an old English unit equal to 220 yards, about 201 meters.', 'use' => 'horse racing distances and some old land records', 'ex' => [1, 5, 8]],
        'light-year' => ['about' => 'A light-year (ly) is the distance light travels in one year, about 9.46 trillion kilometers.', 'use' => 'astronomy, star and galaxy distances', 'ex' => [1, 4.24, 100]],
        'decimeter' => ['about' => 'A decimeter (dm) is one tenth of a meter, or 10 centimeters.', 'use' => 'school maths and science problems, and volume (1 cubic decimeter = 1 liter)', 'ex' => [3, 15, 120]],

        /* ---------------- weight ---------------- */
        'kilogram' => ['about' => 'The kilogram (kg) is the SI base unit of mass, equal to 1,000 grams.', 'use' => 'grocery and kirana shops, body weight, luggage limits, courier charges and mandi rates', 'ex' => [5, 75, 1250]],
        'gram' => ['about' => 'A gram (g) is one thousandth of a kilogram.', 'use' => 'gold and silver, food packets, recipes, nutrition labels and medicine', 'ex' => [10, 250, 750]],
        'milligram' => ['about' => 'A milligram (mg) is one thousandth of a gram, used where very small quantities matter.', 'use' => 'tablet and syrup doses (e.g. Paracetamol 500 mg), vitamins, nutrition labels and lab tests', 'ex' => [100, 500, 2500]],
        'microgram' => ['about' => 'A microgram (µg or mcg) is one millionth of a gram.', 'use' => 'vitamin B12 and D doses, thyroid tablets, pollution readings (µg/m³) and lab reports', 'ex' => [50, 500, 2000]],
        'metric-ton' => ['about' => 'A metric ton (t or MT), also called a tonne, is 1,000 kilograms or 10 quintals.', 'use' => 'truck loads, cement and steel orders, crop production figures, AC capacity labels and freight', 'ex' => [2, 9, 25]],
        'pound' => ['about' => 'A pound (lb) is an imperial unit of mass equal to exactly 0.45359237 kilograms.', 'use' => 'gym weights, body weight in the USA and UK, imported products and baggage on foreign airlines', 'ex' => [10, 150, 500]],
        'ounce' => ['about' => 'An ounce (oz) is one sixteenth of a pound, about 28.35 grams.', 'use' => 'imported food and cosmetics, perfume, boxing gloves and US recipes', 'ex' => [4, 16, 64]],
        'stone' => ['about' => 'A stone (st) is a British unit equal to 14 pounds, about 6.35 kilograms.', 'use' => 'body weight in the UK and Ireland', 'ex' => [9, 12, 16]],
        'quintal' => ['about' => 'A quintal (q or qtl) is a metric unit of mass equal to exactly 100 kilograms. Ten quintals make one metric ton.', 'use' => 'APMC market yards (mandi), MSP announcements, wheat, rice, cotton (kapas), groundnut, sugarcane and fertiliser trade in India', 'ex' => [5, 30, 150],
            'faq' => ['Is 1 quintal always 100 kg?', 'Yes. The quintal used in Indian mandis, MSP notices and government crop data is the metric quintal of exactly 100 kg. The old British “hundredweight” (about 50.8 kg) is a different unit.']],
        'carat' => ['about' => 'A carat (ct) is a unit of mass for gemstones equal to exactly 200 milligrams (0.2 g).', 'use' => 'diamonds and gemstones. Note: “karat” (22K, 24K) for gold purity is a different idea', 'ex' => [0.5, 2, 10],
            'faq' => ['Is carat the same as gold karat (22K)?', 'No. Carat (ct) is a weight unit for gems: 1 ct = 0.2 g. Karat (K) tells the purity of gold, for example 22K means 22 parts gold out of 24.']],
        'tola' => ['about' => 'A tola is a traditional Indian unit of mass. The standard Indian tola is 11.6638 grams (180 grains); jewellers often round it to 10 grams.', 'use' => 'gold and silver trading in India, Nepal and the Gulf, old jewellery bills and family records', 'ex' => [1, 5, 25],
            'faq' => ['Is 1 tola 10 grams or 11.66 grams?', 'The traditional tola is 11.6638 g. Many jewellers today quote gold “per 10 grams” and loosely call it a tola, so always check which one your bill uses.']],
        'maund' => ['about' => 'A maund (man, મણ, मन) is a traditional Indian weight whose size depends on the region. In Gujarat market yards 1 maund = 20 kg, and this page uses that value. The Indian standard maund of the 1956 Weights and Measures Act is 40 seer = 37.3242 kg, and a rounded 40 kg maund is also heard. Pick the one you need in the converter.', 'use' => 'groundnut, cotton, cumin and grain prices in Gujarat market yards (₹ per 20 kg), old land-revenue and family records', 'ex' => [1, 10, 50],
            'faq' => ['Is 1 maund 20 kg or 40 kg?', 'Both are used. In Gujarat market yards 1 maund (મણ) is 20 kg — prices such as “₹1,200 a man” are per 20 kg. The Indian standard maund is 37.3242 kg and many people round it to 40 kg. Select the region in the converter to switch.']],
        'ser' => ['about' => 'A seer (ser, શેર, सेर) is a traditional Indian weight. The Indian standard seer of the 1956 Weights and Measures Act is 80 tola = 0.9331 kg, and 40 seer make one standard maund.', 'use' => 'old grocery and milk measures, family recipes, village trade and old records', 'ex' => [1, 5, 40],
            'faq' => ['Is 1 seer 1 kg?', 'Not exactly. The standard Indian seer is 0.9331 kg (933.1 g) — a little less than a kilogram. When the metric system came in, the kilogram replaced it, so many people now loosely call 1 kg “a seer”.']],
        'thousand' => ['about' => 'A thousand is 1,000 (10³). In the Indian system 100 thousand make one lakh.', 'use' => 'prices, salaries and counts in both the Indian and international systems', 'ex' => [10, 100, 500]],
        'lakh' => ['about' => 'A lakh is 1,00,000 (one hundred thousand, 10⁵) in the Indian numbering system. 10 lakh = 1 million and 100 lakh = 1 crore.', 'use' => 'salaries, loans, property prices, population and news figures in India, Pakistan, Bangladesh and Nepal', 'ex' => [1, 10, 50],
            'faq' => ['How many zeros are in 1 lakh?', '1 lakh has 5 zeros: 1,00,000. In the international system that is 100,000 (one hundred thousand).']],
        'crore' => ['about' => 'A crore is 1,00,00,000 (ten million, 10⁷) in the Indian numbering system. 1 crore = 100 lakh = 10 million.', 'use' => 'government budgets, company turnover, property and film collections in India', 'ex' => [1, 5, 100],
            'faq' => ['How many zeros are in 1 crore?', '1 crore has 7 zeros: 1,00,00,000. In the international system that is 10,000,000 (ten million).']],
        'million' => ['about' => 'A million is 1,000,000 (10⁶) — in Indian terms 10 lakh.', 'use' => 'international news, company reports, YouTube views and dollar figures', 'ex' => [1, 2.5, 100]],
        'billion' => ['about' => 'A billion is 1,000,000,000 (10⁹) — in Indian terms 100 crore (old Indian name: 1 arab).', 'use' => 'national budgets, company valuations and world population', 'ex' => [1, 5, 50]],
        'trillion' => ['about' => 'A trillion is 10¹² — in Indian terms 1 lakh crore.', 'use' => 'GDP, national debt and the largest company valuations', 'ex' => [1, 3.5, 5]],
        'us-ton' => ['about' => 'A US ton (short ton) is 2,000 pounds, about 907.18 kilograms.', 'use' => 'US shipping and freight, American machinery specifications', 'ex' => [1, 5, 40]],
        'grain' => ['about' => 'A grain (gr) is a very small mass unit, exactly 64.79891 milligrams. A tola is 180 grains.', 'use' => 'bullets and gunpowder, traditional medicine and old pharmacy records', 'ex' => [10, 100, 180]],

        /* ---------------- temperature ---------------- */
        'celsius' => ['about' => 'Celsius (°C) is the temperature scale used in India: water freezes at 0 °C and boils at 100 °C at sea level.', 'use' => 'weather reports, body temperature (37 °C normal), cooking and AC settings', 'ex' => [-10, 37, 180]],
        'fahrenheit' => ['about' => 'Fahrenheit (°F) is the temperature scale used in the USA: water freezes at 32 °F and boils at 212 °F.', 'use' => 'body thermometers (98.6 °F normal), US weather and oven recipes', 'ex' => [32, 98.6, 350]],
        'kelvin' => ['about' => 'Kelvin (K) is the SI absolute temperature scale; 0 K is absolute zero and each kelvin equals one degree Celsius.', 'use' => 'physics and chemistry, colour temperature of LED lights (e.g. 6500 K) and space science', 'ex' => [0, 300, 6500]],
        'rankine' => ['about' => 'Rankine (°R) is an absolute scale that uses Fahrenheit-sized degrees, starting from absolute zero.', 'use' => 'some US engineering and thermodynamics calculations', 'ex' => [0, 491.67, 1000]],

        /* ---------------- area ---------------- */
        'square-meter' => ['about' => 'A square meter (m²) is the area of a square with 1 meter sides — the SI unit of area.', 'use' => 'flat carpet area in RERA documents, tiles, paint coverage and plot area', 'ex' => [10, 100, 1000]],
        'square-kilometer' => ['about' => 'A square kilometer (km²) is 1,000,000 square meters, or 100 hectares.', 'use' => 'area of cities, districts, forests and lakes', 'ex' => [1, 25, 500]],
        'square-centimeter' => ['about' => 'A square centimeter (cm²) is one ten-thousandth of a square meter.', 'use' => 'small surfaces such as labels, photos, screens and school geometry', 'ex' => [25, 100, 600]],
        'square-mile' => ['about' => 'A square mile (mi²) equals 640 acres, about 2.59 square kilometers.', 'use' => 'area of US and UK cities, states and countries', 'ex' => [1, 10, 300]],
        'square-yard' => ['about' => 'A square yard (yd²) is 9 square feet, about 0.836 m². In India it is called a “gaj” or “var”.', 'use' => 'plot sales in Gujarat, Delhi, Rajasthan and Hyderabad, and carpets', 'ex' => [50, 200, 1000]],
        'square-foot' => ['about' => 'A square foot (sq ft) is the area of a square with 1 foot sides, about 0.0929 m².', 'use' => 'flat and shop prices (₹ per sq ft), built-up area, tiles, plywood and interior work', 'ex' => [100, 1200, 5000]],
        'square-inch' => ['about' => 'A square inch (in²) is the area of a 1 inch × 1 inch square, 6.4516 cm².', 'use' => 'small parts, printing and pressure (pounds per square inch)', 'ex' => [10, 144, 500]],
        'acre' => ['about' => 'An acre (ac) is 43,560 square feet or about 4,046.86 m².', 'use' => 'farm land records, real estate projects and land sales across India and the USA', 'ex' => [1, 5, 50]],
        'hectare' => ['about' => 'A hectare (ha) is 10,000 square meters, about 2.47 acres.', 'use' => 'government agriculture data, crop yield per hectare, forest and irrigation projects', 'ex' => [1, 2.5, 40]],
        'bigha' => ['about' => 'A bigha (vigha in Gujarati) is a traditional Indian land unit whose size changes by state. This page uses the Gujarat vigha: 1 bigha = 17,424 sq ft = 16 guntha = 1,618.74 m². Pick another state in the converter for Rajasthan, UP, Bihar, Bengal, Assam, MP, Himachal or Uttarakhand.', 'use' => 'farm land deals, 7/12 records and village land talk in Gujarat, Rajasthan, UP, Bihar and Bengal', 'ex' => [1, 5, 25],
            'faq' => ['Is a bigha the same size in every state?', 'No. A Gujarat vigha is 17,424 sq ft (16 guntha), a pucca bigha in Rajasthan, eastern UP and Bihar is about 27,225 sq ft, and a bigha in West Bengal or Assam is 14,400 sq ft. Choose your state in the converter and check the local land record before a deal.']],
        'guntha' => ['about' => 'A guntha is an Indian land unit of 1,089 sq ft (121 sq yd, about 101.17 m²) — the same size in Gujarat, Maharashtra and Karnataka. 40 guntha make one acre and 16 guntha make one Gujarat vigha.', 'use' => 'land records in Gujarat and Maharashtra, small farm and NA plot deals', 'ex' => [1, 10, 40]],

        /* ---------------- volume ---------------- */
        'liter' => ['about' => 'A liter (L) is a metric unit of volume equal to 1,000 milliliters or one cubic decimeter.', 'use' => 'milk, petrol and diesel, water tanks, bottled drinks and engine oil', 'ex' => [1, 20, 1000]],
        'milliliter' => ['about' => 'A milliliter (mL) is one thousandth of a liter, the same volume as one cubic centimeter.', 'use' => 'syrup and medicine doses, cooking, perfume and small bottles', 'ex' => [5, 250, 750]],
        'cubic-meter' => ['about' => 'A cubic meter (m³) is the volume of a 1 m cube, equal to 1,000 liters.', 'use' => 'concrete (RMC), sand and soil, water supply bills and tank capacity', 'ex' => [1, 6, 50]],
        'cubic-centimeter' => ['about' => 'A cubic centimeter (cm³ or cc) equals one milliliter.', 'use' => 'engine size of bikes and cars (150 cc), injections and lab work', 'ex' => [100, 350, 1500]],
        'gallon-us' => ['about' => 'A US gallon (gal) is 231 cubic inches, about 3.785 liters.', 'use' => 'fuel prices and fuel economy in the USA, paint and water containers', 'ex' => [1, 5, 50]],
        'gallon-uk' => ['about' => 'An imperial (UK) gallon is about 4.546 liters, larger than the US gallon.', 'use' => 'older UK fuel figures and some Commonwealth countries', 'ex' => [1, 5, 50]],
        'quart' => ['about' => 'A US quart is a quarter of a US gallon, about 0.946 liters.', 'use' => 'engine oil cans and US recipes', 'ex' => [1, 4, 12]],
        'pint' => ['about' => 'A US pint is half a quart, about 473 milliliters.', 'use' => 'milk, ice cream and drinks in the USA', 'ex' => [1, 2, 8]],
        'cup' => ['about' => 'A metric cup is 240 milliliters here (the US legal cup).', 'use' => 'baking and cooking recipes', 'ex' => [0.5, 2, 6]],
        'fluid-ounce' => ['about' => 'A US fluid ounce (fl oz) is about 29.57 milliliters.', 'use' => 'drink bottles, baby feeding bottles and cosmetics labels', 'ex' => [8, 16, 64]],
        'tablespoon' => ['about' => 'A tablespoon (tbsp) is about 14.79 milliliters, three teaspoons.', 'use' => 'recipes and home medicine doses', 'ex' => [1, 3, 10]],
        'teaspoon' => ['about' => 'A teaspoon (tsp) is about 4.93 milliliters.', 'use' => 'recipes and syrup doses (5 ml spoon)', 'ex' => [1, 2, 6]],
        'cubic-foot' => ['about' => 'A cubic foot (ft³ or CFT) is the volume of a 1 ft cube, about 28.32 liters.', 'use' => 'sand, aggregate and timber (CFT) in Indian construction, fridge capacity and air flow', 'ex' => [10, 100, 1000]],
        'cubic-inch' => ['about' => 'A cubic inch (in³) is about 16.39 cubic centimeters.', 'use' => 'US engine sizes and small containers', 'ex' => [10, 61, 350]],
        'barrel' => ['about' => 'An oil barrel (bbl) is 42 US gallons, about 159 liters.', 'use' => 'crude oil prices and production figures', 'ex' => [1, 10, 1000]],

        /* ---------------- speed ---------------- */
        'meter-per-second' => ['about' => 'Meter per second (m/s) is the SI unit of speed.', 'use' => 'physics, wind speed and sprint speed', 'ex' => [1, 10, 100]],
        'kilometer-per-hour' => ['about' => 'Kilometer per hour (km/h) is the speed unit on Indian roads and vehicle speedometers.', 'use' => 'speed limits, trains, wind in weather reports and cricket bowling speed', 'ex' => [40, 100, 150]],
        'mile-per-hour' => ['about' => 'Mile per hour (mph) is the speed unit used on roads in the USA and UK.', 'use' => 'US/UK cars, speed limits and some cricket and tennis broadcasts', 'ex' => [30, 60, 100]],
        'foot-per-second' => ['about' => 'Foot per second (ft/s) is an imperial speed unit, 0.3048 m/s.', 'use' => 'engineering, ballistics and fluid flow', 'ex' => [10, 100, 1000]],
        'knot' => ['about' => 'A knot (kn) is one nautical mile per hour, about 1.852 km/h.', 'use' => 'ships, aircraft and wind speed in cyclone warnings', 'ex' => [10, 35, 250]],
        'mach' => ['about' => 'Mach 1 is the speed of sound, about 343 m/s (1,235 km/h) in air at 20 °C.', 'use' => 'fighter jets, missiles and space vehicles', 'ex' => [0.8, 2, 5]],
        'kilometer-per-second' => ['about' => 'Kilometer per second (km/s) equals 1,000 m/s.', 'use' => 'rockets, satellites and space science', 'ex' => [1, 7.8, 11.2]],

        /* ---------------- time ---------------- */
        'second' => ['about' => 'The second (s) is the SI base unit of time.', 'use' => 'timers, stopwatches, video length and physics', 'ex' => [30, 90, 3600]],
        'millisecond' => ['about' => 'A millisecond (ms) is one thousandth of a second.', 'use' => 'internet ping, game lag, reaction time and computer timings', 'ex' => [20, 250, 5000]],
        'microsecond' => ['about' => 'A microsecond (µs) is one millionth of a second.', 'use' => 'electronics, computer memory and physics experiments', 'ex' => [10, 500, 10000]],
        'minute' => ['about' => 'A minute (min) is 60 seconds.', 'use' => 'cooking, exams, call duration and travel time', 'ex' => [15, 90, 1440]],
        'hour' => ['about' => 'An hour (h) is 60 minutes or 3,600 seconds.', 'use' => 'working hours, overtime, parking, rentals and travel time', 'ex' => [8, 24, 168]],
        'day' => ['about' => 'A day is 24 hours.', 'use' => 'leave, interest calculations, delivery time and age', 'ex' => [7, 30, 365]],
        'week' => ['about' => 'A week is 7 days.', 'use' => 'pregnancy weeks, salary cycles and project plans', 'ex' => [2, 12, 52]],
        'month' => ['about' => 'A month here is the average Gregorian month of 30.44 days (1/12 of a year).', 'use' => 'loan tenure, rent, subscriptions and baby age', 'ex' => [3, 18, 60]],
        'year' => ['about' => 'A year here is the average Gregorian year of 365.2425 days.', 'use' => 'age, investments, warranties and service records', 'ex' => [1, 10, 25]],
        'decade' => ['about' => 'A decade is 10 years.', 'use' => 'history, long-term planning and statistics', 'ex' => [1, 3, 10]],
        'nanosecond' => ['about' => 'A nanosecond (ns) is one billionth of a second.', 'use' => 'computer processors, RAM latency and fibre optics', 'ex' => [1, 100, 5000]],

        /* ---------------- digital ---------------- */
        'bit' => ['about' => 'A bit (b) is the smallest unit of digital data, a single 0 or 1. Eight bits make one byte.', 'use' => 'network speeds, encryption keys and computer science', 'ex' => [8, 1024, 1000000]],
        'byte' => ['about' => 'A byte (B) is 8 bits, enough to store one English character.', 'use' => 'file sizes and computer memory', 'ex' => [512, 4096, 1000000]],
        'kilobyte' => ['about' => 'A kilobyte (KB) is 1,000 bytes in the decimal (SI) system used here.', 'use' => 'photo and document size limits on government forms (e.g. 50 KB), small files and emails', 'ex' => [20, 500, 2048]],
        'megabyte' => ['about' => 'A megabyte (MB) is 1,000,000 bytes (decimal).', 'use' => 'photos, songs, PDF uploads, mobile data use and app sizes', 'ex' => [5, 250, 4096]],
        'gigabyte' => ['about' => 'A gigabyte (GB) is 1,000 megabytes (decimal), the way phones, pen drives and data packs are sold.', 'use' => 'mobile data packs, RAM, pen drives, SD cards and movie files', 'ex' => [1.5, 64, 500]],
        'terabyte' => ['about' => 'A terabyte (TB) is 1,000 gigabytes (decimal), the unit hard disks are sold in.', 'use' => 'hard disks, CCTV DVR/NVR storage, laptops and cloud backup', 'ex' => [1, 4, 16],
            'faq' => ['Why does a 1 TB hard disk show only 931 GB?', 'Disk makers use decimal units (1 TB = 1,000,000,000,000 bytes) while Windows counts in binary (1 GiB = 1,073,741,824 bytes). 10¹² ÷ 2³⁰ ≈ 931, so nothing is missing.']],
        'petabyte' => ['about' => 'A petabyte (PB) is 1,000 terabytes.', 'use' => 'data centres, big companies and cloud storage', 'ex' => [1, 5, 100]],
        'kibibyte' => ['about' => 'A kibibyte (KiB) is 1,024 bytes — the binary unit computers use internally.', 'use' => 'operating systems, programming and memory pages', 'ex' => [4, 64, 1024]],
        'mebibyte' => ['about' => 'A mebibyte (MiB) is 1,024 KiB or 1,048,576 bytes.', 'use' => 'RAM, Linux tools and download managers', 'ex' => [1, 512, 4096]],
        'gibibyte' => ['about' => 'A gibibyte (GiB) is 1,024 MiB or 1,073,741,824 bytes — what Windows calls “GB”.', 'use' => 'Windows drive sizes, RAM and virtual machines', 'ex' => [1, 16, 931]],
        'tebibyte' => ['about' => 'A tebibyte (TiB) is 1,024 GiB.', 'use' => 'servers, NAS and RAID storage', 'ex' => [1, 4, 20]],

        /* ---------------- data rate ---------------- */
        'bit-per-second' => ['about' => 'Bit per second (bps) is the base unit of data transfer speed.', 'use' => 'serial ports, IoT devices and network theory', 'ex' => [9600, 115200, 1000000]],
        'kilobit-per-second' => ['about' => 'Kilobit per second (Kbps) is 1,000 bits per second.', 'use' => 'audio bitrate (128 Kbps MP3), 2G data and slow connections', 'ex' => [64, 128, 512]],
        'megabit-per-second' => ['about' => 'Megabit per second (Mbps) is 1,000,000 bits per second — the unit broadband and 4G/5G speeds are sold in.', 'use' => 'internet plans (100 Mbps fibre), speed tests, CCTV camera bitrate and Wi-Fi routers', 'ex' => [10, 100, 300],
            'faq' => ['Why does a 100 Mbps connection download at only about 12 MB/s?', 'Internet speed is sold in megabits (Mb) but downloads show megabytes (MB). One byte = 8 bits, so 100 Mbps ÷ 8 = 12.5 MB/s, and a little is lost to network overhead.']],
        'gigabit-per-second' => ['about' => 'Gigabit per second (Gbps) is 1,000 Mbps.', 'use' => 'fibre broadband, LAN ports, network switches and 5G', 'ex' => [1, 2.5, 10]],
        'byte-per-second' => ['about' => 'Byte per second (B/s) equals 8 bits per second.', 'use' => 'file copy speed and old modems', 'ex' => [1000, 125000, 1000000]],
        'kilobyte-per-second' => ['about' => 'Kilobyte per second (KB/s) is 1,000 bytes per second (8 Kbps).', 'use' => 'download speed shown by browsers and apps on slow networks', 'ex' => [100, 512, 2000]],
        'megabyte-per-second' => ['about' => 'Megabyte per second (MB/s) is 1,000,000 bytes per second (8 Mbps).', 'use' => 'download managers, pen drive and SSD copy speed', 'ex' => [1.5, 12.5, 100]],
        'gigabyte-per-second' => ['about' => 'Gigabyte per second (GB/s) is 1,000 MB/s.', 'use' => 'NVMe SSDs, RAM bandwidth and data-centre links', 'ex' => [0.5, 3.5, 7]],

        /* ---------------- pressure ---------------- */
        'pascal' => ['about' => 'The pascal (Pa) is the SI unit of pressure, one newton per square meter.', 'use' => 'science, engineering and building codes', 'ex' => [100, 1000, 101325]],
        'kilopascal' => ['about' => 'A kilopascal (kPa) is 1,000 pascals.', 'use' => 'tyre pressure on some vehicles, weather and engineering', 'ex' => [100, 220, 700]],
        'bar' => ['about' => 'A bar is 100,000 pascals, almost exactly the air pressure at sea level.', 'use' => 'water pumps, pressure cookers, RO purifiers, LPG and compressors', 'ex' => [1, 2.5, 10]],
        'psi' => ['about' => 'PSI (pounds per square inch) is an imperial pressure unit, about 6,895 Pa.', 'use' => 'car and bike tyre pressure (30–35 psi), air compressors and hydraulics', 'ex' => [30, 35, 100]],
        'atmosphere' => ['about' => 'One standard atmosphere (atm) is 101,325 Pa, the average air pressure at sea level.', 'use' => 'chemistry, diving and gas cylinders', 'ex' => [1, 2, 200]],
        'torr' => ['about' => 'A torr is 1/760 of an atmosphere, nearly the same as 1 mmHg.', 'use' => 'vacuum pumps and lab equipment', 'ex' => [1, 100, 760]],
        'millibar' => ['about' => 'A millibar (mbar) is one thousandth of a bar (100 Pa), also called a hectopascal.', 'use' => 'weather maps and cyclone pressure reports', 'ex' => [950, 1013, 1050]],
        'mmhg' => ['about' => 'Millimeter of mercury (mmHg) is the pressure of a 1 mm mercury column.', 'use' => 'blood pressure readings (120/80 mmHg) and barometers', 'ex' => [80, 120, 760]],

        /* ---------------- energy ---------------- */
        'joule' => ['about' => 'The joule (J) is the SI unit of energy and work.', 'use' => 'physics, food science and engineering', 'ex' => [100, 4184, 1000000]],
        'kilojoule' => ['about' => 'A kilojoule (kJ) is 1,000 joules.', 'use' => 'food labels (energy per 100 g) and physics', 'ex' => [100, 840, 8700]],
        'calorie' => ['about' => 'A calorie (cal) is the energy to heat 1 g of water by 1 °C, 4.184 joules.', 'use' => 'chemistry and science textbooks', 'ex' => [100, 1000, 50000]],
        'kilocalorie' => ['about' => 'A kilocalorie (kcal) is 1,000 calories — the “Calorie” on food labels and diet charts.', 'use' => 'diet plans, food labels, gym and calorie counting', 'ex' => [100, 500, 2000]],
        'watt-hour' => ['about' => 'A watt-hour (Wh) is the energy of 1 watt used for one hour.', 'use' => 'power bank, laptop and inverter battery capacity', 'ex' => [50, 600, 1500]],
        'kilowatt-hour' => ['about' => 'A kilowatt-hour (kWh) is 1,000 Wh — one “unit” on your electricity bill.', 'use' => 'electricity bills, solar panel output and EV battery size', 'ex' => [1, 100, 300]],
        'electronvolt' => ['about' => 'An electronvolt (eV) is the energy an electron gains across 1 volt, about 1.6 × 10⁻¹⁹ J.', 'use' => 'atomic, nuclear and particle physics', 'ex' => [1, 1000, 1000000]],
        'btu' => ['about' => 'A BTU (British thermal unit) is about 1,055 joules.', 'use' => 'heating and gas appliances', 'ex' => [1000, 12000, 24000]],
        'foot-pound' => ['about' => 'A foot-pound (ft·lb) is the energy to lift one pound by one foot, about 1.356 J.', 'use' => 'torque wrenches, firearms and US engineering', 'ex' => [10, 100, 1000]],

        /* ---------------- power ---------------- */
        'watt' => ['about' => 'The watt (W) is the SI unit of power, one joule per second.', 'use' => 'bulbs, fans, mobile chargers, solar panels and appliance labels', 'ex' => [9, 75, 1500]],
        'kilowatt' => ['about' => 'A kilowatt (kW) is 1,000 watts.', 'use' => 'sanctioned load on electricity bills, solar systems, motors and EV chargers', 'ex' => [1, 5, 10]],
        'megawatt' => ['about' => 'A megawatt (MW) is 1,000 kilowatts.', 'use' => 'power plants, solar parks and big factories', 'ex' => [1, 50, 500]],
        'horsepower' => ['about' => 'Mechanical horsepower (hp) is about 745.7 watts.', 'use' => 'water pumps and motors (1 HP, 5 HP), tractors and car engines', 'ex' => [0.5, 5, 100]],
        'metric-horsepower' => ['about' => 'Metric horsepower (PS) is about 735.5 watts, slightly less than mechanical hp.', 'use' => 'car and bike brochures in India and Europe', 'ex' => [10, 100, 300]],
        'btu-per-hour' => ['about' => 'BTU per hour (BTU/h) is a power unit used for cooling; 12,000 BTU/h equals a 1 ton AC.', 'use' => 'air conditioner capacity and heaters', 'ex' => [9000, 12000, 24000]],
        'milliwatt' => ['about' => 'A milliwatt (mW) is one thousandth of a watt.', 'use' => 'lasers, Wi-Fi radio power and small electronics', 'ex' => [5, 100, 500]],

        /* ---------------- angle ---------------- */
        'degree' => ['about' => 'A degree (°) is 1/360 of a full turn.', 'use' => 'geometry, maps (latitude/longitude), CCTV field of view and solar panel tilt', 'ex' => [30, 90, 270]],
        'radian' => ['about' => 'A radian (rad) is the angle where the arc equals the radius; a full turn is 2π radians.', 'use' => 'maths, physics and programming', 'ex' => [1, 3.1416, 6.2832]],
        'gradian' => ['about' => 'A gradian (grad) is 1/400 of a full turn.', 'use' => 'surveying in some countries and scientific calculators', 'ex' => [50, 100, 400]],
        'arcminute' => ['about' => 'An arcminute (′) is 1/60 of a degree.', 'use' => 'astronomy, maps and rifle scopes', 'ex' => [1, 30, 60]],
        'arcsecond' => ['about' => 'An arcsecond (″) is 1/60 of an arcminute.', 'use' => 'astronomy and GPS precision', 'ex' => [1, 30, 3600]],
        'revolution' => ['about' => 'A revolution is one full turn, 360 degrees.', 'use' => 'wheels, motors and rotations', 'ex' => [0.25, 1, 10]],

        /* ---------------- frequency ---------------- */
        'hertz' => ['about' => 'The hertz (Hz) is one cycle per second. Indian mains electricity runs at 50 Hz.', 'use' => 'electricity, sound and screen refresh rate (60/120 Hz)', 'ex' => [50, 440, 20000]],
        'kilohertz' => ['about' => 'A kilohertz (kHz) is 1,000 Hz.', 'use' => 'audio sampling (44.1 kHz) and AM radio', 'ex' => [1, 44.1, 530]],
        'megahertz' => ['about' => 'A megahertz (MHz) is one million hertz.', 'use' => 'FM radio (91.1 MHz), RAM speed and mobile networks', 'ex' => [88, 900, 3200]],
        'gigahertz' => ['about' => 'A gigahertz (GHz) is 1,000 MHz.', 'use' => 'processor speed, Wi-Fi bands (2.4 / 5 GHz) and 5G', 'ex' => [2.4, 3.5, 5]],
        'rpm' => ['about' => 'RPM (revolutions per minute) measures how fast something spins; 60 rpm = 1 Hz.', 'use' => 'motors, fans, engines, washing machines and hard disks', 'ex' => [1440, 2800, 7200]],

        /* ---------------- fuel ---------------- */
        'km-per-liter' => ['about' => 'Kilometers per liter (km/L) is how mileage is quoted in India.', 'use' => 'bike and car mileage, ARAI figures and fuel budgeting', 'ex' => [15, 22, 50]],
        'mpg-us' => ['about' => 'Miles per US gallon (mpg) is how fuel economy is shown in the USA.', 'use' => 'US car reviews and imported car brochures', 'ex' => [20, 35, 50]],
        'mpg-uk' => ['about' => 'Miles per imperial gallon (UK mpg) uses the larger UK gallon.', 'use' => 'UK car reviews and specifications', 'ex' => [30, 45, 60]],
        'liter-per-100km' => ['about' => 'Liters per 100 km (L/100km) shows how much fuel is used for 100 km — lower is better.', 'use' => 'European and Australian car figures, trucks and fleet reports', 'ex' => [4.5, 7, 12]],
    ];
}

/** Category-level context: where these conversions matter and one common mistake to avoid. */
function gen_category_info(): array {
    return [
        'length' => ['why' => 'Length conversions come up when you compare product sizes from different countries, read drawings or plan travel.', 'tip' => 'Imperial units (inch, foot, yard, mile) have exact metric values — 1 inch is exactly 2.54 cm — so you can convert without rounding errors.'],
        'weight' => ['why' => 'Weight conversions matter for shopping, farming, cooking, gold buying, freight and medicine.', 'tip' => 'Strictly these are units of mass. On Earth mass and weight are used the same way, so a kg reading on a shop scale converts directly.'],
        'temperature' => ['why' => 'Temperature conversions are needed for weather, fever readings, cooking and science.', 'tip' => 'Temperature scales have different zero points, so you cannot simply multiply — always use the full formula with the offset.'],
        'area' => ['why' => 'Area conversions are essential when buying a flat, plot or farm land, or when ordering tiles and paint.', 'tip' => 'Area factors are the square of length factors: 1 m = 3.28 ft but 1 m² = 10.76 sq ft.'],
        'volume' => ['why' => 'Volume conversions help with fuel, water tanks, recipes, concrete and imported products.', 'tip' => 'US and UK gallons, pints and fluid ounces are different sizes — check which one the label means.'],
        'speed' => ['why' => 'Speed conversions are useful for driving abroad, aviation, sports and physics homework.', 'tip' => 'To change m/s to km/h multiply by 3.6; to go back divide by 3.6.'],
        'time' => ['why' => 'Time conversions help with work hours, billing, loans, project plans and science.', 'tip' => 'Months and years have different lengths, so this converter uses averages (1 month = 30.44 days). For exact dates use a date calculator.'],
        'digital' => ['why' => 'Storage conversions matter when buying hard disks, phones, data packs or uploading files with a size limit.', 'tip' => 'KB, MB, GB and TB here are decimal (1,000-based). KiB, MiB, GiB and TiB are the binary (1,024-based) units your computer shows.'],
        'data-rate' => ['why' => 'Data-rate conversions explain the speed you actually get from an internet plan or a CCTV camera stream.', 'tip' => 'A small “b” means bits and a capital “B” means bytes. 8 bits = 1 byte, so Mbps ÷ 8 = MB/s.'],
        'pressure' => ['why' => 'Pressure conversions are used for tyres, pumps, gas cylinders, weather and blood pressure.', 'tip' => 'Gauges usually show pressure above air pressure; add 1 atm (about 1.013 bar) if you need absolute pressure.'],
        'energy' => ['why' => 'Energy conversions are used for electricity bills, food calories, batteries and physics.', 'tip' => 'One electricity “unit” is 1 kWh. Food “Calories” are kilocalories (kcal).'],
        'power' => ['why' => 'Power conversions help when sizing pumps, inverters, ACs, solar systems and electrical load.', 'tip' => 'Power (W, kW, hp) is a rate; energy (kWh) is power multiplied by time.'],
        'angle' => ['why' => 'Angle conversions are used in geometry, surveying, maps, programming and camera planning.', 'tip' => 'Most programming languages use radians in sin() and cos(), not degrees.'],
        'frequency' => ['why' => 'Frequency conversions appear in electronics, radio, audio and motor speeds.', 'tip' => 'Divide rpm by 60 to get hertz (rotations per second).'],
        'number' => ['why' => 'Lakh–crore to million–billion conversions are needed to read international news, company reports, salaries and loan amounts correctly.', 'tip' => 'Indian commas group by two after the first three digits (1,00,00,000 = 1 crore); international commas group by three (10,000,000 = 10 million).'],
        'fuel' => ['why' => 'Fuel-economy conversions help compare mileage figures from India, the USA, the UK and Europe.', 'tip' => 'L/100km is the inverse of km/L, so a smaller L/100km number means better mileage.'],
    ];
}
