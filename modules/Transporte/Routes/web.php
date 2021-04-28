<?php

use Illuminate\Support\Facades\Route;

$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
	Route::domain($hostname->fqdn)->group(function () {
		Route::middleware(['auth', 'redirect.module', 'locked.tenant'])
			->prefix('transportes')
			->group(function () {
			    //Bus
                Route::get('sales', 'TransporteSalesController@index');
				// Vehiculos
				Route::get('vehiculos', 'TransporteVehiculoController@index');
				Route::post('vehiculos/store', 'TransporteVehiculoController@store');
				Route::put('vehiculos/{id}/update', 'TransporteVehiculoController@update');
				Route::delete('vehiculos/{id}/delete', 'TransporteVehiculoController@destroy');
				// Choferes
				Route::get('choferes', 'TransporteChoferController@index');
				Route::post('choferes/store', 'TransporteChoferController@store');
				Route::put('choferes/{id}/update', 'TransporteChoferController@update');
				Route::delete('choferes/{id}/delete', 'TransporteChoferController@destroy');
				// Destinos
				Route::get('destinos', 'TransporteDestinoController@index');
				Route::post('destinos/store', 'TransporteDestinoController@store');
				Route::put('destinos/{id}/update', 'TransporteDestinoController@update');
				Route::delete('destinos/{id}/delete', 'TransporteDestinoController@destroy');
				Route::post('destinos/{id}/change-status', 'TransporteDestinoController@changeRoomStatus');
                Route::get('destinos/search-districts', 'TransporteDestinoController@searchDistricts');
                Route::get('destinos/get-districts', 'TransporteDestinoController@getDistritos');
                // Encomiendas
                Route::get('encomiendas', 'TransporteEncomiendaController@index');
			});
	});
}
