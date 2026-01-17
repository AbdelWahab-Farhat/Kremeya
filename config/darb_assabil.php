<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Darb Assabil API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for integrating with Darb Assabil local shipping API.
    | https://v2.sabil.ly
    |
    */

    'base_url'        => env('DARB_ASSABIL_API_URL', 'https://v2.sabil.ly'),

    'api_key'         => env('DARB_ASSABIL_API_KEY'),

    'account_id'      => env('DARB_ASSABIL_ACCOUNT_ID'),

    'api_version'     => env('DARB_ASSABIL_API_VERSION', '1.0.0'),

    'service_id'      => env('DARB_ASSABIL_SERVICE_ID'),

    'default_contact' => env('DARB_ASSABIL_DEFAULT_CONTACT'),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    // Who pays for shipping: sender, receiver, or sales
    'payment_by'      => env('DARB_ASSABIL_PAYMENT_BY', 'sender'),

    // Default country code (ISO-3)
    'country_code'    => env('DARB_ASSABIL_COUNTRY_CODE', 'lby'),

    // Default currency
    'currency'        => env('DARB_ASSABIL_CURRENCY', 'lyd'),

    // Enable/disable the integration
    'enabled'         => env('DARB_ASSABIL_ENABLED', true),
];
