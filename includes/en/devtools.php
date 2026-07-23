<?php
/** KRISHNA TOOLS — English guide + FAQ content (developer & API tools). */
return [
  'api-tester' => [
    'guide' => [
      'Enter the API endpoint URL and pick a method (GET, POST, PUT, PATCH or DELETE).',
      'Add any request headers, query parameters or a JSON/raw body your endpoint expects.',
      'Click Send; the request is routed through a CORS-bypass proxy so browser cross-origin limits do not block it.',
      'Inspect the response status, headers and formatted body, then tweak and resend to debug your API.',
    ],
    'faq' => [
      ['Is this REST API tester free to use?', 'Yes, it is completely free and runs in your browser with no signup or install required.'],
      ['Why can I not reach my localhost or internal API?', 'The proxy used to bypass CORS deliberately blocks private and localhost IP ranges for security, so it only works with publicly reachable URLs.'],
      ['Does it support custom headers and authentication?', 'Yes, you can add any headers including Authorization tokens, API keys and content-type to authenticate your requests.'],
    ],
  ],
  'webhook-tester' => [
    'guide' => [
      'Click Generate to create a unique webhook URL that is yours for the next 24 hours.',
      'Paste that URL into the service or app whose webhooks you want to capture.',
      'Trigger the event; incoming requests appear live on the page in real time.',
      'Open any captured request to inspect its method, headers, query string and full payload body.',
    ],
    'faq' => [
      ['How long does a webhook URL stay active?', 'Each generated URL lasts for 24 hours, after which it expires and stops receiving requests.'],
      ['Can I see the request headers and body?', 'Yes, every captured request shows the HTTP method, all headers, query parameters and the raw or JSON body.'],
      ['Is the tool good for debugging Stripe or GitHub webhooks?', 'Yes, it works with any service that sends HTTP webhooks, letting you inspect exactly what the sender delivers.'],
    ],
  ],
  'jwt-decoder' => [
    'guide' => [
      'Paste your JWT into the input field to instantly split it into header, payload and signature.',
      'Read the decoded header and payload claims shown as formatted JSON.',
      'Optionally enter the HS256 secret to verify the signature is valid.',
      'Check expiry (exp) and issued-at (iat) claims to confirm the token is still current.',
    ],
    'faq' => [
      ['Is it safe to decode my JWT here?', 'Yes, decoding happens entirely in your browser and the token is never sent to any server.'],
      ['Can it verify the token signature?', 'Yes, provide the HS256 shared secret and the tool will confirm whether the signature is valid.'],
      ['Does decoding a JWT reveal the secret?', 'No, the payload is only Base64URL encoded not encrypted, but the signing secret is never exposed by decoding.'],
    ],
  ],
  'jwt-generator' => [
    'guide' => [
      'Enter your payload claims such as sub, name and exp as JSON.',
      'Adjust the header if needed; HS256 is the default signing algorithm.',
      'Type the shared secret that will be used to sign the token.',
      'Copy the generated JWT and use it in your Authorization header or tests.',
    ],
    'faq' => [
      ['Which signing algorithm does the generator use?', 'It creates tokens signed with HS256, a symmetric HMAC algorithm using your shared secret.'],
      ['Is my secret sent anywhere?', 'No, the token is signed locally in your browser so your secret never leaves your device.'],
      ['How do I set an expiry on the token?', 'Add an exp claim to the payload with a Unix timestamp for when the token should stop being valid.'],
    ],
  ],
  'regex-tester' => [
    'guide' => [
      'Type or paste your regular expression into the pattern field.',
      'Set flags like g (global), i (case-insensitive) and m (multiline) as needed.',
      'Enter your test string in the text area below.',
      'Watch matches highlight live and review capture groups as you edit the pattern.',
    ],
    'faq' => [
      ['Does the regex run on a server?', 'No, matching happens entirely in your browser using the JavaScript regex engine, so nothing is uploaded.'],
      ['Which regex flavour is supported?', 'It uses the JavaScript (ECMAScript) regex syntax, including named groups and common flags.'],
      ['Can I see capture groups?', 'Yes, each match displays its numbered and named capture groups so you can confirm your pattern extracts the right parts.'],
    ],
  ],
  'cron-parser' => [
    'guide' => [
      'Enter a cron expression such as 0 9 * * 1-5 into the input field.',
      'Read the plain-English description explaining exactly when it runs.',
      'Review the list of upcoming run times to confirm the schedule.',
      'Adjust the fields for minute, hour, day and month until the schedule matches your intent.',
    ],
    'faq' => [
      ['What does the cron expression 0 9 * * 1-5 mean?', 'It runs at 9:00 AM every weekday from Monday to Friday.'],
      ['Does it show the next run times?', 'Yes, the parser lists several upcoming execution times so you can verify the schedule visually.'],
      ['Does it support five-field standard cron syntax?', 'Yes, it parses the standard five-field format of minute, hour, day of month, month and day of week.'],
    ],
  ],
  'uuid-generator' => [
    'guide' => [
      'Choose whether you want a UUID version 4 or a ULID.',
      'Set how many identifiers you want to generate at once.',
      'Click Generate to create cryptographically random unique IDs instantly.',
      'Copy a single value or the whole batch for use in your database or code.',
    ],
    'faq' => [
      ['What is the difference between UUID v4 and ULID?', 'UUID v4 is fully random, while ULID is time-sortable and encodes a timestamp, making it useful for ordered keys.'],
      ['Are the generated UUIDs unique?', 'UUID v4 uses 122 random bits, making collisions astronomically unlikely for practical use.'],
      ['Is generation done in the browser?', 'Yes, all identifiers are created locally in your browser, so nothing is sent to a server.'],
    ],
  ],
  'json-yaml' => [
    'guide' => [
      'Paste your JSON or YAML into the input pane.',
      'Pick the conversion direction, JSON to YAML or YAML to JSON.',
      'The converted output appears instantly in the opposite pane.',
      'Copy the result or fix any syntax errors flagged during parsing.',
    ],
    'faq' => [
      ['Can it convert both JSON to YAML and YAML to JSON?', 'Yes, the tool converts in both directions while preserving nested structures and data types.'],
      ['Is my data uploaded to convert it?', 'No, conversion runs entirely in your browser so your data stays private.'],
      ['Does it handle nested objects and arrays?', 'Yes, deeply nested objects, arrays and mixed structures are converted correctly between the two formats.'],
    ],
  ],
  'json-to-typescript' => [
    'guide' => [
      'Paste a sample JSON object or array into the input field.',
      'The tool infers types and generates matching TypeScript interfaces.',
      'Review nested interfaces created for objects within your JSON.',
      'Copy the generated interfaces straight into your TypeScript project.',
    ],
    'faq' => [
      ['Does it create interfaces for nested objects?', 'Yes, it generates separate named interfaces for nested objects and infers array element types.'],
      ['How are types inferred?', 'Types are inferred from the sample values in your JSON, mapping strings, numbers, booleans, arrays and objects to TypeScript types.'],
      ['Is my JSON sent to a server?', 'No, the interfaces are generated locally in your browser so your JSON never leaves your device.'],
    ],
  ],
  'sql-formatter' => [
    'guide' => [
      'Paste your raw or minified SQL query into the editor.',
      'Choose your preferred indentation and keyword casing.',
      'Click Format to beautify the query with clean line breaks and alignment.',
      'Copy the readable output back into your codebase or query tool.',
    ],
    'faq' => [
      ['Which SQL dialects does the formatter support?', 'It handles common standard SQL syntax used by MySQL, PostgreSQL, SQL Server and similar databases.'],
      ['Does formatting change what my query does?', 'No, it only adjusts whitespace, indentation and casing; the query logic stays exactly the same.'],
      ['Is my SQL uploaded anywhere?', 'No, formatting happens entirely in your browser so your query remains private.'],
    ],
  ],
  'http-status-codes' => [
    'guide' => [
      'Type a status code number or keyword into the search box.',
      'Browse codes grouped by class such as 2xx success and 4xx client errors.',
      'Read the name and meaning for each code you select.',
      'Use the description to decide the correct response for your API.',
    ],
    'faq' => [
      ['What does HTTP status code 404 mean?', 'It means Not Found, indicating the server could not find the requested resource.'],
      ['What is the difference between 301 and 302?', 'A 301 is a permanent redirect while a 302 is a temporary one, which affects caching and SEO behaviour.'],
      ['What does a 500 status code indicate?', 'It signals an Internal Server Error, meaning the server encountered an unexpected condition it could not handle.'],
    ],
  ],
  'mime-types' => [
    'guide' => [
      'Enter a file extension such as pdf, png or json.',
      'The tool returns the correct MIME (content) type for that extension.',
      'Search in reverse to find which extensions map to a given MIME type.',
      'Use the value in your Content-Type header or server configuration.',
    ],
    'faq' => [
      ['What is the MIME type for a JSON file?', 'The correct MIME type for JSON is application/json.'],
      ['Why do MIME types matter?', 'Browsers and servers use the MIME type to decide how to handle a file, so an incorrect type can break downloads or rendering.'],
      ['What is the MIME type for a PNG image?', 'A PNG image uses the MIME type image/png.'],
    ],
  ],
  'user-agent-parser' => [
    'guide' => [
      'Paste a User-Agent string, or use the one from your current browser.',
      'The parser breaks it down into browser, engine, operating system and device.',
      'Review the detected versions for each component.',
      'Use the parsed details for analytics, debugging or feature detection.',
    ],
    'faq' => [
      ['What information can a User-Agent string reveal?', 'It can reveal the browser name and version, rendering engine, operating system and device type.'],
      ['Is my User-Agent sent to a server?', 'No, parsing happens locally in your browser so the string is never uploaded.'],
      ['Can I parse a User-Agent other than my own?', 'Yes, paste any User-Agent string and the tool will break it down for you.'],
    ],
  ],
  'my-ip' => [
    'guide' => [
      'Open the page to instantly see your public IP address.',
      'Review the request headers your browser sent along with the request.',
      'Check whether you appear as IPv4 or IPv6.',
      'Use the details to verify VPN, proxy or firewall behaviour.',
    ],
    'faq' => [
      ['What is my public IP address?', 'The page displays the public IP that servers see for your connection, which may differ from your local network IP.'],
      ['What is the difference between public and private IP?', 'A public IP is assigned by your ISP and visible on the internet, while a private IP is used only within your local network.'],
      ['Why does my IP change?', 'Many ISPs assign dynamic IPs that change over time, and using a VPN or proxy will also change the public IP shown.'],
    ],
  ],
  'dns-lookup' => [
    'guide' => [
      'Enter a domain name such as example.com.',
      'Select the record types you want, like A, MX, TXT, NS or CNAME.',
      'Run the lookup to fetch the current DNS records.',
      'Review the results to verify mail, hosting or verification settings.',
    ],
    'faq' => [
      ['What is the difference between an A and a CNAME record?', 'An A record points a domain to an IP address, while a CNAME points one domain name to another as an alias.'],
      ['What are MX records used for?', 'MX records specify the mail servers responsible for receiving email for a domain.'],
      ['Why do I need to check TXT records?', 'TXT records hold verification and policy data such as SPF, DKIM and domain ownership proofs used by email and other services.'],
    ],
  ],
  'html-minifier' => [
    'guide' => [
      'Paste your HTML markup into the input area.',
      'Choose options such as removing comments and collapsing whitespace.',
      'Click Minify to strip unnecessary characters.',
      'Copy the smaller output to speed up page loads.',
    ],
    'faq' => [
      ['Does minifying HTML change how the page looks?', 'No, it only removes whitespace and comments, so the rendered page stays visually identical.'],
      ['How much smaller will my HTML get?', 'Savings vary, but removing comments and whitespace typically trims a noticeable percentage off the file size.'],
      ['Is my HTML uploaded to minify it?', 'No, minification runs entirely in your browser so your markup stays private.'],
    ],
  ],
  'css-minifier' => [
    'guide' => [
      'Paste your CSS into the input field.',
      'Click Minify to remove whitespace, comments and redundant characters.',
      'Review the compressed single-line output.',
      'Copy it into your production stylesheet to reduce file size.',
    ],
    'faq' => [
      ['Will minifying CSS break my styles?', 'No, it only strips whitespace and comments while keeping all rules intact, so your styles work the same.'],
      ['Why should I minify CSS?', 'Smaller CSS files download faster, improving page load times and performance scores.'],
      ['Does the tool run in my browser?', 'Yes, your CSS is minified locally and never sent to a server.'],
    ],
  ],
  'js-minifier' => [
    'guide' => [
      'Paste your JavaScript code into the editor.',
      'Click Minify to remove whitespace, comments and shorten the code.',
      'Check the compressed output for correctness.',
      'Copy the minified script into your production build.',
    ],
    'faq' => [
      ['Does minifying JavaScript change its behaviour?', 'No, minification preserves the logic while removing whitespace and comments, so the script behaves the same.'],
      ['What is the benefit of minifying JS?', 'Minified JavaScript is smaller and loads faster, reducing bandwidth and improving performance.'],
      ['Is my code sent to any server?', 'No, the minification runs entirely in your browser so your code stays private.'],
    ],
  ],
  'code-beautifier' => [
    'guide' => [
      'Paste your HTML, CSS or JavaScript into the input field.',
      'Select the language and your preferred indentation.',
      'Click Beautify to add clean formatting and consistent indentation.',
      'Copy the readable, well-structured code back into your project.',
    ],
    'faq' => [
      ['Which languages can it beautify?', 'It formats HTML, CSS and JavaScript, adding indentation and line breaks for readability.'],
      ['Does beautifying change what my code does?', 'No, it only reformats whitespace and structure; the code behaves exactly the same.'],
      ['Is my code processed locally?', 'Yes, all beautifying happens in your browser, so nothing is uploaded to a server.'],
    ],
  ],
  'slugify' => [
    'guide' => [
      'Type or paste the text or title you want to convert.',
      'The tool lowercases the text and replaces spaces with hyphens.',
      'Special characters and accents are stripped for a clean URL.',
      'Copy the resulting slug for your page or blog post URL.',
    ],
    'faq' => [
      ['What is a URL slug?', 'A slug is the readable, hyphenated part of a URL that identifies a page, usually derived from its title.'],
      ['How does slugify handle special characters?', 'It removes or transliterates accents and symbols and replaces spaces with hyphens to produce a clean, URL-safe string.'],
      ['Why are slugs important for SEO?', 'Descriptive, keyword-rich slugs help search engines and users understand a page, which can improve rankings and click-through.'],
    ],
  ],
  'string-escape' => [
    'guide' => [
      'Paste the raw string you need to escape or unescape.',
      'Choose the target format: JSON, JavaScript, SQL or HTML.',
      'Pick whether to escape or unescape the text.',
      'Copy the safely encoded result into your code.',
    ],
    'faq' => [
      ['Why do I need to escape strings?', 'Escaping prevents special characters from breaking syntax or causing injection issues in JSON, JavaScript, SQL or HTML.'],
      ['Can it unescape as well as escape?', 'Yes, you can convert in both directions to escape raw text or restore an already escaped string.'],
      ['Does escaping happen in my browser?', 'Yes, all encoding runs locally so your strings are never sent to a server.'],
    ],
  ],
  'chmod-calculator' => [
    'guide' => [
      'Tick the read, write and execute checkboxes for owner, group and others.',
      'Watch the numeric mode such as 755 update as you toggle permissions.',
      'Alternatively type a numeric value to see the permission breakdown.',
      'Copy the chmod command to apply the permissions on Linux.',
    ],
    'faq' => [
      ['What does chmod 755 mean?', 'It gives the owner read, write and execute, and gives the group and others read and execute only.'],
      ['What is the difference between 644 and 755?', '644 allows read and write for the owner and read-only for others with no execute, while 755 adds execute permission needed for directories and scripts.'],
      ['How are the permission numbers calculated?', 'Each digit sums read (4), write (2) and execute (1) for owner, group and others respectively.'],
    ],
  ],
  'color-converter' => [
    'guide' => [
      'Enter a color in HEX, RGB or HSL, or pick one from the color picker.',
      'The equivalent values in the other formats update instantly.',
      'Preview the color in a live swatch.',
      'Copy the format you need for your CSS or design tool.',
    ],
    'faq' => [
      ['Can it convert between HEX, RGB and HSL?', 'Yes, enter a color in any of the three formats and the others are calculated automatically.'],
      ['What is the difference between RGB and HSL?', 'RGB defines a color by red, green and blue channels, while HSL uses hue, saturation and lightness which is often more intuitive to adjust.'],
      ['Does it support alpha transparency?', 'Yes, it handles alpha values so you can work with semi-transparent colors in RGBA and HSLA.'],
    ],
  ],
  'base-converter' => [
    'guide' => [
      'Enter a number in binary, octal, decimal or hexadecimal.',
      'Select which base your input is in.',
      'The equivalent values in all other bases appear instantly.',
      'Copy whichever base representation you need.',
    ],
    'faq' => [
      ['Which number bases does it convert?', 'It converts between binary (base 2), octal (base 8), decimal (base 10) and hexadecimal (base 16).'],
      ['How do I convert decimal to hexadecimal?', 'Enter your decimal number and select decimal as the input base, and the hexadecimal equivalent is shown automatically.'],
      ['Is the conversion done locally?', 'Yes, all base conversions run in your browser so nothing is uploaded.'],
    ],
  ],
  'meta-tag-generator' => [
    'guide' => [
      'Fill in your page title, description and keywords.',
      'Add Open Graph details such as image and URL for social sharing.',
      'Preview how the meta tags will render.',
      'Copy the generated tags into the head section of your HTML.',
    ],
    'faq' => [
      ['What meta tags are important for SEO?', 'The title and meta description are key, alongside canonical and robots tags that guide how search engines index the page.'],
      ['What are Open Graph tags used for?', 'Open Graph tags control how your page appears when shared on social platforms like Facebook and LinkedIn, setting the title, description and preview image.'],
      ['How long should my meta description be?', 'Aim for roughly 150 to 160 characters so it displays fully in search results without being truncated.'],
    ],
  ],
  'robots-txt-generator' => [
    'guide' => [
      'Choose which user agents the rules should apply to.',
      'Specify the paths you want to allow or disallow.',
      'Add your sitemap URL so crawlers can find it.',
      'Copy the generated robots.txt and place it in your site root.',
    ],
    'faq' => [
      ['What is a robots.txt file for?', 'It tells search engine crawlers which parts of your site they may or may not access.'],
      ['Does disallowing a page hide it from Google?', 'Not necessarily; robots.txt blocks crawling but a page can still be indexed if linked elsewhere, so use a noindex tag to keep it out of results.'],
      ['Where should the robots.txt file go?', 'It must be placed in the root directory of your domain so it is reachable at yourdomain.com/robots.txt.'],
    ],
  ],
  'htaccess-redirect' => [
    'guide' => [
      'Enter the source path or URL you want to redirect from.',
      'Enter the destination URL and choose 301 permanent or 302 temporary.',
      'Generate the RewriteRule or Redirect directive.',
      'Copy the rule into your Apache .htaccess file.',
    ],
    'faq' => [
      ['When should I use a 301 versus a 302 redirect?', 'Use a 301 for permanent moves so link equity transfers, and a 302 for temporary changes where the original URL will return.'],
      ['Where do I put the generated rule?', 'Paste it into the .htaccess file in the relevant directory of your Apache server, usually the site root.'],
      ['Does a 301 redirect help SEO?', 'Yes, a 301 passes most ranking signals to the new URL, which is why it is preferred when permanently moving content.'],
    ],
  ],
  'text-to-binary' => [
    'guide' => [
      'Type or paste your text into the input field.',
      'Choose the target encoding: binary, ASCII decimal or hexadecimal.',
      'The converted output appears instantly beside your text.',
      'Use reverse mode to decode binary or hex back into readable text.',
    ],
    'faq' => [
      ['How is text converted to binary?', 'Each character is mapped to its numeric code point and then expressed in base 2, typically as 8-bit bytes.'],
      ['Can it convert binary back to text?', 'Yes, paste binary or hexadecimal and switch to decode mode to recover the original text.'],
      ['Does the conversion run in my browser?', 'Yes, all encoding and decoding happens locally so your text is never uploaded.'],
    ],
  ],
];
