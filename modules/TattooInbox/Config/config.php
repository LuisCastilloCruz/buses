<?php

return [
    'name' => 'TattooInbox',
    'queue' => env('TATTOO_INBOX_QUEUE', 'default'),

    'meta' => [
        'verify_token' => env('META_VERIFY_TOKEN', 'tattoo_inbox_verify_token'),
        'app_secret' => env('META_APP_SECRET'),
        'require_signature' => env('META_REQUIRE_SIGNATURE', false),
        'graph_version' => env('META_GRAPH_VERSION', 'v22.0'),
        'page_access_token' => env('META_PAGE_ACCESS_TOKEN'),
        'whatsapp_access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'whatsapp_phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'instagram_page_id' => env('INSTAGRAM_PAGE_ID'),
    ],

    'ai' => [
        'provider' => env('TATTOO_AI_PROVIDER', 'openai'),
        'api_key' => env('TATTOO_AI_API_KEY'),
        'model' => env('TATTOO_AI_MODEL', 'gpt-4.1-mini'),
        'enabled' => env('TATTOO_AI_ENABLED', false),
    ],


    'quote_defaults' => [
        'minimum_base_price' => 120,
        'small_max_cm' => 5,
        'small_min_price' => 120,
        'small_max_price' => 180,

        'medium_max_cm' => 10,
        'medium_min_price' => 180,
        'medium_max_price' => 320,

        'large_max_cm' => 20,
        'large_min_price' => 320,
        'large_max_price' => 650,

        'xlarge_min_price' => 650,
        'xlarge_max_price' => 1200,

        'color_multiplier' => 1.25,
        'realism_multiplier' => 1.35,
        'coverup_multiplier' => 1.30,
        'difficult_zone_multiplier' => 1.15,
        'high_detail_multiplier' => 1.20,
    ],
];
