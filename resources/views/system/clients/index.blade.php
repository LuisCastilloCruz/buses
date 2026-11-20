@extends('system.layouts.app')

@section('content')

    @if(config('aireception.native_enabled', false))<a class="btn btn-default mb-2" href="/aireception-management">AiReception: planes y suscripciones</a>@endif
    <system-clients-index></system-clients-index>

@endsection