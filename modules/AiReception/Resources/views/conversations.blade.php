@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">AiReception / Conversaciones</h2></header><div class="card-body">
<div class="alert alert-info">Simulacion local, sin WhatsApp ni OpenAI. <a href="/aireception/agent">Abrir demostracion de solicitud y reserva</a>. Las reservas se confirman en el <a href="/aireception/calendar">calendario compartido</a>.</div>
<form method="post" action="/aireception/conversations">@csrf
<label>Cliente <select name="person_id" class="form-control">@foreach($persons as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach</select></label>
<label>Mensaje <input name="text" class="form-control" required maxlength="2000"></label><button class="btn btn-primary">Simular solicitud</button></form>
@foreach($conversations as $conversation)<section class="card mt-3"><div class="card-body"><h4>{{ $conversation->name }} / {{ $conversation->status }}</h4>
@foreach(app('db')->connection('tenant')->table('aireception_messages')->where('conversation_id',$conversation->id)->orderBy('id')->get() as $message)<p><strong>{{ $message->direction }}:</strong> {{ $message->body }}</p>@endforeach
<form method="post" action="/aireception/conversations/{{ $conversation->id }}/handoff">@csrf<button class="btn btn-default" {{ $conversation->status==='human'?'disabled':'' }}>Derivar a recepcion humana</button></form>
<form method="post" action="/aireception/conversations">@csrf<input type="hidden" name="person_id" value="{{ $conversation->person_id }}"><input type="hidden" name="conversation_id" value="{{ $conversation->id }}"><input name="text" required maxlength="2000" placeholder="Continuar conversacion"><button class="btn btn-default">Enviar simulacion</button></form>
</div></section>@endforeach
<h3>Recordatorios simulados</h3><p>No se envian mensajes externos.</p><ul>@foreach(app('db')->connection('tenant')->table('aireception_reminders')->orderByDesc('id')->limit(30)->get() as $reminder)<li>Cita #{{ $reminder->booking_id }} / {{ $reminder->scheduled_at }} / {{ $reminder->status }}</li>@endforeach</ul>
</div></section></div></div>
@endsection
