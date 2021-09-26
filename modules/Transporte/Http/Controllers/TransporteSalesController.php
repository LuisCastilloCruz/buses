<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\Tenant\Cash;
use App\Models\Tenant\Company;
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
use Modules\Transporte\Models\TransporteRuta;

class TransporteSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    use FinanceTrait;
    public function index(Request $request, TransportePasaje $pasaje = null)
    {

        $user = $request->user();
        $terminal = $request->user()->terminal;

        $isCashOpen =  !is_null(Cash::where([['user_id',$user->id],['state',true]])->first());
        if(is_null($terminal)){
            //redirigirlo
            Session::flash('message','No se pudó acceder. No tiene una terminal asignada');
            return redirect()->back();
        }

        if(!is_null($pasaje)){
            $pasaje->load([
                'document',
                'pasajero',
                'asiento',
                'programacion' => function($programacion){
                    $programacion->with('origen:id,nombre','destino:id,nombre');
                }
            ]);
        }

        $estadosAsientos = TransporteEstadoAsiento::where('id','!=',1)
        ->get();

        $document_type_03_filter = config('tenant.document_type_03_filter');

        $establishment =  Establishment::where('id', $user->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::first();

        return view('transporte::bus.Sales',compact(
            'document_type_03_filter',
            'pasaje',
            'terminal',
            'estadosAsientos',
            'series',
            'establishment',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'configuration',
            'isCashOpen'
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
        ->whereHas('destino',function($destino) use($request){
            $destino->where('destino_id',$request->destino_id);
        })
        // ->where('terminal_destino_id',$request->destino_id)
        ->WhereEqualsOrBiggerDate($request->fecha_salida);
        $date = Carbon::parse($request->fecha_salida);
        $today = Carbon::now();

        /* váliddo si es el mismo dia  */
        if($date->isSameDay($today)){
            /* Si es el mismo traigo las programaciones que aun no hayan cumplido la hora */
            $time = date('H:i:s',strtotime("-60 minutes")); //doy una hora para que aún esté disponible la programación
            $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%H:%i:%s') >= '{$time}'");
        }

        $listProgramaciones = $programaciones->get();
        foreach($listProgramaciones as $programacion){

            $programacion->transporte = TransporteVehiculo::find($programacion->vehiculo_id);

            $listSeats = TransporteAsiento::where('vehiculo_id',$programacion->vehiculo_id)->get();

            // $tempProgramaciones = TransporteProgramacion::all();

            foreach($listSeats as $seat){

                $isState = TransportePasaje::with('pasajero','asiento','document:id,document_type_id')
                ->whereDate('fecha_salida',$request->fecha_salida)
                ->where('asiento_id',$seat->id)
                ->where('programacion_id',$programacion->id)
                ->where('estado_asiento_id','!=',4) //diferente de cancelado
                ->first();

                if(!is_null($isState)){
                    $seat->estado_asiento_id = $isState->estado_asiento_id;
                    $seat->transporte_pasaje = $isState;
                }else {
                    $seat->estado_asiento_id = 1;
                    $seat->pasajero = null;
                }
            }

            $asientosOcupados = $listSeats->filter(function($asiento){
                return in_array($asiento->estado_asiento_id,[2,3]);
            });


            $asiendosDisponible = $listSeats->filter(function($asiento){
                return $asiento->estado_asiento_id == 1;
            });

            $asientos = $this->getAsientosPasajesEnRuta($programacion,$asiendosDisponible,$request->fecha_salida);


            $listSeats = $asientosOcupados->merge($asientos);

            $programacion->transporte->asientos = $listSeats;
        }

        return response()->json( [
            'programaciones' => $listProgramaciones
        ]);

    }

    private function getAsientosPasajesEnRuta(TransporteProgramacion $programacion,$listSeats,$fecha){

        /** Busco todas las programaciones donde la terminal de destino de la programación seleccionada
         * sean rutas de alguna programación
         */
        $programaciones = TransporteProgramacion::with('destino')->whereHas('rutas',function($rutas) use($programacion){
            $rutas->where('transporte_rutas.terminal_id',$programacion->terminal_destino_id);
        })
        ->where('vehiculo_id',$programacion->vehiculo_id)
        ->where('terminal_origen_id','!=',$programacion->terminal_origen_id)
        // ->where('terminal_destino_id','!=',$programacion->terminal_destino_id)
        ->get();




        //válido si tengo alguna, si no retorno la misma lista si alterar los valores que ya tiene
        if(count($programaciones) <= 0) return $listSeats;

        $disponibles = collect([]); // aqui almacenaré los asientos disponibles
        $ocupados = collect([]); //aqui los ocupados

        foreach($programaciones as $ruta){ // itero las programaciones en las que la terminal destino es una ruta

            /** Obtengo lista temporal de los asientos disponibles */
            $tempList = $listSeats->whereNotIn('id',$ocupados->pluck('id'));
            foreach($tempList as $seat){

                /** Busco si hay algun asiento ocupado con esa programación y esa fecha asi como tambien el asiento */
                $isState = TransportePasaje::with('pasajero','asiento','document:id,document_type_id')
                ->whereDate('fecha_salida',$fecha)
                // ->whereTime('hora_salida',$programacion->hora_salida)
                ->where('asiento_id',$seat->id)
                ->where('programacion_id',$ruta->id)
                ->where('estado_asiento_id','!=',4) //diferente de cancelado
                ->first();

                if(!is_null($isState)){
                    $seat->estado_asiento_id = $isState->estado_asiento_id;
                    $seat->transporte_pasaje = $isState;
                    $ocupados->push($seat); //inserto el asiento en la lista de ocupados

                    /** Si existe en disponibles lo remuevo */
                    $disponibles = $disponibles->filter(function($asiento) use($seat){
                        return $asiento->id != $seat->id;
                    });
                }else {

                    /** Verifico si existe ya en la lista de disponibles
                     * si ya existe continuo con la iteración
                     */
                    $exist = $disponibles->first(function($asiento) use($seat){
                        return $asiento->id == $seat->id;
                    });

                    if(!is_null($exist)) continue;

                    $seat->estado_asiento_id = 1;
                    $seat->pasajero = null;
                    $disponibles->push($seat); //meto el asiento en la lista de disponibles
                }
            }

        }


        /** Hago merge con la lista de ocupados que se encontró y
         * disponibles
         */

        $newList = $disponibles->merge($ocupados);
        return $newList;
    }

    public function realizarVenta(RealizarVentaRequest $request){
        $company = Company::active();
        $soap_type_id = $company->soap_type_id;

        DB::connection('tenant')->beginTransaction();
        try {

            $attributes = $request->only([
                'document_id',
                'note_id',
                'cliente_id',
                'pasajero_id',
                'asiento_id',
                'nombre_pasajero',
                'precio',
                'fecha_salida',
                'programacion_id',
                'estado_asiento_id',
                'tipo_venta',
                'numero_asiento',
                'destino_id',
                'hora_salida'
            ]);

            TransportePasaje::create(
                array_merge($attributes,[
                    'fecha_salida' => Carbon::parse($request->fecha_salida)->format('Y-m-d'),
                    'origen_id' => $request->user()->terminal->id,
                    'soap_type_id'=>$soap_type_id
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


    public function updateVenta(Request $request,TransportePasaje $pasaje){

        DB::connection('tenant')->beginTransaction();
        try {


            $attributes = $request->only([
                'asiento_id',
                'precio',
                'fecha_salida',
                'programacion_id',
                'numero_asiento',
                'hora_salida',
                'origen_id',
                'destino_id'
            ]);

            $pasaje->update(
                array_merge($attributes,[
                    'fecha_salida' => Carbon::parse($request->fecha_salida)->format('Y-m-d'),
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

    public function ventaReservado(Request $request,TransportePasaje $pasaje){

        DB::connection('tenant')->beginTransaction();
        try {


            $attributes = $request->only([
                // 'asiento_id',
                'document_id',
                'note_id',
                'cliente_id',
                'pasajero_id',
                'precio',
                'fecha_salida',
                'programacion_id',
                'estado_asiento_id',
                // 'numero_asiento',
                'hora_salida',
                // 'origen_id',
                // 'destino_id'
            ]);

            $pasaje->update(
                array_merge($attributes,[
                    'fecha_salida' => Carbon::parse($request->fecha_salida)->format('Y-m-d'),
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
