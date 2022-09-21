<?php

namespace Modules\Transporte\Http\Controllers;

use App\Http\Resources\Tenant\ItemResource;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
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
use Modules\Transporte\Models\TransporteDestino;
use Modules\Transporte\Models\TransporteTerminales;
use App\Models\Tenant\PaymentMethodType;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\Traits\FinanceTrait;
use Illuminate\Support\Facades\Session;
use Modules\Transporte\Models\TransporteUserTerminal;

class TransporteEncomiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    use FinanceTrait;
    public function index(Request $request)
    {

        $estadosPagos = TransporteEstadoPagoEncomienda::all();

        $user_terminal = TransporteUserTerminal::where('user_id',auth()->user()->id)->first();

        if(is_null($user_terminal)){
            //redirigirlo
            Session::flash('message','No se pudó acceder. No tiene una terminal asignada');
            return redirect()->back();
        }

        $user=$user_terminal->user;

        // if(is_null($user_terminal)){
        //     //redirigirlo
        //     Session::flash('message','No se pudó acceder. No tiene una terminal asignada');
        //     return redirect()->back();
        // }

        $terminal = $user_terminal->terminal;
        $persons = Person::where('type','customers')->get();

        $estadosEnvios = TransporteEstadoEnvio::all();

        $document_type_03_filter = config('tenant.document_type_03_filter');


        $establishment =  Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::first();

        $isCashOpen =  !is_null(Cash::where([['user_id',$request->user()->id],['state',true]])->first());
        return view('transporte::encomiendas.index', compact(
            'estadosPagos',
            'estadosEnvios',
            'establishment',
            'series',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'user_terminal',
            'configuration',
            'document_type_03_filter',
            'isCashOpen',
            'persons',
            'user'
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


    public function getEncomiendas(Request $request){

        try{
            extract($request->only(['page','limit']));

            $encomiendas = TransporteEncomienda::with([
                'document.items',
                'document',
                'programacion' => function($progamacion){
                    return $progamacion->with([
                        'vehiculo:id,placa',
                        'origen:id,nombre',
                        'destino:id,nombre',
                    ]);
                },
                'remitente:id,name',
                'destinatario:id,name',
                'estadoPago',
                'estadoEnvio'
            ])
            ->whereNotNull('document_id')
            ->orderBy('id', 'DESC')
            ->take($limit)->skip($limit * ($page - 1) );

            return response()->json([
                'count' => $encomiendas->count(),
                'data' => $encomiendas->get()
            ],200);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Lo sentimos ocurrio un error en su petición'
            ],500);
        }



    }
    public function getEncomiendasNotes(){

        try{

            $encomiendas = TransporteEncomienda::with([
                'saleNote.items',
                'saleNote',
                'programacion' => function($progamacion){
                    return $progamacion->with([
                        'vehiculo:id,placa',
                        'origen:id,nombre',
                        'destino:id,nombre',
                    ]);
                },
                'remitente:id,name',
                'destinatario:id,name',
                'estadoPago',
                'estadoEnvio'
            ])
            ->whereNotNull('note_id')
            ->orderBy('transporte_encomiendas.id', 'DESC')
            ->get();

            //dd( $encomiendas);

            return response()->json($encomiendas,200);

        }catch(Exception $e){
            return response()->json([
                'message' => 'Lo sentimos ocurrio un error en su petición '.$e
            ],500);
        }



    }


    public function getClientes(Request $request){
        extract($request->only(['search']));
        $clientes = Person::select()
        ->orderBy('name');
        if(!empty($search)){
            $clientes->where('name','like',"%{$search}%");
        }

        return response()->json([
            'clientes' => $clientes->take(10)->get()
        ]);
    }
    public function getPasajero(Request $request){

        $cliente = Person::where('number',$request->number)->first();

        if($cliente){
            return response()->json([
                'success' => true,
                'id' => $cliente->id,
                'number' => $cliente->number,
                'name' => $cliente->name,
                'address' => $cliente->address,
                'edad' => $cliente->edad
            ]);
        }else{
            return response()->json([
                'success' => false,
            ]);
        }



    }
    public function getEmpresa(Request $request){

        $cliente = Person::where('number',$request->number)->first();

        if($cliente){
            return response()->json([
                'success' => true,
                'id' => $cliente->id,
                'number' => $cliente->number,
                'name' => $cliente->name,
                'address' => $cliente->address,
                'condition'=> $cliente->condition,
                'state'=> $cliente->state
            ]);
        }else{
            return response()->json([
                'success' => false,
            ]);
        }



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

        /* $programaciones = TransporteProgramacion::with('vehiculo','origen','destino')
        ->where('terminal_origen_id',$terminal->id);
        return response()->json([
            'programaciones' => $programaciones->get()
        ]);
 */
        $destinos = TransporteDestino::get();
        return response()->json([
            'destinos' => $destinos
        ]);
    }

    public function getProgramacionesDisponibles(ProgramacionesDisponiblesRequest $request){

        $programaciones = TransporteProgramacion::with('vehiculo','origen','destino')
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
            $time = date('H:i:s');
            $programaciones->whereRaw("TIME_FORMAT(hora_salida,'%H:%I:%S') >= '{$time}'");
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
        $company = Company::active();
        $soap_type_id = $company->soap_type_id;
        //
        try{

            $data = $request->only(
                'document_id',
                'note_id',
                'remitente_id',
                'destinatario_id',
                'fecha_salida',
                'programacion_id',
                'estado_pago_id',
                'estado_envio_id',
                'destino_id',
                'destinatario_nombre'
            );

            $data = array_merge($data,['terminal_id' => $request->user()->terminal->id,'soap_type_id'=>$soap_type_id]);

            // dd($data);

            $encomienda = TransporteEncomienda::create($data);

            $encomienda->remitente;
            $encomienda->destinatario;
            $encomienda->programacion;
            $encomienda->estadoEnvio;
            $encomienda->estadoPago;
            if($request->note_id){
                $encomienda->saleNote;
            }
            else{
                $encomienda->document;
            }



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
                    'estado_envio_id',
                    'destinatario_nombre'
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
                'message' => 'Se ha actualizado la información',
                'encomienda' => $encomienda,
            ]);

        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar su petición',
            ]);
        }

    }

    public function entregar(Request $request)
    {
        //
        try{
            $encomienda = TransporteEncomienda::findOrFail($request->id);
            $encomienda->estado_envio_id = 4;
            $encomienda->save();

            $encomienda->remitente;
            $encomienda->destinatario;
            $encomienda->programacion;
            $encomienda->estadoEnvio;
            $encomienda->estadoPago;
            $encomienda->document;

            return response()->json([
                'success' => true,
                'message' => 'Se ha actualizado la información',
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
    public function destroy(TransporteEncomienda $encomienda)
    {
        //

        try{

            $encomienda->delete();

            return response()->json([
                'success' => true,
                'message' => 'Se ha actualizado la información con éxito'
            ]);

        }catch(Exception $e){

            return response()->json([
                'success' => false,
                'message' => 'Lo sentimos ocurrió un error'
            ]);

        }

    }

    public function getProductos(Request $request){
        try{
            extract($request->only('search'));

            $items = Item::select();

            $items->where('item_type_id','01')
                ->where('name','LIKE', '%'.$search.'%')
                ->orWhere('second_name', 'LIKE', '%'.$search.'%')
                ->orWhere('description', 'LIKE', '%'.$search.'%');


            //return $items->toSql();

            return response()->json($items->get()->map(function($item){
                $it = new ItemResource($item);
                return $it;
            }),200);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Lo sentimos ocurrio un error'
            ],422);

        }
    }
}
