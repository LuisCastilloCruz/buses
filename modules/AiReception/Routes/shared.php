<?php

$controller = '\\Modules\\AiReception\\Native\\Controller@';
$gate = \Modules\AiReception\Native\ModuleMiddleware::class . ':' . $module . ',';
foreach (['calendar', 'professionals', 'services', 'settings'] as $page) {
    \Illuminate\Support\Facades\Route::get($module . '/' . $page, $controller . 'page')->middleware($gate . 'calendar')->name('tenant.' . $module . '.' . $page);
}
\Illuminate\Support\Facades\Route::get($module . '/api/bootstrap', $controller . 'bootstrap')->middleware($gate . 'calendar');
\Illuminate\Support\Facades\Route::get($module . '/api/calendar', $controller . 'calendar')->middleware($gate . 'calendar');
\Illuminate\Support\Facades\Route::get($module . '/api/availability', $controller . 'availability')->middleware($gate . 'calendar');
\Illuminate\Support\Facades\Route::get($module . '/api/bookings/{id}/availability', $controller . 'rescheduling')->middleware($gate . 'book');
\Illuminate\Support\Facades\Route::get($module . '/api/bookings/{id}/events', $controller . 'events')->middleware($gate . 'calendar');
\Illuminate\Support\Facades\Route::post($module . '/api/commands/{action}', $controller . 'command')->middleware($gate . 'book');
\Illuminate\Support\Facades\Route::post($module . '/api/configure/{kind}', $controller . 'configure')->middleware($gate . ($module === 'odontologia' ? 'manage' : 'configure'));
if ($module === 'aireception') {
    \Illuminate\Support\Facades\Route::get('aireception/agent', $controller . 'page')->middleware($gate . 'conversations');
    \Illuminate\Support\Facades\Route::post('aireception/api/agent', $controller . 'agent')->middleware($gate . 'conversations');
    \Illuminate\Support\Facades\Route::get('aireception/conversations', $controller . 'conversations')->middleware($gate . 'conversations')->name('tenant.aireception.conversations');
    \Illuminate\Support\Facades\Route::post('aireception/conversations', $controller . 'message')->middleware($gate . 'conversations');
    \Illuminate\Support\Facades\Route::post('aireception/conversations/{id}/handoff', $controller . 'handoff')->middleware($gate . 'conversations');
}
