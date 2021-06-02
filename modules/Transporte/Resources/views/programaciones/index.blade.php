@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-programaciones 
    :terminales='@json($terminales)' 
    :programaciones='@json($programaciones)'
    :vehiculos='@json($vehiculos)'
    :series='@json($series)'
    :choferes='@json($choferes)' 
    :user-terminal='@json($user_terminal)'
    />
@endsection
