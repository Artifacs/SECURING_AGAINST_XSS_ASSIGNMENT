/* js/app.js - external script (allowed by CSP 'script-src self').
   Replaces the old inline onsubmit="return confirm(...)" handlers, which the CSP blocks. */
document.addEventListener('submit', function (ev) {
    var msg = ev.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) ev.preventDefault();
});
