@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    //$document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    $document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);

    $allowed_items = 90;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 5);
    $total_weight = 0;

    $configuracion = \App\Models\Tenant\Configuration::get();

        $color1= $configuracion[0]['color1'];
        $color2= $configuracion[0]['color2'];
        $formato=$configuracion[0]['formats'];
        $fondo=$configuracion[0]['fondo'];



@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" alt="{{ $company->name }}"  class="company_logo" style="max-width: 300px">
            </td>
        @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="45%" class="pl-3">
            <div class="text-left">
                <p style="font-size: medium"><b>{{ $company->name }}</b></p>
                <h5><b>RUC: </b>{{$company->number }}</h5>
                <p style="text-transform: uppercase;font-size: 11px">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </p>

                @if($establishment->email!== '-')
                    <p><b>Correo: </b> {{$establishment->email}}</p>
                @endif
                @if($establishment->telephone!== '-')
                    <p><b>Teléfono: </b> {{$establishment->telephone}}</p>
                @endif
            </div>
        </td>
        <td width="35%" class="py-2 px-2 text-center" style="border: 1px solid {{$color1}}">
            <h3 class="font-bold">{{ 'R.U.C. '.$company->number }}</h3>
            <h5 class="text-center text-white font-bold" style="background: {{$color1}}">GUIA DE REMISION ELECTRÓNICA REMITENTE</h5>
            <h3 class="text-center font-bold">{{ $document_number }}</h3>
        </td>
    </tr>
</table>
<div class="mt-2" style="border: 1px solid black; border-radius: 10px">
    <table class="full-width mt-10 mb-10">
        <tbody >
        <tr>
            <td class="pl-3 text-left" width="35%"><strong>Fecha Emisión:</strong> {{ $document->date_of_issue->format('d/m/Y') }}</td>
            <td class="pl-3 text-center" width="35%"><strong>Fecha de Traslado:</strong> {{ $document->date_of_shipping->format('d/m/Y') }}</td>
            <td class="pl-3 text-right"width="35%"><strong>Número de factura:</strong>
            @if ($document->reference_document){{$document->reference_document->number_full}}
            @else
                <td class="pl-3"></td>
                @endif
                </td>
                <td>
                @if ($document->reference_document)
                    @if ($document->reference_document->purchase_order)
                        <td class="pl-3"><strong>O. COMPRA:</strong> {{$document->reference_document->purchase_order}}</td>
                    @else
                        <td class="pl-3"></td>
                    @endif
                @else
                    <td class="pl-3"></td>
                    @endif
                    </td>
        </tr>
        </tbody>
    </table>
</div>
<table class="mt-2 full-width mt-10 mb-10">
    <tbody>
    <tr>
        <td class="pl-3" width="48%" style="border: 1px solid black">
            <table>
                <tr>
                    <td class="full-width">
                        <strong style="font-size: 11px">DIRECCIÓN DE PARTIDA</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ $document->origin->address }} - {{ $document->origin->location_id }}
                    </td>
                </tr>
            </table>

        </td>
        <td width="4%">

        </td>
        <td class="pl-3" width="48%" style="border: 1px solid black">
            <table>
                <tr>
                    <td class="full-width">
                        <strong style="font-size: 11px">DIRECCIÓN DE LLEGADA</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="font-size: 10px">{{ $document->delivery->address }} - {{ $document->delivery->location_id }}</p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
    </tbody>
</table>
<table class="mt-2 full-width mt-10 mb-10">
    <tbody>
    <tr>
        <td class="pl-3" width="48%" style="border: 1px solid black">
            <table>
                <tr>
                    <td class="full-width">
                        <strong style="font-size: 11px">REMITENTE</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="font-size: 10px">{{$company->name}}</p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>RUC:</strong> {{$company->number}}
                    </td>
                </tr>
            </table>

        </td>
        <td width="4%">

        </td>
        <td class="pl-3" width="48%" style="border: 1px solid black">
            <table>
                <tr>
                    <td class="full-width">
                        <strong style="font-size: 11px">DESTINATARIO</strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="font-size: 10px">{{ $customer->name }}</p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>RUC:</strong> {{$customer->number}}
                    </td>
                </tr>
            </table>

        </td>
    </tr>
    </tbody>
</table>
<div class="mt-2" style="border: 1px solid black; border-radius: 10px">
    <table class="full-width mt-10 mb-10">
        <tbody>
        <tr>
            <td class="pl-3"><strong>Motivo Traslado:</strong> {{ $document->transfer_reason_type->description }}</td>
            <td class="pr-3 text-right"><strong>Modalidad de Transporte:</strong> {{ $document->transport_mode_type->description }}</td>
        </tr>
        {{-- <tr>
            <td>Peso Bruto Total({{ $document->unit_type_id }}): {{ $document->total_weight }}</td>
            <td>Número de Bultos: {{ $document->packages_number }}</td>
        </tr>
        <tr>
            <td>P.Partida: {{ $document->origin->location_id }} - {{ $document->origin->address }}</td>
            <td>P.Llegada: {{ $document->delivery->location_id }} - {{ $document->delivery->address }}</td>
        </tr> --}}
        </tbody>
    </table>
