<?php

namespace Modules\Transporte\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use DateTime;
use Modules\Transporte\Http\Requests\ManifiestoFormRequest;
use Modules\Transporte\Models\TransporteChofer;
use Modules\Transporte\Models\TransporteEncomienda;
use Modules\Transporte\Models\TransporteManifiesto;
use Modules\Transporte\Models\TransportePasaje;
use Modules\Transporte\Models\TransporteProgramacion;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

class TransporteManifiestosController extends Controller
{
    //

    public function index(){
        $establishment =  Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $choferes = TransporteChofer::all();

        $manifiestos = TransporteManifiesto::with([
            'chofer',
            'copiloto',
            'serie'
        ])->get();

        
        return view('transporte::manifiestos.index',compact('series','choferes','manifiestos'));
    }

    // public function getProgramaciones(Request $request){
    //     $user = $request->user();

    //     $date = Carbon::parse($request->fecha_salida);
    //     $today = Carbon::now();

    //     if(is_null($request->fecha_salida)){
    //         return collect([]);
    //     }

    //     $programaciones = TransporteProgramacion::with('vehiculo','origen','destino')
    //     ->where('terminal_origen_id',$user->terminal_id)
    //     ->WhereEqualsOrBiggerDate($request->fecha_salida);

    //     $date = Carbon::parse($request->fecha_salida);
    //     $today = Carbon::now();

    //     /* váliddo si es el mismo dia  */
    //     if($date->isSameDay($today)){
    //         /* Si es el mismo traigo las programaciones que aun no hayan cumplido la hora */
    //         $time = date('h:i:s');
    //         $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%h:%i:%s') >= '{$time}'");
    //     }

    //     return response()->json($programaciones->get(),200);
    // }


    public function store(ManifiestoFormRequest $request){
        try {

            $programacion = TransporteProgramacion::findOrFail($request->programacion_id);

            $numero = TransporteManifiesto::where('tipo',$request->tipo)
            ->count() + 1;


            $manifiesto = TransporteManifiesto::create([
                'serie' => $request->serie,
                'tipo' => $request->tipo,
                'numero' => $numero,
                'chofer_id' => $request->chofer_id,
                'copiloto_id' => $request->copiloto_id,
                'observaciones' => $request->observaciones,
                'programacion_id' => $programacion->id,
                'fecha' => $request->fecha,
                'hora' => $request->hora
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Se ha generado el manifiesto correctamente',
                'manifiesto' => $manifiesto,
            ]);
            
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Lo sentimos, ocurrio un error al generar el manifiesto',
                'error' => $th->getMessage()
            ],500);
        }
    }

    public function imprimirManifiesto(Request $request,TransporteManifiesto $manifiesto){


        if($manifiesto->tipo == 1){ //encomiendas
            $this->documentEncomiendas($request,$manifiesto);

        }else if($manifiesto->tipo == 2){ //pasajes
           $this->documentPasajeros($request,$manifiesto);
        }



    }

    private function documentPasajeros(Request $request,TransporteManifiesto $manifiesto){
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);

        $programacion = $manifiesto->programacion;
        $vehiculo = $programacion->vehiculo;

        $pasajes = TransportePasaje::with([
            'pasajero.identity_document_type',
            'asiento',
        ])
        ->where('programacion_id',$programacion->id)
        ->whereDate('fecha_salida',$manifiesto->fecha)
        ->get();

        $user = $request->user();


        $pasajesEnTerminal = TransportePasaje::whereHas('programacion',function($programacion) use($user,$manifiesto){
            $programacion->where('terminal_origen_id',$user->terminal_id)
            ->whereTime('hora_salida',$manifiesto->hora);
        })
        ->whereDate('fecha_salida',$manifiesto->fecha)->count();


        $pasajesRecogidosRuta = 0;

        foreach($programacion->rutas as $ruta){
            $tempPasajes = TransportePasaje::with([
                'pasajero',
                'asiento',
            ])
            ->whereHas('programacion',function($program) use($programacion,$ruta){
                $program->where('vehiculo_id',$programacion->vehiculo_id)
                ->where('terminal_origen_id',$ruta->terminal_id);
            })
            ->whereDate('fecha_salida',$manifiesto->fecha)
            ->get();
            $pasajesRecogidosRuta += count($tempPasajes);

            foreach($tempPasajes as $pasaje){
                $pasajes->add($pasaje);
            }
        }

        $content = view('transporte::manifiestos.manifiesto_pasajes.body',compact(
            'programacion',
            'manifiesto',
            'pasajes',
            'vehiculo',
            'pasajesEnTerminal',
            'pasajesRecogidosRuta'
        ));

        $pdf->WriteHTML($content);

        $name = 'manifiesto_pasajeros_'.(new DateTime())->getTimestamp().'.pdf';

        $pdf->Output($name,'I');

    }

    private function documentEncomiendas(Request $request,TransporteManifiesto $manifiesto){
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);

        $programacion = $manifiesto->programacion;
        $vehiculo = $programacion->vehiculo;

        $encomiendas = TransporteEncomienda::with([
            'destinatario',
            'document'
        ])
        ->where('programacion_id',$programacion->id)
        ->whereDate('fecha_salida',$manifiesto->fecha)
        ->get();

        $user = $request->user();

        $content = view('transporte::manifiestos.manifiesto_encomiendas.body',compact(
            'programacion',
            'manifiesto',
            'encomiendas',
            'vehiculo',
        ));

        $pdf->WriteHTML($content);

        $name = 'manifiesto_encomiendas_'.(new DateTime())->getTimestamp().'.pdf';

        $pdf->Output($name,'I');

    }
}
