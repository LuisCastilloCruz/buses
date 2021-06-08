<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Transporte\Http\Requests\ProgramacionesDisponiblesRequest;
use Modules\Transporte\Http\Requests\TransporteEncomiendaRequest;
use Modules\Transporte\Models\TransporteEncomienda;
use Modules\Transporte\Models\TransportePasaje;
use Modules\Transporte\Models\TransporteProgramacion;
use Modules\Transporte\Models\TransporteTerminales;
use App\Models\Tenant\PaymentMethodType;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\Traits\FinanceTrait;
use Illuminate\Support\Facades\Session;

class TransportePasajeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    use FinanceTrait;
    public function index()
    {

        $user = Auth::user();

        $user_terminal = $user->user_terminal;

        if(is_null($user_terminal)) {
            //redirigirlo
            Session::flash('message','No se pudó acceder. No tiene una terminal asignada');
            return redirect()->back();
        }

        $pasajes = TransportePasaje::with('document')->all();

        //dd($pasajes->document->series);
        $establishment =  Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::first();


        return view('transporte::pasajes.index', compact(
            'pasajes',
            'establishment',
            'series',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'user_terminal',
            'configuration'
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
        $clientes = Person::select()
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
        try{

            $encomienda = TransporteEncomienda::create(
                $request->only(
                    'document_id',
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
            $encomienda->document;


            return response()->json([
                'success' => true,
                'encomienda' => $encomienda,
            ]);

        }catch(\Throwable $th){
            return response()->json([
                'success' => false,
                'error' => $th->getMessage(),
                'message' => 'Ocurrió un error al procesar su petición'
            ]);
        }



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
    public function update(TransporteEncomiendaRequest $request, TransporteEncomienda $encomienda)
    {
        //
        try{

            $encomienda->update(
                $request->only(
                    'document_id',
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
            $encomienda->document;


            return response()->json([
                'success' => true,
                'encomienda' => $encomienda,
            ]);

        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar su petición',
            ]);
        }

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
