<?php

namespace Modules\AiReception\Native;

use Illuminate\Support\ServiceProvider;
class Provider extends ServiceProvider
{
    public function boot()
    {
        app('router')->pushMiddlewareToGroup('web', TenantSession::class);
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'aireception');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/native.php');
    }
}
