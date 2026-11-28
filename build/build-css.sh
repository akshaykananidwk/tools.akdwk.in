#!/usr/bin/env bash
# Rebuild assets/css/tailwind.css from the classes used in PHP/JS files (needs Node.js; nothing to install permanently).
set -e
cd "$(dirname "$0")/.."
printf '@tailwind base;\n@tailwind components;\n@tailwind utilities;\n' > /tmp/kt-tw-in.css
npx --yes tailwindcss@3.4.17 -c build/tailwind.config.js -i /tmp/kt-tw-in.css -o assets/css/tailwind.css --minify
echo "assets/css/tailwind.css: $(wc -c < assets/css/tailwind.css) bytes"
