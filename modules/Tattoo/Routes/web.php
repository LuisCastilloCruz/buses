<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])
    ->namespace('Modules\Tattoo\Http\Controllers')
    ->group(function () {

        Route::get('tattoo-dashboard', 'TattooDashboardController@index')
            ->name('tenant.tattoo-dashboard.index');

        Route::prefix('tattoo-artists')->group(function () {
            Route::get('', 'TattooArtistController@index')->name('tenant.tattoo-artists.index');
            Route::get('/records', 'TattooArtistController@records');
            Route::get('/record/{id}', 'TattooArtistController@record');
            Route::post('', 'TattooArtistController@store');
            Route::get('/columns', 'TattooArtistController@columns');
            Route::delete('/{id}', 'TattooArtistController@destroy');

            Route::post('/portfolio', 'TattooArtistPortfolioController@store')
                ->name('tenant.tattoo-artists.portfolio.store');

            Route::get('/portfolio/record/{id}', 'TattooArtistPortfolioController@record')
                ->name('tenant.tattoo-artists.portfolio.record');

            Route::delete('/portfolio/{id}', 'TattooArtistPortfolioController@destroy')
                ->name('tenant.tattoo-artists.portfolio.destroy');
        });

        Route::prefix('tattoo-styles')->group(function () {
            Route::get('', 'TattooStyleController@index')->name('tenant.tattoo-styles.index');
            Route::get('/records', 'TattooStyleController@records');
            Route::get('/record/{id}', 'TattooStyleController@record');
            Route::post('', 'TattooStyleController@store');
            Route::get('/columns', 'TattooStyleController@columns');
            Route::delete('/{id}', 'TattooStyleController@destroy');
        });

        Route::prefix('tattoo-appointments')->group(function () {
            Route::get('', 'TattooAppointmentController@index')->name('tenant.tattoo-appointments.index');
            Route::get('/records', 'TattooAppointmentController@records');
            Route::get('/record/{id}', 'TattooAppointmentController@record');
            Route::post('', 'TattooAppointmentController@store');
            Route::get('/columns', 'TattooAppointmentController@columns');
            Route::delete('/{id}', 'TattooAppointmentController@destroy');
        });

        Route::prefix('tattoo-sessions')->group(function () {
            Route::get('', 'TattooSessionController@index')->name('tenant.tattoo-sessions.index');
            Route::get('/records', 'TattooSessionController@records');
            Route::get('/record/{id}', 'TattooSessionController@record');
            Route::post('', 'TattooSessionController@store');
            Route::get('/columns', 'TattooSessionController@columns');
            Route::delete('/{id}', 'TattooSessionController@destroy');
        });

        Route::prefix('tattoo-consents')->group(function () {
            Route::get('', 'TattooConsentController@index')->name('tenant.tattoo-consents.index');
            Route::get('/records', 'TattooConsentController@records');
            Route::get('/record/{id}', 'TattooConsentController@record');
            Route::post('', 'TattooConsentController@store');
            Route::get('/columns', 'TattooConsentController@columns');
            Route::delete('/{id}', 'TattooConsentController@destroy');
        });
    });