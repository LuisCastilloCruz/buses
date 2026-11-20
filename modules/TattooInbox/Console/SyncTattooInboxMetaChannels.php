<?php

namespace Modules\TattooInbox\Console;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Console\Command;
use Modules\TattooInbox\Services\MetaChannelRegistryService;

class SyncTattooInboxMetaChannels extends Command
{
    protected $signature = 'tattoo-inbox:sync-meta-channels {websiteId?}';
    protected $description = 'Sincroniza los canales Meta de los tenants hacia la tabla maestra central del Tattoo Inbox';

    protected $environment;
    protected $registry;

    public function __construct(Environment $environment, MetaChannelRegistryService $registry)
    {
        parent::__construct();

        $this->environment = $environment;
        $this->registry = $registry;
    }

    public function handle()
    {
        $websiteId = $this->argument('websiteId');

        $query = Website::query();

        if ($websiteId) {
            $query->where('id', $websiteId);
        }

        $websites = $query->get();

        if ($websites->isEmpty()) {
            $this->warn('No se encontraron websites para sincronizar.');
            return 0;
        }

        foreach ($websites as $website) {
            try {
                $hostname = Hostname::query()
                    ->where('website_id', $website->id)
                    ->orderBy('id')
                    ->first();

                $this->environment->tenant($website);

                $result = $this->registry->syncCurrentTenantChannels(
                    (int) $website->id,
                    $hostname ? (int) $hostname->id : null,
                    $hostname ? $hostname->fqdn : null
                );

                $this->info(sprintf(
                    'Website %s sincronizado. Hostname: %s. Registros: %s',
                    $website->id,
                    $hostname ? $hostname->fqdn : 'N/A',
                    json_encode($result, JSON_UNESCAPED_UNICODE)
                ));
            } catch (\Throwable $e) {
                $this->error(sprintf(
                    'Error sincronizando website %s: %s',
                    $website->id,
                    $e->getMessage()
                ));
            } finally {
                if (method_exists($this->environment, 'end')) {
                    $this->environment->end();
                }
            }
        }

        return 0;
    }
}