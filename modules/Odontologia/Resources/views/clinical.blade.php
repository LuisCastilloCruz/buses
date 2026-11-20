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
<textarea name="note" class="form-control" maxlength="12000" required placeholder="Solo datos ficticios de validacion"></textarea><button class="btn btn-primary mt-2">Guardar anotacion</button></form>
<h3>Historial</h3>
@php($labels=['history'=>'Antecedentes','allergies'=>'Alergias declaradas','evolution'=>'Evolucion','treatment_plan'=>'Plan de tratamiento profesional','consent'=>'Constancia de consentimiento','odontogram'=>'Odontograma'])
@foreach($records as $record)<article class="card mb-3"><div class="card-body"><h4>{{ $labels[$record['kind']] ?? $record['kind'] }}</h4><p class="text-muted">Actualizado: {{ $record['updated_at'] }} / Version {{ $record['version'] }}</p>
@if($record['kind']==='odontogram')<table class="table"><thead><tr><th>Pieza</th><th>Superficie</th><th>Observacion profesional</th></tr></thead><tbody>@foreach($record['content']['teeth'] as $tooth)<tr><td>{{ $tooth['tooth'] }}</td><td>{{ $tooth['surface'] }}</td><td>{{ $tooth['note'] }}</td></tr>@endforeach</tbody></table>
@else<p style="white-space:pre-wrap">{{ $record['content']['note'] }}</p>@endif
@if($record['kind']!=='odontogram' || count($record['content']['teeth'])===1)
<details><summary>Actualizar anotacion (conservar version anterior)</summary><form method="post">@csrf<input type="hidden" name="person_id" value="{{ $person }}"><input type="hidden" name="id" value="{{ $record['id'] }}"><input type="hidden" name="version" value="{{ $record['version'] }}"><input type="hidden" name="kind" value="{{ $record['kind'] }}">
@if($record['kind']==='odontogram')<label>Pieza <input name="tooth" type="number" value="{{ $record['content']['teeth'][0]['tooth'] }}" required></label><label>Superficie<select name="surface">@foreach(['whole','mesial','distal','buccal','lingual','occlusal'] as $surface)<option {{ $surface===$record['content']['teeth'][0]['surface']?'selected':'' }}>{{ $surface }}</option>@endforeach</select></label>@endif
<textarea name="note" class="form-control" required maxlength="{{ $record['kind']==='odontogram'?1000:12000 }}">{{ $record['kind']==='odontogram'?$record['content']['teeth'][0]['note']:$record['content']['note'] }}</textarea><button class="btn btn-primary mt-2">Guardar nueva version</button></form></details>
@endif
<form method="post" enctype="multipart/form-data" action="/odontologia/clinical/attachments" class="mt-3">@csrf<input type="hidden" name="record_id" value="{{ $record['id'] }}"><label>Adjunto privado (PDF o imagen, hasta 2 MB)<input type="file" name="attachment" accept="application/pdf,image/png,image/jpeg" required></label><button class="btn btn-default">Adjuntar</button></form>
@foreach(app('db')->connection('tenant')->table('odontologia_clinical_attachments')->where('record_id',$record['id'])->get() as $attachment)<p><a href="/odontologia/clinical/attachments/{{ $attachment->id }}">Descargar documento #{{ $attachment->id }}</a> / {{ $attachment->size }} bytes</p>@endforeach
</div></article>@endforeach
@endif
</div></section></div></div>
@endsection
