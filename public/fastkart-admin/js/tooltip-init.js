
"use strict";

/*
 * Bootstrap 5 stores at most one component instance per element. The stock
 * theme file called .tooltip() on every button / a / input, which claimed that
 * single slot on elements that are also Bootstrap triggers. When the row
 * "Actions" button was clicked, Bootstrap refused to register the dropdown
 * instance ("Bootstrap doesn't allow more than one instance per element"), so
 * Dropdown.clearMenus() could not find it and the open menu never closed on an
 * outside click. Elements carrying their own data-bs-toggle are left alone;
 * their title attribute still shows the browser's native tooltip.
 */
$(document).ready(function () {
    $("button[title], a[title], input[title]")
        .not("[data-bs-toggle]")
        .tooltip();
});

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
