<?php

namespace Modules\Tattoo\Providers;

use Illuminate\Support\ServiceProvider;

class TattooServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'tattoo');
    }

    public function register()
    {
        //
    }

    public function provides()
    {
        return [];
    }
}