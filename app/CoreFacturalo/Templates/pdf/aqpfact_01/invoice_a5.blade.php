@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::all();

     $configuracion = \App\Models\Tenant\Configuration::all();
     foreach($configuracion as $config){
        $color1= $config['color1'];
        $color2= $config['color2'];
     }

    if($document_base) {
        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {
        $affected_document_number = null;
    }

    $payments = $document->payments;
    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:50%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<table class="full-width datos-empresa" border="0">
    <tr>
        @if($company->logo)
            <td width="30%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 250px;">
                </div>
            </td>
        @else
            <td width="30%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 250px">--}}
            </td>
        @endif                        
        <td width="45%" class="pl-2">
            <table class="empresa-datos">               >
                <tr><td width="327px" style="text-align:center;"><h4><b>{{ $company->name }}</b></h4></td></tr>                      
                <tr><td width="327px"><h5>{{ 'RUC: '.$company->number }}</h5></td></tr>  
                <tr>
                    <td width="327px">
                        <h6 style="text-transform: uppercase;">
                        {{ ($establishment->address !== '-')? $establishment->address : '' }}
                        {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                        {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                        {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                        </h6>
                    </td>
                </tr> 

                @isset($establishment->trade_address)
                <tr><td width="327px"> <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6></td></tr> 
                @endisset

                <tr><td width="327px"><h6>{{ ($establishment->telephone !== '-')? 'Teléfono: '.$establishment->telephone : '' }} {{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6></td></tr> 

                @isset($establishment->web_address)
                <tr><td width="327px"> <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6></td></tr> 
                @endisset

                @isset($establishment->aditional_information)
                <tr><td width="327px"> <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6></td></tr> 
                @endisset

            </table>
        </td>
        <td width="25%" class="py-4 px-2 text-center" style="border: 2px solid {{$color1}}">
            <h3 class="text-center">{{ $company->number }}</h3>
            <h5 class="text-center" style="background: {{$color1}};color:#ffffff;"><b>{{ $document->document_type->description }}</b></h5>
            <h3 class="text-center"><b>{{ $document_number }}</b></h3>
        </td>
    </tr>
</table>
<table class="full-width mt-1 client-aqp">
    <tr>
        <td width="100px"><b>FECHA DE EMISIÓN</b></td>
        <td width="8px">:</td>
        <td>
            <table>
                <tr>
                    <td width="80px">{{$document->date_of_issue->format('d-m-Y')}}</td>
                @if($invoice)
                    <td width="100px"><b>FECHA DE VENC.</b></td>
                    <td width="8px" class="align-top"> :</td>
                    <td width="80px" class="align-top">{{$invoice->date_of_due->format('d-m-Y')}}</td>
                @endif
                </tr>
            </table>
        </td>

        @if ($document->detraction)

            <td width="100px"><b>N. CTA DETRACCIONES</b></td>
            <td width="8px" class="align-top">:</td>
            <td class="align-top">{{ $document->detraction->bank_account}}</td>
        @endif
    </tr>

    @if ($document->detraction)
        <td width="120px"><b>B/S SUJETO A DETRACCIÓN</b></td>
        <td width="8px">:</td>
        @inject('detractionType', 'App\Services\DetractionTypeService')
        <td width="220px">{{$document->detraction->detraction_type_id}} - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>

    @endif
    <tr>
        <td width="100px"><b>CLIENTE:</b></td>
        <td>:</td>
        <td>{{ $customer->name }}</td>

        @if ($document->detraction)
            <td width="100px"><b>MÉTODO DE PAGO</b></td>
            <td width="8px">:</td>
            <td width="220px">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
        @endif
    </tr>
    <tr>
        <td width="100px"><b>{{ $customer->identity_document_type->description }}</b></td>
        <td>:</td>
        <td>{{$customer->number}}</td>


        @if ($document->detraction)
            <td width="100px"><b>P. DETRACCIÓN</b></td>
            <td width="8px">:</td>
            <td>{{ $document->detraction->percentage}}%</td>
        @endif
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td width="100px" class="align-top"><b>DIRECCIÓN:</b></td>
        <td>:</td>
        <td>
            {{ $customer->address }}
            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>

        @if ($document->detraction)
            <td width="100px">MONTO DETRACCIÓN</td>
            <td width="8px">:</td>
            <td>S/ {{ $document->detraction->amount}}</td>
        @endif
        @if ($document->detraction)
            @if($document->detraction->pay_constancy)
            <tr>
                <td colspan="3">
                </td>
                <td width="100px">C. PAGO</td>
                <td width="8px">:</td>
                <td>{{ $document->detraction->pay_constancy}}</td>
            </tr>
            @endif
        @endif
    </tr>
    @endif
</table>

{{--<table class="full-width mt-3">--}}
    {{--@if ($document->purchase_order)--}}
        {{--<tr>--}}
            {{--<td width="25%">Orden de Compra: </td>--}}
            {{--<td>:</td>--}}
            {{--<td class="text-left">{{ $document->purchase_order }}</td>--}}
        {{--</tr>--}}
    {{--@endif--}}
    {{--@if ($document->quotation_id)--}}
        {{--<tr>--}}
            {{--<td width="15%">Cotización:</td>--}}
            {{--<td class="text-left" width="85%">{{ $document->quotation->identifier }}</td>--}}
        {{--</tr>--}}
    {{--@endif--}}
{{--</table>--}}

@if ($document->guides)
<br/>
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
            <td>{{ $guide->document_type_description }}</td>
            @else
            <td>{{ $guide->document_type_id }}</td>
            @endif
            <td>:</td>
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

@if ($document->reference_guides)
<p class="p-0 m-0"><b>Guias de remisión</b>:

    @foreach($document->reference_guides as $guide)
        
           <span class="guias">{{ $guide->series }} : {{ $guide->number }}</span>
        
    @endforeach
</p>
@endif



<table class="full-width mt-3">
    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td width="50px">ANTICIPO</td>
            <td width="8px">:</td>
            <td>{{$p->number}}</td>
        </tr>
        @endforeach
    @endif
    @if ($document->purchase_order)
        <tr>
            <td width="50px"><b>ORDEN DE COMPRA</b></td>
            <td width="8px">:</td>
            <td>{{ $document->purchase_order }}</td>
        </tr>
    @endif
    @if ($document->quotation_id)
        <tr>
            <td width="50px"><b>COTIZACIÓN</b></td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->identifier }}</td>
            @isset($document->quotation->delivery_date)
                    <td width="120px">T. ENTREGA</td>
                    <td width="8px">:</td>
                    <td>{{ $document->quotation->delivery_date}}</td>
            @endisset
        </tr>
    @endif
    @isset($document->quotation->sale_opportunity)
        <tr>
            <td width="50px"><b>O. VENTA</b></td>
            <td width="8px">:</td>
            <td>{{ $document->quotation->sale_opportunity->number_full}}</td>
        </tr>
    @endisset
    @if(!is_null($document_base))
    <tr>
        <td width="50px"><b>DOC. AFECTADO</b></td>
        <td width="8px">:</td>
        <td>{{ $affected_document_number }}</td>

        <td width="50px"><b>TIPO DE NOTA</b></td>
        <td width="8px">:</td>
        <td>{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>
    </tr>
    <tr>
        <td><b>DESCRIPCIÓN</b></td>
        <td>:</td>
        <td>{{ $document_base->note_description }}</td>
    </tr>
    @endif
</table>

{{--<table class="full-width mt-3">--}}
    {{--<tr>--}}
        {{--<td width="25%">Documento Afectado:</td>--}}
        {{--<td width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td>--}}
        {{--<td width="15%">Tipo de nota:</td>--}}
        {{--<td width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td>--}}
    {{--</tr>--}}
    {{--<tr>--}}
        {{--<td class="align-top">Descripción:</td>--}}
        {{--<td class="text-left" colspan="3">{{ $document_base->note_description }}</td>--}}
    {{--</tr>--}}
{{--</table>--}}

<table class="full-width mt-1 mb-1">
    <thead class="encabezado">
    <tr class="bg-grey- fondo-ficho">
        <th class="text-center text-white py-2" style="background: {{$color1}}" width="8%">CANT.</th>
        <th class="text-center text-white py-2" style="background: {{$color1}}" width="8%">UNIDAD</th>
        <th class="text-left text-white py-2" style="background: {{$color1}}">DESCRIPCIÓN</th>
        <th class="text-right text-white py-2" width="12%" style="background: {{$color2}}">P.UNIT</th>
        <th class="text-right text-white py-2 fondo2" width="8%" style="background: {{$color2}}">DTO.</th>
        <th class="text-right text-white py-2 fondo2" width="12%" style="background: {{$color2}}">TOTAL</th>
    </tr>
    </thead>
    <tbody class="items-aqp">
    @foreach($document->items as $row)
        <tr>
            <td class="text-center align-top borde-gris">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top borde-gris">{{ $row->item->unit_type_id }}</td>
            <td class="text-left align-top borde-gris">

                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!}
                @endif

                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                @foreach($row->additional_information as $information)
                    @if ($information)
                        <br/><span style="font-size: 9px">{{ $information }}</span>
                    @endif
                @endforeach

                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif
                @if($row->item->is_set == 1)
                 <br>
                 @inject('itemSet', 'App\Services\ItemSetService')
                    {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                @endif
            </td>
            <td class="text-right align-top borde-gris">{{ number_format($row->unit_price, 2) }}</td>
            <td class="text-right align-top borde-gris">
                @if($row->discounts)
                    @php
                        $total_discount_line = 0;
                        foreach ($row->discounts as $disto) {
                            $total_discount_line = $total_discount_line + $disto->amount;
                        }
                    @endphp
                    {{ number_format($total_discount_line, 2) }}
                @else
                0
                @endif
            </td>
            <td class="text-right align-top borde-gris">{{ number_format($row->total, 2) }}</td>
        </tr>

    @endforeach

    @if ($document->prepayments)
        @foreach($document->prepayments as $p)
        <tr>
            <td class="text-center align-top">
                1
            </td>
            <td class="text-center align-top">NIU</td>
            <td class="text-left align-top">
                ANTICIPO: {{($p->document_type_id == '02')? 'FACTURA':'BOLETA'}} NRO. {{$p->number}}
            </td>
            <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
            <td class="text-right align-top">
                0
            </td>
            <td class="text-right align-top">-{{ number_format($p->total, 2) }}</td>
        </tr>
        <tr>
            <td colspan="6" class="border-bottom"></td>
        </tr>
        @endforeach
    @endif

        @if($document->total_exportation > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. EXPORTACIÓN: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exportation, 2) }}</td>
            </tr>
        @endif
        @if($document->total_free > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. GRATUITAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_free, 2) }}</td>
            </tr>
        @endif
        @if($document->total_unaffected > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. INAFECTAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_unaffected, 2) }}</td>
            </tr>
        @endif
        @if($document->total_exonerated > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. EXONERADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_exonerated, 2) }}</td>
            </tr>
        @endif
        @if($document->total_taxed > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">OP. GRAVADAS: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_taxed, 2) }}</td>
            </tr>
        @endif
         @if($document->total_discount > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">{{(($document->total_prepayment > 0) ? 'ANTICIPO':'DESCUENTO TOTAL')}}: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_discount, 2) }}</td>
            </tr>
        @endif
        @if($document->total_plastic_bag_taxes > 0)
            <tr>
                <td colspan="5" class="text-right font-bold">ICBPER: {{ $document->currency_type->symbol }}</td>
                <td class="text-right font-bold">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td colspan="5" class="text-right font-bold">IGV: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total_igv, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right font-bold">TOTAL A PAGAR: {{ $document->currency_type->symbol }}</td>
            <td class="text-right font-bold">{{ number_format($document->total, 2) }}</td>
        </tr>
        @if($balance < 0)
           <tr>
               <td colspan="5" class="text-right font-bold">VUELTO: {{ $document->currency_type->symbol }}</td>
               <td class="text-right font-bold">{{ number_format(abs($balance),2, ".", "") }}</td>
           </tr>
        @endif
    </tbody>
</table>
<table class="full-width">
    <tr>
        <td width="65%" style="text-align: top; vertical-align: top;">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    <p style="text-transform: uppercase;">Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
                    @if (count((array) $document->legends)>1)
                        <p><span class="font-bold">Leyendas</span></p>
                    @endif
                @else
                    <p> {{$row->code}}: {{ $row->value }} </p>
                @endif

            @endforeach
            <br/>

            @if ($document->detraction)
            <p>
                <span class="font-bold">
                Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                </span>
            </p>
            <br/>

            @endif
            @if ($customer->department_id == 16)
                <br/><br/><br/>
                <div>
                    <center>
                        Representación impresa del Comprobante de Pago Electrónico.
                        <br/>Esta puede ser consultada en:
                        <br/><b>{!! url('/buscar') !!}</b>
                        <br/> "Bienes transferidos en la Amazonía
                        <br/>para ser consumidos en la misma".
                    </center>
                </div>
                <br/>
            @endif
            @foreach($document->additional_information as $information)
                @if ($information)
                    @if ($loop->first)
                        <strong>Información adicional</strong>
                    @endif
                    <p>{{ $information }}</p>
                @endif
            @endforeach
            <br>
            @if(in_array($document->document_type->id,['01','03']))
                @foreach($accounts as $account)
                    <p>
                    <span class="font-bold">{{$account->bank->description}}</span> {{$account->currency_type->description}}
                    <span class="font-bold">N°:</span> {{$account->number}}
                    @if($account->cci)
                    <span class="font-bold">CCI:</span> {{$account->cci}}
                    @endif
                    </p>
                @endforeach
            @endif
        </td>
        <td width="35%" class="text-right">
            <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" width="16%"/>
            <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
        </td>
    </tr>
</table>

@if($document->payment_method_type_id)
    <table class="full-width">
        <tr>
            <td>
                <strong>PAGO: </strong>{{ $document->payment_method_type->description }}
            </td>
        </tr> 
    </table>
@endif
@if($payments->count())
    <table class="full-width">
        <tr>
        <td>
        <strong>PAGOS:</strong> </td></tr>
            @php
                $payment = 0;
            @endphp
            @foreach($payments as $row)
                <tr>
                    <td>&#8226; {{ $row->payment_method_type->description }} - {{ $row->reference ? $row->reference.' - ':'' }} {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                </tr>
            @endforeach
        </tr>

    </table>
@endif



</body>
</html>
