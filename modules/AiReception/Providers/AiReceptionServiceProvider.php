<?php

namespace Modules\AiReception\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Modules\AiReception\Services\OpenAiIntakeClassifier;

class AiReceptionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'aireception');

        $this->app->bind(OpenAiIntakeClassifier::class, function ($app) {
            return new OpenAiIntakeClassifier(
                new Client(),
                (string) config('aireception.openai_api_key', ''),
                (string) config('aireception.openai_model', 'gpt-4.1-mini'),
                (int) config('aireception.timeout_seconds', 15)
            );
        });
    }

    public function boot()
    {
        // Intencionalmente sin rutas, webhooks, migraciones ni tareas.
        // Esta fase no debe modificar ningun negocio existente.
    }
}
