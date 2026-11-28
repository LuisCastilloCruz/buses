<?php

// Modulo desactivado por defecto. No habilitar en produccion sin validar
// aislamiento de tenants, cuotas, proteccion de datos y flujos de reservas.
return [
    'native_enabled' => env('AIRECEPTION_NATIVE_ENABLED', false),
    'name' => 'AiReception',
    'v5_transport_mode' => 'simulated',
    'gateway_authorized' => false,
    'ollama_enabled' => false,
    'channel_processing_enabled' => false,
    'channel_actors' => [],
    'gateway_channels' => [],
    'gateway' => [],
    'audio' => [],
    'reminder_templates' => [],
    'enabled' => env('AI_RECEPTION_ENABLED', false),
    'external_connections_enabled' => env('AI_RECEPTION_EXTERNAL_CONNECTIONS_ENABLED', false),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('AI_RECEPTION_MODEL', 'gpt-4.1-mini'),
    'timeout_seconds' => 15,
];
