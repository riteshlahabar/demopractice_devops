<?php

return [
    'pages' => [
        '404',
        'about-us',
        'cart',
        'checkout',
        'contact-us',
        'faq',
        'forgot',
        'index-5',
        'login',
        'order-success',
        'order-tracking',
        'otp',
        'product-left-thumbnail',
        'search',
        'shop-left-sidebar',
        'sign-up',
        'user-dashboard',
        'wishlist',
    ],
    'product_type_labels' => [
        'medicine' => 'Medicine',
        'fertilizer' => 'Fertilizer',
        'seed' => 'Seeds',
        'seeds' => 'Seeds',
        'veterinary' => 'Veterinary Products',
        'veterinary_products' => 'Veterinary Products',
        'equipment' => 'Equipment',
        'other' => 'Other',
    ],
    'invoice_templates' => ['invoice-1', 'invoice-2', 'invoice-3'],
    'email_templates' => [
        'abandonment-email',
        'offer-template',
        'order-success',
        'reset-password',
        'welcome',
    ],
    // LGD state whose districts fill the "Your Location" box until admin adds Delivery Areas (27 = Maharashtra).
    'default_delivery_state_code' => 27,

    /*
     * Cards above the FAQ accordion. Each card filters the questions by its
     * key, which is also the Category option in the admin FAQs form.
     */
    'faq_categories' => [
        'getting-started' => ['label' => 'Getting Started', 'description' => 'Creating an account, signing in and registering as a dealer.', 'image' => 'fastkart-store/images/inner-page/faq/start.png'],
        'orders-delivery' => ['label' => 'Orders & Delivery', 'description' => 'Delivery time, order tracking and the areas we deliver to.', 'image' => 'fastkart-store/images/inner-page/faq/help.png'],
        'pricing-payment' => ['label' => 'Pricing & Payment', 'description' => 'Payment methods, dealer credit and GST invoices.', 'image' => 'fastkart-store/images/inner-page/faq/price.png'],
        'support' => ['label' => 'Support & Returns', 'description' => 'Cancelling an order, returns and how to reach our team.', 'image' => 'fastkart-store/images/inner-page/faq/contact.png'],
    ],
];
