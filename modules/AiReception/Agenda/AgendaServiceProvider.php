<?php

namespace Modules\AiReception\Agenda;

class AgendaServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind(BookingService::class, function ($app) {
            $db = $app['db']->connection('agenda');
            LabSafety::check($db);
            return new BookingService($db);
        });
    }
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}
