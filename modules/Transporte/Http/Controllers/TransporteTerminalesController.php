<?php

namespace Modules\Transporte\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Transporte\Http\Requests\TransporteTerminalesRequest;
use Modules\Transporte\Models\TransporteDestino;
use Modules\Transporte\Models\TransporteTerminales;

class TransporteTerminalesController extends Controller
{
    //

    public function index(){
        $terminales = TransporteTerminales::with(['destino'])
        ->orderBy('id', 'DESC')
        ->get();
        $destinos = TransporteDestino::all();
        
        return view('transporte::terminales.index', compact('terminales','destinos'));
    }

    public function store(TransporteTerminalesRequest $request){

        $terminal = TransporteTerminales::create($request->only('direccion','destino_id','nombre'));
        $terminal->destino; //cargo el destino o ciudad

        return response()->json([
            'success' => true,
            'data'    => $terminal
        ]);

    }

    public function update(TransporteTerminalesRequest $request,TransporteTerminales $terminal){
        
        $terminal->update($request->only([
            'nombre',
            'direccion',
            'destino_id'
        ]));
        $terminal->destino;

        return response()->json([
            'success' => true,
            'data'    => $terminal
        ]);

    }
}
