(function () {
    'use strict';

    /*
     * Tabbed admin forms are still one <form> with one submit button, so a
     * field can be required while its tab (or its variant accordion) is closed.
     * The browser refuses to report a validation error on a display:none
     * control - Chrome logs "An invalid form control is not focusable" and the
     * submit silently does nothing - so anything invalid must be revealed
     * before the browser tries to focus it.
     */

    function revealCollapses(element) {
        var collapse = element.closest('.collapse');

        while (collapse) {
            if (!collapse.classList.contains('show')) {
                collapse.classList.add('show');

                var toggle = document.querySelector('[data-bs-target="#' + collapse.id + '"]');
                if (toggle) {
                    toggle.classList.remove('collapsed');
                    toggle.setAttribute('aria-expanded', 'true');
                }
            }

            collapse = collapse.parentElement ? collapse.parentElement.closest('.collapse') : null;
        }
    }

    function revealTab(element) {
        var pane = element.closest('.tab-pane');
        if (!pane || !pane.parentElement || pane.classList.contains('active')) {
            return;
        }

        // Deliberately not Bootstrap's own tab switch: .tab-pane carries .fade,
        // so its 150ms transition would leave the field still invisible at the
        // moment the browser tries to focus it. Toggling the classes directly
        // shows the pane in the same tick. Bootstrap stays in sync because it
        // reads these same classes on the next real click.
        Array.prototype.forEach.call(pane.parentElement.children, function (candidate) {
            if (!candidate.classList.contains('tab-pane')) {
                return;
            }

            candidate.classList.toggle('active', candidate === pane);
            candidate.classList.toggle('show', candidate === pane);
        });

        var trigger = document.querySelector('[data-bs-target="#' + pane.id + '"]');
        var strip = trigger ? trigger.closest('.nav') : null;

        if (strip) {
            strip.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (button) {
                var isActive = button === trigger;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
        }
    }

    function reveal(element) {
        if (!element) {
            return;
        }

        revealCollapses(element);
        revealTab(element);
    }

    function initForm(form) {
        if (form.dataset.adminFormTabsReady === '1') {
            return;
        }
        form.dataset.adminFormTabsReady = '1';

        // "invalid" fires per control during constraint validation, before the
        // browser reports the first one, so revealing here makes it focusable.
        form.addEventListener('invalid', function (event) {
            reveal(event.target);
        }, true);

        // Server-side errors: open the first field the backend rejected. The
        // active tab is already chosen in Blade; this handles collapsed rows.
        reveal(form.querySelector('.is-invalid'));
    }

    /*
     * Conditional tabs: a tab strip item can be hidden by the visibility rules
     * in form.blade.php. If the tab that just went away was the active one, move
     * to the first tab still on screen so the pane area is never blank.
     */
    window.adminFormTabsSync = function (form) {
        var items = form.querySelectorAll('.admin-form-tabs .nav-item');
        if (!items.length) {
            return;
        }

        var activeIsHidden = false;
        var firstVisibleTrigger = null;

        items.forEach(function (item) {
            var hidden = item.style.display === 'none';
            var trigger = item.querySelector('[data-bs-toggle="tab"]');
            if (!trigger) {
                return;
            }

            if (!hidden && !firstVisibleTrigger) {
                firstVisibleTrigger = trigger;
            }

            if (hidden && trigger.classList.contains('active')) {
                activeIsHidden = true;
            }
        });

        if (activeIsHidden && firstVisibleTrigger) {
            firstVisibleTrigger.click();
        }
    };

    function init() {
        document.querySelectorAll('.admin-form-card form').forEach(initForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
