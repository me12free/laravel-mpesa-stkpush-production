<?php
/**
 * Copyright (c) 2025 John Ekiru <me12free@users.noreply.github.com>
 *
 * Migration for payments table for M-Pesa STK Push package.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Primary key (UUID)
            $table->uuid('user_id')->nullable(); // Optional user reference
            $table->decimal('amount', 10, 2); // Payment amount
            $table->string('currency', 3)->default('KES'); // Currency (default: KES)
            $table->string('gateway'); // Payment gateway (e.g., mpesa)
            $table->string('transaction_reference')->unique(); // Unique reference
            $table->string('mpesa_checkout_id')->nullable()->index(); // M-Pesa checkout ID
            $table->text('meta')->nullable(); // Encrypted meta/callback data
            $table->string('payer_name')->nullable(); // Encrypted payer name
            $table->string('phone')->nullable(); // Encrypted phone
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->index(); // Status
            $table->timestamps(); // Created/updated at
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
