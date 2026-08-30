<?php

return [
    'currency' => env('PAYMENT_CURRENCY', 'RWF'),
    'currency_symbol' => 'RWF ',
    'gateways' => [
        'momo' => [
            'name' => 'Mobile Money (MTN / Airtel Rwanda)',
            'enabled' => true,
            'test_mode' => (bool) env('PAYMENT_MOMO_TEST_MODE', true),
        ],
        'stripe' => [
            'name' => 'Credit / Debit Card (Stripe)',
            'enabled' => !empty(env('PAYMENT_STRIPE_KEY')),
            'publishable_key' => env('PAYMENT_STRIPE_KEY', ''),
            'secret_key' => env('PAYMENT_STRIPE_SECRET', ''),
        ],
        'flutterwave' => [
            'name' => 'Flutterwave (Africa & Card)',
            'enabled' => !empty(env('PAYMENT_FLUTTERWAVE_PUBLIC_KEY')),
            'public_key' => env('PAYMENT_FLUTTERWAVE_PUBLIC_KEY', ''),
            'secret_key' => env('PAYMENT_FLUTTERWAVE_SECRET_KEY', ''),
        ],
        'paypal' => [
            'name' => 'PayPal',
            'enabled' => !empty(env('PAYMENT_PAYPAL_CLIENT_ID')),
            'client_id' => env('PAYMENT_PAYPAL_CLIENT_ID', ''),
            'secret' => env('PAYMENT_PAYPAL_SECRET', ''),
        ],
        'sandbox' => [
            'name' => 'Demo Sandbox Instant Pay',
            'enabled' => true,
        ]
    ]
];
