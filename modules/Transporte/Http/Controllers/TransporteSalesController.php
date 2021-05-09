<?php

namespace Modules\Transporte\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Transporte\Http\Requests\ProgramacionesDisponiblesRequest;
use Modules\Transporte\Http\Requests\RealizarVentaRequest;
use Modules\Transporte\Models\TransporteCategory;
use Modules\Transporte\Http\Requests\TransporteCategoryRequest;
use Modules\Transporte\Models\TransporteAsiento;
use Modules\Transporte\Models\TransporteDestino;
use Modules\Transporte\Models\TransporteEstadoAsiento;
use Modules\Transporte\Models\TransportePasaje;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteVehiculo;

class TransporteSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $plantilla='<table class="mx-auto bus"><tr><td>1</td><td>2</td><td>3</td></tr><tr><td>4</td><td>5</td><td>6</td></tr> <tr><td>7</td><td>8</td><td>9</td></tr></table>';
        //$plantilla="si sale";

        $user = Auth::user();

        $user_terminal = $user->user_terminal;

        if(!is_null($user_terminal)){  }

        $terminal = $user_terminal->terminal;


        $programaciones = TransporteProgramacion::with('origen','destino')
        ->where('terminal_origen_id',$terminal->id)
        ->get();

        $estadosAsientos = TransporteEstadoAsiento::all();

        return view('transporte::bus.Sales',compact('programaciones','terminal','estadosAsientos'));
    }


    public function getCiudades(Request $request){

        try{

            extract($request->only('search'));
            $destinos = TransporteDestino::with(['terminales' => function($terminales){
                $terminales->with('programaciones.origen','programaciones.destino');
            }]);

            if(!empty($search)){
                $destinos->where('nombre','like',"%{$search}%");
            }

            return response()->json([
                'data' => $destinos->get(),
                'success' => true,
            ]);


        }catch(\Throwable $th){
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Lo sentimos ocurrio un error',
                'error' => $th->getMessage()
            ],500);
        }
    }

    public function getProgramacionesDisponibles(ProgramacionesDisponiblesRequest $request){

        $programaciones = TransporteProgramacion::with('vehiculo','origen','destino')
        ->where('terminal_origen_id',$request->origen_id)
        ->where('terminal_destino_id',$request->destino_id)
        ->WhereEqualsOrBiggerDate($request->fecha_salida);
        $date = Carbon::parse($request->fecha_salida);
        $today = Carbon::now();

        /* váliddo si es el mismo dia  */
        if($date->isSameDay($today)){
            /* Si es el mismo traigo las programaciones que aun no hayan cumplido la hora */
            $time = date('h:i:s');
            $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%h:%i:%s') >= '{$time}'");
        }

        // return $programaciones->toSql();

        return response()->json([
            'programaciones' => $programaciones->get()->map(function($programacion) use($request){
                $programacion->vehiculo->seats->map(function($seat)  use($request,$programacion){
                    $seconds = $this->convertToSeconds($programacion->tiempo_aproximado) / 3600; //convierto a horas
                    $fechaSalida = "{$request->fecha_salida} {$programacion->hora_salida}";
                    $horaAproximada = Carbon::parse($fechaSalida)->addHours($seconds);

                    $isState = TransportePasaje::whereDate('fecha_salida',$request->fecha_salida)
                    ->whereTime('fecha_salida','>=',$programacion->hora_salida)
                    ->whereTime('fecha_llegada','<=',$horaAproximada->format('H:i:s'))
                    ->where('asiento_id',$seat->id)
                    ->first();

                    $seat->estado_asiento_id = !is_null($isState) ? $isState->estado_asiento_id : 1;

                    return $seat;
                });

                return $programacion;
            })
        ]);
    }

    public function realizarVenta(RealizarVentaRequest $request){

        DB::connection('tenant')->beginTransaction();
        try {
            
    
            // $asiento = TransporteAsiento::find($request->asiento_id);
            $programacion = TransporteProgramacion::find($request->programacion_id);
    
            $hours = $this->convertToSeconds($programacion->tiempo_aproximado) / 3600; //convierto a horas

            $fechaSalida = "{$request->fecha_salida} {$programacion->hora_salida}";
    
            $fechaLLegada = Carbon::parse($fechaSalida)->addHours($hours);

            $attributes = $request->only([
                'serie',
                // 'document_id',
                'pasajero_id',
                'asiento_id',
                'precio',
                'fecha_salida',
                'programacion_id',
            ]);

            TransportePasaje::create(
                array_merge($attributes,[
                    'estado_asiento_id' => $request->estado_asiento_id,
                    'fecha_salida' => Carbon::parse($fechaSalida)->format('Y-m-d H:i:s'),
                    'fecha_llegada' => $fechaLLegada
                ])
            );
    
            // $asiento->update([
            //     'estado_asiendo_id' => 'estado_asiento_id', 
            //     'fecha_desocupado' => $fecha_desocupado->format('Y-m-d h:m:i'),
            // ]);
    
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Éxito!!'
            ]);


        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Lo sentimos ocurrio un error',
            ],500);
        }

    }


    private function convertToSeconds($time){
        list($hours, $mins, $secs) = explode(':', $time);
        return ($hours * 3600 ) + ($mins * 60 ) + $secs;
    }



    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(TransporteCategoryRequest $request)
    {
        $rate = TransporteCategory::create($request->only('description', 'active'));

        return response()->json([
            'success' => true,
            'data'    => $rate
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(TransporteCategoryRequest $request, $id)
    {
        $rate = TransporteCategory::findOrFail($id);
        $rate->fill($request->only('description', 'active'));
        $rate->save();

        return response()->json([
            'success' => true,
            'data'    => $rate
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            TransporteCategory::where('id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Información actualizada'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data'    => 'Ocurrió un error al procesar su petición. Detalles: ' . $th->getMessage()
            ], 500);
        }
    }


    
}
