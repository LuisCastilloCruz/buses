<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\System\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Transporte\Http\Requests\ProgramacionesDisponiblesRequest;
use Modules\Transporte\Http\Requests\TransporteEncomiendaRequest;
use Modules\Transporte\Models\TransporteEncomienda;
use Modules\Transporte\Models\TransporteEstadoEnvio;
use Modules\Transporte\Models\TransporteEstadoPagoEncomienda;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteTerminales;

class TransporteEncomiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $estadosPagos = TransporteEstadoPagoEncomienda::all();

        
        $estadosEnvios = TransporteEstadoEnvio::all();
        
        $encomiendas = TransporteEncomienda::with([
            'programacion' => function($progamacion){
                return $progamacion->with([
                    'vehiculo:id,placa',
                    'origen:id,nombre',
                    'destino:id,nombre'
                ]);
            },
            'remitente:id,name',
            'destinatario:id,name',
            'estadoPago',
            'estadoEnvio'
        ])->orderBy('id', 'DESC')
        ->get();
        return view('transporte::encomiendas.index', compact(
            'encomiendas',
            'estadosPagos',
            'estadosEnvios'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('transporte::create');
    }


    public function getClientes(Request $request){
        extract($request->only(['search']));
        $clientes = Client::select()
        ->orderBy('name');
        if(!empty($search)){
            $clientes->where('name','like',"%{$search}%");
        }

        return response()->json([
            'clientes' => $clientes->get()
        ]);
    }

    public function getTerminales(Request $request){
        extract($request->only(['search']));
        $terminales = TransporteTerminales::select()
        ->orderBy('nombre');
        if(!empty($search)){
            $terminales->where('nombre','like',"%{$search}%");
        }

        return response()->json([
            'terminales' => $terminales->get()
        ]);
    }

    public function getDestinos(Request $request,TransporteTerminales $terminal){
        
        $programaciones = TransporteProgramacion::with('vehiculo','origen','destino')
        ->where('terminal_origen_id',$terminal->id);
        return response()->json([
            'programaciones' => $programaciones->get()
        ]);
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
            'programaciones' => $programaciones->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(TransporteEncomiendaRequest $request)
    {
        //
        $encomienda = TransporteEncomienda::create(
            $request->only(
                'descripcion',
                'remitente_id',
                'destinatario_id',
                'fecha_salida',
                'programacion_id',
                'estado_pago_id',
                'estado_envio_id'
            )
        );

        $encomienda->remitente;
        $encomienda->destinatario;
        $encomienda->programacion;
        $encomienda->estadoEnvio;
        $encomienda->estadoPago;


        return response()->json([
            'success' => true,
            'encomienda' => $encomienda,
        ]);
        

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('transporte::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('transporte::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
