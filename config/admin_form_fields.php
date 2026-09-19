<?php

/**
 * Custom admin form field types and the partial that renders each one.
 *
 * A new widget is added by registering it here - the shared form template and
 * the resolver stay untouched.
 *
 * `wrap` is false when the partial emits its own grid column.
 */
return [
    'product_translation_tools' => [
        'view' => 'admin.products.partials.translation-tools',
        'wrap' => false,
    ],
    'product_variants_repeater' => [
        'view' => 'admin.products.partials.variants.repeater',
    ],
    'product_media_repeater' => [
        'view' => 'admin.products.partials.media.repeater',
    ],
    'checkbox_list' => [
        'view' => 'admin.shared.fields.checkbox-list',
    ],
    'location_picker' => [
        'view' => 'admin.shared.fields.location-picker',
        'wrap' => false,
    ],
    'district_picker' => [
        'view' => 'admin.shared.fields.district-picker',
        'wrap' => false,
    ],
    'private_file' => [
        'view' => 'admin.shared.fields.private-file',
    ],
    'product_additional_information_repeater' => [
        'view' => 'admin.products.partials.additional-information.repeater',
    ],
];
