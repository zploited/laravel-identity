<?php

namespace Zploited\Laravel\Identity\Tests;

use Zploited\Laravel\Identity\IdentityServiceProvider;

/**
 *
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
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