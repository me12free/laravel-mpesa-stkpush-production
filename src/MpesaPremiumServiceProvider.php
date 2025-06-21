<?php
/**
 * Copyright (c) 2025 John Ekiru <johnewoi72@gmail.com>
 *
 * Premium Laravel M-Pesa STK Push Integration
 *
 * Service provider for publishing config, migrations, binding services, and registering routes.
 * Ensures package is ready for Laravel integration.
 */
namespace MpesaPremium;

use Illuminate\Support\ServiceProvider;

class MpesaPremiumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/mpesa-stkpush.php' => config_path('mpesa-stkpush.php'),
        ], 'config');
        // Publish migration
        if (! class_exists('CreatePaymentsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/2025_06_21_000000_create_payments_table.php' => database_path('migrations/2025_06_21_000000_create_payments_table.php'),
            ], 'migrations');
        }
        // Publish view
        $this->publishes([
            __DIR__.'/../resources/views/payment.blade.php' => resource_path('views/payment.blade.php'),
        ], 'views');
        // Register package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/mpesa.php');
        // Register package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mpesa-premium');
        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mpesa-premium');
        // Publish translations
        $this->publishes([
            __DIR__.'/../lang' => resource_path('lang/vendor/mpesa-premium'),
        ], 'lang');
        // Publish public assets
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/mpesa-premium'),
        ], 'public');
        // Register commands and About info
        if ($this->app->runningInConsole()) {
            $this->commands([
                \MpesaPremium\Commands\InfoCommand::class,
            ]);
            if (class_exists(\Illuminate\Foundation\Console\AboutCommand::class)) {
                \Illuminate\Foundation\Console\AboutCommand::add('Mpesa Premium', fn () => [
                    'Version' => '1.0.0',
                    'Author' => 'John Ekiru',
                ]);
            }
        }
    }
    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/mpesa-stkpush.php', 'mpesa-stkpush'
        );
    }
}
