<?php
/**
 * Basic Testbench test for package bootstrapping.
 */
namespace MpesaPremium\Tests;

require_once __DIR__.'/TestCase.php';

use MpesaPremium\MpesaPremiumServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MpesaPremiumServiceProvider::class)]
class BasicTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MpesaPremiumServiceProvider::class];
    }

    public function test_package_boots(): void
    {
        $this->assertTrue(class_exists(MpesaPremiumServiceProvider::class));
    }
}
