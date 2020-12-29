<?php
namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\WS\Zip\ZipFly;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentEmailRequest;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Requests\Tenant\DocumentVoidedRequest;
use App\Http\Resources\Tenant\DocumentCollection;
use App\Http\Resources\Tenant\DocumentResource;
use App\Mail\Tenant\DocumentEmail;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\NoteCreditType;
use App\Models\Tenant\Catalogs\NoteDebitType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\DetractionType;
use App\Models\Tenant\Catalogs\PaymentMethodType as CatPaymentMethodType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\StateType;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Nexmo\Account\Price;
use Illuminate\Support\Facades\Cache;
use App\Imports\DocumentsImport;
use App\Imports\DocumentsImportTwoFormat;
use Maatwebsite\Excel\Excel;
use Modules\BusinessTurn\Models\BusinessTurn;
use App\Exports\PaymentExport;
use Modules\Item\Models\Category;
use Modules\Item\Http\Requests\CategoryRequest;
use Modules\Item\Http\Requests\BrandRequest;
use Modules\Item\Models\Brand;
use Carbon\Carbon;
use App\Traits\OfflineTrait;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use Modules\Finance\Traits\FinanceTrait;

use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class DocumentController extends Controller
{
    use StorageDocument, OfflineTrait, FinanceTrait;
    private $max_count_payment = 0;

    public function __construct()
    {

        $this->middleware('input.request:document,web', ['only' => ['store']]);
    }

    public function index()
    {
        $is_client = $this->getIsClient();
        $import_documents = config('tenant.import_documents');
        $import_documents_second = config('tenant.import_documents_second_format');

        return view('tenant.documents.index', compact('is_client','import_documents','import_documents_second'));
    }

    public function columns()
    {
        return [
            'number' => 'Número',
            'date_of_issue' => 'Fecha de emisión'
        ];
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function searchCustomers(Request $request)
    {

        //tru de boletas en env esta en true filtra a los con dni   , false a todos
        $identity_document_type_id = $this->getIdentityDocumentTypeId($request->document_type_id, $request->operation_type_id);
//        $operation_type_id_id = $this->getIdentityDocumentTypeId($request->operation_type_id);

        $customers = Person::where('number','like', "%{$request->input}%")
                            ->orWhere('name','like', "%{$request->input}%")
                            ->whereType('customers')->orderBy('name')
                            ->whereIn('identity_document_type_id',$identity_document_type_id)
                            ->whereIsEnabled()
                            ->get()->transform(function($row) {
                                return [
                                    'id' => $row->id,
                                    'description' => $row->number.' - '.$row->name,
                                    'name' => $row->name,
                                    'number' => $row->number,
                                    'identity_document_type_id' => $row->identity_document_type_id,
                                    'identity_document_type_code' => $row->identity_document_type->code,
                                    'addresses' => $row->addresses,
                                    'address' =>  $row->address
                                ];
                            });

        return compact('customers');
    }


    public function create()
    {
        if(auth()->user()->type == 'integrator')
            return redirect('/documents');

        $configuration = Configuration::first();
        $is_contingency = 0;
        return view('tenant.documents.form', compact('is_contingency', 'configuration'));
    }

    public function create_tensu()
    {
        if(auth()->user()->type == 'integrator')
            return redirect('/documents');

        $is_contingency = 0;
        return view('tenant.documents.form_tensu', compact('is_contingency'));
    }


    public function tables()
    {
        $customers = $this->table('customers');
        // $prepayment_documents = $this->table('prepayment_documents');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();// Establishment::all();
        $series = collect(Series::all())->transform(function($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        $document_types_note = DocumentType::whereIn('id', ['07', '08'])->get();
        $note_credit_types = NoteCreditType::whereActive()->orderByDescription()->get();
        $note_debit_types = NoteDebitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $company = Company::active();
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $user = auth()->user()->type;
        $sellers = User::whereIn('type', ['seller'])->orWhere('id', auth()->user()->id)->get();
        $payment_method_types = $this->table('payment_method_types');
        $business_turns = BusinessTurn::where('active', true)->get();
        $enabled_discount_global = config('tenant.enabled_discount_global');
        $is_client = $this->getIsClient();
        $select_first_document_type_03 = config('tenant.select_first_document_type_03');

        $document_types_guide = DocumentType::whereIn('id', ['09', '31'])->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'active' => (bool) $row->active,
                'short' => $row->short,
                'description' => ucfirst(mb_strtolower($row->description)),
            ];
        });
        // $cat_payment_method_types = CatPaymentMethodType::whereActive()->get();
        // $detraction_types = DetractionType::whereActive()->get();

//        return compact('customers', 'establishments', 'series', 'document_types_invoice', 'document_types_note',
//                       'note_credit_types', 'note_debit_types', 'currency_types', 'operation_types',
//                       'discount_types', 'charge_types', 'company', 'document_type_03_filter',
//                       'document_types_guide');

        // return compact('customers', 'establishments', 'series', 'document_types_invoice', 'document_types_note',
        //                'note_credit_types', 'note_debit_types', 'currency_types', 'operation_types',
        //                'discount_types', 'charge_types', 'company', 'document_type_03_filter');

        $payment_destinations = $this->getPaymentDestinations();

        return compact( 'customers','establishments', 'series', 'document_types_invoice', 'document_types_note',
                        'note_credit_types', 'note_debit_types', 'currency_types', 'operation_types',
                        'discount_types', 'charge_types', 'company', 'document_type_03_filter',
                        'document_types_guide', 'user', 'sellers','payment_method_types','enabled_discount_global',
                        'business_turns','is_client','select_first_document_type_03', 'payment_destinations');

    }

    public function item_tables()
    {
        $items = $this->table('items');
        $categories = [];//Category::cascade();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $is_client = $this->getIsClient();

        return compact('items', 'categories', 'affectation_igv_types', 'system_isc_types', 'price_types',
                       'operation_types', 'discount_types', 'charge_types', 'attribute_types','is_client');
    }

    public function table($table)
    {
        if ($table === 'customers') {
            $customers = Person::with('addresses')->whereType('customers')->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number.' - '.$row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'addresses' => $row->addresses,
                    'address' =>  $row->address,


                ];
            });
            return $customers;
        }

        if ($table === 'prepayment_documents') {
            $prepayment_documents = Document::whereHasPrepayment()->get()->transform(function($row) {

                $total = round($row->pending_amount_prepayment, 2);
                $amount = ($row->affectation_type_prepayment == '10') ? round($total/1.18, 2) : $total;

                return [
                    'id' => $row->id,
                    'description' => $row->series.'-'.$row->number,
                    'series' => $row->series,
                    'number' => $row->number,
                    'document_type_id' => ($row->document_type_id == '01') ? '02':'03',
                    // 'amount' => $row->total_value,
                    // 'total' => $row->total,
                    'amount' => $amount,
                    'total' => $total,

                ];
            });
            return $prepayment_documents;
        }

        if ($table === 'payment_method_types') {

            $payment_method_types = PaymentMethodType::whereNotIn('id', ['05', '08', '09'])->get();
            $end_payment_method_types = PaymentMethodType::whereIn('id', ['05', '08', '09'])->get(); //by requirement

            return $payment_method_types->merge($end_payment_method_types);
        }

        if ($table === 'items') {

            $establishment_id = auth()->user()->establishment_id;
            $warehouse = ModuleWarehouse::where('establishment_id', $establishment_id)->first();

            // $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();
            $items_u = Item::whereWarehouse()->whereIsActive()->orderBy('description')->take(20)->get();
            $items_s = Item::where('unit_type_id','ZZ')->whereIsActive()->orderBy('description')->take(10)->get();
            $items = $items_u->merge($items_s);

            return collect($items)->transform(function($row) use($warehouse){
                $detail = $this->getFullDescription($row, $warehouse);
                return [
                    'id' => $row->id,
                    'full_description' => $detail['full_description'],
                    'brand' => $detail['brand'],
                    'category' => $detail['category'],
                    'stock' => $detail['stock'],
                    'internal_id' => $row->internal_id,
                    'description' => $row->description,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => number_format($row->sale_unit_price, 4, ".",""),
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'calculate_quantity' => (bool) $row->calculate_quantity,
                    'has_igv' => (bool) $row->has_igv,
                    'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,
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
                            'warehouse_description' => $row->warehouse->description,
                            'stock' => $row->stock,
                            'warehouse_id' => $row->warehouse_id,
                            'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                        ];
                    }),
                    'attributes' => $row->attributes ? $row->attributes : [],
                    'lots_group' => collect($row->lots_group)->transform(function($row){
                        return [
                            'id'  => $row->id,
                            'code' => $row->code,
                            'quantity' => $row->quantity,
                            'date_of_due' => $row->date_of_due,
                            'checked'  => false
                        ];
                    }),
                    'lots' => [],
                    // 'lots' => $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse->id)->transform(function($row) {
                    //     return [
                    //         'id' => $row->id,
                    //         'series' => $row->series,
                    //         'date' => $row->date,
                    //         'item_id' => $row->item_id,
                    //         'warehouse_id' => $row->warehouse_id,
                    //         'has_sale' => (bool)$row->has_sale,
                    //         'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                    //     ];
                    // })->values(),
                    'lots_enabled' => (bool) $row->lots_enabled,
                    'series_enabled' => (bool) $row->series_enabled,

                ];
            });
