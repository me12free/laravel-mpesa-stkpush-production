<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * This controller handles payment initiation and M-Pesa STK Push callbacks.
 * All code is production-ready, reusable, and thoroughly commented for clarity.
 */
namespace MpesaPremium;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;

class StkPushController extends Controller
{
    /**
     * Initiate a payment and trigger M-Pesa STK Push.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiate(Request $request)
    {
        // Validate user input for payment
        $validated = $request->validate([
            'payer_name' => 'required|string|max:255', // Name of the payer
            'phone' => 'required|string',              // Phone number in international format
            'amount' => 'required|numeric|min:1',      // Amount in KES
            'reference' => 'required|string',          // Unique payment reference
        ]);
        // Throttle to prevent abuse: max 3 attempts per minute per phone/IP
        $throttleKey = 'stkpush:' . $validated['phone'] . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'Too many attempts. Please wait ' . ceil($seconds/60) . ' minutes.'
            ], 429);
        }
        RateLimiter::hit($throttleKey, 60);
        // Create a new payment record (status: pending)
        $payment = Payment::create([
            'payer_name' => $validated['payer_name'],
            'phone' => $validated['phone'],
            'amount' => $validated['amount'],
            'currency' => 'KES',
            'gateway' => 'mpesa',
            'transaction_reference' => $validated['reference'],
            'status' => 'pending',
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            // User ID is optional; set if authenticated
            'user_id' => Auth::check() ? Auth::user()->getAuthIdentifier() : null,
        ]);
        // Initiate the STK Push via the service
        $service = app(\MpesaPremium\StkPushService::class);
        try {
            $result = $service->initiateStkPush([
                'phone' => $validated['phone'],
                'amount' => $validated['amount'],
                'reference' => $validated['reference'],
                'description' => 'Payment by ' . $validated['payer_name'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate STK Push',
            ], 500);
        }
        // Store the M-Pesa CheckoutRequestID for later callback matching
        if (isset($result['CheckoutRequestID'])) {
            $payment->mpesa_checkout_id = $result['CheckoutRequestID'];
            $payment->save();
        }
        // Respond with status and reference for client-side polling or UI update
        return response()->json([
            'status' => $result['status'] ?? 'pending',
            'message' => $result['message'] ?? 'STK Push initiated. Complete payment on your phone.',
            'reference' => $payment->transaction_reference,
        ]);
    }

    /**
     * Handle M-Pesa STK Push callback from Safaricom.
     *
     * Security: Only accept requests with the correct secret and from allowed IPs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        // Rate limit callback endpoint to prevent abuse
        $throttleKey = 'stkpush_callback:' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 30)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return response()->json([
                'result' => 'rate limited',
                'retry_after' => $seconds
            ], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
        $expectedSecret = config('mpesa-stkpush.callback_secret');
        $providedSecret = $request->query('secret') ?? $request->input('secret');
        $allowedIps = config('mpesa-stkpush.allowed_ips', []);
        $ipAllowed = empty($allowedIps) || in_array($request->ip(), $allowedIps);
        // Only proceed if secret and IP are valid
        if (!$expectedSecret || $providedSecret !== $expectedSecret || !$ipAllowed) {
            Log::warning('STK Push Callback: Unauthorized', [
                'ip' => $request->ip(),
                'provided' => $providedSecret
            ]);
            return response()->json(['result' => 'unauthorized'], 401);
        }
        $data = $request->all();
        $body = $data['Body']['stkCallback'] ?? null;
        if (!$body) {
            return response()->json(['result' => 'missing body'], 400);
        }
        $checkoutId = $body['CheckoutRequestID'] ?? null;
        $resultCode = $body['ResultCode'] ?? null;
        $resultDesc = $body['ResultDesc'] ?? null;
        $amount = null;
        $receipt = null;
        // Parse callback metadata for amount and receipt
        if (isset($body['CallbackMetadata']['Item'])) {
            foreach ($body['CallbackMetadata']['Item'] as $item) {
                if ($item['Name'] === 'Amount') $amount = $item['Value'];
                if ($item['Name'] === 'MpesaReceiptNumber') $receipt = $item['Value'];
            }
        }
        // Find the payment by CheckoutRequestID
        $payment = Payment::where('mpesa_checkout_id', $checkoutId)->first();
        if (!$payment) {
            return response()->json(['result' => 'payment not found'], 404);
        }
        $meta = $payment->meta ?? [];
        $plainStatus = $resultCode == 0 ? 'success' : 'failed';
        $meta['mpesa_status'] = $plainStatus;
        $meta['mpesa_result_desc'] = $resultDesc;
        $meta['mpesa_receipt'] = $receipt;
        $meta['mpesa_callback'] = $body;
        $payment->meta = $meta;
        $payment->status = $plainStatus;
        $payment->save();
        return response()->json(['result' => 'ok']);
    }
}
