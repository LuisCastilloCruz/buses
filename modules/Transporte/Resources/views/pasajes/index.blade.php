@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-pasajes
        {{-- :list-programaciones='@json($programaciones)' --}}
        {{-- :pasajes='@json($pasajes)' --}}
        :establishment='@json($establishment)'
        :series='@json($series)'
        :document-types-invoice='@json($document_types_invoice)'
        :payment-method-types='@json($payment_method_types)'
        :payment-destinations='@json($payment_destinations)'
        :user-terminal='@json($terminal)'
        :configuration='@json($configuration)'
        :estado-asientos='@json($estadosAsientos)'
        :user='@json($user)'
    />

@endsection
