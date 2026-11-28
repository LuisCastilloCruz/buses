<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Keep the existing token driver configuration, but reject non-string
        // credentials before Laravel 5.7 forwards them to the user provider.
        $this->app['auth']->extend('token', function ($app, $name, array $config) {
            $guard = new \App\Auth\StrictTokenGuard(
                $app['auth']->createUserProvider($config['provider'] ?? null),
                $app['request'],
                $config['input_key'] ?? 'api_token',
                $config['storage_key'] ?? 'api_token'
            );
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
