<?php

use Illuminate\Support\Facades\Route;
Route::middleware(['web', 'auth', 'locked.tenant'])->group(function () {
    Route::post('aireception/api/proposals', '\Modules\AiReception\Native\ProposalController@prepare')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,book');
    Route::post('aireception/api/proposals/confirm', '\Modules\AiReception\Native\ProposalController@confirm')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,book');
    Route::get('aireception/automations', '\Modules\AiReception\Native\AutomationController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/automations/policy','\Modules\AiReception\Native\AutomationController@policy')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/automations/consent', '\Modules\AiReception\Native\AutomationController@consent')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/automations/{id}/process', '\Modules\AiReception\Native\AutomationController@process')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::get('aireception/inbox','\Modules\AiReception\Native\InboxController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,conversations');
    Route::post('aireception/inbox/{id}/link','\Modules\AiReception\Native\InboxController@link')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::get('aireception/channels','\Modules\AiReception\Native\InboxController@channels')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/channels','\Modules\AiReception\Native\InboxController@saveChannel')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/conversations/{id}/{action}','\Modules\AiReception\Native\ConversationController@action')->where('action','resume|assign|reply')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,conversations');
    Route::get('aireception/overview','\Modules\AiReception\Native\AgentController@overview')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::get('aireception/agent-settings','\Modules\AiReception\Native\AgentController@index')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
    Route::post('aireception/agent-settings','\Modules\AiReception\Native\AgentController@save')->middleware(\Modules\AiReception\Native\ModuleMiddleware::class.':aireception,configure');
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
