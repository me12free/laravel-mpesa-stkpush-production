<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Package routes for payment initiation and callback.
 */
use Illuminate\Support\Facades\Route;
use MpesaPremium\StkPushController;

Route::group(['prefix' => 'api/mpesa', 'middleware' => ['api']], function () {
    Route::post('/stkpush', [StkPushController::class, 'initiate'])->name('payments.stkpush');
    Route::post('/callback', [StkPushController::class, 'callback'])->name('payments.callback');
});
