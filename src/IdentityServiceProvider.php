<?php

namespace Zploited\Laravel\Identity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Zploited\Laravel\Identity\Auth\IdentityGuard;
use Zploited\Laravel\Identity\Auth\IdentityUserProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerIdentityGuard();
    }

    public function boot()
    {
        $this->registerIdentityUserProvider();

        $this->publishes([
            __DIR__.'/../config/identity.php' => config_path('identity.php'),   // configuration
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/identity.php', 'identity');
    }

    /**
     * Registers a new identity guard driver
     * @return void
     */
    protected function registerIdentityGuard()
    {
        Auth::extend('identity', function($app, $name, array $config) {
            return new IdentityGuard(Auth::createUserProvider($config['provider']), request());
        });
    }

    protected function registerIdentityUserProvider()
    {
        Auth::provider('identity', function ($app, array $config) {
            return new IdentityUserProvider();
        });
    }
}