@if(config('aireception.native_enabled', false))
 @php($nativeAccess = app(\Modules\AiReception\Native\Access::class))
 @foreach(['aireception'=>'AiReception','odontologia'=>'Odontologia'] as $nativeModule=>$nativeTitle)
  @if($nativeAccess->allows($nativeModule,'calendar'))
   <li class="nav-parent {{ request()->is($nativeModule.'*') ? 'nav-expanded nav-active' : '' }}"><a class="nav-link" href="#"><i class="fas fa-calendar-alt"></i><span>{{ $nativeTitle }}</span></a><ul class="nav nav-children">
    @if($nativeModule==='odontologia')<li><a href="/odontologia">Odontologos, especialidades y sillones</a></li>@endif
    <li><a href="/{{ $nativeModule }}/calendar">Calendario</a></li>
    <li><a href="/{{ $nativeModule }}/professionals">Profesionales y horarios</a></li>
    <li><a href="/{{ $nativeModule }}/services">Servicios</a></li>
    @if($nativeModule==='aireception' && $nativeAccess->allows($nativeModule,'conversations'))<li><a href="/aireception/inbox">Bandeja de mensajes</a></li><li><a href="/aireception/conversations">Conversaciones y derivacion</a></li>@endif
    @if($nativeModule==='aireception' && $nativeAccess->allows($nativeModule,'configure'))<li><a href="/aireception/overview">Recepcion y consumo</a></li><li><a href="/aireception/agent-settings">Configuracion del agente</a></li><li><a href="/aireception/channels">WhatsApp sandbox</a></li><li><a href="/aireception/automations">Automatizaciones</a></li>@endif
    <li><a href="/{{ $nativeModule }}/settings">Configuracion</a></li>
   </ul></li>
  @endif
 @endforeach
@endif
