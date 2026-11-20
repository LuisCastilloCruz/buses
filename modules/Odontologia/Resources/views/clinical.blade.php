@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">Historia odontologica protegida</h2></header><div class="card-body">
<div class="alert alert-warning">Validacion con datos ficticios. No habilitar para pacientes reales sin revision profesional, legal y de seguridad. No se utiliza IA clinica.</div>
<a href="/odontologia" class="btn btn-default">Administracion</a>
<form method="get"><label>Paciente <select name="person_id" required>@foreach($people as $p)<option value="{{ $p->id }}" {{ $person===$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select></label><button class="btn btn-primary">Consultar historia</button></form>
@if($person)
<h3>Registrar anotacion profesional</h3><form method="post">@csrf<input type="hidden" name="person_id" value="{{ $person }}">
<label>Tipo <select name="kind"><option value="history">Antecedentes</option><option value="allergies">Alergias declaradas</option><option value="evolution">Evolucion</option><option value="treatment_plan">Plan profesional</option><option value="consent">Constancia de consentimiento</option><option value="odontogram">Odontograma: pieza y superficie</option></select></label>
<label>Pieza FDI (odontograma)<input type="number" name="tooth" min="11" max="85"></label><label>Superficie<select name="surface">@foreach(['whole','mesial','distal','buccal','lingual','occlusal'] as $surface)<option>{{ $surface }}</option>@endforeach</select></label>
<textarea name="note" class="form-control" maxlength="12000" required placeholder="Solo datos ficticios de validacion"></textarea><button class="btn btn-primary mt-2">Guardar registro cifrado</button></form>
<h3>Historial</h3>@foreach($records as $record)<article class="card mb-2"><div class="card-body"><strong>{{ $record['kind'] }} / Version {{ $record['version'] }}</strong><small> {{ $record['updated_at'] }}</small><pre>{{ json_encode($record['content'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div></article>@endforeach
@endif
</div></section></div></div>
@endsection
