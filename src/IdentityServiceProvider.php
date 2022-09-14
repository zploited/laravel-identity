<?php

namespace Zploited\Identity\Client\Laravel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Zploited\Identity\Client\Identity;
use Zploited\Identity\Client\Laravel\Guards\BearerGuard;
use Zploited\Identity\Client\Laravel\Guards\SessionGuard;

class IdentityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/identity-client.php' => config_path('identity-client.php')
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/identity-client.php', 'identity-client');

        // Binding the identity object into a singleton
        $this->app->singleton(Identity::class, function() {
            return new Identity(config('identity-client.identity'));
        });

        // Registers the Session Guard
        Auth::extend('identity:session', function($app, $name, array $config) {
            return new SessionGuard(null, '_identity');
        });

        // Registers the Bearer Guard
        Auth::extend('identity:bearer', function($app, $name, array $config) {
            return new BearerGuard(null);
        });
    }
}