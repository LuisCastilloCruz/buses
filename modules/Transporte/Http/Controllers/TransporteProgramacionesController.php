<?php

namespace Modules\Transporte\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Transporte\Http\Requests\TransporteProgramacionesRequest;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteTerminales;
use Modules\Transporte\Models\TransporteVehiculo;

class TransporteProgramacionesController extends Controller
{
    //

    public function index(){
        $terminales = TransporteTerminales::all();

        $programaciones = TransporteProgramacion::with('rutas','vehiculo','origen','destino')
        ->get()
        ->map(function($programacion){
            $programacion->hora_view = date('g:i a',strtotime($programacion->hora_salida));
            return $programacion;
        });
        $vehiculos = TransporteVehiculo::all();
        return view('transporte::programaciones.index',compact(
            'terminales',
            'programaciones',
            'vehiculos'
        ));
    }

    public function store(TransporteProgramacionesRequest $request){

        // return $request->only('terminal_destino_id');

        $programacion = TransporteProgramacion::create($request->only(
            'terminal_destino_id',
            'terminal_origen_id',
            'vehiculo_id',
            'hora_salida',
            // 'tiempo_aproximado'
        ));

        $programacion->destino;
        $programacion->origen;
        $programacion->vehiculo;
        $programacion->rutas;
        $programacion->hora_view = date('g:i a',strtotime($programacion->hora_salida));

        return response()->json([
            'success' => true,
            'data'    => $programacion
        ]);

    }

    public function update(TransporteProgramacionesRequest $request,TransporteProgramacion $programacion){

        // return $request->only('terminal_destino_id');
        $programacion->update($request->only([
            'terminal_destino_id',
            'terminal_origen_id',
            'vehiculo_id',
            'hora_salida',
            // 'tiempo_aproximado'
        ]));
        $programacion->destino;
        $programacion->origen;
        $programacion->vehiculo;
        $programacion->rutas;
        $programacion->hora_view = date('g:i a',strtotime($programacion->hora_salida));
        return response()->json([
            'success' => true,
            'data'    => $programacion
        ]);
    }

    public function destroy(TransporteProgramacion $programacion){
        try {

            if(count($programacion->encomiendas) > 0){
                throw new Exception('Lo sentimos no se puede eliminar la programación, tiene encomiendas',888);
            }

            if(count($programacion->pasajes) > 0){
                throw new Exception('Lo sentimos no se puede eliminar la programación, tiene pasajes',888);
            }

            if(count($programacion->manifiestos) > 0){
                throw new Exception('Lo sentimos no se puede eliminar la programación, tiene manifiestos',888);
            }
            
            $programacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Información actualizada'
            ],200);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getCode() == 888 ? $th->getMessage() : 'Ocurrió un error al procesar su petición'
            ],500);
            
        }
    }


    public function configuracionRutas(Request $request,TransporteProgramacion $programacion){

        $rutas = (array) $request->rutas;
        $programacion->syncRutas($rutas);
        $programacion->origen;
        $programacion->destino;
        $programacion->vehiculo;
        $programacion->rutas; //cargo de nuevo las rutas

        return response()->json([
            'success' => true,
            'data'    => $programacion
        ]);

    }
}
