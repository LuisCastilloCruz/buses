@extends('tenant.layouts.app')

@section('content')
    <tenant-transporte-encomiendas :encomiendas='@json($encomiendas)'></tenant-transporte-encomiendas>
@endsection
