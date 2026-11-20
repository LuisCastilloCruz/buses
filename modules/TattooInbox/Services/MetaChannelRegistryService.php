<?php

namespace Modules\TattooInbox\Services;

use Illuminate\Support\Facades\Log;
use Modules\TattooInbox\Models\System\TattooInboxMetaChannel;
use Modules\TattooInbox\Models\TattooInboxSetting;

class MetaChannelRegistryService
{
    /**
     * Sincroniza los identificadores Meta del tenant actual hacia la tabla maestra central.
     */
    public function syncCurrentTenantChannels(
        int $websiteId,
        ?int $hostnameId = null,
        ?string $fqdn = null
    ): array {
        $settings = TattooInboxSetting::current();
        $synced = [];

        if (!empty($settings->whatsapp_phone_number_id)) {
            if (config('aireception.native_enabled', false)) app(\Modules\AiReception\Native\ChannelOwnership::class)->claim((string)$settings->whatsapp_phone_number_id, $websiteId, 'tattoo_inbox');
            $record = TattooInboxMetaChannel::query()->updateOrCreate(
                [
                    'platform' => 'whatsapp',
                    'meta_identifier_type' => 'whatsapp_phone_number_id',
                    'meta_identifier_value' => (string) $settings->whatsapp_phone_number_id,
                ],
                [
                    'tenant_website_id' => $websiteId,
                    'tenant_hostname_id' => $hostnameId,
                    'tenant_fqdn' => $fqdn,
                    'label' => $settings->studio_name ?: $settings->artist_name ?: 'WhatsApp Tattoo Inbox',
                    'is_active' => true,
                    'meta' => [
                        'studio_name' => $settings->studio_name,
                        'artist_name' => $settings->artist_name,
                        'display_phone' => $settings->contact_phone,
                    ],
                ]
            );

            $synced[] = [
                'platform' => 'whatsapp',
                'identifier' => (string) $settings->whatsapp_phone_number_id,
                'mapping_id' => $record->id,
            ];
        }

        if (!empty($settings->instagram_page_id)) {
            $record = TattooInboxMetaChannel::query()->updateOrCreate(
                [
                    'platform' => 'instagram',
                    'meta_identifier_type' => 'instagram_page_id',
                    'meta_identifier_value' => (string) $settings->instagram_page_id,
                ],
                [
                    'tenant_website_id' => $websiteId,
                    'tenant_hostname_id' => $hostnameId,
                    'tenant_fqdn' => $fqdn,
                    'label' => $settings->studio_name ?: $settings->artist_name ?: 'Instagram Tattoo Inbox',
                    'is_active' => true,
                    'meta' => [
                        'studio_name' => $settings->studio_name,
                        'artist_name' => $settings->artist_name,
                    ],
                ]
            );

            $synced[] = [
                'platform' => 'instagram',
                'identifier' => (string) $settings->instagram_page_id,
                'mapping_id' => $record->id,
            ];
        }

        Log::info('[TATTOOINBOX][CHANNEL_REGISTRY] syncCurrentTenantChannels', [
            'website_id' => $websiteId,
            'hostname_id' => $hostnameId,
            'fqdn' => $fqdn,
            'synced' => $synced,
        ]);

        return $synced;
    }
}