@extends('tenant.layouts.app')
@section('content')
<div class="row" v-pre><div class="col-lg-12"><section class="card"><header class="card-header"><h2 class="card-title">AiReception / WhatsApp sandbox</h2></header><div class="card-body">
<div class="alert alert-warning">Solo cuentas de prueba autorizadas. Guardar configuracion no valida acceso a Meta ni activa respuestas externas. El laboratorio escucha unicamente en localhost.</div>
<table class="table"><thead><tr><th>Canal</th><th>Phone Number ID</th><th>Recepcion habilitada</th><th>Ruta webhook</th></tr></thead><tbody>@foreach($channels as $c)<tr><td>{{ $c->label }}</td><td>{{ $c->phone_id }}</td><td>{{ $c->enabled?'Si':'No' }}</td><td>/api/aireception/meta/{{ $c->id }}</td></tr>@endforeach</tbody></table>
<form method="post" action="/aireception/channels" autocomplete="off">@csrf
<label>Nombre<input name="label" class="form-control" required maxlength="100"></label><label>Phone Number ID<input name="phone_id" class="form-control" required></label><label>WABA ID<input name="waba_id" class="form-control" required></label><label>Version Graph API validada<input name="version" class="form-control" placeholder="vXX.0" required></label>
<label>App Secret<input type="password" name="app_secret" class="form-control" required minlength="16" autocomplete="new-password"></label><label>Token de verificacion<input type="password" name="verify_token" class="form-control" required minlength="16" autocomplete="new-password"></label><label>Token de acceso (opcional para solo recepcion)<input type="password" name="access_token" class="form-control" autocomplete="new-password"></label><label>Recepcion<select name="enabled"><option value="0">Desactivada</option><option value="1">Habilitar solo sandbox</option></select></label><button class="btn btn-primary">Guardar canal privado</button>
</form><p>Los secretos no se muestran ni se precargan. Para cambiar un canal vuelve a introducir su configuracion completa. No es posible tomar un canal conectado a otra empresa o a TattooInbox.</p>
</div></section></div></div>
@endsection
