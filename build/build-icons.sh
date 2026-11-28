#!/usr/bin/env bash
# Rebuild assets/js/icons.js (only the lucide icons the site uses). Needs PHP + Node.js.
# Run after adding tools/pages with new icon names. Unknown icons still work: icons.js loads the full library for them.
set -e
cd "$(dirname "$0")/.."
T=$(mktemp -d)
php -r 'require "includes/tools_registry.php"; require "includes/generated_tools.php"; $n=[];
  foreach (kt_tools() as $t) $n[$t["icon"]]=1; foreach (kt_categories() as $c) $n[$c["icon"]]=1; foreach (gen_unit_categories() as $c) $n[$c["icon"]]=1;
  echo implode("\n", array_keys($n));' > "$T/icons-reg.txt"
grep -rhoE "data-lucide=\"[a-z0-9-]+\"|icon: *'[a-z0-9-]+'|'icon' *=> *'[a-z0-9-]+'|\[ *'[a-z_]+\.php', *'[a-z0-9-]+'|\['[a-z]+', '[a-z0-9-]+', '" \
  --include=*.php --include=*.js . | grep -v node_modules | grep -oE "'[a-z0-9-]+'(, ')?$|\"[a-z0-9-]+\"$" | tr -d "'\", " > "$T/icons-code.txt"
(cd "$T" && npm install --silent --no-audit --no-fund lucide@1.69.0 >/dev/null && cp "$OLDPWD/build/gen-icons.cjs" . && node gen-icons.cjs "$OLDPWD/assets/js/icons.js")
rm -rf "$T"
