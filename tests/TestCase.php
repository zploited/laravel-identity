<?php
namespace Zploited\Identity\Client\Laravel\Tests;

use Zploited\Identity\Client\Laravel\IdentityServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getApplicationProviders(): array
    {
        return [
            IdentityServiceProvider::class
        ];
    }
}