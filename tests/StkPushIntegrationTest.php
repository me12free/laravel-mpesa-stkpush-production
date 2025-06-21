<?php
/**
 * Advanced integration tests for MpesaPremium package.
 */
namespace MpesaPremium\Tests;

require_once __DIR__.'/TestCase.php';

use MpesaPremium\MpesaPremiumServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MpesaPremiumServiceProvider::class)]
class StkPushIntegrationTest extends TestCase
{
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
        $app['config']->set('mpesa-stkpush.allowed_ips', []);
    }

    public function test_config_is_loaded(): void
    {
        $this->assertEquals('test_key', config('mpesa-stkpush.consumer_key'));
    }

    public function test_translation_is_available(): void
    {
        $this->assertEquals('Payment successful!', Lang::get('mpesa-premium::messages.payment_success'));
    }

    public function test_artisan_command_registered(): void
    {
        $result = Artisan::call('mpesa-premium:info');
        $this->assertEquals(0, $result);
    }

    public function test_routes_are_registered(): void
    {
        $routes = app('router')->getRoutes();
        $this->assertTrue($routes->hasNamedRoute('payments.stkpush'));
        $this->assertTrue($routes->hasNamedRoute('payments.callback'));
    }
}
