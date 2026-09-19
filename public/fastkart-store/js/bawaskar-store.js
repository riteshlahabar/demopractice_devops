(function () {
    var config = window.storefrontUiConfig || {};
    var csrfToken = config.csrfToken || '';
    var cartState = config.cart || {};
    var wishlistConfig = config.wishlist || {};
    var wishlistIds = new Set(Array.isArray(wishlistConfig.ids) ? wishlistConfig.ids.map(function (id) { return parseInt(id, 10); }) : []);

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatPrice(amount) {
        var value = Number(amount || 0);
        return 'Rs. ' + value.toFixed(2);
    }

    function formatQty(quantity) {
        var value = Number(quantity || 0).toFixed(3);
        return value.replace(/\.0+$/, '').replace(/(\.\d*[1-9])0+$/, '$1');
    }

    function ensureBadge(target, className, hiddenClass) {
        var badge = target.querySelector('.' + className);
        if (!badge) {
            badge = document.createElement('span');
            badge.className = hiddenClass ? className + ' ' + hiddenClass : className;
            target.appendChild(badge);
        }
        return badge;
    }

    function setBadgeContent(target, value, hiddenText) {
        if (!target) {
            return;
        }
        target.innerHTML = '';
        target.appendChild(document.createTextNode(String(value)));
        var hidden = document.createElement('span');
        hidden.className = 'visually-hidden';
        hidden.textContent = hiddenText;
        target.appendChild(hidden);
    }

    function updateWishlistCount(count) {
        document.querySelectorAll('[data-store-wishlist-link], a.header-icon.swap-icon').forEach(function (target) {
            if (wishlistConfig.url && target.getAttribute('href') === 'javascript:void(0)') {
                target.setAttribute('href', wishlistConfig.url);
            }
            var badge = ensureBadge(target, 'store-wishlist-count', 'position-absolute top-0 start-100 translate-middle badge');
            setBadgeContent(badge, count, 'wishlist items');
            badge.classList.toggle('d-none', count <= 0);
        });
    }

    function setWishlistButtonState(button, inWishlist) {
        button.classList.toggle('active', inWishlist);
        button.classList.toggle('is-active', inWishlist);
        button.setAttribute('data-in-wishlist', inWishlist ? '1' : '0');
        button.setAttribute('aria-pressed', inWishlist ? 'true' : 'false');
    }

    function syncWishlistButtons(productId, inWishlist) {
        document.querySelectorAll('[data-store-wishlist-toggle][data-product-id="' + productId + '"]').forEach(function (button) {
            setWishlistButtonState(button, inWishlist);
        });
    }

    function toggleWishlistEmptyState() {
        var grid = document.querySelector('[data-store-wishlist-grid]');
        var empty = document.querySelector('[data-store-wishlist-empty]');
        if (!grid || !empty) {
            return;
        }
        var cards = grid.querySelectorAll('[data-store-wishlist-card]');
        var hasCards = cards.length > 0;
        grid.style.display = hasCards ? '' : 'none';
        empty.classList.toggle('d-none', hasCards);
    }

    function removeWishlistCard(productId) {
        document.querySelectorAll('[data-store-wishlist-card][data-product-id="' + productId + '"]').forEach(function (card) {
            card.remove();
        });
        toggleWishlistEmptyState();
    }

    function renderWishlistPreview(items) {
        var dropdown = document.querySelector('a.header-icon.swap-icon + .onhover-div .cart-list');
        if (!dropdown) {
            return;
        }
        if (!Array.isArray(items) || items.length === 0) {
            dropdown.innerHTML = '<li><div class="drop-cart"><div class="drop-contain"><h5>Your wishlist is empty.</h5><h6>Save products to review them later.</h6></div></div></li>';
            return;
        }
        dropdown.innerHTML = items.map(function (item) {
            return '<li><div class="drop-cart"><a href="' + escapeHtml(item.product_url) + '" class="drop-image"><img src="' + escapeHtml(item.image_url) + '" class="blur-up lazyload" alt="' + escapeHtml(item.name) + '"></a><div class="drop-contain"><a href="' + escapeHtml(item.product_url) + '"><h5>' + escapeHtml(item.name) + '</h5></a><h6>' + formatPrice(item.price) + '</h6></div></div></li>';
        }).join('');
    }

    function updateCartCount(count) {
        var normalized = formatQty(count || 0);
        document.querySelectorAll('[data-store-cart-count-target], .header-badge .badge, a.header-icon.bag-icon .badge-number').forEach(function (target) {
            setBadgeContent(target, normalized, 'cart items');
        });
    }

    function renderCartPreview(items) {
        var previewItems = Array.isArray(items) ? items.slice(0, 3) : [];
        document.querySelectorAll('[data-store-cart-list], .onhover-dropdown.header-badge .cart-list').forEach(function (list) {
            if (previewItems.length === 0) {
                list.innerHTML = '<li class="product-box-contain"><div class="drop-cart"><div class="drop-contain"><h5>Your cart is empty.</h5><h6>Add products to continue shopping.</h6></div></div></li>';
                return;
            }
            list.innerHTML = previewItems.map(function (item) {
                var shownRate = item.quantity_label === 'case(s)' ? item.case_price : item.unit_price;
                return '<li class="product-box-contain"><div class="drop-cart"><a href="' + escapeHtml(item.product_url) + '" class="drop-image"><img src="' + escapeHtml(item.image_url) + '" class="blur-up lazyload" alt="' + escapeHtml(item.name) + '"></a><div class="drop-contain"><a href="' + escapeHtml(item.product_url) + '"><h5>' + escapeHtml(item.name) + (item.variant_name ? ' - ' + escapeHtml(item.variant_name) : '') + '</h5></a><h6><span>' + escapeHtml(formatQty(item.quantity)) + ' ' + escapeHtml(item.quantity_label || '') + ' x</span> ' + escapeHtml(formatPrice(shownRate)) + '</h6><form method="POST" action="' + escapeHtml(item.remove_url || '#') + '" data-store-cart-remove-form><input type="hidden" name="_token" value="' + escapeHtml(csrfToken) + '"><button type="submit" class="close-button close_button"><i class="fa-solid fa-xmark"></i></button></form></div></div></li>';
            }).join('');
        });
    }

    function updateCartTotal(grandTotal) {
        document.querySelectorAll('[data-store-cart-total], .onhover-dropdown.header-badge .price-box h4.theme-color.fw-bold').forEach(function (target) {
            target.textContent = formatPrice(grandTotal || 0);
        });
    }

    function applyCartState() {
        updateCartCount(cartState.count || 0);
        renderCartPreview(cartState.items || []);
        updateCartTotal(cartState.grandTotal || 0);
    }

    function updateCartIssueState(payload) {
        var row = document.querySelector('[data-store-cart-issues-row]');
        var alert = document.querySelector('[data-store-cart-issues]');
        if (!row || !alert) {
            return;
        }
        var hasIssues = !!(payload && payload.has_issues);
        if (!hasIssues) {
            row.classList.add('d-none');
            alert.textContent = '';
            return;
        }
        var issueItem = Array.isArray(payload.items) ? payload.items.find(function (item) { return item.has_issue; }) : null;
        alert.textContent = issueItem
            ? 'Only ' + formatQty(issueItem.available_stock) + ' quantity is available for ' + issueItem.name + '. Please update your cart before checkout.'
            : 'Some quantities exceed available stock. Please update your cart before checkout.';
        row.classList.remove('d-none');
    }

    function showCartMessage(message, type) {
        var row = document.querySelector('[data-store-cart-message-row]');
        var alert = document.querySelector('[data-store-cart-message]');
        if (!row || !alert) {
            return;
        }
        if (!message) {
            row.classList.add('d-none');
            alert.textContent = '';
            alert.className = 'alert mb-0';
            return;
        }
        alert.className = 'alert mb-0 alert-' + (type || 'success');
        alert.textContent = message;
        row.classList.remove('d-none');
    }

    function renderCartPageRow(item) {
        var hasDiscount = Number(item.mrp || 0) > Number(item.unit_price || 0) + 0.0001;
        var savings = Number(item.savings || 0);
        var availableStock = Number(item.available_stock || 0);
        return '' +
            '<tr class="product-box-contain" data-product-id="' + escapeHtml(item.id) + '">' +
                '<td class="product-detail">' +
                    '<div class="product border-0">' +
                        '<a href="' + escapeHtml(item.product_url) + '" class="product-image">' +
                            '<img src="' + escapeHtml(item.image_url) + '" class="img-fluid blur-up lazyload" alt="' + escapeHtml(item.name) + '">' +
                        '</a>' +
                        '<div class="product-detail">' +
                            '<ul>' +
                                '<li class="name"><a href="' + escapeHtml(item.product_url) + '">' + escapeHtml(item.name) + '</a></li>' +
                                '<li class="text-content"><span class="text-title">Category:</span> ' + escapeHtml(item.category_name || 'Product') + '</li>' +
                                (item.variant_name ? '<li class="text-content"><span class="text-title">Size / Pack:</span> ' + escapeHtml(item.variant_name) + '</li>' : '') +
                                '<li class="text-content"><span class="text-title">Quantity</span> - ' + escapeHtml(formatQty(item.quantity)) + ' ' + escapeHtml(item.quantity_label || 'retail pack(s)') + (Number(item.units_per_case || 1) > 1 ? ' (' + escapeHtml(formatQty(item.units_per_case)) + ' retail packs per case)' : '') + '</li>' +
                                '<li><h5 class="text-content d-inline-block">Price :</h5> <span>' + escapeHtml(formatPrice(item.unit_price)) + ' per retail pack</span>' + (Number(item.units_per_case || 1) > 1 ? ' <span class="text-content">/ ' + escapeHtml(formatPrice(item.case_price)) + ' per case</span>' : '') + (hasDiscount ? ' <span class="text-content">' + escapeHtml(formatPrice(item.mrp)) + '</span>' : '') + '</li>' +
                                (savings > 0 ? '<li><h5 class="saving theme-color">Saving : ' + escapeHtml(formatPrice(savings)) + '</h5></li>' : '') +
                                (item.has_issue ? '<li><h6 class="text-danger">Available stock: ' + escapeHtml(formatQty(availableStock)) + ' retail packs</h6></li>' : '') +
                                '<li class="quantity-price-box"><div class="cart_qty"><div class="input-group"><input class="form-control input-number qty-input" type="number" min="0" step="1" name="items[' + escapeHtml(item.line_key || item.id) + ']" value="' + escapeHtml(formatQty(item.quantity)) + '"></div></div></li>' +
                                '<li><h5>Total: ' + escapeHtml(formatPrice(item.line_total)) + '</h5></li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                '</td>' +
                '<td class="price">' +
                    '<h4 class="table-title text-content">Price</h4>' +
                    '<h5>' + escapeHtml(formatPrice(item.unit_price)) + ' / retail pack</h5>' +
                    (Number(item.units_per_case || 1) > 1 ? '<h6 class="theme-color">' + escapeHtml(formatPrice(item.case_price)) + ' / case</h6>' : '') +
                    (hasDiscount ? '<h6 class="text-content"><del>' + escapeHtml(formatPrice(item.mrp)) + '</del></h6>' : '') +
                    (savings > 0 ? '<h6 class="theme-color">You Save : ' + escapeHtml(formatPrice(savings)) + '</h6>' : '') +
                '</td>' +
                '<td class="quantity">' +
                    '<h4 class="table-title text-content">Qty</h4>' +
                    '<div class="quantity-price"><div class="cart_qty"><div class="input-group"><input class="form-control input-number qty-input" type="number" min="0" step="1" name="items[' + escapeHtml(item.line_key || item.id) + ']" value="' + escapeHtml(formatQty(item.quantity)) + '"></div></div></div>' +
                '</td>' +
                '<td class="subtotal"><h4 class="table-title text-content">Total</h4><h5>' + escapeHtml(formatPrice(item.line_total)) + '</h5></td>' +
                '<td class="save-remove"><h4 class="table-title text-content">Action</h4><button type="submit" formaction="' + escapeHtml(item.remove_url || '#') + '" class="remove close_button border-0 bg-transparent">Remove</button></td>' +
            '</tr>';
    }

    function syncCartPage(payload) {
        var rows = document.querySelector('[data-store-cart-rows]');
        var content = document.querySelector('[data-store-cart-content]');
        var empty = document.querySelector('[data-store-cart-empty]');
        var checkoutLink = document.querySelector('[data-store-cart-checkout-link]');
        var items = Array.isArray(payload.items) ? payload.items : [];
        var hasItems = items.length > 0;

        if (rows) {
            rows.innerHTML = items.map(renderCartPageRow).join('');
        }
        if (content) {
            content.classList.toggle('d-none', !hasItems);
        }
        if (empty) {
            empty.classList.toggle('d-none', hasItems);
        }

        var countTarget = document.querySelector('[data-store-cart-page-count]');
        if (countTarget) {
            countTarget.textContent = formatQty(payload.count || 0);
        }
        var subtotalTarget = document.querySelector('[data-store-cart-page-subtotal]');
        if (subtotalTarget) {
            subtotalTarget.textContent = formatPrice(payload.subtotal || 0);
        }
        var gstTarget = document.querySelector('[data-store-cart-page-gst]');
        if (gstTarget) {
            gstTarget.textContent = formatPrice(payload.gst_total || 0);
        }
        var totalTarget = document.querySelector('[data-store-cart-page-total]');
        if (totalTarget) {
            totalTarget.textContent = formatPrice(payload.grand_total || 0);
        }

        if (checkoutLink) {
            var disabled = !hasItems || !!payload.has_issues;
            checkoutLink.classList.toggle('disabled', disabled);
            checkoutLink.setAttribute('aria-disabled', disabled ? 'true' : 'false');
            checkoutLink.setAttribute('href', disabled ? (config.cartUrl || checkoutLink.getAttribute('href')) : (config.checkoutUrl || checkoutLink.getAttribute('href')));
        }

        updateCartIssueState(payload);
    }

    function applyInitialState() {
        updateWishlistCount(Number(wishlistConfig.count || 0));
        document.querySelectorAll('[data-store-wishlist-toggle]').forEach(function (button) {
            var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
            setWishlistButtonState(button, wishlistIds.has(productId));
        });
        renderWishlistPreview(wishlistConfig.items || []);
        toggleWishlistEmptyState();
        applyCartState();
    }

    function handleWishlistToggle(event) {
        var button = event.target.closest('[data-store-wishlist-toggle]');
        if (!button || !wishlistConfig.toggleUrl || !csrfToken) {
            return;
        }

        event.preventDefault();

        var productId = parseInt(button.getAttribute('data-product-id') || '0', 10);
        if (!productId || button.classList.contains('is-loading')) {
            return;
        }

        button.classList.add('is-loading');

        fetch(wishlistConfig.toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ product_id: productId })
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Wishlist request failed');
                }
                return response.json();
            })
            .then(function (payload) {
                var ids = Array.isArray(payload.ids) ? payload.ids.map(function (id) { return parseInt(id, 10); }) : [];
                wishlistIds = new Set(ids);
                wishlistConfig.count = Number(payload.count || 0);
                syncWishlistButtons(productId, !!payload.in_wishlist);
                updateWishlistCount(wishlistConfig.count);

                if (payload.in_wishlist) {
                    wishlistIds.add(productId);
                } else {
                    wishlistIds.delete(productId);
                    removeWishlistCard(productId);
                }
            })
            .catch(function () {
                window.location.href = wishlistConfig.url || button.getAttribute('href') || window.location.href;
            })
            .finally(function () {
                button.classList.remove('is-loading');
            });
    }

    function updateCartFromPayload(payload) {
        cartState.count = Number(payload.count || 0);
        cartState.subtotal = Number(payload.subtotal || 0);
        cartState.gstTotal = Number(payload.gst_total || 0);
        cartState.grandTotal = Number(payload.grand_total || 0);
        cartState.items = Array.isArray(payload.items) ? payload.items : [];
        applyCartState();
        syncCartPage(payload);
    }

    function submitCartRequest(form, action, formData) {
        fetch(action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
            .then(function (response) {
                if (response.status === 401) {
                    return response.json().then(function (payload) {
                        window.location.href = payload.login_url || config.loginUrl || window.location.href;
                        return null;
                    });
                }
                if (response.status === 422) {
                    return response.json().then(function (payload) {
                        var message = 'Unable to update cart.';
                        if (payload && payload.errors) {
                            Object.keys(payload.errors).some(function (key) {
                                var value = payload.errors[key];
                                if (Array.isArray(value) && value.length > 0) {
                                    message = value[0];
                                    return true;
                                }
                                return false;
                            });
                        } else if (payload && payload.message) {
                            message = payload.message;
                        }
                        throw { type: 'validation', message: message };
                    });
                }
                if (!response.ok) {
                    throw new Error('Cart request failed');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload) {
                    return;
                }
                updateCartFromPayload(payload);
                showCartMessage(payload.message, 'success');
            })
            .catch(function (error) {
                if (error && error.type === 'validation') {
                    showCartMessage(error.message, 'danger');
                    return;
                }
                window.location.reload();
            })
            .finally(function () {
                form.classList.remove('is-loading');
            });
    }

    function handleCartSubmit(event) {
        var form = event.target;
        if (!form || !form.matches('form[data-store-cart-add], form[data-store-cart-form], form[data-store-cart-remove-form]')) {
            return;
        }

        event.preventDefault();
        if (form.classList.contains('is-loading')) {
            return;
        }

        form.classList.add('is-loading');

        var action = form.getAttribute('action');
        if (event.submitter && event.submitter.getAttribute('formaction')) {
            action = event.submitter.getAttribute('formaction');
        }

        submitCartRequest(form, action, new FormData(form));
    }

    function handleDeadCartButton(event) {
        var button = event.target.closest('.addcart-button, .add-cart-button');
        if (!button || button.closest('form[data-store-cart-add]') || button.disabled) {
            return;
        }

        var modalAddButton = button.closest('.modal-button');
        if (modalAddButton) {
            event.preventDefault();
            window.location.href = config.userAuthenticated ? (config.cartUrl || window.location.href) : (config.loginUrl || window.location.href);
        }
    }

    document.addEventListener('click', handleWishlistToggle);
    document.addEventListener('click', handleDeadCartButton);
    document.addEventListener('submit', handleCartSubmit);
    document.addEventListener('DOMContentLoaded', applyInitialState);

    if (document.readyState !== 'loading') {
        applyInitialState();
    }
})();
