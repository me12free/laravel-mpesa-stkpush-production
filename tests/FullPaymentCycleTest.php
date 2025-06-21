<?php
/**
 * Full payment cycle and logic tests for MpesaPremium package.
 */
namespace MpesaPremium\Tests;

require_once __DIR__.'/TestCase.php';

use MpesaPremium\MpesaPremiumServiceProvider;
use MpesaPremium\Payment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\MpesaPremium\StkPushController::class)]
#[CoversClass(\MpesaPremium\StkPushService::class)]
#[CoversClass(\MpesaPremium\OAuthService::class)]
class FullPaymentCycleTest extends TestCase
{
    use RefreshDatabase, \Illuminate\Foundation\Testing\WithFaker, \Illuminate\Foundation\Testing\WithoutMiddleware;

    protected function getPackageProviders($app)
    {
        return [MpesaPremiumServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('mpesa-stkpush.consumer_key', 'test_key');
        $app['config']->set('mpesa-stkpush.consumer_secret', 'test_secret');
        $app['config']->set('mpesa-stkpush.shortcode', '123456');
        $app['config']->set('mpesa-stkpush.passkey', 'test_passkey');
        $app['config']->set('mpesa-stkpush.callback_url', 'https://example.com/callback?secret=secret');
        $app['config']->set('mpesa-stkpush.callback_secret', 'secret');
        $app['config']->set('mpesa-stkpush.allowed_ips', []); // Allow all IPs in test
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Mock OAuthService to prevent real HTTP calls
        $this->app->bind(\MpesaPremium\OAuthService::class, function () {
            $mock = \Mockery::mock(\MpesaPremium\OAuthService::class);
            $mock->shouldReceive('getAccessToken')->andReturn('FAKE_TOKEN');
            return $mock;
        });
        // Mock StkPushService for error test
        $this->app->bind(\MpesaPremium\StkPushService::class, function ($app) {
            $mock = \Mockery::mock(\MpesaPremium\StkPushService::class.'[initiateStkPush]', [$app->make(\MpesaPremium\OAuthService::class)]);
            $mock->shouldReceive('initiateStkPush')->andReturnUsing(function ($params) {
                if (($params['reference'] ?? null) === 'ORDER999') {
                    return [
                        'status' => 'error',
                        'message' => 'Failed to initiate STK Push',
                    ];
                }
                return [
                    'status' => 'pending',
                    'message' => 'STK Push initiated. Complete payment on your phone.',
                    'CheckoutRequestID' => 'CHECKOUT123',
                ];
            });
            return $mock;
        });
        // Ensure .env exists for Testbench
        $envPath = base_path('vendor/orchestra/testbench-core/laravel/.env');
        if (!file_exists($envPath)) {
            @mkdir(dirname($envPath), 0777, true);
            file_put_contents($envPath, '');
        }
        // Set app key directly for encryption
        \Illuminate\Support\Facades\Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        // Ensure the package view namespace is available
        View::addNamespace('mpesa-premium', base_path('resources/views'));
        // Load package migrations for payments table
        $this->loadMigrationsFrom(base_path('database/migrations'));
    }

    public function test_payment_form_renders_and_localization_works()
    {
        $view = View::make('mpesa-premium::payment', ['errors' => session('errors')]);
        $this->assertStringContainsString('Make a Payment', $view->render());
        $this->assertEquals('Payment successful!', Lang::get('mpesa-premium::messages.payment_success'));
    }

    public function test_payment_initiation_creates_payment_and_returns_json()
    {
        $response = $this->postJson('/api/mpesa/stkpush', [
            'payer_name' => 'Test User',
            'phone' => '+254700000000',
            'amount' => 100,
            'reference' => 'ORDER123',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'message', 'reference']);
        $this->assertDatabaseHas('payments', [
            'transaction_reference' => 'ORDER123',
            'status' => 'pending',
        ]);
    }

    public function test_callback_updates_payment_status_to_success()
    {
        $payment = Payment::create([
            'payer_name' => 'Test User',
            'phone' => '+254700000000',
            'amount' => 100,
            'currency' => 'KES',
            'gateway' => 'mpesa',
            'transaction_reference' => 'ORDER456',
            'status' => 'pending',
            'mpesa_checkout_id' => 'CHECKOUT123',
        ]);
        $callbackData = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'MERCHANT123',
                    'CheckoutRequestID' => 'CHECKOUT123',
                    'ResultCode' => 0,
                    'ResultDesc' => 'Success',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 100],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'MPESA123'],
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->postJson('/api/mpesa/callback?secret=secret', $callbackData);
        $response->assertStatus(200);
        $response->assertJson(['result' => 'ok']);
        $payment->refresh();
        $this->assertEquals('success', $payment->status);
        $this->assertEquals('MPESA123', $payment->meta['mpesa_receipt']);
    }

    public function test_callback_rejects_invalid_secret()
    {
        $response = $this->postJson('/api/mpesa/callback?secret=wrong', []);
        $response->assertStatus(401);
        $response->assertJson(['result' => 'unauthorized']);
    }

    public function test_payment_initiation_fails_on_service_error()
    {
        $response = $this->postJson('/api/mpesa/stkpush', [
            'payer_name' => 'Test User',
            'phone' => '+254700000000',
            'amount' => 100,
            'reference' => 'ORDER999',
        ]);
        $response->assertStatus(200); // The mock returns a normal response, not an exception
        $response->assertJson(['status' => 'error', 'message' => 'Failed to initiate STK Push']);
    }

    public function test_payment_initiation_fails_on_invalid_input()
    {
        $response = $this->postJson('/api/mpesa/stkpush', [
            'payer_name' => '',
        ]);
        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    public function test_callback_fails_on_missing_body()
    {
        $response = $this->postJson('/api/mpesa/callback?secret=secret', []);
        $response->assertStatus(400);
        $response->assertJson(['result' => 'missing body']);
    }

    public function test_callback_fails_on_missing_payment()
    {
        $callbackData = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'MERCHANT123',
                    'CheckoutRequestID' => 'NONEXISTENT',
                    'ResultCode' => 0,
                    'ResultDesc' => 'Success',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 100],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'MPESA123'],
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->postJson('/api/mpesa/callback?secret=secret', $callbackData);
        $response->assertStatus(404);
        $response->assertJson(['result' => 'payment not found']);
    }

    public function test_callback_handles_failed_payment()
    {
        $payment = Payment::create([
            'payer_name' => 'Test User',
            'phone' => '+254700000000',
            'amount' => 100,
            'currency' => 'KES',
            'gateway' => 'mpesa',
            'transaction_reference' => 'ORDER789',
            'status' => 'pending',
            'mpesa_checkout_id' => 'CHECKOUT789',
        ]);
        $callbackData = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'MERCHANT123',
                    'CheckoutRequestID' => 'CHECKOUT789',
                    'ResultCode' => 1,
                    'ResultDesc' => 'Failed',
                ]
            ]
        ];
        $response = $this->postJson('/api/mpesa/callback?secret=secret', $callbackData);
        $response->assertStatus(200);
        $response->assertJson(['result' => 'ok']);
        $payment->refresh();
        $this->assertEquals('failed', $payment->status);
    }
}
