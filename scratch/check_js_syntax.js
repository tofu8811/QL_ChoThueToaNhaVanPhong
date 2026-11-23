const fs = require('fs');
let code = fs.readFileSync('resources/views/partials/scripts/contracts.blade.php', 'utf8');
// remove blade directives like <script> or @...
code = code.replace(/<script[^>]*>/gi, '').replace(/<\/script>/gi, '');
code = code.replace(/@\w+(\(.*?\))?/g, '');
try {
  new Function(code);
  console.log('JS syntax is completely valid!');
} catch (err) {
  console.error('JS Syntax Error:', err.message);
}
