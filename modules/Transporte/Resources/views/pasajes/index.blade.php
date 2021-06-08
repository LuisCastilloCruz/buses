@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-pasajes
        :pasajes='@json($pasajes)'
        :establishment='@json($establishment)'
        :series='@json($series)'
        :document-types-invoice='@json($document_types_invoice)'
        :payment-method-types='@json($payment_method_types)'
        :payment-destinations='@json($payment_destinations)'
        :user-terminal='@json($user_terminal)'
        :configuration='@json($configuration)'
    />

@endsection