//            return $items;
        }

        return [];
    }

    public function getFullDescription($row, $warehouse){

        $desc = ($row->internal_id)?$row->internal_id.' - '.$row->description : $row->description;
        $category = ($row->category) ? "{$row->category->name}" : "";
        $brand = ($row->brand) ? "{$row->brand->name}" : "";



        if($row->unit_type_id != 'ZZ')
        {
            $warehouse_stock = ($row->warehouses && $warehouse) ? number_format($row->warehouses->where('warehouse_id', $warehouse->id)->first()->stock,2) : 0;
            $stock = ($row->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        }
        else{
            $stock = '';
        }

        $desc = "{$desc} - {$brand}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
        ];
    }


    public function record($id)
    {
        $record = new DocumentResource(Document::findOrFail($id));

        return $record;
    }

    public function store(DocumentRequest $request)
    {
        DB::beginTransaction();
        try{
            $response='';
            $fact = DB::connection('tenant')->transaction(function () use ($request) {
                $facturalo = new Facturalo();
                $facturalo->save($request->all());
                $facturalo->createXmlUnsigned();
                $facturalo->signXmlUnsigned();
                $facturalo->updateHash();
                $facturalo->updateQr();
                $facturalo->createPdf();
                $facturalo->senderXmlSignedBill();

                return $facturalo;
            });

            $document = $fact->getDocument();
            $response = $fact->getResponse();

            DB::commit();
            return [
                'success' => true,
                'data' => [
                    'id' => $document->id,
                    'response' =>$response

                ],
            ];
        }
        catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'status'=>422,
                'data' => [
                    'id' => '',
                    'message'=>'Ocurrió un error en el registro; falta el certificado, no hay conexión con la SUNAT, o probablemente ya no tiene stock en uno o varios de los productos que está intentando vender. \n Deshabilite el envío automático a la SUNAT, Deshabilite el control de stock en Configuración -> Inventarios o sino agregue stock a los productos.',
                    'response' =>[
                        'code'=> "8",
                        'description' =>'Ocurrió un error en el registro; falta el certificado, no hay conexión con la SUNAT, o probablemente ya no tiene stock en uno o varios de los productos que está intentando vender. \n Deshabilite el envío automático a la SUNAT, Deshabilite el control de stock en Configuración -> Inventarios o sino agregue stock a los productos.',
                        'sent'=> false,

                    ]

                ],
            ];
        }

    }

    public function reStore($document_id)
    {
        $fact = DB::connection('tenant')->transaction(function () use ($document_id) {
            $document = Document::find($document_id);

            $type = 'invoice';
            if($document->document_type_id === '07') {
                $type = 'credit';
            }
            if($document->document_type_id === '08') {
                $type = 'debit';
            }

            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->setType($type);
            $facturalo->createXmlUnsigned();
            $facturalo->signXmlUnsigned();
            $facturalo->updateHash();
            $facturalo->updateQr();
            $facturalo->updateSoap('02', $type);
            $facturalo->updateState('01');
            $facturalo->createPdf($document, $type, 'ticket');
//            $facturalo->senderXmlSignedBill();
        });

//        $document = $fact->getDocument();
//        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => 'El documento se volvio a generar.',
        ];
    }

    public function email(DocumentEmailRequest $request)
    {
        $company = Company::active();
        $document = Document::find($request->input('id'));
        $customer_email = $request->input('customer_email');

        Mail::to($customer_email)->send(new DocumentEmail($company, $document));

        return [
            'success' => true
        ];
    }

    public function send($document_id) {
        $document = Document::find($document_id);

        $fact = DB::connection('tenant')->transaction(function () use ($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->loadXmlSigned();
            $facturalo->onlySenderXmlSignedBill();
            return $facturalo;
        });

        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => $response['description'],
        ];
    }

    public function consultCdr($document_id)
    {
        $document = Document::find($document_id);

        $fact = DB::connection('tenant')->transaction(function () use ($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->consultCdr();
        });

        $response = $fact->getResponse();

        return [
            'success' => true,
            'message' => $response['description'],
        ];
    }

    public function sendServer($document_id, $query = false) {
        $document = Document::find($document_id);
        // $bearer = config('tenant.token_server');
        // $api_url = config('tenant.url_server');
        $bearer = $this->getTokenServer();
        $api_url = $this->getUrlServer();
        $client = new Client(['base_uri' => $api_url, 'verify' => false]);

       // $zipFly = new ZipFly();
        if(!$document->data_json) throw new Exception("Campo data_json nulo o inválido - Comprobante: {$document->fullnumber}");

        $data_json = (array) $document->data_json;
        $data_json['numero_documento'] = $document->number;
        $data_json['external_id'] = $document->external_id;
        $data_json['hash'] = $document->hash;
        $data_json['qr'] = $document->qr;
        $data_json['query'] = $query;
        $data_json['file_xml_signed'] = base64_encode($this->getStorage($document->filename, 'signed'));
        $data_json['file_pdf'] = base64_encode($this->getStorage($document->filename, 'pdf'));
        // dd($data_json);
        $res = $client->post('/api/documents_server', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer '.$bearer,
                'Accept' => 'application/json',
            ],
            'form_params' => $data_json
        ]);

        $response = json_decode($res->getBody()->getContents(), true);

        if ($response['success']) {
            $document->send_server = true;
            $document->save();
        }

        return $response;
    }

    public function checkServer($document_id) {
        $document = Document::find($document_id);
        $bearer = $this->getTokenServer();
        $api_url = $this->getUrlServer();

        $client = new Client(['base_uri' => $api_url, 'verify' => false]);

        $res = $client->get('/api/document_check_server/'.$document->external_id, [
            'headers' => [
                'Authorization' => 'Bearer '.$bearer,
                'Accept' => 'application/json',
            ],
        ]);

        $response = json_decode($res->getBody()->getContents(), true);

        if ($response['success']) {
            $state_type_id = $response['state_type_id'];
            $document->state_type_id = $state_type_id;
            $document->save();

            if ($state_type_id === '05') {
                $this->uploadStorage($document->filename, base64_decode($response['file_cdr']), 'cdr');
            }
        }

        return $response;
    }

    public function searchCustomerById($id)
    {

        $customers = Person::with('addresses')->whereType('customers')
                    ->where('id',$id)
                    ->get()->transform(function($row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->number.' - '.$row->name,
                            'name' => $row->name,
                            'number' => $row->number,
                            'identity_document_type_id' => $row->identity_document_type_id,
                            'identity_document_type_code' => $row->identity_document_type->code,
                            'addresses' => $row->addresses,
                            'address' =>  $row->address
                        ];
                    });

        return compact('customers');
    }

    public function getIdentityDocumentTypeId($document_type_id, $operation_type_id){

        // if($operation_type_id === '0101' || $operation_type_id === '1001') {

        if(in_array($operation_type_id, ['0101', '1001', '1004'])) {

            if($document_type_id == '01'){
                $identity_document_type_id = [6];
            }else{
                if(config('tenant.document_type_03_filter')){
                    $identity_document_type_id = [1];
                }else{
                    $identity_document_type_id = [1,4,6,7,0];
                }
            }
        } else {
            $identity_document_type_id = [1,4,6,7,0];
        }

        return $identity_document_type_id;
    }

    public function changeToRegisteredStatus($document_id)
    {
        $document = Document::find($document_id);
        if($document->state_type_id === '01') {
            $document->state_type_id = '05';
            $document->save();

            return [
                'success' => true,
                'message' => 'El estado del documento fue actualizado.',
            ];
        }
    }

    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentsImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function importTwoFormat(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new DocumentsImportTwoFormat();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function messageLockedEmission(){

        $configuration = Configuration::first();
        // $quantity_documents = Document::count();
        $quantity_documents = $configuration->quantity_documents;

        if($configuration->limit_documents !== 0 && ($quantity_documents > $configuration->limit_documents))
            return [
                'success' => false,
                'message' => 'Alcanzó el límite permitido para la emisión de comprobantes',
            ];


        return [
            'success' => true,
            'message' => '',
        ];
    }

    public function getRecords($request){


        $d_end = $request->d_end;
        $d_start = $request->d_start;
        $date_of_issue = $request->date_of_issue;
        $document_type_id = $request->document_type_id;
        $state_type_id = $request->state_type_id;
        $number = $request->number;
        $series = $request->series;
        $pending_payment = ($request->pending_payment == "true") ? true:false;
        $customer_id = $request->customer_id;
        $item_id = $request->item_id;
        $category_id = $request->category_id;


        if($d_start && $d_end){

            $records = Document::where('document_type_id', 'like', '%' . $document_type_id . '%')
                            ->where('series', 'like', '%' . $series . '%')
                            ->where('number', 'like', '%' . $number . '%')
                            ->where('state_type_id', 'like', '%' . $state_type_id . '%')
                            ->whereBetween('date_of_issue', [$d_start , $d_end])
                            ->whereTypeUser()
                            ->latest();

        }else{

            $records = Document::where('date_of_issue', 'like', '%' . $date_of_issue . '%')
                            ->where('document_type_id', 'like', '%' . $document_type_id . '%')
                            ->where('state_type_id', 'like', '%' . $state_type_id . '%')
                            ->where('series', 'like', '%' . $series . '%')
                            ->where('number', 'like', '%' . $number . '%')
                            ->whereTypeUser()
                            ->latest();
        }

        if($pending_payment){
            $records = $records->where('total_canceled', false);
        }

        if($customer_id){
            $records = $records->where('customer_id', $customer_id);
        }

        if($item_id){
            $records = $records->whereHas('items', function($query) use($item_id){
                                    $query->where('item_id', $item_id);
                                });
        }

        if($category_id){

            $records = $records->whereHas('items', function($query) use($category_id){
                                    $query->whereHas('relation_item', function($q) use($category_id){
                                        $q->where('category_id', $category_id);
                                    });
                                });
        }

        return $records;
    }

    public function data_table()
    {

        $customers = $this->table('customers');
        $items = $this->getItems();
        $categories = Category::orderBy('name')->get();
        $state_types = StateType::get();
        $document_types = DocumentType::whereIn('id', ['01', '03','07', '08'])->get();
        $series = Series::whereIn('document_type_id', ['01', '03','07', '08'])->get();
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();// Establishment::all();

        return compact( 'customers', 'document_types','series','establishments', 'state_types', 'items', 'categories');

    }


    public function getItems(){

        $items = Item::orderBy('description')->take(20)->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" :$row->description,
            ];
        });

        return $items;

    }


    public function getDataTableItem(Request $request) {

        $items = Item::where('description','like', "%{$request->input}%")
                        ->orWhere('internal_id','like', "%{$request->input}%")
                        ->orderBy('description')
                        ->get()->transform(function($row) {
                            return [
                                'id' => $row->id,
                                'description' => ($row->internal_id) ? "{$row->internal_id} - {$row->description}" :$row->description,
                            ];
                        });

        return $items;

    }


    private function updateMaxCountPayments($value)
    {
        if($value > $this->max_count_payment)
        {
            $this->max_count_payment = $value;
        }
       // $this->max_count_payment = 20 ;//( $value > $this->max_count_payment) ? $value : $this->$max_count_payment;
    }

    private function transformReportPayment($resource)
    {

        $records = $resource->transform(function($row) {

            $total_paid = collect($row->payments)->sum('payment');
            $total = $row->total;
            $total_difference = round($total - $total_paid, 2);

            $this->updateMaxCountPayments($row->payments->count());

            return (object)[

                'id' => $row->id,
                'ruc' => $row->customer->number,
                // 'date' =>  $row->date_of_issue->format('Y-m-d'),
                // 'date' =>  $row->date_of_issue,
                'date' =>  $row->date_of_issue->format('d/m/Y'),
                'invoice' => $row->number_full,
                'comercial_name' => $row->customer->trade_name,
                'business_name' => $row->customer->name,
                'zone' => $row->customer->department->description,
                'total' => number_format($row->total, 2, ".",""),

                'payments' => $row->payments,

                /*'payment1' =>  ( isset($row->payments[0]) ) ?  number_format($row->payments[0]->payment, 2) : '',
                'payment2' =>  ( isset($row->payments[1]) ) ?  number_format($row->payments[1]->payment, 2) : '',
                'payment3' =>   ( isset($row->payments[2]) ) ?  number_format($row->payments[2]->payment, 2) : '',
                'payment4' =>   ( isset($row->payments[3]) ) ?  number_format($row->payments[3]->payment, 2) : '', */

                'balance' => $total_difference,
                'person_type' => isset($row->person->person_type->description) ? $row->person->person_type->description:'',
                'department' => $row->customer->department->description,
                'district' => $row->customer->district->description,

                /*'reference1' => ( isset($row->payments[0]) ) ?  $row->payments[0]->reference : '',
                'reference2' =>  ( isset($row->payments[1]) ) ?  $row->payments[1]->reference : '',
                'reference3' =>  ( isset($row->payments[2]) ) ?  $row->payments[2]->reference : '',
                'reference4' =>  ( isset($row->payments[3]) ) ?  $row->payments[3]->reference : '', */
            ];
        });

        return $records;
    }

    public function report_payments(Request $request)
    {
        // $month_format = Carbon::parse($month)->format('m');

        if($request->anulled == 'true') {
           $records = Document::whereBetween('date_of_issue', [$request->date_start, $request->date_end])->get();
        } else {
            $records = Document::whereBetween('date_of_issue', [$request->date_start, $request->date_end])->where('state_type_id', '!=', '11')->get();
        }

        $source =  $this->transformReportPayment( $records );

        return (new PaymentExport)
                ->records($source)
                ->payment_count($this->max_count_payment)
                ->download('Reporte_Pagos_'.Carbon::now().'.xlsx');

    }

    public function destroyDocument($document_id)
    {
        try {

            DB::connection('tenant')->transaction(function () use ($document_id) {

                $record = Document::findOrFail($document_id);
                $this->deleteAllPayments($record->payments);
                $record->delete();

            });

            return [
                'success' => true,
                'message' => 'Documento eliminado con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El Documento esta siendo usada por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar el Documento'];

        }


    }

    public function storeCategories(CategoryRequest $request)
    {
        $id = $request->input('id');
        $category = Category::firstOrNew(['id' => $id]);
        $category->fill($request->all());
        $category->save();


        return [
            'success' => true,
            'message' => ($id)?'Categoría editada con éxito':'Categoría registrada con éxito',
            'data' => $category

        ];
    }

    public function storeBrands(BrandRequest $request){
         $id = $request->input('id');
        $brand = Brand::firstOrNew(['id' => $id]);
        $brand->fill($request->all());
        $brand->save();


        return [
            'success' => true,
            'message' => ($id)?'Marca editada con éxito':'Marca registrada con éxito',
            'data' => $brand
        ];
    }

    public function searchExternalId(Request $request)
    {
        return response()->json(Document::where('external_id', $request->external_id)->first());
    }

    public function esc(Request $request)
    {
        $note_id=$request->id;
        $data=$this->record($note_id);
        $observation= $data->observation;
        $number = $data->number;

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
            $this->toPrintEsc($cocina,$number,$imp_coc,$printerTipoConexion1,$printerRuta1,$observation);
        }
        if(!empty($barra)){
            $this->toPrintEsc($barra,$number,$imp_bar,$printerTipoConexion2,$printerRuta2,$observation);
        }
    }
    public function toPrintEsc($data,$number,$printer,$tipo,$ruta,$observation)
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
            $printer->text("Pedido No. ".$number."\n");
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
