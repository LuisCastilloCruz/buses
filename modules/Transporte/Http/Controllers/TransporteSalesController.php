<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\Tenant\Cash;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Transporte\Http\Requests\ProgramacionesDisponiblesRequest;
use Modules\Transporte\Models\TransporteCategory;
use Modules\Transporte\Http\Requests\TransporteCategoryRequest;
use Modules\Transporte\Models\TransporteDestino;
use Modules\Transporte\Models\TransporteEstadoAsiento;
use Modules\Transporte\Models\TransportePasaje;
use Modules\Transporte\Models\TransporteProgramacion;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Series;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\PaymentMethodType;
use Exception;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Transporte\Models\TransporteViajes;
use Modules\Transporte\Traits\PasajerosRuta;

class TransporteSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    const MINUTES = 60;
    CONST DAYS = 1440;

    use FinanceTrait, PasajerosRuta;
    public function index(Request $request, TransportePasaje $pasaje = null)
    {

        $user = $request->user();
        $terminal = $request->user()->terminal;
        $config = Configuration::first();

        $isCashOpen =  !is_null(Cash::where([['user_id',$user->id],['state',true]])->first());
        //dd($isCashOpen);
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

        $configuracion_socket = json_decode($config->configuracion_socket, true);

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
            'isCashOpen',
            'user',
            'configuracion_socket'
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

       try{
           $user = auth()->user();

           if($user->type=="admin"){

               $programaciones = TransporteProgramacion::with('origen','destino','vehiculo','vehiculo.seats')->where('terminal_origen_id',$request->origen_id)
                   ->where('active',true)
                   ->where('terminal_destino_id', $request->destino_id);
                //    ->whereHas('destino',function($destino) use($request){
                //        $destino->where('destino_id',$request->destino_id);
                //    });
                    //  $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%H:%i:%s') >= '{$time}'");

           }else{
               $programaciones = TransporteProgramacion::with('origen','destino','vehiculo.seats')->where('terminal_origen_id',$request->origen_id)
               ->where('terminal_destino_id', $request->destino_id)
                ->where('active',true)
            //        ->whereHas('destino',function($destino) use($request){
            //            $destino->where('destino_id',$request->destino_id);
            //        })
                   // ->where('terminal_destino_id',$request->destino_id)
                ->WhereEqualsOrBiggerDate($request->fecha_salida);
               $date = Carbon::parse($request->fecha_salida);
               $today = Carbon::now();

                /* váliddo si es el mismo dia  */
                if($date->isSameDay($today)){
                    /* Si es el mismo traigo las programaciones que aun no hayan cumplido la hora */
                    $time = date('H:i:s',strtotime("-120 minutes")); //doy una hora para que aún esté disponible la programación
                    $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%H:%i:%s') >= '{$time}'");
                }
           }




            $listProgramaciones = $programaciones->get();

            // $viajes = collect([]);



            // foreach($listProgramaciones as $programacion){

            //     $pasajes = collect([]);

            //     $programacionPadre = $programacion->programacion;

            //     // $rutas = $programacionPadre->rutas()->get();

            //     $date = new Carbon(sprintf('%s %s', $request->fecha_salida,$programacion->hora_salida));

            //     $viaje = TransporteViajes::where('terminal_origen_id',$programacion->terminal_origen_id)
            //     ->where('terminal_destino_id',$programacion->terminal_destino_id)
            //     ->whereTime('hora_salida', $programacion->hora_salida)
            //     ->whereDate('fecha_salida', $request->fecha_salida )
            //     ->where('programacion_id',$programacionPadre->id)
            //     ->first();

            //     $viaje = !is_null($viaje) ? $viaje : TransporteViajes::create([
            //         'terminal_origen_id' => $programacion->terminal_origen_id,
            //         'hora_salida' => $programacion->hora_salida,
            //         'fecha_salida' => $request->fecha_salida,
            //         'vehiculo_id' => $programacion->vehiculo_id,
            //         'terminal_destino_id' => $programacion->terminal_destino_id,
            //         'programacion_id' => $programacionPadre->id
            //     ]);

            //     $viaje->update([
            //         'vehiculo_id' => $programacion->vehiculo_id,
            //     ]);

            //     $viajes->push($viaje);

            //     // $rutas->prepend($programacionPadre->origen);
            //     // $rutas->push($programacionPadre->destino);

            //     // // return response()->json($viaje->terminal_origen_id);
            //     // $indexOrigen = $this->getPositionInRoute($viaje->origen,$rutas);
            //     // $indexDestino = $this->getPositionInRoute($viaje->destino, $rutas);

            //     // $mayores = $this->getRutasMayores($indexOrigen,$rutas)->pluck('id');
            //     // $menores = $this->getRutasMenores($indexOrigen,$rutas)->pluck('id');

            //     // $intermedios = $this->getRoutesBeetween($indexOrigen,$indexDestino,$rutas)->pluck('id');

            //     // $listMenores = TransporteProgramacion::with('origen','destino')
            //     // ->whereIn('terminal_origen_id',$menores)
            //     // ->whereIn('terminal_destino_id',$mayores)
            //     // ->where('programacion_id',$programacionPadre->id)
            //     // ->get(); // obtengo todas las programaciones que vienen hacia mi


            //     // $list = TransporteProgramacion::with('origen','destino')
            //     // ->where('terminal_origen_id',$viaje->terminal_origen_id)
            //     // ->where('programacion_id',$programacionPadre->id)
            //     // ->get(); // obtengo todas las programaciones que vienen hacia mi


            //     // $listIntermedios = TransporteProgramacion::with('origen','destino')
            //     // ->whereIn('terminal_origen_id',$intermedios)
            //     // ->where('programacion_id',$programacionPadre->id)
            //     // ->get(); // obtengo los intemedios entre el punto de origen y destino si es que existen

            //     // // return

            //     // // return response()->json($merged->map(function($item){
            //     // //     return sprintf('%s -> %s',$item->origen->nombre,$item->destino->nombre);
            //     // // }));

            //     // foreach($listMenores as $menor){
            //     //     $timeClone = clone $date;

            //     //     $tiempoEstimado = TransporteProgramacion::where('terminal_origen_id',$menor->terminal_origen_id)
            //     //     ->where('terminal_destino_id',$viaje->terminal_origen_id)
            //     //     ->where('programacion_id',$programacionPadre->id)
            //     //     ->first();


            //     //     $timeClone = $timeClone->subMinutes($tiempoEstimado->tiempo_estimado);

            //     //     $travels = TransporteViajes::where('terminal_origen_id',$menor->terminal_origen_id)
            //     //     ->where('terminal_destino_id',$menor->terminal_destino_id)
            //     //     ->whereDate('fecha_salida',$timeClone->format('Y-m-d'))
            //     //     ->whereTime('hora_salida' , $timeClone->format('H:i:s'))
            //     //     ->where('programacion_id',$programacionPadre->id)
            //     //     ->get();


            //     //    $searchPasajes = TransportePasaje::with( 'origen', 'destino', 'pasajero','document:id,document_type_id')
            //     //    ->whereIn('viaje_id',$travels->pluck('id'))
            //     //    ->where('estado_asiento_id','!=',4) //diferente de cancelado
            //     //    ->get();

            //     //    $pasajes = [...$pasajes, ...$searchPasajes];

            //     // }

            //     // foreach($listIntermedios as $intermedio){
            //     //     $timeClone = clone $date;

            //     //     $tiempoEstimado = TransporteProgramacion::where('terminal_origen_id',$viaje->terminal_origen_id)
            //     //     ->where('terminal_destino_id',$intermedio->terminal_origen_id)
            //     //     ->where('programacion_id',$programacionPadre->id)
            //     //     ->first();

            //     //     if(is_null($tiempoEstimado)) continue;

            //     //     // $minutos = $this->hoursToMinutes();

            //     //     $timeClone = $timeClone->addMinutes($tiempoEstimado->tiempo_estimado);

            //     //     $travels = TransporteViajes::where('terminal_origen_id',$intermedio->terminal_origen_id)
            //     //     ->where('terminal_destino_id',$intermedio->terminal_destino_id)
            //     //     ->whereDate('fecha_salida',$timeClone->format('Y-m-d'))
            //     //     ->whereTime('hora_salida', $timeClone->format('H:i:s'))
            //     //     ->where('programacion_id',$programacionPadre->id)
            //     //     ->get();


            //     //    $searchPasajes = TransportePasaje::with('origen', 'destino', 'pasajero','document:id,document_type_id')
            //     //    ->whereIn('viaje_id',$travels->pluck('id'))
            //     //    ->where('estado_asiento_id','!=',4) //diferente de cancelado
            //     //    ->get();

            //     //    $pasajes = [...$pasajes, ...$searchPasajes];



            //     // }

            //     // foreach($list as $item){
            //     //     $travels = TransporteViajes::where('terminal_origen_id',$item->terminal_origen_id)
            //     //     ->where('terminal_destino_id',$item->terminal_destino_id)
            //     //     ->whereDate('fecha_salida', $request->fecha_salida)
            //     //     ->whereTime('hora_salida', $programacion->hora_salida)
            //     //     ->where('programacion_id',$programacionPadre->id)
            //     //     ->get();


            //     //    $searchPasajes = TransportePasaje::with('origen', 'destino', 'pasajero','document:id,document_type_id')
            //     //    ->whereIn('viaje_id',$travels->pluck('id'))
            //     //    ->where('estado_asiento_id','!=',4) //diferente de cancelado
            //     //    ->get();

            //     //    $pasajes = [...$pasajes, ...$searchPasajes];

            //     // }


            //     $viaje->load('vehiculo.seats');
            //     // $viaje->setAttribute('asientos_ocupados',$pasajes);


            //     // return response()->json($listMenores->map(function($item){
            //     //     return sprintf('%s -> %s',$item->origen->nombre,$item->destino->nombre);
            //     // }));

            // }


            return response()->json( [
                'programaciones' => $listProgramaciones
            ]);

       }catch(Exception $e){

        return response()->json([
            'programaciones' => [],
            'error' => $e->getMessage() ,
            'line' => $e->getLine()
        ]);
       }

    }

    public function getAsientosOcupados(Request $request){

        $programacion = TransporteProgramacion::find($request->input('programacion_id'));
        $fechaSalida = $request->input('fecha_salida');

        $pasajes = $this->getPasajeros($programacion, $fechaSalida, true);

        return response()->json($pasajes);

    }

    public function realizarVenta(Request $request){
        $company = Company::active();
        $soap_type_id = $company->soap_type_id;

        $user = $request->user();

        $terminal = $user->terminal;

        DB::connection('tenant')->beginTransaction();

        if($request->tipo_venta == 2){

            $request->validate([
                //'cliente_id'=> ['required'],
                'estado_asiento_id' => ['required'],
                'fecha_salida' => ['required'],
                //'pasajero_id' => ['required'],
                'destino_id' => ['required'],
                'numero_asiento' => ['required'],
                'hora_salida' => ['required'],
                //'programacion_id' => ['required']
            ]);

        }



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
                'estado_asiento_id',
                'tipo_venta',
                'numero_asiento',
                'destino_id',
                'hora_salida',
            ]);

            if($request->tipo_venta == 2) {//venta con programacion
                $programacion = TransporteProgramacion::with('programacion')
                    ->find($request->input('programacion_id'));

                $parentProgramacion = $programacion->programacion;


                $viaje = TransporteViajes::where('terminal_origen_id', $programacion->terminal_origen_id)
                    ->where('terminal_destino_id', $programacion->terminal_destino_id)
                    ->whereTime('hora_salida', $programacion->hora_salida)
                    ->whereDate('fecha_salida', $request->fecha_salida)
                    ->where('programacion_id', $parentProgramacion->id)
                    ->first();

                $viaje = !is_null($viaje) ? $viaje : TransporteViajes::create([
                    'terminal_origen_id' => $programacion->terminal_origen_id,
                    'hora_salida' => $programacion->hora_salida,
                    'fecha_salida' => $request->fecha_salida,
                    'vehiculo_id' => $programacion->vehiculo_id,
                    'terminal_destino_id' => $programacion->terminal_destino_id,
                    'programacion_id' => $parentProgramacion->id
                ]);
            }


            if($request->input('tipo_venta') == 1){ //venta libre
                TransportePasaje::create(
                    array_merge($attributes,[
                        'fecha_salida' => Carbon::parse($request->fecha_salida)->format('Y-m-d'),
                        'origen_id' => $terminal->id,
                        'soap_type_id'=>$soap_type_id,
                        // 'fecha_llegada' => $fechaLLegada,
                        'sucursal_id' => $terminal->id,
                        'user_id' => $user->id,
                        'viaje_id' => null
                    ])
                );

            }else if($request->input('tipo_venta') == 2){//venta con programacion


                TransportePasaje::create(
                    array_merge($attributes,[
                        'fecha_salida' => Carbon::parse($request->fecha_salida)->format('Y-m-d'),
                        'origen_id' => $terminal->id,
                        'soap_type_id'=>$soap_type_id,
                        'user_name' => auth()->user()->name,
                        'sucursal_id' => $terminal->id,
                        'color' => $terminal->color,
                        'user_id' => $user->id,
                        'viaje_id' => $viaje->id
                    ])
                );



            }



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
                'estado_asiento_id',
                // 'numero_asiento',
                'hora_salida',
                'viaje_id'
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
    public function eliminarReserva(Request $request){

        try {

            $pasaje = TransportePasaje::findOrFail($request->id);
            $pasaje->estado_asiento_id=4;
            $pasaje->save();

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



}
