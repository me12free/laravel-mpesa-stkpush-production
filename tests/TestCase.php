<?php

namespace MpesaPremium\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Run package migrations for all tests
        Artisan::call('migrate', ['--database' => 'testing', '--path' => 'database/migrations', '--realpath' => true]);
    }
}
