<?php
namespace Modules\AiReception\Native;
/** Last-message dialogue act, not identity, availability or authority. */
final class ReceptionAct {
 public static function kind(string $text):string {
  $t=mb_strtolower(trim($text),'UTF-8');
  if(preg_match('/resum|recapitul|recuerda|recordar|que (?:he |habia )?(?:pedido|elegi|solicite)/iu',$t))return 'summary';
  if(preg_match('/hablar con|humano|asesor|reclamo|queja/iu',$t))return 'human';
  if(preg_match('/^(hola|buenas|buenos dias|buenas tardes|saludos|buen dia)\b/iu',$t))return 'greeting';
  if(preg_match('/direcci[o\x{f3}]n|ubicaci[o\x{f3}]n|donde|horario general|en que horario|horario atienden/iu',$t))return 'information';
  if(preg_match('/precio|presio|cuesta|costo|promoci|descuento|oferta/iu',$t))return 'catalogue';
  if(preg_match('/politic|pol\x{ed}tic|requisit|estacionamiento|documento|promoci|descuento/iu',$t))return 'knowledge';
  if(preg_match('/(?:informaci[o\x{f3}]n|revisar|mostrar|consultar|ver).*servici|que servicios|cat[a\x{e1}]logo/iu',$t))return 'catalogue';
  if(preg_match('/cancel|reprogram|mover mi cita|cambiar mi cita/iu',$t))return 'change';
  if(preg_match('/reserv|agend|cita|turno|quiero|quisiera|gustaria|necesito|mejor (?:el|con|a las)/iu',$t)&&!preg_match('/no se|no s\x{e9}|no quiero|no necesito/iu',$t))return 'booking';
  return 'other';
 }
}
