import fs from 'fs';

const input = 'openapi-merged.json';
const output = 'openapi-merged.api.json';

if (!fs.existsSync(input)) {
  console.error('File not found:', input);
  process.exit(1);
}

const doc = JSON.parse(fs.readFileSync(input, 'utf8'));
const src = doc.paths || {};
const dst = {};

for (const [k, v] of Object.entries(src)) {
  let newKey = k.startsWith('/api') ? k : '/api' + k;
  newKey = newKey.replace(/\/places\/places(\/|$)/, '/places$1');
  dst[newKey] = Object.assign(dst[newKey] || {}, v);
}

doc.paths = dst;
fs.writeFileSync(output, JSON.stringify(doc, null, 2), 'utf8');
console.log('Done ->', output);
