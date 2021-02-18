<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Person;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Establishment;
use App\CoreFacturalo\Requests\Inputs\Common\LegendInput;
use App\Models\Tenant\Item;
use App\Models\Tenant\Series;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\DocumentType;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Template;
use Modules\Item\Models\Category;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenant\PaymentMethodType;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;
use Modules\Order\Http\Resources\OrderNoteCollection;
use Modules\Order\Http\Resources\OrderNoteDocumentCollection;
use Modules\Order\Http\Resources\OrderNoteResource;
use Modules\Order\Http\Resources\OrderNoteResource2;
use Modules\Order\Http\Requests\OrderNoteRequest;
use Modules\Order\Mail\OrderNoteEmail;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Configuration;
use App\Http\Controllers\Tenant\SaleNoteController;
use App\CoreFacturalo\Requests\Inputs\DocumentInput;
use App\CoreFacturalo\Requests\Web\Validation\DocumentValidation;
use App\Http\Requests\Tenant\SaleNoteRequest;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Controllers\Tenant\DocumentController;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;


class OrderNoteController extends Controller
{

    use StorageDocument, FinanceTrait;

    protected $order_note;
    protected $company;

    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;

        return view('order::order_notes.index', compact('soap_company'));
    }


    public function create()
    {
        return view('order::order_notes.form');
    }

    public function edit($id)
    {
        $resourceId = $id;
        return view('order::order_notes.form_edit', compact('resourceId'));
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión',
            'delivery_date' => 'Fecha de entrega',
            'user_name' => 'Vendedor'
        ];
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new OrderNoteCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request){

        if($request->column == 'user_name'){

            $records = OrderNote::whereHas('user', function($query) use($request){
                            $query->where('name', 'like', "%{$request->value}%");
                        })
                        ->whereTypeUser()
                        ->latest();

        }else{

            $records = OrderNote::where($request->column, 'like', "%{$request->value}%")
                                ->whereTypeUser()
                                ->latest();

        }

        return $records;
    }


    public function documents(Request $request)
    {

        $records = OrderNote::doesntHave('documents')
                            ->doesntHave('sale_notes')
                            ->where('state_type_id', '01')
                            ->whereTypeUser()
                            ->latest();

        return new OrderNoteDocumentCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function document_tables()
    {
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id',$establishment->id)->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();

        return compact('series', 'establishment');
    }


    public function generateDocuments(Request $request) {

        DB::connection('tenant')->transaction(function () use ($request) {

            foreach ($request->documents as $row) {

                if($row['document_type_id'] === "80"){

                    app(SaleNoteController::class)->store(new SaleNoteRequest($row));

                }else{

                    $data_val = DocumentValidation::validation($row);

                    app(DocumentController::class)->store(new DocumentRequest(DocumentInput::set($data_val)));

                }

            }

        });

        return [
            'success' => true,
            'message' => 'Comprobantes generados'
        ];

    }


    public function searchCustomers(Request $request)
    {

        $customers = Person::where('number','like', "%{$request->input}%")
                            ->orWhere('name','like', "%{$request->input}%")
                            ->whereType('customers')->orderBy('name')
                            ->get()->transform(function($row) {
                                return [
                                    'id' => $row->id,
                                    'description' => $row->number.' - '.$row->name,
                                    'name' => $row->name,
                                    'number' => $row->number,
                                    'identity_document_type_id' => $row->identity_document_type_id,
                                    'identity_document_type_code' => $row->identity_document_type->code
                                ];
                            });

        return compact('customers');
    }

    public function tables() {

        $customers = $this->table('customers');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $currency_types = CurrencyType::whereActive()->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $company = Company::active();
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $payment_method_types = PaymentMethodType::orderBy('id','desc')->get();

        return compact('customers', 'establishments','currency_types', 'discount_types', 'charge_types','company', 'document_type_03_filter','payment_method_types');
    }

    public function option_tables()
    {
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id',$establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();

        return compact('series', 'document_types_invoice', 'payment_method_types', 'payment_destinations');
    }

    public function item_tables() {
        $items = $this->table('items');
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();

        return compact('items', 'categories', 'affectation_igv_types', 'system_isc_types', 'price_types', 'discount_types', 'charge_types', 'attribute_types');
    }

    public function record($id)
    {
        $record = new OrderNoteResource(OrderNote::findOrFail($id));

        return $record;
    }

    public function record2($id)
    {
        $record = new OrderNoteResource(OrderNote::findOrFail($id));

        return $record;
    }


    public function getFullDescription($row){

        $desc = ($row->internal_id)?$row->internal_id.' - '.$row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    public function store(OrderNoteRequest $request) {

        DB::connection('tenant')->transaction(function () use ($request) {
            $data = $this->mergeData($request);

            $this->order_note =  OrderNote::create($data);

            foreach ($data['items'] as $row) {
                $this->order_note->items()->create($row);
            }

            $this->setFilename();
            $this->createPdf($this->order_note, "a4", $this->order_note->filename);

        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->order_note->id,
                'external_id' => $this->order_note->external_id,
				'number' => $this->order_note->id,
            ],
        ];
    }

    public function update(OrderNoteRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {

            $data = $this->mergeData($request);

            $this->order_note = OrderNote::firstOrNew(['id' => $request['id']]);
            $this->order_note->fill($data);
            //$this->order_note->items()->delete();

            foreach ($request['items'] as $row) {

                // $this->order_note->items()->create($row);
                $item_id = isset($row['id']) ? $row['id'] : null;
                $order_note_item = OrderNoteItem::firstOrNew(['id' => $item_id]);
                $order_note_item->fill($row);
                $order_note_item->order_note_id = $this->order_note->id;
                $order_note_item->save();

            }

            $this->setFilename();
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->order_note->id,
            ],
        ];

    }


    public function destroy_order_note_item($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $item = OrderNoteItem::findOrFail($id);
            $item->delete();

        });

        return [
            'success' => true,
            'message' => 'Item eliminado'
        ];
    }


    public function duplicate(Request $request)
    {
       // return $request->id;
       $obj = OrderNote::find($request->id);
       $this->order_note = $obj->replicate();
       $this->order_note->external_id = Str::uuid()->toString();
       $this->order_note->state_type_id = '01' ;
       $this->order_note->save();

       foreach($obj->items as $row)
       {
         $new = $row->replicate();
         $new->order_note_id = $this->order_note->id;
         $new->save();
       }

        $this->setFilename();

        return [
            'success' => true,
            'data' => [
                'id' => $this->order_note->id,
            ],
        ];

    }

    public function voided($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $obj =  OrderNote::find($id);
            $obj->state_type_id = '11';
            $obj->update();

        });

        return [
            'success' => true,
            'message' => 'Pedido anulado con éxito'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $values = [
            'user_id' => auth()->id(),
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($inputs['customer_id']),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01'
        ];

        $inputs->merge($values);

        return $inputs->all();
    }



    private function setFilename(){

        $name = [$this->order_note->prefix,$this->order_note->id,date('Ymd')];
        $this->order_note->filename = join('-', $name);
        $this->order_note->save();

    }


    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number.' - '.$row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $customers;

                break;

            case 'items':

                $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

                $items = Item::orderBy('description')->whereIsActive()->whereNotIsSet()
                    // ->with(['warehouses' => function($query) use($warehouse){
                    //     return $query->where('warehouse_id', $warehouse->id);
                    // }])
                    ->get()->transform(function($row) use($warehouse){
                    $full_description = $this->getFullDescription($row);
                    // $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;
                    return [
                        'id' => $row->id,
                        'category_id'=> $row->category_id,
                        'full_description' => $full_description,
                        'description' => $row->description,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => $row->sale_unit_price,
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'is_set' => (bool) $row->is_set,
                        'has_igv' => (bool) $row->has_igv,
                        'calculate_quantity' => (bool) $row->calculate_quantity,
                        'item_unit_types' => collect($row->item_unit_types)->transform(function($row) {
                            return [
                                'id' => $row->id,
                                'description' => "{$row->description}",
                                'item_id' => $row->item_id,
                                'unit_type_id' => $row->unit_type_id,
                                'quantity_unit' => $row->quantity_unit,
                                'price1' => $row->price1,
                                'price2' => $row->price2,
                                'price3' => $row->price3,
                                'price_default' => $row->price_default,
                            ];
                        }),
                        'warehouses' => collect($row->warehouses)->transform(function($row) use($warehouse){
                            return [
                                'warehouse_id' => $row->warehouse->id,
                                'warehouse_description' => $row->warehouse->description,
                                'stock' => $row->stock,
                                'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                            ];
                        }),
                        'lots' => collect($row->item_lots->where('has_sale', false))->transform(function($row) {
                            return [
                                'id' => $row->id,
                                'series' => $row->series,
                                'date' => $row->date,
                                'item_id' => $row->item_id,
                                'warehouse_id' => $row->warehouse_id,
                                'has_sale' => (bool)$row->has_sale,
                                'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                            ];
                        })->values(),
                        'series_enabled' => (bool) $row->series_enabled,
                    ];
                });
                return $items;

                break;
            default:
                return [];

                break;
        }
    }

    public function searchCustomerById($id)
    {

        $customers = Person::whereType('customers')
                    ->where('id',$id)
                    ->get()->transform(function($row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->number.' - '.$row->name,
                            'name' => $row->name,
                            'number' => $row->number,
                            'identity_document_type_id' => $row->identity_document_type_id,
                            'identity_document_type_code' => $row->identity_document_type->code
                        ];
                    });

        return compact('customers');
    }

    public function download($external_id, $format) {
        $order_note = OrderNote::where('external_id', $external_id)->first();

        if (!$order_note) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        $this->reloadPDF($order_note, $format, $order_note->filename);

        return $this->downloadStorage($order_note->filename, 'order_note');
    }

    public function toPrint($external_id, $format) {
        $order_note = OrderNote::where('external_id', $external_id)->first();

        if (!$order_note) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        $this->reloadPDF($order_note, $format, $order_note->filename);
        $temp = tempnam(sys_get_temp_dir(), 'order_note');

        file_put_contents($temp, $this->getStorage($order_note->filename, 'order_note'));

        return response()->file($temp);
    }

    private function reloadPDF($order_note, $format, $filename) {
        $this->createPdf($order_note, $format, $filename);
    }

    public function createPdf($order_note = null, $format_pdf = null, $filename = null) {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($order_note != null) ? $order_note : $this->order_note;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->order_note->filename;

        // $base_template = config('tenant.pdf_template');
        $base_template = Configuration::first()->formats;

        $html = $template->pdf($base_template, "order_note", $company, $document, $format_pdf);

        if ($format_pdf === 'ticket' OR $format_pdf === 'ticket_80') {

            $width = 78;
            if(config('tenant.enabled_template_ticket_80')) $width = 76;

            $company_name      = (strlen($company->name) / 20) * 10;
            $company_address   = (strlen($document->establishment->address) / 30) * 10;
            $company_number    = $document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($document->customer->address) / 200) * 10;
            $p_order           = $document->purchase_order != '' ? '10' : '0';

            $total_exportation = $document->total_exportation != '' ? '10' : '0';
            $total_free        = $document->total_free != '' ? '10' : '0';
            $total_unaffected  = $document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($document->items);
            $discount_global = 0;
            foreach ($document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends           = $document->legends != '' ? '10' : '0';

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                    ($quantity_rows * 8) +
                    ($discount_global * 3) +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $total_taxed],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else if($format_pdf === 'a5'){

             $company_name      = (strlen($company->name) / 20) * 10;
            $company_address   = (strlen($document->establishment->address) / 30) * 10;
            $company_number    = $document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($document->customer->address) / 200) * 10;
            $p_order           = $document->purchase_order != '' ? '10' : '0';

            $total_exportation = $document->total_exportation != '' ? '10' : '0';
            $total_free        = $document->total_free != '' ? '10' : '0';
            $total_unaffected  = $document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($document->items);
            $discount_global = 0;
            foreach ($document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends           = $document->legends != '' ? '10' : '0';


            $alto = ($quantity_rows * 8) +
                    ($discount_global * 3) +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $total_taxed;
            $diferencia = 148 - (float)$alto;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $alto
                    ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);


        }  else {

            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                                 DIRECTORY_SEPARATOR.'pdf'.
                                                 DIRECTORY_SEPARATOR.$base_template.
                                                 DIRECTORY_SEPARATOR.'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ]
                ]);
            }
        }

        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.$base_template.
                                             DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if ($format_pdf != 'ticket') {
            if(config('tenant.pdf_template_footer')) {
                $html_footer = $template->pdfFooter($base_template,$this->order_note);
                $pdf->SetHTMLFooter($html_footer);
            }
            //$html_footer = $template->pdfFooter();
            //$pdf->SetHTMLFooter($html_footer);
        }

        $this->uploadFile($filename, $pdf->output('', 'S'), 'order_note');
    }

    public function uploadFile($filename, $file_content, $file_type) {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function email(Request $request)
    {

        $client = Person::find($request->customer_id);
        $order_note = OrderNote::find($request->id);
        $customer_email = $request->input('customer_email');

        // $this->reloadPDF($order_note, "a4", $order_note->filename);

        Mail::to($customer_email)->send(new OrderNoteEmail($client, $order_note));
        return [
            'success' => true
        ];
    }
    public function esc(Request $request)
    {
        $note_id=$request->id;
        $data=$this->record($note_id);
        $observation= $data->observation;
        $cocina=[];
        $barra=[];
        $imp_coc='';
        $imp_bar='';
        $printerTipoConexion1='';
        $printerTipoConexion2='';
        $printerRuta1='';
        $printerRuta2='';
        $config = Configuration::get();

        foreach($config as $printers){
            $imp_coc= $printers['PrinterNombre1'];
            $imp_bar= $printers['PrinterNombre2'];
            $printerTipoConexion1= $printers['PrinterTipoConexion1'];
            $printerTipoConexion2= $printers['PrinterTipoConexion2'];
            $printerRuta1= $printers['PrinterRuta1'];
            $printerRuta2= $printers['PrinterRuta2'];
        }

        foreach ($data['items'] as $row) {
            $categoria=Category::find($row->item->category_id);
            if ($imp_coc != '' && $imp_coc != '-' &&  $categoria->printer==$imp_coc) {//COCINA
                $data =[

                    'quantity'=> $row->quantity,
                    'printer' =>$row->printer,
                    'description'=> $row->item->description

                ];
                array_push($cocina,$data);

            }
            else if ($imp_bar != '' && $imp_bar != '-' &&  $categoria->printer==$imp_bar) {//BARRA
                $array=[
                    'items'=>[
                        'quantity'=> $row->quantity,
                        'printer' =>$row->printer,
                        'description'=> $row->item->description
                    ]
                ];
                array_push($barra,$array);
            }
        }

        if(!empty($cocina)){
            $this->toPrintEsc($cocina,$note_id,$imp_coc,$printerTipoConexion1,$printerRuta1,$observation);
        }
        if(!empty($barra)){
            $this->toPrintEsc($barra,$note_id,$imp_bar,$printerTipoConexion2,$printerRuta2,$observation);
        }
    }
    public function toPrintEsc($data,$note_id,$printer,$tipo,$ruta,$observation)
    {
        //$logo = EscposImage::load("resources/rawbtlogo.png", false);
        //$logo =  Storage::disk('tenant')->get(storage_path('public/uploads/logos/logo_20601411076.png'));
        //$logo =EscposImage::load(public_path("storage/uploads/logos/aqpfact.jpg"));

        /* Start the printer */

        $connector = null;
        if($tipo=="USB"){
            $connector = new WindowsPrintConnector($printer);
        }
        else if($tipo=="RED"){
            $connector = new NetworkPrintConnector($ruta, 9100);
        }
        /* Print a "Hello world" receipt" */
        $printer = new Printer($connector);

        /* Print top logo */
//        $profile = CapabilityProfile::load("simple");
//        if ($profile->getSupportsGraphics()) {
//           $printer->graphics($logo);
//        }
//        if ($profile->getSupportsBitImageRaster() && !$profile->getSupportsGraphics()) {
//            $printer->bitImage($logo);
//        }

        try {

            $date = date('Y-m-d H:i:s A');

            /* Name of shop */
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text("Pedido No. PD-".$note_id."\n");
            $printer->feed();


            /* Title of receipt */
            $printer->selectPrintMode();
            $printer->setEmphasis(true);
            $printer->text($date."\n");
            $printer->setEmphasis(false);

            /* Items */
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis(true);

//            foreach ($items as $item) {
//                $printer->text($item->getAsString(32)); // for 58mm Font A
//            }

            $printer->feed();
            $printer->setEmphasis(true);
            $printer->text("CANT. DESCRIPCION\n");
            $printer->setEmphasis(false);
            $printer->text("-------------------------------\n");


            foreach ($data as $row) {
                $printer->text(' '.round($row['quantity'],2).'  '.substr($row['printer'],0,27).' '.$row['description']."\n");
            }
            // $printer->text($subtotal->getAsString(32));
            $printer->feed();

            /* Tax and total */
            //$printer->text($tax->getAsString(32));
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            // $printer->text($total->getAsString(32));
            $printer->selectPrintMode();

            /* Footer */
//            $printer->feed(2);
//            $printer->setJustification(Printer::JUSTIFY_CENTER);
//            $printer->text("Gracias campeón shopping\n");
//            $printer->feed(2);
            $printer->text("$observation\n");
            $printer->feed(3);

            /* Barcode Default look */

//            $printer->barcode("ABC", Printer::BARCODE_CODE39);
//            $printer->feed();
//            $printer->feed();


            // Demo that alignment QRcode is the same as text
//            $printer2 = new Printer($connector); // dirty printer profile hack !!
//            $printer2->setJustification(Printer::JUSTIFY_CENTER);
//            $printer2->qrCode("https://rawbt.ru/mike42", Printer::QR_ECLEVEL_M, 8);
//            $printer2->text("rawbt.ru/mike42\n");
//            $printer2->setJustification();
//            $printer2->feed();


            /* Cut the receipt and open the cash drawer */
            $printer->cut();
            $printer->pulse();

        } catch (Exception $e) {
            $printer->close();
            //echo $e->getMessage();
        } finally {
            $printer->close();
        }
    }
}
