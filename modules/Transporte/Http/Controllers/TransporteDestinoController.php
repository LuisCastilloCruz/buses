<?php

namespace Modules\Transporte\Http\Controllers;

use Modules\Transporte\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Transporte\Http\Requests\TransporteAddRateToRoomRequest;
use Modules\Transporte\Models\TransporteDestino;
use Modules\Transporte\Models\TransporteChofer;
use Modules\Transporte\Models\TransporteCategory;
use Modules\Transporte\Http\Requests\TransporteDestinoRequest;
use Modules\Transporte\Http\Requests\TransporteChoferRequest;
use Modules\Transporte\Models\TransporteRate;
use Modules\Transporte\Models\TransporteRoomRate;
class TransporteDestinoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index()
	{

        $destinos = TransporteDestino::orderBy('id', 'DESC')
            ->get();

        return view('transporte::destinos.index', compact('destinos'));
	}

	/**
	 * Store a newly created resource in storage.
	 * @param Request $request
	 * @return Response
	 */
	public function store(TransporteDestinoRequest $request)
	{
		$destino = TransporteDestino::create($request->only('nombre'));

		return response()->json([
			'success' => true,
			'data'    => $destino
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 * @param Request $request
	 * @param int $id
	 * @return Response
	 */
	public function update(TransporteDestinoRequest $request, $id)
	{
        $destino = TransporteDestino::findOrFail($id);
        $destino = $destino->fill($request->only('nombre'));
        $destino->save();

		return response()->json([
			'success' => true,
			'data'    => $destino
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
			TransporteDestino::where('id', $id)
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

	public function tables()
	{
		$rates = TransporteRate::where('active', true)
			->orderBy('description')
			->get();

		return response()->json([
			'success' => true,
			'rates'   => $rates,
		], 200);
	}

}
