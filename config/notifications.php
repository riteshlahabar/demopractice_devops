<?php

/*
 * App notifications: the inbox row is always written; a push is sent through
 * the configured driver. `log` only writes to laravel.log, so everything works
 * before Firebase exists. Switch to `fcm` once the Firebase service account
 * JSON is on the server.
 */
return [
    'push' => [
        'driver' => env('PUSH_DRIVER', 'log'),

        'fcm' => [
            'project_id' => env('FIREBASE_PROJECT_ID'),
            // Absolute path to the service-account JSON (keep it outside public_html).
            'credentials' => env('FIREBASE_CREDENTIALS'),
            'timeout' => 10,
        ],
    ],

    // FCM topic each role's app subscribes to; group messages go here.
    'topics' => [
        'customer' => 'role_customer',
        'dealer' => 'role_dealer',
        'salesman' => 'role_salesman',
    ],

    'device_platforms' => ['android', 'ios'],

    /*
     * Automatic notifications, keyed by event then status. A status that is
     * not listed sends nothing. `:name` placeholders are filled from the
     * record; English only, like the other server messages.
     */
    'templates' => [
        'order' => [
            'placed' => ['Order placed', 'Your order :order_no has been placed. We will update you when it is approved.'],
            'approved' => ['Order approved', 'Your order :order_no has been approved.'],
            'packing' => ['Order being packed', 'Your order :order_no is being packed.'],
            'dispatched' => ['Order dispatched', 'Your order :order_no has been dispatched.'],
            'out_for_delivery' => ['Out for delivery', 'Your order :order_no is out for delivery today.'],
            'delivered' => ['Order delivered', 'Your order :order_no has been delivered. Thank you!'],
            'cancelled' => ['Order cancelled', 'Your order :order_no has been cancelled.'],
        ],
        'order_review' => [
            'salesman_review' => ['New order to review', ':dealer placed order :order_no. Please review it.'],
        ],
        'return' => [
            'approved' => ['Return approved', 'Your return :return_no has been approved.'],
            'rejected' => ['Return rejected', 'Your return :return_no has been rejected.'],
            'received' => ['Return received', 'We have received your return :return_no.'],
            'refunded' => ['Refund processed', 'Refund of Rs :amount for return :return_no has been processed.'],
        ],
        'payment' => [
            'paid' => ['Payment received', 'We received your payment of Rs :amount (:payment_no).'],
            'collected' => ['Payment collected', 'Your payment of Rs :amount (:payment_no) has been collected.'],
            'verified' => ['Payment verified', 'Your payment of Rs :amount (:payment_no) has been verified.'],
            'failed' => ['Payment failed', 'Your payment of Rs :amount (:payment_no) failed.'],
            'refunded' => ['Payment refunded', 'Your payment of Rs :amount (:payment_no) has been refunded.'],
        ],
        'dealer_account' => [
            'active' => ['Account approved', 'Your dealer account has been approved. You can now place orders.'],
        ],
        'leave' => [
            'approved' => ['Leave approved', 'Your :leave_type leave from :from_date to :to_date has been approved.'],
            'rejected' => ['Leave rejected', 'Your :leave_type leave from :from_date to :to_date has been rejected.'],
        ],
        'expense' => [
            'approved' => ['Expense approved', 'Your :expense_type expense of Rs :amount has been approved.'],
            'rejected' => ['Expense rejected', 'Your :expense_type expense of Rs :amount has been rejected.'],
            'paid' => ['Expense paid', 'Your :expense_type expense of Rs :amount has been paid.'],
        ],
        'advance' => [
            'approved' => ['Request approved', 'Your :advance_type request of Rs :amount has been approved.'],
            'disbursed' => ['Amount disbursed', 'Your :advance_type of Rs :amount has been disbursed.'],
            'rejected' => ['Request rejected', 'Your :advance_type request of Rs :amount has been rejected.'],
            'closed' => ['Fully recovered', 'Your :advance_type of Rs :amount is now fully recovered.'],
        ],
        'tour_plan' => [
            'approved' => ['Tour plan approved', 'Your tour plan for :plan_date has been approved.'],
            'cancelled' => ['Tour plan cancelled', 'Your tour plan for :plan_date has been cancelled.'],
        ],
        'announcement' => [
            'published' => [':title', ':body'],
        ],
    ],
];
