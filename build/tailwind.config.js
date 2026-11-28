// Tailwind build for Krishna Tools — produces assets/css/tailwind.css (replaces the slow cdn.tailwindcss.com script).
// Rebuild after adding new Tailwind classes in PHP or JS:   bash build/build-css.sh
const path = require('path');
const R = path.resolve(__dirname, '..');
module.exports = {
  content: [R + '/*.php', R + '/includes/**/*.php', R + '/admin/**/*.php', R + '/blog/**/*.php', R + '/install/**/*.php', R + '/api/**/*.php', R + '/assets/js/**/*.js'],
  theme: { extend: {} },
  plugins: [],
};
