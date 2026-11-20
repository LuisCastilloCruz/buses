<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->group(function () {

        Route::get('tattoo-inbox', 'TattooInboxController@index')
            ->name('tenant.tattoo-inbox.index');

        Route::prefix('tattoo-inbox')->group(function () {
            Route::get('/conversations', 'TattooInboxController@conversations')
                ->name('tenant.tattoo-inbox.conversations');

            Route::get('/conversation/{id}', 'TattooInboxController@conversation')
                ->name('tenant.tattoo-inbox.conversation');

            Route::post('/conversation/{id}/send', 'TattooInboxController@sendMessage')
                ->name('tenant.tattoo-inbox.send');

            Route::post('/conversation/{id}/recalculate-quote', 'TattooInboxController@recalculateQuote')
                ->name('tenant.tattoo-inbox.recalculate');

            Route::post('/conversation/{id}/takeover', 'TattooInboxController@takeOver')
                ->name('tenant.tattoo-inbox.takeover');

            Route::post('/conversation/{id}/reactivate-bot', 'TattooInboxController@reactivateBot')
                ->name('tenant.tattoo-inbox.reactivate-bot');

            Route::post('/conversation/{id}/stage', 'TattooInboxController@updateStage')
                ->name('tenant.tattoo-inbox.stage');
        });

        Route::prefix('tattoo-inbox-settings')->group(function () {
            Route::get('/', 'TattooInboxSettingsController@index')
                ->name('tenant.tattoo-inbox.settings');

            Route::post('/update', 'TattooInboxSettingsController@update')
                ->name('tenant.tattoo-inbox.settings.update');

            Route::post('/rules', 'TattooInboxSettingsController@storeRule')
                ->name('tenant.tattoo-inbox.settings.rules.store');

            Route::post('/rules/{id}', 'TattooInboxSettingsController@updateRule')
                ->name('tenant.tattoo-inbox.settings.rules.update');

            Route::delete('/rules/{id}', 'TattooInboxSettingsController@destroyRule')
                ->name('tenant.tattoo-inbox.settings.rules.destroy');
        });
    });