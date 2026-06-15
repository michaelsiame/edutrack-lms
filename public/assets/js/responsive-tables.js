/* responsive-tables.js
   Copies each <th> label into a data-label on the matching <td> so that the
   CSS card-stack layout (polish.css, <=768px) can show field names on phones.
   Zero per-page markup needed; runs once on load and after dynamic updates. */
(function () {
    function labelTables(root) {
        (root || document).querySelectorAll('table.od-table').forEach(function (table) {
            var heads = Array.prototype.map.call(
                table.querySelectorAll('thead th'),
                function (th) { return th.textContent.trim(); }
            );
            if (!heads.length) return;
            table.querySelectorAll('tbody tr').forEach(function (tr) {
                Array.prototype.forEach.call(tr.children, function (cell, i) {
                    if (cell.hasAttribute('colspan')) return;       // empty-state rows
                    if (cell.hasAttribute('data-label')) return;    // respect explicit labels
                    if (heads[i] != null) cell.setAttribute('data-label', heads[i]);
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { labelTables(); });
    } else {
        labelTables();
    }

    // Re-label tables injected after load (pagination, Alpine, etc.).
    if (window.MutationObserver) {
        var pending = false;
        new MutationObserver(function () {
            if (pending) return;
            pending = true;
            requestAnimationFrame(function () { pending = false; labelTables(); });
        }).observe(document.body, { childList: true, subtree: true });
    }
})();
