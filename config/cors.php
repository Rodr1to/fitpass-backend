<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines which cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Allows GET, POST, PUT, PATCH, DELETE, OPTIONS etc.

    'allowed_origins' => explode(',', env('FRONTEND_URLS', '')), // Allow all origins BY DEFAULT - WE WILL CHANGE THIS

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allows common headers

    'exposed_headers' => [],

    'max_age' => 0, // How long OPTIONS request results can be cached (0 = disabled)

    'supports_credentials' => false, // Set to true if you need cookies/sessions across domains

];