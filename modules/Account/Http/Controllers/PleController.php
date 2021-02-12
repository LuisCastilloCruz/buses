<?php
namespace Modules\Account\Http\Controllers;

use Modules\Account\Exports\ReportAccountingConcarExport;
use Modules\Account\Exports\ReportAccountingFoxcontExport;
use Modules\Account\Exports\ReportAccountingContasisExport;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\Note;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Configuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Account\Models\CompanyAccount;

class PleController extends Controller
{
    public function index()
    {
        return view('account::account.ple');
    }

    public function download(Request $request)
    {
        $type    = $request->input('type');
        $month   = $request->input('month');
        $periodo = Carbon::parse($month.'-01')->format('Ym');
        $moneda  = '1';

        $d_start = Carbon::parse($month.'-01')->format('Y-m-d');
        $d_end = Carbon::parse($month.'-01')->endOfMonth()->format('Y-m-d');

        $records = $this->getDocuments($d_start, $d_end,$type);
        $company = Company::active();

        switch ($type) {

            case '140100': // ventas

                $records = $this->getStructureVentas($records);

                $temp = tempnam(sys_get_temp_dir(), 'txt');
                $file = fopen($temp, 'w+');
                foreach ($records as $record)
                {
                    $line = implode('|', $record);
                    fwrite($file, $line."\r\n");
                }
                fclose($file);

                $lineas=($records) ? '1' : '0';
                $filename = "LE". $company->number.$periodo."00".$type."001". $lineas.$moneda."1";
                return response()->download($temp, $filename.'.txt');

            case '080100': //compras

                $records = $this->getStructureCompras($records);

                //dd($records);
                $temp = tempnam(sys_get_temp_dir(), 'txt');
                $file = fopen($temp, 'w+');
                foreach ($records as $record)
                {
                    $line = implode('|', $record);
                    fwrite($file, $line."\r\n");
                }
                fclose($file);

                $lineas=($records) ? '1' : '0';
                $filename = "LE". $company->number.$periodo."00".$type."001". $lineas.$moneda."1";
                return response()->download($temp, $filename.'.txt');

            case '080200': //compras

                $records = $this->getStructureComprasND($records);

                //dd($records);
                $temp = tempnam(sys_get_temp_dir(), 'txt');
                $file = fopen($temp, 'w+');
                foreach ($records as $record)
                {
                    $line = implode('|', $record);
                    fwrite($file, $line."\r\n");
                }
                fclose($file);

                $lineas=($records) ? '1' : '0';
                $filename = "LE". $company->number.$periodo."00".$type."001". $lineas.$moneda."1";
                return response()->download($temp, $filename.'.txt');

            case 'foxcont':

                $data = [
                    'records' => $this->getStructureFoxcont($records),
                ];

                return (new ReportAccountingFoxcontExport)
                    ->data($data)
                    ->download($filename.'.xlsx');
        }

    }

    private function getDocuments($d_start, $d_end,$type)
    {
       if($type=='140100') // ventas
       {
            return Document::query()
                ->whereBetween('date_of_issue', [$d_start, $d_end])
                ->whereIn('document_type_id', ['01', '03','07','08'])
                ->whereIn('currency_type_id', ['PEN','USD'])
                ->orderBy('series')
                ->orderBy('number')
                ->get();

       }
       else if($type=='080100') // compras
       {
            return Purchase::query()
                ->whereBetween('date_of_issue', [$d_start, $d_end])
                ->whereIn('document_type_id', ['01', '03','07','08','04','02','14'])
                ->whereIn('currency_type_id', ['PEN','USD'])
                ->orderBy('series')
                ->orderBy('number')
                ->get();

       }
       else if($type=='080200') // compras ND
       {
            return Purchase::query()
                ->whereBetween('date_of_issue', [$d_start, $d_end])
                ->whereIn('document_type_id', ['9999']) // para que me dé vacío
                ->whereIn('currency_type_id', ['PEN','USD'])
                ->orderBy('series')
                ->orderBy('number')
                ->get();

       }
    }

