<?php

namespace Modules\Transporte\Http\Controllers;

use App\Models\Tenant\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Transporte\Models\TransportePasaje;
use Modules\Transporte\Models\TransporteTerminales;
use Mpdf\Mpdf;
use App\Models\Tenant\Company;
use DateTime;

class TransporteReportes extends Controller
{
    //
    protected $company;

    public function __construct()
    {
        $this->company = Company::active();
    }

    public function index(){
        $oficinas = TransporteTerminales::all();

        return view('transporte::reportes.index',compact('oficinas'));
    }

    public function getPreviewReporteVentarPorDia(Request $request){

        extract($request->only(['oficina','fecha','page','limit']));

        $vendedores = User::select('id','name')
        ->whereHas('transporte_user_terminal',function($query) use ($oficina){
            $query->where('terminal_id',$oficina);
        }) 
        ->take($limit)->skip($limit * ($page - 1) );

        $total = $vendedores->count();
        $vendedores = $vendedores->get();

        foreach($vendedores as $vendedor){

            $totalVendido = TransportePasaje::where('user_id',$vendedor->id)
            ->whereDate('created_at',$fecha)
            ->sum('precio');
            
            $pasajes = TransportePasaje::with('origen','destino')
            ->where('user_id',$vendedor->id)
            ->whereDate('created_at',$fecha)
            ->get();

            $vendedor->setAttribute('pasajes_vendidos',$pasajes);
            $vendedor->setAttribute('total_vendido',$totalVendido);

        }


        return response()->json([
            'total' => $total,
            'data' => $vendedores
        ]);
    }


    private function generarReportePorVentas(array $data){

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);


        $content = view('transporte::reportes.templates.reporte_diario_venta.index',$data);
        $pdf->SetHTMLFooter('<div style="text-align: center; font-size: 7pt">Numéro de autorización SUNAT: '.$this->company->num_aut_manifiesto_pasajero.'</div>'
            ,0);
        $pdf->WriteHTML($content);

        $name = 'reporte_por_dia_ventas'.(new DateTime())->getTimestamp().'.pdf';

        $pdf->Output($name,'I');



    }

   

    public function reporteDiarioPorVentas(Request $request){

        extract($request->only(['oficina','fecha','page','limit']));

        $sucursal = TransporteTerminales::find($oficina);

        $vendedores = User::whereHas('transporte_user_terminal',function($query) use ($oficina){
            $query->where('terminal_id',$oficina);
        })
        
        ->get();

        foreach($vendedores as $vendedor){

            $totalVendido = TransportePasaje::where('user_id',$vendedor->id)
            ->whereDate('created_at',$fecha) 
            ->sum('precio');
            
            $pasajes = TransportePasaje::with('origen','destino')
            ->where('user_id',$vendedor->id)
            ->get();

            $vendedor->setAttribute('pasajes_vendidos',$pasajes);
            $vendedor->setAttribute('total_vendido',$totalVendido);

        }

        $this->generarReportePorVentas([
            'sucursal' => $sucursal,
            'fecha' => $fecha,
            'vendedores' => $vendedores
        ]);

    }

}
