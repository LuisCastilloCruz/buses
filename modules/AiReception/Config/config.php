<?php

// Modulo desactivado por defecto. No habilitar en produccion sin validar
// aislamiento de tenants, cuotas, proteccion de datos y flujos de reservas.
return [
    'native_enabled' => env('AIRECEPTION_NATIVE_ENABLED', false),
    'name' => 'AiReception',
    'enabled' => env('AI_RECEPTION_ENABLED', false),
    'external_connections_enabled' => env('AI_RECEPTION_EXTERNAL_CONNECTIONS_ENABLED', false),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('AI_RECEPTION_MODEL', 'gpt-4.1-mini'),
    'timeout_seconds' => 15,
];
