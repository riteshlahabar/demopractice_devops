<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fallback locales
    |--------------------------------------------------------------------------
    |
    | The admin Languages module is the real source of truth. This list is only
    | used when that table cannot be read (migrations not run, database down),
    | so a request never ends up with an empty locale list.
    |
    */

    'fallback_locales' => ['en', 'hi', 'mr', 'gu', 'pa', 'te'],

    /*
    |--------------------------------------------------------------------------
    | App translation sync
    |--------------------------------------------------------------------------
    |
    | The mobile apps post their English UI strings once per locale so the
    | server can translate and store them alongside the website's own strings.
    | The cap keeps a single request bounded.
    |
    */

    'app_sync_max_items' => 400,

    'app_string_group' => 'app',

    /*
    |--------------------------------------------------------------------------
    | App translations (app_translations table)
    |--------------------------------------------------------------------------
    |
    | Each app registers its English strings; the admin Translate button then
    | fills every other language in small batches so one request always ends
    | well inside the shared-hosting time limit.
    |
    */

    'apps' => [
        'customer' => 'Customer App',
        'dealer' => 'Dealer App',
        'salesman' => 'Salesman App',
    ],

    'app_register_max_items' => 1500,

    'app_translate_batch_size' => 40,

];
