<?php

return [
    'api_key'           => env('PAYMOB_API_KEY', ''),
    'cc_integration_id' => env('PAYMOB_CC_INTEGRATION_ID', ''),
    'iframe_id'         => env('PAYMOB_IFRAME_ID', ''),
    'hmac_secret'       => env('PAYMOB_HMAC_SECRET', ''),
    'currency'          => env('PAYMOB_CURRENCY', 'EGP'),
];
