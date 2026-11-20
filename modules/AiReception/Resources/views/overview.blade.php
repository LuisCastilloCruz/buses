@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">AiReception / Recepcion y consumo</h2></header><div class="card-body"><p>Periodo {{ $period }}. Las pruebas simuladas no representan cargos reales del proveedor.</p>
<div class="row">@foreach($counts as $count)<div class="col-md-3"><div class="card card-body"><strong>{{ $count->status }}</strong><h3>{{ $count->total }}</h3></div></div>@endforeach</div>
<p>Consumo estimado USD {{ number_format(($budget->used_units ?? 0)/1000000,6) }} / Reserva pendiente USD {{ number_format(($budget->reserved_units ?? 0)/1000000,6) }} / Limite USD {{ number_format(($budget->limit_units ?? 0)/1000000,2) }}</p>
<a href="/aireception/calendar">Calendario</a> | <a href="/aireception/conversations">Conversaciones</a> | <a href="/aireception/automations">Automatizaciones</a>
<table class="table"><thead><tr><th>Tipo</th><th>Estado</th><th>Modelo</th><th>Tokens entrada/salida</th><th>USD estimado</th></tr></thead><tbody>@foreach($usage as $u)<tr><td>{{ $u->kind }}</td><td>{{ $u->state }}</td><td>{{ $u->model }}</td><td>{{ $u->input_tokens }} / {{ $u->output_tokens }}</td><td>{{ number_format(($u->used_units ?? 0)/1000000,6) }}</td></tr>@endforeach</tbody></table>
<h3>Historial de acciones</h3><table class="table"><thead><tr><th>Fecha</th><th>Accion</th><th>Usuario</th></tr></thead><tbody>@foreach($audit as $event)<tr><td>{{ $event->created_at }}</td><td>{{ $event->action }}</td><td>{{ $event->actor_id }}</td></tr>@endforeach</tbody></table>
</div></section></div></div>
@endsection
