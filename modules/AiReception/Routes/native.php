<?php

use Illuminate\Support\Facades\Route;
Route::middleware(['web', 'auth', 'locked.tenant'])->group(function () {
    Route::post('aireception/api/proposals', '\Modules\AiReception\Native\ProposalController@prepare')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,book');
    Route::post('aireception/api/proposals/confirm', '\Modules\AiReception\Native\ProposalController@confirm')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,book');
    Route::get('aireception/automations', '\Modules\AiReception\Native\AutomationController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/automations/consent', '\Modules\AiReception\Native\AutomationController@consent')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/automations/{id}/process', '\Modules\AiReception\Native\AutomationController@process')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    foreach (['aireception'] as $module) {
        require __DIR__ . '/shared.php';
    }
});

Route::middleware(['web','auth:admin'])->group(function(){
    Route::get('aireception-management','\Modules\AiReception\Native\SystemController@index');
    Route::post('aireception-management/{kind}','\Modules\AiReception\Native\SystemController@save');
});

Route::get('api/aireception/meta/{id}','\Modules\AiReception\Native\WebhookController@verify');
Route::post('api/aireception/meta/{id}','\Modules\AiReception\Native\WebhookController@receive');
