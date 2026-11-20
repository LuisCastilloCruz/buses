<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook público global Meta (WhatsApp / Instagram)
|--------------------------------------------------------------------------
| OJO:
| Este archivo ya es cargado con prefix('api') desde RouteServiceProvider,
| por eso aquí NO debes volver a poner prefix('api/...').
|--------------------------------------------------------------------------
*/

Route::prefix('tattoo-inbox/meta')
    ->group(function () {
        Route::get('/webhook', 'MetaWebhookController@verify');
        Route::post('/webhook', 'MetaWebhookController@receive');
    });

/*
|--------------------------------------------------------------------------
| API protegida del módulo dentro del tenant/subdominio
|--------------------------------------------------------------------------
*/

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
    Route::domain($hostname->fqdn)
        ->prefix('tattoo-inbox')
        ->middleware(['auth:api', 'locked.tenant'])
        ->group(function () {
            Route::get('/leads', 'TattooInboxLeadController@index');
            Route::get('/leads/{id}', 'TattooInboxLeadController@show');

            Route::get('/conversations', 'TattooInboxConversationController@index');
            Route::get('/conversations/{id}', 'TattooInboxConversationController@show');

            Route::get('/quote-rules', 'TattooInboxQuoteRuleController@index');
            Route::post('/quote-rules', 'TattooInboxQuoteRuleController@store');

            Route::post('/conversations/{id}/send-manual-message', 'TattooInboxConversationController@sendManualMessage');
            Route::post('/conversations/{id}/recalculate-quote', 'TattooInboxConversationController@recalculateQuote');
        });
}