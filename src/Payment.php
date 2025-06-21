<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * This model represents a payment record for M-Pesa and other gateways.
 * Fields are encrypted for privacy. Meta stores extra info and callback data.
 */
namespace MpesaPremium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Crypt;

class Payment extends Model
{
    use HasUuids;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'payer_name', 'phone', 'amount', 'currency', 'gateway',
        'transaction_reference', 'mpesa_checkout_id', 'meta', 'status', 'user_id',
    ];

    /**
     * Attribute casting for meta and IDs.
     */
    protected $casts = [
        'meta' => 'encrypted:array',
        'id' => 'string',
        'user_id' => 'string',
    ];

    /**
     * Encrypt payer name before saving.
     */
    public function setPayerNameAttribute($value)
    {
        $this->attributes['payer_name'] = $value ? Crypt::encryptString($value) : null;
    }
    /**
     * Decrypt payer name when accessed.
     */
    public function getPayerNameAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
    /**
     * Encrypt phone before saving.
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = $value ? Crypt::encryptString($value) : null;
    }
    /**
     * Decrypt phone when accessed.
     */
    public function getPhoneAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
    /**
     * Relationship to user (if available).
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
}
