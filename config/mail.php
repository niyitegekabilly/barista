<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => [
            'host' => env('MAIL_HOST', 'mail.beyondbarista.rw'),
            'port' => (int)env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'username' => env('MAIL_USERNAME', 'info@beyondbarista.rw'),
            'password' => env('MAIL_PASSWORD', 'Amakuru@2026'),
            'timeout' => (int)(env('MAIL_TIMEOUT') ?: 15),
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@beyondbarista.rw'),
        'name' => env('MAIL_FROM_NAME', 'Beyond Barista Academy'),
    ],
];
