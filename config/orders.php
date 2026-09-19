<?php

return [
    'enforce_stock' => filter_var(
        env('ENFORCE_STOCK_ON_ORDER', true),
        FILTER_VALIDATE_BOOL
    ),
];