</div>
<table class="full-width mt-10 mb-10">
    <tr>
        <td width="50%" class="border-box pl-3" style="border: 1px solid black">
            <table class="full-width">
                <tr>
                    <td colspan="2"><strong style="font-size: 11px">EMPRESA DE TRANSPORTE</strong></td>
                </tr>
                <tr>
                    <td><strong>Transportista:</strong> <span style="font-size: 10px">{{ $document->dispatcher->name }}</span></td>
                </tr>
                <tr>
                    <td><strong>{{ $document_type_dispatcher->description }}:</strong> {{ $document->dispatcher->number }}</td>
                </tr>
                <tr>
                    <td></td>
                    {{-- <td>Dirección: {{ $document->driver->license }}</td> --}}
                </tr>
            </table>
        </td>
        <td width="3%"></td>
        <td width="45%" class="pl-3" style="border: 1px solid black; border-radius: 10px">
            <table class="full-width" >
                <tr>
                    <td colspan="2"><strong style="font-size: 11px">UNIDAD DE TRANSPORTE - CONDUCTOR</strong></td>
                </tr>
                <tr>
                    <td><strong style="font-size: 9px">Placa del vehículo:</strong> {{ $document->license_plate }}</td>
                </tr>
                <tr>
                    <td><strong>Dni del conductor:</strong>: {{ $document->driver->number }}</td>
                </tr>
                <tr>
                    <td><strong>N° Licencia:</strong> {{ $document->driver->license }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<table class="full-width mt-0 mb-0" >
    <thead >
    <tr class="">
        <th class="text-center py-1 desc text-white"  width="8%" style="background: {{$color1}}">ITEM</th>
        <th class="text-center py-1 desc text-white"  width="10%" style="background: {{$color1}}">CÓDIGO</th>
        <th class="text-center py-1 desc text-white"  width="8%" style="background: {{$color1}}">CANTIDAD</th>
        <th class="text-center py-1 desc text-white"  width="8%" style="background: {{$color2}}">U.M.</th>
        <th class="text-center py-1 desc text-white"  width="42%" style="background: {{$color2}}">DESCRIPCIÓN</th>
        <th class="text-center py-1 desc text-white"   width="12%" style="background: {{$color2}}">PESO</th>
    </tr>
    </thead>
    <tbody class="">
    @foreach($document->items as $row)
        @php
            $total_weight_line = 0;
        @endphp
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl">{{ $loop->iteration }}</td>
            <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif

            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->unit_type_id }}</td>
            <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                {!!$row->item->description!!}
                @if($row->relation_item->attributes)
                    @foreach($row->relation_item->attributes as $attr)
                        @if($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;
                                $total_weight_line += $attr->value * $row->quantity;

                            @endphp
                        @endif
                        <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
                {{ $total_weight_line }}
            </td>
        </tr>

    @endforeach

    @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
    @endfor
    <tr>
        <td class="cell-solid-offtop"></td>
        <td class="cell-solid-offtop"></td>
        <td class="cell-solid-offtop"></td>
        <td class="cell-solid-offtop"></td>
        <td class="cell-solid-offtop"></td>
        <td class="cell-solid-offtop"></td>
    </tr>
    </tbody>
</table>


<table class="full-widthmt-10 mb-10">
    <tr>
        <td width="75%">
            <table class="full-width">
                <tr>
                    @php
                        $total_packages = $document->items()->sum('quantity');
                    @endphp
                    <td ><strong>TOTAL NÚMERO DE BULTOS:</strong>
                        @if(((int)$total_packages != $total_packages))
                            {{ $total_packages }}
                        @else
                            {{ number_format($total_packages, 0) }}
                        @endif
                    </td>
                </tr>
            </table>
        </td>

        <td width="25%" class="pl-3">
            <table class="full-width">
                <tr>
                    <td ><strong>PESO TOTAL:</strong> {{ $document->total_weight }}  {{ $document->unit_type_id }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<table class="full-width border-box mt-10 mb-10">
    <tr>
        <td width="50%" class="border-box pl-3">
            <table class="full-width">
                <tr>
                    <td colspan="2"><strong>OBSERVACIONES:</strong></td>
                </tr>
                <tr>
                    <td>{{ $document->observations }}</td>
                </tr>
            </table>
        </td>
        <td width="3%"></td>

        <td width="47%" class="">
            <table class="full-width">
                <tr>
                    <td rowspan="2"><strong>Representación impresa de la Guía de Remisión</strong></td>
                </tr>
            </table>
        </td>

    </tr>
</table>
</body>
</html>
