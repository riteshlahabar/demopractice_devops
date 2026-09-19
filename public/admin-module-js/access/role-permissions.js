/*
 * Role permissions grid: "All" boxes for a row, a group and the whole page,
 * and Add / Edit / Delete switch View on (they start from the list page).
 */
(function () {
    var form = document.querySelector('[data-role-permissions]');
    if (!form) return;

    function actions(scope) {
        return Array.prototype.slice.call(scope.querySelectorAll('input[data-action]'));
    }

    function syncRow(row) {
        var boxes = actions(row);
        var viewBox = row.querySelector('input[data-action="view"]');
        if (viewBox && boxes.some(function (b) { return b !== viewBox && b.checked; })) viewBox.checked = true;
        var all = row.querySelector('[data-check-scope="row"]');
        if (all) all.checked = boxes.length > 0 && boxes.every(function (b) { return b.checked; });
    }

    function syncScope(scope, selector) {
        var box = scope.querySelector(selector);
        var boxes = actions(scope);
        if (box) box.checked = boxes.length > 0 && boxes.every(function (b) { return b.checked; });
    }

    function syncAll() {
        form.querySelectorAll('[data-perm-row]').forEach(syncRow);
        form.querySelectorAll('[data-perm-group]').forEach(function (group) { syncScope(group, '[data-check-scope="group"]'); });
        syncScope(form, '[data-check-scope="all"]');
    }

    form.addEventListener('change', function (event) {
        var target = event.target;
        var scope = target.getAttribute('data-check-scope');

        if (scope) {
            var container = scope === 'row' ? target.closest('[data-perm-row]') : scope === 'group' ? target.closest('[data-perm-group]') : form;
            actions(container).forEach(function (b) { b.checked = target.checked; });
        } else if (target.getAttribute('data-action') === 'view' && !target.checked) {
            // Without View the other actions cannot be reached.
            actions(target.closest('[data-perm-row]')).forEach(function (b) { b.checked = false; });
        }

        syncAll();
    });

    syncAll();
})();
