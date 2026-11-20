<?php

namespace Modules\TattooInbox\Models;

use App\Models\Tenant\ModelTenant;

class TattooInboxSetting extends ModelTenant
{
    protected $table = 'tattoo_inbox_settings';

    protected $fillable = [
        'studio_name',
        'artist_name',
        'contact_phone',
        'contact_email',
        'district',
        'address',
        'bot_enabled',
        'auto_quote_enabled',
        'human_takeover_enabled',
        'openai_enabled',
        'openai_vision_enabled',
        'openai_api_key',
        'openai_model',
        'openai_vision_detail',
        'assistant_prompt',
        'assistant_tone',
        'booking_instructions',
        'aftercare_instructions',
        'meta_verify_token',
        'meta_graph_version',
        'meta_page_access_token',
        'whatsapp_access_token',
        'whatsapp_phone_number_id',
        'instagram_page_id',
        'currency',
        'meta',
    ];

    protected $casts = [
        'bot_enabled' => 'boolean',
        'auto_quote_enabled' => 'boolean',
        'human_takeover_enabled' => 'boolean',
        'openai_enabled' => 'boolean',
        'openai_vision_enabled' => 'boolean',
        'meta' => 'array',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'studio_name' => null,
                'artist_name' => null,
                'bot_enabled' => true,
                'auto_quote_enabled' => true,
                'human_takeover_enabled' => true,
                'openai_enabled' => (bool) config('tattooinbox.ai.enabled'),
                'openai_vision_enabled' => true,
                'openai_model' => config('tattooinbox.ai.model', 'gpt-4.1-mini'),
                'openai_vision_detail' => 'auto',
                'meta_verify_token' => config('tattooinbox.meta.verify_token'),
                'meta_graph_version' => config('tattooinbox.meta.graph_version', 'v22.0'),
                'currency' => 'PEN',
            ]
        );
    }
}
