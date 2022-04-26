@extends('tenant.layouts.app')

@section('content')

    <tenant-quotations-index
    	:type-user="{{json_encode(Auth::user()->type)}}"
    	:soap-company="{{ json_encode($soap_company) }}"
        :configuration="{{ json_encode($configuration) }}">
    </tenant-quotations-index>

@endsection
