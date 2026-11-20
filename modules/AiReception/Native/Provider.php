<?php

namespace Modules\AiReception\Native;

use Illuminate\Support\ServiceProvider;
class Provider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([\Modules\AiReception\Console\QueueReminders::class]);
        config(['queue.connections.aireception'=>['driver'=>'database','connection'=>'system','table'=>'aireception_jobs','queue'=>'aireception','retry_after'=>90]]);
        app('router')->pushMiddlewareToGroup('web', TenantSession::class);
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'aireception');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/native.php');
    }
}
