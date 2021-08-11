<?php


$hostname = app(Hyn\Tenancy\Contracts\CurrentHostname::class);

if ($hostname) {
	Route::domain($hostname->fqdn)->group(function () {
		Route::middleware(['auth', 'redirect.module', 'locked.tenant'])
			->prefix('transportes')
			->group(function () {
			    //index
                Route::get('/', 'TransporteSalesController@index') ->name('tenant.transportes.index');
			    //Bus
                Route::get('sales/{pasaje?}', 'TransporteSalesController@index');
				Route::get('sales/get-ciudades', 'TransporteSalesController@getCiudades');
				Route::post('sales/programaciones-disponibles', 'TransporteSalesController@getProgramacionesDisponibles');
				Route::post('sales/realizar-venta-boleto', 'TransporteSalesController@realizarVenta');
				Route::put('sales/{pasaje}/actualizar-boleto', 'TransporteSalesController@updateVenta');

				// Vehiculos
				Route::get('vehiculos', 'TransporteVehiculoController@index');
				Route::post('vehiculos/store', 'TransporteVehiculoController@store');
				Route::put('vehiculos/{id}/update', 'TransporteVehiculoController@update');
				Route::delete('vehiculos/{id}/delete', 'TransporteVehiculoController@destroy');
				Route::put('vehiculos/{vehiculo}/guardar-asientos','TransporteVehiculoController@guardarAsientos');
				Route::delete('vehiculos/{asiento}/eliminar','TransporteVehiculoController@eliminarAsiento');
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
				Route::get('encomiendas/get-encomiendas', 'TransporteEncomiendaController@getEncomiendas');
				Route::get('encomiendas/get-encomiendas-notes', 'TransporteEncomiendaController@getEncomiendasNotes');
				Route::post('encomiendas/store', 'TransporteEncomiendaController@store');
				Route::get('encomiendas/get-clientes','TransporteEncomiendaController@getClientes');
				Route::get('encomiendas/get-terminales','TransporteEncomiendaController@getTerminales');
				Route::get('encomiendas/{terminal}/get-destinos','TransporteEncomiendaController@getDestinos');
				Route::post('encomiendas/programaciones-disponibles','TransporteEncomiendaController@getProgramacionesDisponibles');
				Route::put('encomiendas/{encomienda}/update','TransporteEncomiendaController@update');
				Route::delete('encomiendas/{encomienda}/delete','TransporteEncomiendaController@destroy');
				Route::get('encomiendas/get-productos','TransporteEncomiendaController@getProductos');

				//terminales
				Route::get('terminales','TransporteTerminalesController@index');
				Route::post('terminales/store','TransporteTerminalesController@store');
				Route::put('terminales/{terminal}/update','TransporteTerminalesController@update');
				Route::delete('terminales/{terminal}/delete','TransporteTerminalesController@destroy');
                // Pasajes
                Route::get('pasajes', 'TransportePasajeController@index');
				Route::delete('pasajes/{pasaje}/delete', 'TransportePasajeController@destroy');
				Route::get('pasajes/{terminal}/get-destinos','TransportePasajeController@getDestinos');
				Route::get('pasajes/get-pasajes','TransportePasajeController@getPasajes');
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
				Route::put('manifiestos/{manifiesto}/actualizar-manifiesto','TransporteManifiestosController@update');
				Route::get('manifiestos/{manifiesto}/imprimir-manifiesto','TransporteManifiestosController@imprimirManifiesto');
				Route::get('manifiestos/{manifiesto}/asignar-encomiendas','TransporteManifiestosController@indexManifiestoEncomiendas');
				Route::post('manifiestos/get-encomiendas','TransporteManifiestosController@getEncomiendas');
				Route::post('manifiestos/get-encomiendas-sin-asignar','TransporteManifiestosController@getEncomiendasSinAsignar');
				Route::post('manifiestos/asignacion-encomienda','TransporteManifiestosController@asignarEncomienda');
				Route::post('manifiestos/desasignar-encomienda','TransporteManifiestosController@desasignarEncomienda');
				Route::get('manifiestos/get-manifiestos','TransporteManifiestosController@getManifiestos');

				//usuarios terminales
				Route::get('usuarios-terminales','TransporteUsersTerminalController@index');
				Route::get('usuarios-terminales/get-tables','TransporteUsersTerminalController@getTables');
				Route::post('usuarios-terminales/store','TransporteUsersTerminalController@store');
				Route::put('usuarios-terminales/{usuario_terminal}/update','TransporteUsersTerminalController@update');
				Route::delete('usuarios-terminales/{id}/delete','TransporteUsersTerminalController@destroy');
			});
	});
}
