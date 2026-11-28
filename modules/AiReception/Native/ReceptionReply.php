<?php
namespace Modules\AiReception\Native;
/** Presentation of backend-verified results only. No generated facts or tool execution. */
final class ReceptionReply {
 public static function present(array $reply,string $user,array $profile=[]):array {
  if(!isset($reply['reply'])||!is_string($reply['reply']))throw new \InvalidArgumentException('Verified response required');
  if(!empty($reply['handoff'])&&!ReceptionTools::clinical($user)){
   $reply['reply']=preg_match('/molest|reclamo|queja|mal servicio/iu',$user)?'Lamento la molestia. Te derivo a una persona del equipo para revisar tu caso.':'Te derivo a una persona del equipo para ayudarte. No se ha confirmado ninguna operacion.';
  }
  // Profiles cannot rewrite transaction outcomes, dates, amounts or confirmation tokens.
  if(preg_match('/<\/?think\b/iu',$reply['reply']))throw new \RuntimeException('Internal content is not a customer response');
  return $reply;
 }
}
