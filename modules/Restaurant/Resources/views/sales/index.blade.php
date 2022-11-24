@extends('tenant.layouts.app')

@section('content')
<div class="row">
    <tenant-restaurant-sales-index  :id_user2='@json($id_user2)' :type_user='@json($type_user)' :configuration='@json($configuration)' :items='@json($items)'></tenant-restaurant-sales-index>
</div>
@endsection
