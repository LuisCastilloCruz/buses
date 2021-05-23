@extends('tenant.layouts.app')

@section('content')

    <tenant-order-notes-not-sent :isClient="{{json_encode($is_client)}}"></tenant-order-notes-not-sent>

@endsection
