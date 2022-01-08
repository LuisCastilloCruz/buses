@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-encomiendas
    :estados-pago='@json($estadosPagos)'
    :estados-envio='@json($estadosEnvios)'
    :establishment='@json($establishment)'
    :series='@json($series)'
    :document-types-invoice='@json($document_types_invoice)'
    :payment-method-types='@json($payment_method_types)'
    :payment-destinations='@json($payment_destinations)'
    :user-terminal='@json($user_terminal)'
    :configuration='@json($configuration)'
    :document_type_03_filter='@json($document_type_03_filter)'
    :is-cash-open='@json($isCashOpen)'
    :persons='@json($persons)'
    :user='@json($user)'
    />

@endsection
