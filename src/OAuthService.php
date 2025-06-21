<?php
/**
 * Copyright (c) 2025 John Ekiru <me12free@users.noreply.github.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Handles OAuth token retrieval and caching for Safaricom API.
 * Ensures secure, production-grade access token management.
 */
namespace MpesaPremium;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OAuthService
{
    /**
     * Get a valid M-Pesa access token, caching for 3500 seconds.
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'mpesa_access_token';
        return Cache::remember($cacheKey, 3500, function () {
            $consumerKey = config('mpesa-stkpush.consumer_key');
            $consumerSecret = config('mpesa-stkpush.consumer_secret');
            $endpoint = config('mpesa-stkpush.oauth_endpoint', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
            $response = Http::withBasicAuth($consumerKey, $consumerSecret)->get($endpoint);
            if ($response->failed()) {
                throw new \Exception('Failed to get M-Pesa access token: ' . $response->body());
            }
            return $response->json('access_token');
        });
    }
}
