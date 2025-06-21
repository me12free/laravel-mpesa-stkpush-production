<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Example Artisan command for package info.
 */
namespace MpesaPremium\Commands;

use Illuminate\Console\Command;

class InfoCommand extends Command
{
    protected $signature = 'mpesa-premium:info';
    protected $description = 'Show info about the Mpesa Premium package';

    public function handle()
    {
        $this->info('Mpesa Premium STK Push Package by John Ekiru');
    }
}
