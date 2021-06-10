@extends('tenant.layouts.app')

@section('content')

    <tenant-order-notes-not-sent :type-user="{{json_encode(Auth::user()->type)}}" :isClient="{{json_encode($is_client)}}"></tenant-order-notes-not-sent>

@endsection
