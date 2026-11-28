<?php
namespace Modules\AiReception\Native;
/** Deterministic state transitions. Provider output is never confirmation or identity. */
final class DialogueState {
 public static function advance(array $old,array $selection,string $text):array {
  $state=$old+['intent'=>'information','phase'=>'collecting','selection'=>[],'offered'=>[],'pending'=>null,'booking_id'=>null,'site_id'=>null];
  $changed=false;foreach(['service_id','professional_id','day','start'] as $key)if(($state['selection'][$key]??null)!==($selection[$key]??null))$changed=true;
  if($changed){$state['pending']=null;$state['phase']='collecting';foreach(['service_id','professional_id','day'] as $key)if(($state['selection'][$key]??null)!==($selection[$key]??null)){$state['offered']=[];break;}}
  $state['selection']=$selection;if(preg_match('/^(no|mejor no|ya no)\b/iu',trim($text))){$state['pending']=null;$state['offered']=[];$state['phase']='clarification';}
  if(preg_match('/cancel/iu',$text))$state['intent']='cancel';elseif(preg_match('/reprogram|cambiar mi cita|mover mi cita/iu',$text))$state['intent']='reschedule';elseif(preg_match('/quiero|reserv|cita/iu',$text)&&$state['intent']==='information')$state['intent']='book';
  if(preg_match('/(?:cita|reserva)\s*#\s*(\d+)/iu',$text,$m))$state['booking_id']=(int)$m[1];
  $required=$state['intent']==='cancel'?['booking_id']:($state['intent']==='reschedule'?['booking_id','day','start']:['service_id','professional_id','day','start']);$state['missing']=[];foreach($required as $key)if($key==='booking_id'?!$state['booking_id']:!isset($selection[$key]))$state['missing'][]=$key;
  return $state;
 }
 public static function permitted(array $state,string $tool,array $args):bool {
  if(in_array($tool,['availability','prepare_booking'],true)){
   foreach(['service_id','professional_id','day'] as $key)if(!isset($state['selection'][$key])||($args[$key]??null)!==$state['selection'][$key])return false;
   if($tool==='prepare_booking')return isset($state['selection']['start'])&&($args['start']??null)===$state['selection']['start']&&in_array($args['start'],$state['offered']??[],true);
  }
  if(in_array($tool,['prepare_cancellation','prepare_rescheduling'],true)){
   if(($args['booking_id']??null)!==($state['booking_id']??null)||!$state['booking_id'])return false;
   if($tool==='prepare_cancellation')return $state['intent']==='cancel';
   return $state['intent']==='reschedule'&&($args['day']??null)===($state['selection']['day']??null)&&($args['start']??null)===($state['selection']['start']??null);
  }
  return true;
 }
}
