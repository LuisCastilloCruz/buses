<?php
namespace Modules\AiReception\Native;
final class ReceptionTools {
 public function definitions():array {
  $integer=['type'=>'integer','minimum'=>1];$date=['type'=>'string','pattern'=>'^\\d{4}-\\d{2}-\\d{2}$'];$time=['type'=>'string','pattern'=>'^\\d{2}:\\d{2}$'];
  $definitions=['request_summary'=>[],'greeting'=>[],'clarify_selection'=>['field'=>['type'=>'string','enum'=>['service','professional','day','time']]],'knowledge_search'=>['query'=>['type'=>'string','minLength'=>2,'maxLength'=>200]],'business_info'=>[],'list_services'=>[],'list_professionals'=>[],'availability'=>['professional_id'=>$integer,'service_id'=>$integer,'day'=>$date],'prepare_booking'=>['professional_id'=>$integer,'service_id'=>$integer,'day'=>$date,'start'=>$time],'prepare_cancellation'=>['booking_id'=>$integer],'prepare_rescheduling'=>['booking_id'=>$integer,'day'=>$date,'start'=>$time],'own_bookings'=>[],'human_handoff'=>[]];$tools=[];
  foreach($definitions as $name=>$properties)$tools[]=['type'=>'function','name'=>$name,'description'=>($name==='availability'?'Consultar horarios reales cuando ya se conocen servicio, profesional y fecha. No inventar hora.':($name==='request_summary'?'Resumir la seleccion actual cuando el usuario pregunta que eligio.':($name==='prepare_cancellation'?'Preparar cancelacion de la cita identificada por el cliente; requiere confirmacion posterior.':($name==='prepare_rescheduling'?'Preparar cambio de fecha y hora para la cita identificada por el cliente. Requiere confirmacion posterior.':($name==='prepare_booking'?'Preparar cita solo cuando el cliente eligio una hora ofrecida. Nunca confirmar.':'Authorized administrative '.$name.'. Booking preparation never confirms a booking.'))))),'strict'=>true,'parameters'=>['type'=>'object','properties'=>$properties?:new \stdClass(),'required'=>array_keys($properties),'additionalProperties'=>false]];
  foreach($tools as &$tool){if($tool['name']==='list_services')$tool['description']='Consultar servicios, productos y sus precios verificados del catalogo. Usar para precio, cuesta o servicios.';if($tool['name']==='business_info')$tool['description']='Consultar nombre, direccion y horario general del negocio. No consulta precios ni disponibilidad de citas.';}unset($tool);
  return $tools;
 }
 public function availableDefinitions(array $selection,string $text='',array $state=[],bool $booking=true):array {
  $act=ReceptionAct::kind($text);
  $tools=[];$fields=['service'=>'service_id','professional'=>'professional_id','day'=>'day','time'=>'start'];$missing=[];
  foreach($fields as $field=>$key)if(!isset($selection[$key]))$missing[]=$field;
  foreach($this->definitions() as $tool){
   if($tool['name']==='greeting'&&$act!=='greeting'&&trim($text)!=='')continue;
   if($act==='information'&&$tool['name']!=='business_info')continue;
   if(preg_match('/^(si|s\x{ed}|ok|dale|de acuerdo)[.!]?$/iu',trim($text))&&$missing){
    $next=$missing===['time']&&empty($state['offered'])?'availability':'clarify_selection';
    if($tool['name']!==$next)continue;
   }

   if($act==='knowledge'&&$tool['name']!=='knowledge_search')continue;
   if($act==='catalogue'&&!in_array($tool['name'],['list_services','knowledge_search'],true))continue;
   if($tool['name']==='prepare_cancellation'&&(($state['intent']??'')!=='cancel'||empty($state['booking_id'])))continue;
   if($tool['name']==='prepare_rescheduling'&&(($state['intent']??'')!=='reschedule'||empty($state['booking_id'])))continue;

   if($booking&&$act==='booking'&&in_array('service_id',$state['explicit_fields']??[],true)){
    $next=isset($selection['service_id'],$selection['professional_id'],$selection['day'])?'availability':'clarify_selection';
    if(isset($selection['start'])&&in_array($selection['start'],$state['offered']??[],true))$next='prepare_booking';
    if($tool['name']!==$next)continue;
   }

   if($tool['name']==='request_summary'&&$act!=='summary'&&trim($text)!=='')continue;
   if(!$booking&&in_array($tool['name'],['availability','prepare_booking','prepare_cancellation','prepare_rescheduling','own_bookings'],true))continue;
   if(!$booking&&in_array($act,['booking','change'],true)&&$tool['name']!=='human_handoff')continue;
   if(preg_match('/(?:nueva|otra)\s+(?:cita|reserva)|(?:cita|reserva)\s+nueva/iu',$text)&&!in_array($tool['name'],['availability','clarify_selection','human_handoff'],true))continue;
   if(preg_match('/hablar con (una )?(persona|humano)|revela.*(prompt|instrucciones)|ignora.*reglas|datos de otra empresa|SQL/iu',$text)&&$tool['name']!=='human_handoff')continue;
   if(preg_match('/precio|presio|cuesta|costo/iu',$text)&&!in_array($tool['name'],['list_services','knowledge_search','human_handoff'],true))continue;
   if($tool['name']==='prepare_booking'&&!isset($selection['service_id'],$selection['professional_id'],$selection['day'],$selection['start']))continue;
   if($tool['name']==='availability'&&!isset($selection['service_id'],$selection['professional_id'],$selection['day']))continue;
   if($tool['name']==='clarify_selection'){if(!$missing||($missing===['time']&&empty($state['offered'])))continue;$tool['parameters']['properties']['field']['enum']=$missing;}
   $tools[]=$tool;
  }
  if(!$tools){foreach($this->definitions() as $tool)if($tool['name']==='human_handoff')$tools[]=$tool;}
  return $tools;
 }
 public static function clinical(string $text):bool {return (bool)preg_match('/diagnost|prescri|medicamento|receta|dolor|sangrad|urgencia|emergencia|no puedo respirar|desmayo|hinchaz/iu',$text);}
 public function run(string $name,array $arguments,int $person,?int $conversation=null):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();$actor=(int)auth('web')->id();$db=app('db')->connection('tenant');
  if(!$db->table('persons')->where('type','customers')->where('id',$person)->exists())abort(404);
  $schemas=[];foreach($this->definitions() as $definition)$schemas[$definition['name']]=array_keys((array)$definition['parameters']['properties']);
  if(!isset($schemas[$name]) || array_diff(array_keys($arguments),$schemas[$name]) || array_diff($schemas[$name],array_keys($arguments)))abort(422,'Herramienta o argumentos no autorizados.');
  if(in_array($name,['request_summary','greeting'],true)){
   if(!$conversation || !$db->table('aireception_conversations')->where('id',$conversation)->where('person_id',$person)->exists())abort(404);
   if($name==='greeting'){$profile=app(AgentSettings::class)->privateConfiguration()['profile']??[];$incoming=$db->table('aireception_messages')->where('conversation_id',$conversation)->where('direction','incoming')->count();return ['reply'=>$incoming<=1?($profile['greeting']??'Hola. ¿En qué puedo ayudarte?'):'¿En qué puedo ayudarte?'];}
   $state=app(ConversationMemory::class)->load($conversation,$person);$parts=[];if(isset($state['service_id'])){$name=$db->table('aireception_services')->where('business_id',$business)->where('id',$state['service_id'])->value('name');if($name)$parts[]=$name;}if(isset($state['professional_id'])){$name=$db->table('aireception_professionals')->where('business_id',$business)->where('id',$state['professional_id'])->value('name');if($name)$parts[]='con '.$name;}if(isset($state['day']))$parts[]='para '.$state['day'];if(isset($state['start']))$parts[]='a las '.$state['start'];return ['reply'=>$parts?'Tu solicitud es '.implode(' ',$parts).'. Es una preferencia; no confirma una reserva.':'Aún no tengo una selección suficiente. ¿Qué servicio necesitas?'];
  }
  if($name==='clarify_selection'){$questions=['service'=>'¿Qué servicio necesitas?','professional'=>'¿Con qué profesional prefieres atenderte?','day'=>'¿Qué fecha prefieres?','time'=>'¿Qué horario de los disponibles prefieres?'];if(!isset($questions[$arguments['field']??'']))abort(422);return ['reply'=>$questions[$arguments['field']]];}
  if($name==='human_handoff')return ['reply'=>'Te derivo a recepcion humana. La automatizacion queda pausada.','handoff'=>true];
  if($name==='business_info'){$c=$db->table('companies')->first();$e=$db->table('establishments')->orderBy('id')->first();$profile=app(AgentSettings::class)->privateConfiguration()['profile']??[];return ['reply'=>$c->name.'. Direccion registrada: '.(($e ? $e->address : null)?:'Consultar con recepcion').'.'.(!empty($profile['hours'])?' Horario informativo configurado: '.$profile['hours'].'. La disponibilidad de citas se consulta por separado.':'')];}
  if($name==='knowledge_search'){$sources=app(KnowledgeBase::class)->search($arguments['query']);return ['reply'=>$sources?implode("\n",array_map(function($s){return $s['excerpt'].' [Fuente: '.$s['source'].' v'.$s['version'].']';},$sources)):'No dispongo de una fuente verificada. Consulta a recepcion humana.','sources'=>$sources];}
  if(in_array($name,['availability','prepare_booking','prepare_cancellation','prepare_rescheduling'],true)&&!app(DomainRegistry::class)->supportsBookings())return ['reply'=>'Este negocio no tiene una agenda conectada para esa operacion. Te derivo a una persona del equipo.','handoff'=>true];
  $engine=app(EngineFactory::class)->make('aireception');
  if($name==='list_services'){$rows=$db->table('aireception_services as s')->leftJoin('items as i','i.id','=','s.item_id')->where('s.business_id',$business)->where('s.active',true)->select('s.name','s.duration_minutes','i.sale_unit_price','i.currency_type_id')->limit(20)->get();$lines=[];foreach($rows as $s)$lines[]=$s->name.' ('.$s->duration_minutes.' min)'.($s->sale_unit_price!==null?' / precio de catalogo '.number_format((float)$s->sale_unit_price,2).' '.$s->currency_type_id:' / precio no confirmado');return ['reply'=>$lines?implode("\n",$lines):'No hay servicios habilitados. Consulta a recepcion.'];}
  if($name==='list_professionals'){$names=$db->table('aireception_professionals')->where('business_id',$business)->where('active',true)->pluck('name')->all();return ['reply'=>$names?'Profesionales: '.implode(', ',$names).'.':'No hay profesionales habilitados.'];}
  if($name==='own_bookings'){$rows=$db->table('aireception_bookings')->where('business_id',$business)->where('person_id',$person)->orderByDesc('day')->limit(10)->get();$lines=[];foreach($rows as $b)$lines[]='Cita #'.$b->id.': '.$b->day.' '.$b->start.' / '.$b->status;return ['reply'=>$lines?implode("\n",$lines):'No hay citas para el cliente seleccionado.'];}
  if($name==='availability'){$v=app('validator')->make($arguments,['professional_id'=>'required|integer|min:1','service_id'=>'required|integer|min:1','day'=>'required|date_format:Y-m-d'])->validate();$slots=$engine->availability($actor,$business,(int)$v['professional_id'],(int)$v['service_id'],$v['day']);return ['reply'=>$slots?'Horarios disponibles el '.$v['day'].': '.implode(', ',array_column(array_slice($slots,0,12),'start')).'. El horario se revalida al confirmar.':'No hay horarios disponibles para esa seleccion.','slots'=>$slots];}
  if(in_array($name,['prepare_cancellation','prepare_rescheduling'],true)){$action=$name==='prepare_cancellation'?'cancel':'reschedule';$proposal=app(BookingProposals::class)->prepareChange($action,$person,$arguments);$booking=$db->table('aireception_bookings')->where('business_id',$business)->where('person_id',$person)->where('id',$arguments['booking_id'])->first();if(!$booking)abort(409);$detail=$action==='reschedule'?' al '.$arguments['day'].' a las '.$arguments['start']:' del '.$booking->day.' a las '.$booking->start;return ['reply'=>'Solicitud de '.($action==='cancel'?'cancelacion':'reprogramacion').' para cita #'.$arguments['booking_id'].$detail.'. Requiere confirmacion explicita; aun no se ha modificado.','proposal'=>$proposal];}
  $proposal=app(BookingProposals::class)->prepare($arguments+['person_id'=>$person]);$serviceName=$db->table('aireception_services')->where('business_id',$business)->where('id',$arguments['service_id'])->value('name');$professionalName=$db->table('aireception_professionals')->where('business_id',$business)->where('id',$arguments['professional_id'])->value('name');if(!$serviceName||!$professionalName)abort(409);return ['reply'=>'Propuesta: '.$serviceName.' con '.$professionalName.' para '.$proposal['proposal']['day'].' a las '.$proposal['proposal']['start'].'. Aun no esta reservada. Confirma explicitamente para guardarla.','proposal'=>$proposal];
 }
 public function simulated(string $text,int $person):array {
  if(self::clinical($text))return ['reply'=>'Esta consulta necesita al equipo clinico. No puedo evaluar sintomas ni indicar tratamientos. Si se trata de una urgencia, busca atencion inmediata.','handoff'=>true];
  if(preg_match('/humano|persona|recepcionista|asesor/iu',$text))return $this->run('human_handoff',[],$person);
  if(preg_match('/cancel|cambiar|reprogram/iu',$text))return $this->run('own_bookings',[],$person)+['reply_hint'=>'Gestiona la cancelacion o reprogramacion autorizada en el calendario.'];
  if(preg_match('/precio|cuesta|servicio|tratamiento|cita|reserv/iu',$text))return $this->run('list_services',[],$person)+['reply_hint'=>'Elige un servicio, profesional y horario en el formulario; luego confirma.'];
  if(preg_match('/profesional|quien|odontologo|artista/iu',$text))return $this->run('list_professionals',[],$person);
  if(preg_match('/ubic|direccion|donde/iu',$text))return $this->run('business_info',[],$person);
  return ['reply'=>'Consulta los servicios y profesionales o abre la disponibilidad real del calendario. No se ha creado ninguna cita.'];
 }
}
