<?php


$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
	Route::domain($hostname->fqdn)->group(function () {
		Route::middleware(['auth', 'redirect.module', 'locked.tenant'])
			->prefix('transportes')
			->group(function () {
			    //Bus
                Route::get('sales', 'TransporteSalesController@index');
				Route::get('sales/get-ciudades', 'TransporteSalesController@getCiudades');
				Route::post('sales/programaciones-disponibles', 'TransporteSalesController@getProgramacionesDisponibles');
				Route::post('sales/realizar-venta-boleto', 'TransporteSalesController@realizarVenta');
				// Vehiculos
				Route::get('vehiculos', 'TransporteVehiculoController@index');
				Route::post('vehiculos/store', 'TransporteVehiculoController@store');
				Route::put('vehiculos/{id}/update', 'TransporteVehiculoController@update');
				Route::delete('vehiculos/{id}/delete', 'TransporteVehiculoController@destroy');
				Route::put('vehiculos/{vehiculo}/guardar-asientos','TransporteVehiculoController@guardarAsientos');
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
				Route::post('encomiendas/store', 'TransporteEncomiendaController@store');
				Route::get('encomiendas/get-clientes','TransporteEncomiendaController@getClientes');
				Route::get('encomiendas/get-terminales','TransporteEncomiendaController@getTerminales');
				Route::get('encomiendas/{terminal}/get-destinos','TransporteEncomiendaController@getDestinos');
				Route::post('encomiendas/programaciones-disponibles','TransporteEncomiendaController@getProgramacionesDisponibles');
				Route::put('encomiendas/{encomienda}/update','TransporteEncomiendaController@update');
				//terminales
				Route::get('terminales','TransporteTerminalesController@index');
				Route::post('terminales/store','TransporteTerminalesController@store');
				Route::put('terminales/{terminal}/update','TransporteTerminalesController@update');
				Route::delete('terminales/{terminal}/delete','TransporteTerminalesController@destroy');
                // Pasajes
                Route::get('pasajes', 'TransportePasajeController@index');
				//programaciones
				Route::get('programaciones','TransporteProgramacionesController@index');
				Route::post('programaciones/store','TransporteProgramacionesController@store');
				Route::put('programaciones/{programacion}/update','TransporteProgramacionesController@update');
				Route::put('programaciones/{programacion}/configuracion-rutas','TransporteProgramacionesController@configuracionRutas');
				Route::delete('programaciones/{programacion}/delete','TransporteProgramacionesController@destroy');


				//Manifiestos
				Route::get('manifiestos/','TransporteManifiestosController@index');
				Route::post('manifiestos/get-programaciones','TransporteManifiestosController@getProgramaciones');
				Route::post('manifiestos/guardar-manifiesto','TransporteManifiestosController@store');
				Route::get('manifiestos/{manifiesto}/imprimir-manifiesto','TransporteManifiestosController@imprimirManifiesto');

			});
	});
}
