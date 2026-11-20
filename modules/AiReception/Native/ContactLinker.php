<?php
namespace Modules\AiReception\Native;
final class ContactLinker {
 public function link(int $messageId,int $person,string $evidence):void {
  $access=app(Access::class);$access->require('aireception','configure');$business=$access->business();$db=app('db')->connection('tenant');
  if(strlen(trim($evidence))<10 || strlen($evidence)>255)abort(422,'Registra referencia de verificacion.');
  if(!$db->table('persons')->where('id',$person)->where('type','customers')->exists())abort(404);
  $db->transaction(function()use($db,$messageId,$person,$evidence,$business){
   $db->table('companies')->orderBy('id')->lockForUpdate()->first();$m=$db->table('aireception_inbound_messages')->where('id',$messageId)->first();if(!$m)abort(404);
   $channel=app('db')->connection('system')->table('aireception_channels')->where('id',$m->channel_id)->where('website_id',$business)->first();if(!$channel)abort(403);
   $old=$db->table('aireception_contact_links')->where('channel_id',$m->channel_id)->where('sender_hash',$m->sender_identity)->first();
   if($old && (int)$old->person_id!==$person)abort(409,'Identidad ya vinculada; requiere revision, no sobrescritura.');
   if(!$old)$db->table('aireception_contact_links')->insert(['channel_id'=>$m->channel_id,'sender_hash'=>$m->sender_identity,'person_id'=>$person,'verified_by'=>auth('web')->id(),'evidence'=>$evidence,'verified_at'=>date('Y-m-d H:i:s')]);
   $rows=$db->table('aireception_inbound_messages')->where('channel_id',$m->channel_id)->where('sender_identity',$m->sender_identity)->orderBy('provider_timestamp')->orderBy('id')->get();
   foreach($rows as $row)$this->attach($db,$row,$person);
   $db->table('aireception_events')->insert(['business_id'=>$business,'actor_id'=>auth('web')->id(),'booking_id'=>null,'action'=>'verify_contact','details'=>json_encode(['channel_id'=>$m->channel_id,'person_id'=>$person,'evidence'=>$evidence]),'created_at'=>date('Y-m-d H:i:s')]);
  },3);
 }
 public function attach($db,$message,int $person):void {
  if($db->transactionLevel()<1)throw new \LogicException('Identity bridge requires transaction.');
  if($db->table('aireception_messages')->where('channel_id',$message->channel_id)->where('external_id',$message->message_id)->exists())return;
  $conversation=$db->table('aireception_conversations')->where('channel_id',$message->channel_id)->where('sender_hash',$message->sender_identity)->first();$now=date('Y-m-d H:i:s');
  if($conversation && (int)$conversation->person_id!==$person)abort(409);
  $id=$conversation?$conversation->id:$db->table('aireception_conversations')->insertGetId(['person_id'=>$person,'channel_id'=>$message->channel_id,'sender_hash'=>$message->sender_identity,'status'=>'human','assigned_user_id'=>null,'created_at'=>$now,'updated_at'=>$now]);
  $content=app('encrypter')->decrypt($message->encrypted_content);
  $db->table('aireception_messages')->insert(['conversation_id'=>$id,'channel_id'=>$message->channel_id,'external_id'=>$message->message_id,'direction'=>'incoming','body'=>app('encrypter')->encrypt($message->type==='text'?$content['text']:'Audio recibido. Requiere procesamiento autorizado.'),'is_encrypted'=>true,'provider_timestamp'=>$message->provider_timestamp,'created_at'=>$now]);
  $db->table('aireception_inbound_messages')->where('id',$message->id)->update(['state'=>'linked_manual']);
 }
}
