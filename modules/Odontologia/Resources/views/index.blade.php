@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">Odontologia / Administracion</h2></header><div class="card-body">
<a class="btn btn-primary" href="/odontologia/calendar">Calendario compartido</a> <a class="btn btn-default" href="/odontologia/services">Servicios y duraciones</a>
@if(app(\Modules\AiReception\Native\Access::class)->allows('odontologia','billing'))<a class="btn btn-default" href="/documents/create">Facturacion existente</a>@endif
<p class="mt-3">Pacientes del catalogo de clientes existente. Historia clinica y odontograma pendientes; no se registran diagnosticos en esta version.</p>
<h3>Odontologos</h3><table class="table table-striped"><thead><tr><th>Nombre</th><th>Especialidad</th><th>Registro</th></tr></thead><tbody>@foreach($dentists as $dentist)<tr><td>{{ $dentist->name }}</td><td>{{ $dentist->specialty }}</td><td>{{ $dentist->registration }}</td></tr>@endforeach</tbody></table>
<h3>Consultorios y sillones</h3><ul>@foreach($chairs as $chair)<li>{{ $chair->name }} / {{ $chair->room }}</li>@endforeach</ul>
@if($canManage)
<h3>Agregar especialidad</h3><form method="post" action="/odontologia/admin/specialty">@csrf<input name="name" required maxlength="100" placeholder="Especialidad"><button class="btn btn-default">Guardar</button></form>
<h3>Agregar sillon</h3><form method="post" action="/odontologia/admin/chair">@csrf<input name="name" required placeholder="Sillon"><input name="room" required placeholder="Consultorio"><button class="btn btn-default">Guardar</button></form>
<h3>Agregar odontologo</h3><form method="post" action="/odontologia/admin/dentist">@csrf<input name="name" required placeholder="Nombre"><input name="registration" placeholder="Registro profesional"><select name="specialty_id">@foreach($specialties as $specialty)<option value="{{ $specialty->id }}">{{ $specialty->name }}</option>@endforeach</select><select name="chair_id">@foreach($chairs as $chair)<option value="{{ $chair->id }}">{{ $chair->name }}</option>@endforeach</select><button class="btn btn-primary">Guardar</button></form>
@endif
<h3>Pacientes</h3><ul>@foreach($patients as $patient)<li>{{ $patient->name }}</li>@endforeach</ul>
</div></section></div></div>
@endsection
