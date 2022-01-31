<?php

namespace Zploited\Laravel\Identity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Zploited\Laravel\Identity\Auth\CookieGuard;
use Zploited\Laravel\Identity\Auth\BearerGuard;

class IdentityServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerIdentityGuard();
        $this->registerTokenCookieGuard();
    }

    public function boot()
    {
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
        Auth::extend('bearer', function($app, $name, array $config) {
            $provider = (isset($config['provider'])) ? Auth::createUserProvider($config['provider'])  : null;

            return new BearerGuard($provider, request());
        });
    }

    protected function registerTokenCookieGuard()
    {
        Auth::extend('cookie', function ($app, $name, array $config) {
            $provider = (isset($config['provider'])) ? Auth::createUserProvider($config['provider'])  : null;

            return new CookieGuard($provider, 'token');
        });
    }
}