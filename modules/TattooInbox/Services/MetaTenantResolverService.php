<?php

namespace Modules\TattooInbox\Services;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Log;
use Modules\TattooInbox\Models\System\TattooInboxMetaChannel;

class MetaTenantResolverService
{
    /**
     * Resuelve el tenant correcto a partir de un item ya normalizado por el parser.
     *
     * Retorna:
     * - website
     * - hostname
     * - channel
     *
     * @param array $item
     * @return array|null
     */
    public function resolveFromParsedItem(array $item): ?array
    {
        $platform = $item['platform'] ?? null;
        $destinationKey = $item['destination_key'] ?? null;
        $destinationValue = $item['destination_value'] ?? null;

        if (!$platform || !$destinationKey || !$destinationValue) {
            Log::warning('[TATTOOINBOX][TENANT_RESOLVER] Datos incompletos para resolver tenant', [
                'platform' => $platform,
                'destination_key' => $destinationKey,
                'destination_value' => $destinationValue,
                'item' => $item,
            ]);

            return null;
        }

        $channel = TattooInboxMetaChannel::query()
            ->where('platform', $platform)
            ->where('meta_identifier_type', $destinationKey)
            ->where('meta_identifier_value', (string) $destinationValue)
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            Log::warning('[TATTOOINBOX][TENANT_RESOLVER] No existe mapeo central para canal Meta', [
                'platform' => $platform,
                'destination_key' => $destinationKey,
                'destination_value' => $destinationValue,
            ]);

            return null;
        }

        $website = null;
        $hostname = null;

        if (!empty($channel->tenant_website_id)) {
            $website = Website::query()->find($channel->tenant_website_id);
        }

        if (!empty($channel->tenant_hostname_id)) {
            $hostname = Hostname::query()->find($channel->tenant_hostname_id);
        }

        if (!$hostname && !empty($channel->tenant_fqdn)) {
            $hostname = Hostname::query()
                ->where('fqdn', $channel->tenant_fqdn)
                ->first();
        }

        if (!$website && $hostname && !empty($hostname->website_id)) {
            $website = Website::query()->find($hostname->website_id);
        }

        if (!$hostname && $website) {
            $hostname = Hostname::query()
                ->where('website_id', $website->id)
                ->orderBy('id')
                ->first();
        }

        if (!$website) {
            Log::error('[TATTOOINBOX][TENANT_RESOLVER] Canal encontrado pero website no resoluble', [
                'mapping_id' => $channel->id,
                'tenant_website_id' => $channel->tenant_website_id,
                'tenant_hostname_id' => $channel->tenant_hostname_id,
                'tenant_fqdn' => $channel->tenant_fqdn,
            ]);

            return null;
        }

        Log::info('[TATTOOINBOX][TENANT_RESOLVER] Tenant resuelto correctamente', [
            'mapping_id' => $channel->id,
            'platform' => $platform,
            'destination_key' => $destinationKey,
            'destination_value' => $destinationValue,
            'website_id' => $website->id ?? null,
            'hostname_id' => $hostname->id ?? null,
            'hostname' => $hostname->fqdn ?? null,
        ]);

        return [
            'website' => $website,
            'hostname' => $hostname,
            'channel' => $channel,
        ];
    }
}