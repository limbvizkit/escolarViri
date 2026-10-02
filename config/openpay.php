<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenPay Merchant Identity
    |--------------------------------------------------------------------------
    |
    | The merchant identifier is the canonical OPENPAY_MERCHANT_ID.
    | OPENPAY_ID is kept as a legacy fallback only; if both are present,
    | OPENPAY_MERCHANT_ID wins.
    |
    */

    'merchant_id' => env('OPENPAY_MERCHANT_ID', env('OPENPAY_ID')),

    /*
    |--------------------------------------------------------------------------
    | OpenPay Keys
    |--------------------------------------------------------------------------
    |
    | public_key is used by the front-end JavaScript tokenizer.
    | secret_key is used server-side by this service to authenticate
    | API requests via Basic Auth (username = secret_key, password = empty).
    |
    */

    'public_key' => env('OPENPAY_PUBLIC_KEY'),

    'secret_key' => env('OPENPAY_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OpenPay Environment
    |--------------------------------------------------------------------------
    |
    | Supported values: "sandbox" or "production". Defaults to sandbox.
    |
    */

    'mode' => env('OPENPAY_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for an OpenPay API response.
    |
    */

    'timeout' => (int) env('OPENPAY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook HTTP Basic Auth
    |--------------------------------------------------------------------------
    |
    | OpenPay notifies charge events to a public URL protected by Basic Auth.
    | These credentials are configured in the OpenPay dashboard, not in the
    | payload. Leave empty to reject webhook requests (503).
    |
    */

    'webhook_user' => env('OPENPAY_WEBHOOK_USER'),

    'webhook_password' => env('OPENPAY_WEBHOOK_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | OpenPay API URLs
    |--------------------------------------------------------------------------
    |
    | {merchant_id} will be replaced at runtime by the configured value.
    |
    */

    'urls' => [
        'sandbox' => 'https://sandbox-api.openpay.mx/v1/{merchant_id}',
        'production' => 'https://api.openpay.mx/v1/{merchant_id}',
    ],

];
