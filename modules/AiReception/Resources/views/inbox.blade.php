@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">AiReception / Mensajes recibidos</h2></header><div class="card-body">
<p>Recepcion de eventos firmados. La identidad debe verificarse antes de vincular una conversacion a un cliente. No se crean citas automaticamente.</p><a href="/aireception/conversations">Conversaciones y atencion humana</a>
@foreach($rows as $row)<article class="card mb-3"><div class="card-body"><strong>{{ $row->sender }}</strong><span class="badge badge-light">{{ $row->state }}</span><p>{{ $row->type==='text'?$row->text:'Audio pendiente de procesamiento autorizado' }}</p>
@if($row->state!=='linked_manual' && app(\Modules\AiReception\Native\Access::class)->allows('aireception','configure'))<form method="post" action="/aireception/inbox/{{ $row->id }}/link">@csrf<select name="person_id" required>@foreach($people as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select><input name="evidence" required minlength="10" maxlength="255" placeholder="Referencia de verificacion de identidad"><button class="btn btn-primary">Vincular identidad verificada</button></form>@endif
</div></article>@endforeach
</div></section></div></div>
@endsection
