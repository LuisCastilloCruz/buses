<?php

namespace Modules\Odontologia\Providers;

use Illuminate\Support\ServiceProvider;
class OdontologiaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'odontologia');
        $this->app->bind('aireception.domain.dental', \Modules\Odontologia\Services\ReceptionCalendarAdapter::class);
    }
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'odontologia');
        if (config('aireception.native_enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        }
    }
}
