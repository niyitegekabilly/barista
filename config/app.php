<?php

return [
    'name' => env('APP_NAME', 'Beyond Barista Academy'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost/bbacademy'),
    'timezone' => env('APP_TIMEZONE', 'Africa/Kigali'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => 'en',
    'locales' => [
        'en' => 'English',
        'fr' => 'Français',
        'rw' => 'Ikinyarwanda'
    ],
    'version' => '1.0.0',
];
