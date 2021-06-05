@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-manifiestos 
    :list-manifiestos='@json($manifiestos)' 
    :series='@json($series)'
    />

@endsection
