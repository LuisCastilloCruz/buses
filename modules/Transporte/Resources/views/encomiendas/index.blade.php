@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-encomiendas 
    :encomiendas='@json($encomiendas)'
    :estados-pago='@json($estadosPagos)'
    :estados-envio='@json($estadosEnvios)'
    />

@endsection
