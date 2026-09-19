/*
 * Keeps the current page's menu open and highlighted.
 *
 * The Fastkart sidebar script closes every submenu on load and then looks for
 * the page by matching the link href against the path, which never matches
 * the absolute, query-string URLs Laravel generates. The server already knows
 * the active link (data-admin-active), so this runs after that script and
 * re-opens the chain above it.
 */
(function () {
    var links = document.querySelectorAll('.sidebar-wrapper [data-admin-active]');

    links.forEach(function (link) {
        link.classList.add('active');

        var list = link.closest('ul');

        while (list && !list.classList.contains('sidebar-links')) {
            if (list.classList.contains('sidebar-submenu') || list.classList.contains('submenu-content')) {
                list.style.display = 'block';

                var toggle = list.parentElement ? list.parentElement.querySelector(':scope > a') : null;

                if (toggle) {
                    toggle.classList.add('active');
                }
            }

            list = list.parentElement ? list.parentElement.closest('ul') : null;
        }
    });
})();
