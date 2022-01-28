<?php

namespace Zploited\Laravel\Identity\Tests;

use Illuminate\Support\Facades\Route;
use Zploited\Laravel\Identity\IdentityServiceProvider;

/**
 *
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('auth.guards.identity', [
            'driver' => 'identity',
            'provider' => 'users'
        ]);

        Route::middleware('auth:identity')->get('/', function() {

        });

        Route::get('/login', function () {})->name('login');
    }

    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            IdentityServiceProvider::class
        ];
    }

    /**
     * @param $app
     * @return string
     */
    protected function getApplicationTimezone($app): string
    {
        return 'Europe/Copenhagen';
    }
}