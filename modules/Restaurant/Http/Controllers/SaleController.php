<?php
namespace Modules\Restaurant\Http\Controllers;

use App\Models\System\Configuration;
use Exception;

use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use App\Models\Tenant\Series;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\ItemWarehouse;

use App\Http\Resources\Tenant\OrderCollection;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Resources\Tenant\ItemWarehouseCollection;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use App\Models\Tenant\Item;
use App\Models\Tenant\Catalogs\DocumentType;
use Modules\Restaurante\Models\Mesa;
use Modules\Restaurante\Models\Pedido;
use Modules\Restaurante\Models\PedidoDetalle;

class SaleController extends Controller
{

  use StorageDocument;

  protected $company;

    public function index()
    {
        $items = Item::where('apply_restaurant',true)->get();
        $configuration= Configuration::all();

        $user = auth()->user();
        $type_user = $user->type;
        $id_user2=$user;

        return view('restaurant::sales.index',compact('items','configuration','id_user2','type_user'));
    }

    public function columns()
    {
        return [
            'id' => 'Codigo de Pedido',
            'number_document' => 'Comprobante Electronico',
        ];
    }

    public function store(Request $request)
    {
        try {

            $mesa_id= $request->pedido['mesa_id'];

            $pedido = new Pedido();
            $pedido->mesa_id = $mesa_id;
            //$pedido->estado_mesa= $request->pedido['estado_mesa'];
            $pedido->user_id = auth()->user()->id;
            $pedido->tipo = $request->pedido['tipo'];
            $pedido->establishment_id = auth()->user()->establishment_id;
            $pedido->save();

            $mesa = Mesa::findOrFail($mesa_id);
            $mesa->estado = $request->pedido['estado_mesa'];
            $mesa->update();

            if ($pedido->id) {
                foreach ($request->pedido['pedidos_detalle'] as $detalle) {
                    $pedido_detalle = new PedidoDetalle();
                    $pedido_detalle->pedido_id = $pedido->id;
                    $pedido_detalle->producto_id = $detalle['producto_id'];
                    $pedido_detalle->descripcion = $detalle['descripcion'];
                    $pedido_detalle->cantidad = $detalle['cantidad'];
                    $pedido_detalle->precio = $detalle['precio'];
                    $pedido_detalle->save();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $pedido
            ], 200);

        }catch(Exception $e){

            return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Lo sentimos ocurrio un error'
            ],500);

        }
    }

    public function insertItem(Request $request){
            $pedido_detalle = new PedidoDetalle();
            $pedido_detalle->pedido_id = $request->pedido_id;
            $pedido_detalle->producto_id = $request->item["producto_id"];
            $pedido_detalle->descripcion = $request->item["descripcion"];
            $pedido_detalle->cantidad = 1;
            $pedido_detalle->precio = $request->item["precio"];
            $pedido_detalle->save();
    }

    public function updatePedidoDocument(Request $request){
        try{


            $pedido = Pedido::findOrFail($request->pedido_id);
            $pedido->document_id=$request->document_id;
            $pedido->update();

            $mesa = Mesa::findOrFail($request->mesa_id);
            $mesa->estado=0;
            $mesa->update();

            return response()->json([
                'success' => true,
                'data'    => [$pedido,$mesa]
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'data'    => 'Ocurrió un error al procesar su petición. Detalles: ' . $th->getMessage()
            ], 500);

        }
    }

    public function getPedidosDetalle(Request $request){
        try{

            $pedidos_detalles = PedidoDetalle::with('item')
                ->where('pedido_id',$request->pedido_id)
                ->orderBy('id', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pedidos_detalles
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Lo sentimos ocurrio un error'
            ],500);

        }
    }

    public function getEstadoMesa(Request $request){
        try{
            //dd($request->nivel_id);

//            $pedido = Pedido::with(['mesa' => function ($query) {$query->where('numero', '=', 1);}])
//                ->where('tipo','mesa')
//                ->where('mesa_id',$request->mesa_id)
//                ->where('user_id', auth()->user()->id)
//                ->where('establishment_id', auth()->user()->establishment_id)
//                ->first();

            $pedido = DB::connection('tenant')
                ->table('restaurante_pedidos')
                ->join('restaurante_mesas', 'restaurante_mesas.id', '=', 'restaurante_pedidos.mesa_id')
                ->select(DB::raw("restaurante_pedidos.id as id"))
                ->where('restaurante_mesas.id', $request->mesa_id)
                ->where('restaurante_mesas.nivel_id', $request->nivel_id)
                ->where('restaurante_mesas.estado', 1)
                ->where('restaurante_pedidos.establishment_id', auth()->user()->establishment_id)
                ->orderBy('id','DESC')
                ->first();

            //dd($pedido->id);

            return response()->json([
                'success' => true,
                'pedidoId' => $pedido ? $pedido->id : null
            ], 200);

        }catch(Exception $e){

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Lo sentimos ocurrio un error'
            ],500);

        }
    }

    public function updateItem(Request $request){
        $item = PedidoDetalle::findOrFail($request->item_id);
        $item->fill($request->only('cantidad'));
        $item->save();

        return response()->json([
            'success' => true,
            'data'    => $item
        ], 200);
    }
    public function deleteItem(Request $request){
        $item = PedidoDetalle::where('id',$request->item_id);
        $item->delete();

        return response()->json([
            'success' => true,
            'data'    => $item
        ], 200);
    }



}

