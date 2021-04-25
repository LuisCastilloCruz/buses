<?php

namespace Modules\Transporte\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Transporte\Models\TransporteVehiculo;
use Modules\Transporte\Http\Requests\TransporteVehiculoRequest;
class TransporteVehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $vehiculos = TransporteVehiculo::orderBy('id', 'DESC')
            ->get();

        return view('transporte::vehiculos.index', compact('vehiculos'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('transporte::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $room = TransporteVehiculo::create($request->only('placa', 'nombre','asientos','pisos'));

        return response()->json([
            'success' => true,
            'data'    => $room
        ], 200);
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
    public function update(TransporteVehiculoRequest $request, $id)
    {
        $vehiculo = TransporteVehiculo::findOrFail($id);
        $vehiculo->fill($request->only('placa', 'nombre','asientos','pisos'));
        $vehiculo->save();

        return response()->json([
            'success' => true,
            'data'    => $vehiculo
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
            TransporteVehiculo::where('id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data'    => 'Ocurrió un error al procesar su petición. Detalles: ' . $th->getMessage()
            ], 500);
        }
    }
}
