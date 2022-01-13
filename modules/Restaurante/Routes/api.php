<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|


Route::middleware('auth:api')->get('/restaurante', function (Request $request) {
    return $request->user();
});*/




Route::middleware(['auth:api', 'locked.tenant'])->prefix('restaurante')->group(function () {
    Route::get('/', 'Api\ApiController@index') ->name('tenant.restaurante.index');

    //NIVELES
    Route::get('niveles', 'Api\NivelesController@records') ->name('tenant.restaurante.niveles.records');

    //MESAS
    Route::get('{nivel}/mesas', 'Api\MesasController@records') ->name('tenant.restaurante.mesas.records');
    
});