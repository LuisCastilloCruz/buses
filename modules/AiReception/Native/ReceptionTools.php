<?php
namespace Modules\AiReception\Native;
final class ReceptionTools {
 public function definitions():array {
  $integer=['type'=>'integer','minimum'=>1];$date=['type'=>'string','pattern'=>'^\\d{4}-\\d{2}-\\d{2}$'];$time=['type'=>'string','pattern'=>'^\\d{2}:\\d{2}$'];
  $definitions=['knowledge_search'=>['query'=>['type'=>'string','minLength'=>2,'maxLength'=>200]],'business_info'=>[],'list_services'=>[],'list_professionals'=>[],'availability'=>['professional_id'=>$integer,'service_id'=>$integer,'day'=>$date],'prepare_booking'=>['professional_id'=>$integer,'service_id'=>$integer,'day'=>$date,'start'=>$time],'prepare_cancellation'=>['booking_id'=>$integer],'prepare_rescheduling'=>['booking_id'=>$integer,'day'=>$date,'start'=>$time],'own_bookings'=>[],'human_handoff'=>[]];$tools=[];
  foreach($definitions as $name=>$properties)$tools[]=['type'=>'function','name'=>$name,'description'=>'Authorized administrative '.$name.'. Booking preparation never confirms a booking.','strict'=>true,'parameters'=>['type'=>'object','properties'=>$properties?:new \stdClass(),'required'=>array_keys($properties),'additionalProperties'=>false]];
  return $tools;
 }
 public static function clinical(string $text):bool {return (bool)preg_match('/diagnost|prescri|medicamento|receta|dolor|sangrad|urgencia|emergencia|no puedo respirar|desmayo|hinchaz/iu',$text);}
 public function run(string $name,array $arguments,int $person):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();$actor=(int)auth('web')->id();$db=app('db')->connection('tenant');
  if(!$db->table('persons')->where('type','customers')->where('id',$person)->exists())abort(404);
  $schemas=[];foreach($this->definitions() as $definition)$schemas[$definition['name']]=array_keys((array)$definition['parameters']['properties']);
  if(!isset($schemas[$name]) || array_diff(array_keys($arguments),$schemas[$name]) || array_diff($schemas[$name],array_keys($arguments)))abort(422,'Herramienta o argumentos no autorizados.');
  if($name==='human_handoff')return ['reply'=>'Te derivo a recepcion humana. La automatizacion queda pausada.','handoff'=>true];
  if($name==='business_info'){$c=$db->table('companies')->first();$e=$db->table('establishments')->orderBy('id')->first();return ['reply'=>$c->name.'. Direccion registrada: '.(($e ? $e->address : null)?:'Consultar con recepcion').'.'];}
  if($name==='knowledge_search'){$sources=app(KnowledgeBase::class)->search($arguments['query']);return ['reply'=>$sources?implode("\n",array_map(function($s){return $s['excerpt'].' [Fuente: '.$s['source'].' v'.$s['version'].']';},$sources)):'No dispongo de una fuente verificada. Consulta a recepcion humana.','sources'=>$sources];}
  $engine=app(EngineFactory::class)->make('aireception');
  if($name==='list_services'){$rows=$db->table('aireception_services as s')->leftJoin('items as i','i.id','=','s.item_id')->where('s.business_id',$business)->where('s.active',true)->select('s.name','s.duration_minutes','i.sale_unit_price','i.currency_type_id')->limit(20)->get();$lines=[];foreach($rows as $s)$lines[]=$s->name.' ('.$s->duration_minutes.' min)'.($s->sale_unit_price!==null?' / precio de catalogo '.number_format((float)$s->sale_unit_price,2).' '.$s->currency_type_id:' / precio no confirmado');return ['reply'=>$lines?implode("\n",$lines):'No hay servicios habilitados. Consulta a recepcion.'];}
  if($name==='list_professionals'){$names=$db->table('aireception_professionals')->where('business_id',$business)->where('active',true)->pluck('name')->all();return ['reply'=>$names?'Profesionales: '.implode(', ',$names).'.':'No hay profesionales habilitados.'];}
  if($name==='own_bookings'){$rows=$db->table('aireception_bookings')->where('business_id',$business)->where('person_id',$person)->orderByDesc('day')->limit(10)->get();$lines=[];foreach($rows as $b)$lines[]='Cita #'.$b->id.': '.$b->day.' '.$b->start.' / '.$b->status;return ['reply'=>$lines?implode("\n",$lines):'No hay citas para el cliente seleccionado.'];}
  if($name==='availability'){$v=app('validator')->make($arguments,['professional_id'=>'required|integer|min:1','service_id'=>'required|integer|min:1','day'=>'required|date_format:Y-m-d'])->validate();$slots=$engine->availability($actor,$business,(int)$v['professional_id'],(int)$v['service_id'],$v['day']);return ['reply'=>$slots?'Horarios disponibles el '.$v['day'].': '.implode(', ',array_column(array_slice($slots,0,12),'start')).'. El horario se revalida al confirmar.':'No hay horarios disponibles para esa seleccion.','slots'=>$slots];}
  if(in_array($name,['prepare_cancellation','prepare_rescheduling'],true)){$action=$name==='prepare_cancellation'?'cancel':'reschedule';$proposal=app(BookingProposals::class)->prepareChange($action,$person,$arguments);return ['reply'=>'Solicitud de '.($action==='cancel'?'cancelacion':'reprogramacion').' para cita #'.$arguments['booking_id'].'. Requiere confirmacion explicita; aun no se ha modificado.','proposal'=>$proposal];}
  $proposal=app(BookingProposals::class)->prepare($arguments+['person_id'=>$person]);return ['reply'=>'Propuesta para '.$proposal['proposal']['day'].' a las '.$proposal['proposal']['start'].'. Aun no esta reservada. Confirma explicitamente para guardarla.','proposal'=>$proposal];
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
