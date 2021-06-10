<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
use Modules\Transporte\Models\TransporteUserTerminal;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Series;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Traits\FinanceTrait;

class TransporteSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    use FinanceTrait;
    public function index()
    {
        $user_terminal = TransporteUserTerminal::where('user_id',auth()->user()->id)->first();

        if(is_null($user_terminal)){
            //redirigirlo
            Session::flash('message','No se pudó acceder. No tiene una terminal asignada');
            return redirect()->back();
        }

        $terminal = $user_terminal->terminal;


        $programaciones = TransporteProgramacion::with('origen','destino')
        ->where('terminal_origen_id',$terminal->id)
        ->get();

        $estadosAsientos = TransporteEstadoAsiento::where('id','!=',1)
        ->get();

        $establishment =  Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::first();

        return view('transporte::bus.Sales',compact(
            'programaciones',
            'terminal',
            'estadosAsientos',
            'series',
            'establishment',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'configuration'
        ));
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

        $programaciones = TransporteProgramacion::with('origen','destino')
        ->where('terminal_origen_id',$request->origen_id)
        ->where('terminal_destino_id',$request->destino_id)
        ->WhereEqualsOrBiggerDate($request->fecha_salida);
        $date = Carbon::parse($request->fecha_salida);
        $today = Carbon::now();

        /* váliddo si es el mismo dia  */
        if($date->isSameDay($today)){
            /* Si es el mismo traigo las programaciones que aun no hayan cumplido la hora */
            $time = date('h:i:s');
            $programaciones->whereTime('hora_salida','>=',$time);
        }



        $listProgramaciones = $programaciones->get();
        foreach($listProgramaciones as $programacion){

            $programacion->transporte = TransporteVehiculo::find($programacion->vehiculo_id);

            $listSeats = TransporteAsiento::where('vehiculo_id',$programacion->vehiculo_id)->get();

            // $tempProgramaciones = TransporteProgramacion::all();

            foreach($listSeats as $seat){

                // foreach($tempProgramaciones as $tempProgramacion){
                //     $hours = $this->convertToSeconds($tempProgramacion->tiempo_aproximado) / 3600; //convierto a horas
                //     $fechaSalida = "{$request->fecha_salida} {$tempProgramacion->hora_salida}";
                //     $horaAproximada = Carbon::parse($fechaSalida)->addHours($hours);

                //     $isState = TransportePasaje::whereRaw(DB::raw('DATE(fecha_salida) = ?'))
                //     ->whereRaw(DB::raw('TIME(fecha_salida) >= ?'))
                //     ->whereRaw(DB::raw('TIME(fecha_llegada) < ?'))
                //     ->whereRaw('asiento_id = ?')
                //     ->setBindings([
                //         $request->fecha_salida,
                //         $programacion->hora_salida,
                //         $horaAproximada->format('H:i:s'),
                //         $seat->id
                //     ])
                //     ->first();
                //     if($isState){
                //         $seat->estado_asiento_id = $isState->estado_asiento_id;
                //         break;
                //     }else {
                //         $seat->estado_asiento_id = 1;
                //     }
                // }

                $isState = TransportePasaje::with('pasajero')
                ->whereDate('fecha_salida',$request->fecha_salida)
                ->where('asiento_id',$seat->id)
                ->where('programacion_id',$programacion->id)
                ->first();

                if(!is_null($isState)){
                    $seat->estado_asiento_id = $isState->estado_asiento_id;
                    $seat->transporte_pasaje = $isState;
                }else {
                    $seat->estado_asiento_id = 1;
                    $seat->pasajero = null;
                }
            }
            $programacion->transporte->asientos = $listSeats;
        }

        return response()->json( [
            'programaciones' => $listProgramaciones
        ]);

    }

    public function realizarVenta(RealizarVentaRequest $request){

        DB::connection('tenant')->beginTransaction();
        try {


            // $asiento = TransporteAsiento::find($request->asiento_id);
            $programacion = TransporteProgramacion::find($request->programacion_id);

            // $hours = $this->convertToSeconds($programacion->tiempo_aproximado) / 3600; //convierto a horas

            $fechaSalida = "{$request->fecha_salida} {$programacion->hora_salida}";

            // $fechaLLegada = Carbon::parse($fechaSalida)->addHours($hours)
            // ->subMinute();

            $attributes = $request->only([
                'document_id',
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
                    // 'fecha_llegada' => $fechaLLegada
                ])
            );

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Éxito!!'
            ],200);


        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar su petición',
                'error' => $th->getMessage()
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
