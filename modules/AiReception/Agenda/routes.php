<?php

use Illuminate\Support\Facades\Route;
use Modules\AiReception\Agenda\Http\Controller;
Route::middleware('web')->group(function () {
    Route::get('/login', Controller::class . '@loginForm');
    Route::post('/login', Controller::class . '@login');
    Route::post('/logout', Controller::class . '@logout');
    Route::get('/', Controller::class . '@page');
    Route::get('/calendar', Controller::class . '@page');
    Route::get('/agent', Controller::class . '@page');
    Route::get('/settings', Controller::class . '@page');
    Route::get('/api/bootstrap', Controller::class . '@bootstrap');
    Route::post('/api/business', Controller::class . '@switchBusiness');
    Route::get('/api/calendar', Controller::class . '@calendar');
    Route::get('/api/availability', Controller::class . '@availability');
    Route::get('/api/bookings/{id}/availability', Controller::class . '@rescheduling');
    Route::get('/api/bookings/{id}/events', Controller::class . '@events');
    Route::post('/api/commands/{action}', Controller::class . '@command');
    Route::post('/api/configure/{kind}', Controller::class . '@configure');
    Route::post('/api/agent', Controller::class . '@agent');
});