    private function getStructureCompras($documents)
    {

        $company_account = CompanyAccount::first();
        $rows = [];
        foreach ($documents as $index => $row)
        {
            $date_of_issue = Carbon::parse($row->date_of_issue);
            $currency_type_id = ($row->currency_type_id === 'PEN')?'S':'D';
            $detail = substr($row->supplier->name.',  '.$row->number_full, 0, 60);

            $number_index = $date_of_issue->format('m').str_pad($index + 1, 4, "0", STR_PAD_LEFT);
            $regimen="";

            if($regimen=='RER'){
                $number_index="M-RER";
            }

            $fechaMod  = '';
            $seriesMod = '';
            $numberMod = '';
            $tipoMod   = '';
            if($row->document_type_id=='07' || $row->document_type_id=='08' ){
                $seriesMod = $row->note->affected_document->series;
                $numberMod = $row->note->affected_document->number;
                $fechaMod= str_pad($row->note->affected_document->date_of_issue->format('d/m/yy'), 8,'00', STR_PAD_RIGHT);
                $tipoMod =  $row->note->affected_document->document_type_id;
            }

            $type_basimp= $row->type_basimp;
            $estado=$row->state_type_id;
            $tc    = $row->exchange_rate_sale;
            $total_taxed = $row->total_taxed;
            $total_igv =$row->total_igv;
            $total_free =$row->total_free;
            $total_exportation = $row->total_exportation;
            $total_exonerated  = $row->total_exonerated;
            $total_unaffected  = $row->total_unaffected;
            $total             = $row->total;

            if($row->currency_type_id == 'USD'){
                $total_taxed = $total_taxed*$tc;
                $total_igv   = $total_igv *$tc;
                $total_exportation =$total_exportation*$tc;
                $total_exonerated  = $total_exonerated *$tc;
                $total_unaffected  = $total_unaffected *$tc;
                $total       = $total *$tc;
            }

            $basimp1="";
            $basimp2="";
            $basimp3="";
            $igv1   ="";
            $igv2   ="";
            $igv3   ="";
            $total_no_grabado   = $total_exonerated+$total_unaffected;
            if($type_basimp=='01'){ //credito fizcal
                $basimp1 = $total_taxed;
                $igv1    = $total_igv;
            }
            elseif($type_basimp=='02') // prorrata
            {
                $basimp2 = $total_exportation+$total_free+$total_taxed;
                $igv2    = $total_igv;
            }
            elseif($type_basimp=='03') // costo/gasto
            {
                $basimp3 = $total_exportation+$total_free+$total_taxed;
                $igv3    = $total_igv;
            }

            $rows[] = [
                'col_1' => str_pad($row->date_of_issue->format('yym'), 8,'00', STR_PAD_RIGHT),
                'col_2' =>$number_index,
                'col_3' =>'M'.$number_index,
                'col_4' =>$row->date_of_issue->format('d/m/yy'),
                'col_5' =>'',
                'col_6' =>$row->document_type_id,
                'col_7' =>$row->series,
                'col_8' =>'',
                'col_9' =>$row->number,
                'col_10' =>'',
                'col_11'=>($estado =='11' ) ? '' : $row->supplier->identity_document_type_id,
                'col_12'=>($estado =='11' ) ? '' : $row->supplier->number,
                'col_13' =>($estado =='11' ) ? '' : $detail,
                'col_14' =>($estado =='11') ? ''  : $basimp1,
                'col_15' =>($estado =='11') ? '' : $igv1,
                'col_16' =>($estado =='11')  ? '' : $basimp2, //BI 2 compra destinado a operación no grabada
                'col_17' =>($estado =='11') ? '' : $igv2, //IGV 2
                'col_18' =>($estado =='11') ? '' : $basimp3, //BI 3 compra destinado a exportación
                'col_19' =>($estado =='11') ? '' : $igv3,//IGV 3
                'col_20' =>($estado =='11') ? '' : $total_no_grabado,
                'col_21' =>($row->total_isc>0 && $estado !='11') ? $row->total_isc : '',
                'col_22' =>'0.00',
                'col_23' =>'',
                'col_24' =>($estado =='11' ) ? '' : $total,
                'col_25' =>($estado =='11' ) ? '' : $row->currency_type_id,
                'col_26' =>($tc <1 || $estado =='11' || $row->currency_type_id =='PEN') ? '' : $tc,
                'col_27' =>$fechaMod,
                'col_28' =>$tipoMod,
                'col_29' =>$seriesMod,
                'col_30' =>'',
                'col_31' =>$numberMod,
                'col_32' =>'',
                'col_33' =>'',
                'col_34' =>'',
                'col_35' =>'',
                'col_36' =>'',
                'col_37' =>'',
                'col_38' =>'',
                'col_39' =>'',
                'col_40' =>'',
                'col_41' =>'',
                'col_42' =>($estado=='11') ? '2' : '1',
                'col_43' =>''
            ];




        }
        return $rows;
    }
    private function getStructureComprasND($documents)
    {

        $company_account = CompanyAccount::first();
        $rows = [];
        foreach ($documents as $index => $row)
        {

            $date_of_issue = Carbon::parse($row->date_of_issue);
            $currency_type_id = ($row->currency_type_id === 'PEN')?'S':'D';
            $detail = substr($row->supplier->name.',  '.$row->number_full, 0, 60);

            $number_index = $date_of_issue->format('m').str_pad($index + 1, 4, "0", STR_PAD_LEFT);
            $regimen="";

            if($regimen=='RER'){
                $number_index="M-RER";
            }

            foreach ($row->items as $item) {
               $fechaMod  = '';
               $seriesMod = '';
               $numberMod = '';
               $tipoMod   = '';
                if($row->document_type_id=='07' || $row->document_type_id=='08' ){
                    $seriesMod = $row->note->affected_document->series;
                    $numberMod = $row->note->affected_document->number;
                    $fechaMod= str_pad($row->note->affected_document->date_of_issue->format('d/m/yy'), 8,'00', STR_PAD_RIGHT);
                    $tipoMod =  $row->note->affected_document->document_type_id;
                }

                $type_basimp= $row->type_basimp;
                $estado=$row->state_type_id;
                $tc    = $row->exchange_rate_sale;
                $total_taxed = $row->total_taxed;
                $total_igv =$row->total_igv;
                $total_free =$row->total_free;
                $total_exportation = $row->total_exportation;
                $total_exonerated  = $row->total_exonerated;
                $total_unaffected  = $row->total_unaffected;
                $total             = $row->total;

                if($row->currency_type_id == 'USD'){
                    $total_taxed = $total_taxed*$tc;
                    $total_igv   = $total_igv *$tc;
                    $total_exportation =$total_exportation*$tc;
                    $total_exonerated  = $total_exonerated *$tc;
                    $total_unaffected  = $total_unaffected *$tc;
                    $total       = $total *$tc;
                }

                $basimp1="";
                $basimp2="";
                $basimp3="";
                $igv1   ="";
                $igv2   ="";
                $igv3   ="";
                $total_no_grabado   = $total_exonerated+$total_unaffected;
                if($type_basimp=='01'){ //credito fizcal
                    $basimp1 = $total_taxed;
                    $igv1    = $total_igv;
                }
                elseif($type_basimp=='02') // prorrata
                {
                    $basimp2 = $total_exportation+$total_free+$total_taxed;
                    $igv2    = $total_igv;
                }
                elseif($type_basimp=='03') // costo/gasto
                {
                    $basimp3 = $total_exportation+$total_free+$total_taxed;
                    $igv3    = $total_igv;
                }

                $rows[] = [
                    'col_1' => str_pad($row->date_of_issue->format('yym'), 8,'00', STR_PAD_RIGHT),
                    'col_2' =>$number_index,
                    'col_3' =>'M'.$number_index,
                    'col_4' =>$row->date_of_issue->format('d/m/yy'),
                    'col_5' =>'',
                    'col_6' =>$row->document_type_id,
                    'col_7' =>$row->series,
                    'col_8' =>'',
                    'col_9' =>$row->number,
                    'col_10' =>'',
                    'col_11'=>($estado =='11' ) ? '' : $row->supplier->identity_document_type_id,
                    'col_12'=>($estado =='11' ) ? '' : $row->supplier->number,
                    'col_13' =>($estado =='11' ) ? '' : $detail,

                    'col_14' =>($estado =='11') ? ''  : $basimp1,
                    'col_15' =>($estado =='11') ? '' : $igv1,
                    'col_16' =>($estado =='11')  ? '' : $basimp2, //BI 2 compra destinado a operación no grabada
                    'col_17' =>($estado =='11') ? '' : $igv2, //IGV 2
                    'col_18' =>($estado =='11') ? '' : $basimp3, //BI 3 compra destinado a exportación
                    'col_19' =>($estado =='11') ? '' : $igv3,//IGV 3
                    'col_20' =>($estado =='11') ? '' : $total_no_grabado,
                    'col_21' =>($row->total_isc>0 && $estado !='11') ? $row->total_isc : '',
                    'col_22' =>'',
                    'col_23' =>($estado =='11' ) ? '' : $total,
                    'col_24' =>($estado =='11' ) ? '' : $row->currency_type_id,
                    'col_25' =>($tc >1 && $estado !='11') ? $tc :  '',
                    'col_26' =>$fechaMod,
                    'col_27' =>$tipoMod,
                    'col_28' =>$seriesMod,
                    'col_29' =>'',
                    'col_30' =>$numberMod,
                    'col_31' =>'',
                    'col_32' =>'',
                    'col_33' =>'',
                    'col_34' =>'',
                    'col_35' =>'',
                    'col_36' =>'',
                    'col_37' =>'',
                    'col_38' =>'',
                    'col_39' =>'',
                    'col_40' =>'',
                    'col_41' =>($estado=='11') ? '2' : '1',
                    'col_42' =>''
                ];

            }


        }
        return $rows;
    }
    private function getStructureVentas($documents)
    {
        $company_account = CompanyAccount::first();
        $rows = [];
        foreach ($documents as $index => $row)
        {
            $date_of_issue = Carbon::parse($row->date_of_issue);
            $currency_type_id = ($row->currency_type_id === 'PEN')?'S':'D';
            $detail = $row->customer->name;

            $number_index = $date_of_issue->format('m').str_pad($index + 1, 4, "0", STR_PAD_LEFT);
            $regimen="";

            if($regimen=='RER'){
                $number_index="M-RER";
            }

            foreach ($row->items as $item) {
                $estado=$row->state_type_id;
                $tc    = $row->exchange_rate_sale;
                $total_taxed = $row->total_taxed;
                $total_igv =$row->total_igv;
                $total_icbper=$row->total_plastic_bag_taxes;
                $total_exportation = $row->total_exportation;
                $total_exonerated  = $row->total_exonerated;
                $total_unaffected  = $row->total_unaffected;
                $total             = $row->total;

                if($row->currency_type_id == 'USD'){
                    $total_taxed = $total_taxed*$tc;
                    $total_igv   = $total_igv *$tc;
                    $total_icbper= $total_icbper*$tc;
                    $total_exportation =$total_exportation*$tc;
                    $total_exonerated  = $total_exonerated *$tc;
                    $total_unaffected  = $total_unaffected *$tc;
                    $total             = $total *$tc;
                }

                /*================NOTAS DE CRÉDITO DEBITO===============*/
                $fechaMod  = '';
                $seriesMod = '';
                $numberMod = '';
                $tipoMod   = '';
                if($row->document_type_id=='07' || $row->document_type_id=='08'){
                    $seriesMod = $row->note->affected_document->series;
                    $numberMod = $row->note->affected_document->number;
                    $fechaMod= str_pad($row->note->affected_document->date_of_issue->format('d/m/yy'), 8,'00', STR_PAD_RIGHT);
                    $tipoMod =  $row->note->affected_document->document_type_id;
                }

                 /*================CAMBIO SIGNOS==================*/
                if($row->document_type_id=='07'){ //nota de crédito
                    $total_taxed       = $total_taxed       * -1;
                    $total_igv         = $total_igv         * -1;
                    $total_exportation = $total_exportation * -1;
                    $total_exonerated  = $total_exonerated  * -1;
                    $total_unaffected  = $total_unaffected  * -1;
                    $total             = $total             * -1;
                }


                $rows[] = [
                    'col_1' => str_pad($row->date_of_issue->format('yym'), 8,'00', STR_PAD_RIGHT),
                    'col_2' =>$number_index,
                    'col_3' =>'M'.$number_index,
                    'col_4' =>$row->date_of_issue->format('d/m/yy'),
                    'col_5' =>'',
                    'col_6' =>$row->document_type_id,
                    'col_7' =>$row->series,
                    'col_8' =>$row->number,
                    'col_9' =>'',
                    'col_10'=>($estado =='11' ) ? '' : $row->customer->identity_document_type_id,
                    'col_11'=>($estado =='11' ) ? '' : $row->customer->number,
                    'col_12' =>($estado =='11' ) ? '' : $detail,
                    'col_13' =>($total_exportation>0 && $estado !='11') ? $total_exportation : '',
                    'col_14' =>($estado =='11') ? '' : $total_taxed,
                    'col_15' =>'',//para nota revisar
                    'col_16' =>($estado =='11' ) ? '' : $total_igv,
                    'col_17' =>'',//para nota revisar
                    'col_18' =>($total_exonerated>0 && $estado !='11') ? $total_exonerated : '',
                    'col_19' =>($total_unaffected>0 && $estado !='11') ? $total_unaffected : '',
                    'col_20' =>($row->total_isc>0 && $estado !='11') ? $row->total_isc : '',
                    'col_21' =>'',
                    'col_22' =>'',
                    'col_23' =>($total_icbper>0 && $estado !='11')? $total_icbper:'0.00',
                    'col_24' =>'',
                    'col_25' =>($estado =='11' ) ? '' : $total,
                    'col_26' =>($estado =='11' || $row->currency_type_id =='PEN') ? '' : $row->currency_type_id,
                    'col_27' =>($tc <1 || $estado =='11' || $row->currency_type_id =='PEN') ? '' : $tc,
                    'col_28' =>$fechaMod,
                    'col_29' =>$tipoMod,
                    'col_30' =>$seriesMod,
                    'col_31' =>$numberMod,
                    'col_32' =>'',
                    'col_33' =>'',
                    'col_34' =>'',
                    'col_35' =>($estado=='11') ? '2' : '1',
                    'col_36' =>''
                ];

            }


        }
        return $rows;
    }

}
