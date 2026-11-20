<?php

namespace Modules\TattooInbox\Providers;

use Hyn\Tenancy\Environment;
use Illuminate\Support\ServiceProvider;
use Modules\TattooInbox\Console\SyncTattooInboxMetaChannels;
use Modules\TattooInbox\Services\MetaChannelRegistryService;
use Modules\TattooInbox\Services\MetaInboxService;
use Modules\TattooInbox\Services\MetaMessageParserService;
use Modules\TattooInbox\Services\MetaTenantResolverService;
use Modules\TattooInbox\Services\TattooInboxAiService;
use Modules\TattooInbox\Services\TattooQuoteService;

class TattooInboxServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'tattooinbox');
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'tattooinbox');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTattooInboxMetaChannels::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton(MetaMessageParserService::class, function () {
            return new MetaMessageParserService();
        });

        $this->app->singleton(MetaTenantResolverService::class, function () {
            return new MetaTenantResolverService();
        });

        $this->app->singleton(MetaChannelRegistryService::class, function () {
            return new MetaChannelRegistryService();
        });

        $this->app->singleton(TattooQuoteService::class, function () {
            return new TattooQuoteService();
        });

        $this->app->singleton(TattooInboxAiService::class, function () {
            return new TattooInboxAiService();
        });

        $this->app->singleton(MetaInboxService::class, function ($app) {
            return new MetaInboxService(
                $app->make(MetaMessageParserService::class),
                $app->make(TattooQuoteService::class),
                $app->make(TattooInboxAiService::class),
                $app->make(MetaTenantResolverService::class),
                $app->make(Environment::class),
                $app->make(\Modules\TattooInbox\Services\TenantContext::class)
            );
        });
    }

    public function provides()
    {
        return [
            MetaMessageParserService::class,
            MetaTenantResolverService::class,
            MetaChannelRegistryService::class,
            TattooQuoteService::class,
            TattooInboxAiService::class,
            MetaInboxService::class,
        ];
    }
}
