@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-programaciones 
    :terminales='@json($terminales)' 
    :programaciones='@json($programaciones)'
    :vehiculos='@json($vehiculos)' />
@endsection
