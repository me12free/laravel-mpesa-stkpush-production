<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Handles the initiation of STK Push requests to Safaricom API.
 * Uses OAuthService for secure token management.
 */
namespace MpesaPremium;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StkPushService
{
    protected $oauth;
    /**
     * Constructor: inject OAuthService for token management.
     */
    public function __construct(OAuthService $oauth = null)
    {
        $this->oauth = $oauth ?: new OAuthService();
    }
    /**
     * Initiate an STK Push request to Safaricom API.
     *
     * @param array $params ['phone', 'amount', 'reference', 'description']
     * @return array Safaricom API response
     */
    public function initiateStkPush(array $params): array
    {
        // Validate required parameters
        foreach (['phone', 'amount', 'reference'] as $key) {
            if (empty($params[$key])) {
                throw new \InvalidArgumentException("Missing required parameter: $key");
            }
        }
        $endpoint = config('mpesa-stkpush.endpoint');
        $token = $this->oauth->getAccessToken();
        $timestamp = now()->format('YmdHis');
        $payload = [
            'BusinessShortCode' => config('mpesa-stkpush.shortcode'),
            'Password' => $this->generatePassword($timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $params['amount'],
            'PartyA' => $params['phone'],
            'PartyB' => config('mpesa-stkpush.shortcode'),
            'PhoneNumber' => $params['phone'],
            'CallBackURL' => config('mpesa-stkpush.callback_url'),
            'AccountReference' => $params['reference'],
            'TransactionDesc' => $params['description'] ?? 'Payment',
        ];
        $response = Http::withToken($token)->post($endpoint, $payload);
        if ($response->failed()) {
            Log::error('STK Push failed', ['response' => $response->body()]);
            return ['status' => 'error', 'message' => 'Failed to initiate STK Push'];
        }
        return $response->json();
    }
    /**
     * Generate the password for STK Push authentication.
     *
     * @param string $timestamp
     * @return string
     */
    public function generatePassword($timestamp): string
    {
        $shortcode = config('mpesa-stkpush.shortcode');
        $passkey = config('mpesa-stkpush.passkey');
        return base64_encode($shortcode . $passkey . $timestamp);
    }
}
