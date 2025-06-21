<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Configuration for all M-Pesa credentials, endpoints, and security settings.
 * Update these values in your .env for production use.
 */
return [
    // Safaricom STK Push endpoint
    'endpoint' => env('MPESA_STK_ENDPOINT', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'),
    // OAuth endpoint for token generation
    'oauth_endpoint' => env('MPESA_OAUTH_ENDPOINT', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
    // Consumer key/secret from Safaricom developer portal
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    // Your business shortcode and passkey
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    // Callback URL and secret for secure callbacks
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'callback_secret' => env('MPESA_CALLBACK_SECRET'),
    // Allowed Safaricom IPs for callbacks (production only)
    'allowed_ips' => [
        '196.201.214.200', '196.201.214.206', '196.201.214.207',
        '196.201.214.208', '196.201.214.209', '196.201.214.210',
        '196.201.214.211', '196.201.214.212', '196.201.214.213',
        '196.201.214.214', '196.201.214.216', '196.201.214.218',
        '196.201.214.219', '196.201.214.220', '196.201.214.221',
        '196.201.214.222', '196.201.214.223',
    ],
    // Branding options for the payment interface
    'branding' => [
        'powered_by' => true, // Show "Powered by M-Pesa Premium" link
        'upgrade_link' => true, // Show "Upgrade to Premium" link
    ],
];
