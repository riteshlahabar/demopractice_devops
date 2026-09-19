<?php

return [
    /*
     * ICICI Bank Eazypay. Nothing here has a working default: with no merchant
     * id or key configured the gateway reports itself unavailable rather than
     * building a request the bank will reject.
     */
    'enabled' => (bool) env('EAZYPAY_ENABLED', false),

    'merchant_id' => env('EAZYPAY_MERCHANT_ID', ''),

    // AES-128 key issued by the bank. Sixteen bytes; it is the shared secret
    // behind both the request encryption and the response signature, so it
    // never leaves the server and never appears in a log line.
    'encryption_key' => env('EAZYPAY_ENCRYPTION_KEY', ''),

    'sub_merchant_id' => env('EAZYPAY_SUB_MERCHANT_ID', ''),

    // Bank supplied. 9 is the usual "all online modes" value.
    'paymode' => env('EAZYPAY_PAYMODE', '9'),

    'base_url' => env('EAZYPAY_BASE_URL', 'https://eazypay.icicibank.com/EazyPG'),

    // Where the bank sends the payer back. Must be reachable over HTTPS and
    // registered with the bank; a mismatch is rejected at their end.
    'return_url' => env('EAZYPAY_RETURN_URL', ''),

    'response' => [
        // E000 is Eazypay's success code for every online payment mode.
        'success_codes' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('EAZYPAY_SUCCESS_CODES', 'E000'))
        ))),

        /*
         * The fields whose values are joined with "|" and hashed with SHA-512
         * to reproduce the "RS" signature the bank sends back. Order matters
         * and is fixed by the bank's specification; it lives in config so it
         * can be corrected against the live UAT response without a code change.
         */
        'signature_fields' => [
            'ID',
            'Response Code',
            'Unique Ref Number',
            'Service Tax Amount',
            'Processing Fee Amount',
            'Total Amount',
            'Transaction Amount',
            'Transaction Date',
            'Interchange Value',
            'TDR',
            'Payment Mode',
            'SubMerchantId',
            'ReferenceNo',
            'TPS',
        ],
    ],
];
