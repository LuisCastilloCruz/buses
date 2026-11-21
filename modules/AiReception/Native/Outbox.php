<?php
namespace Modules\AiReception\Native;
/** Durable tenant outbox. Unknown POST outcomes are never automatically repeated. */
final class Outbox {
 public function enqueue(int $conversation,array $message,string $key,?array $booking=null):int {
  $access=app(Access::class);$business=$access->business();if(!$access->enabled('aireception'))abort(403);
  if(!preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$key))abort(422);
  $mode=config('aireception.v5_transport_mode','simulated');if(!in_array($mode,['simulated','sandbox'],true))abort(403);
  $db=app('db')->connection('tenant');
  return $db->transaction(function()use($db,$business,$conversation,$message,$key,$booking,$mode){
   $c=$db->table('aireception_conversations')->where('id',$conversation)->lockForUpdate()->first();if(!$c || !$c->channel_id)abort(409,'Conversacion sin canal verificado.');
   $channel=app('db')->connection('system')->table('aireception_channels')->where('id',$c->channel_id)->where('website_id',$business)->where('enabled',true)->first();if(!$channel)abort(403);
   $link=$db->table('aireception_contact_links')->where('channel_id',$c->channel_id)->where('sender_hash',$c->sender_hash)->where('person_id',$c->person_id)->first();if(!$link)abort(403);
   $in=$db->table('aireception_inbound_messages')->where('channel_id',$c->channel_id)->where('sender_identity',$c->sender_hash)->orderByDesc('id')->first();if(!$in)abort(409);
   $content=app('encrypter')->decrypt($in->encrypted_content);$identity=$content['sender_identity'];if(!preg_match('/^phone:([0-9]{6,15})$/D',$identity,$phone))abort(409,'Identidad sin destinatario telefonico autorizado.');$recipient=$phone[1];
   if(!hash_equals($c->sender_hash,hash_hmac('sha256',$identity,config('app.key').'|'.$business)))abort(403);
   if(($message['type']??'')==='text'){if(!is_string($message['body']??null) || !trim($message['body']) || strlen($message['body'])>4096)abort(422);}elseif(($message['type']??'')!=='template')abort(422);
   $payload=['recipient'=>$recipient,'sender_identity'=>$identity,'message'=>$message];$hash=hash('sha256',json_encode([$conversation,$payload,$booking,$mode]));
   $old=$db->table('aireception_outbox')->where('business_id',$business)->where('request_key',$key)->first();if($old){if(!hash_equals($old->request_hash,$hash))abort(409);return (int)$old->id;}
   $now=date('Y-m-d H:i:s');return (int)$db->table('aireception_outbox')->insertGetId(['business_id'=>$business,'channel_id'=>$c->channel_id,'conversation_id'=>$c->id,'person_id'=>$c->person_id,'request_key'=>$key,'request_hash'=>$hash,'encrypted_payload'=>app('encrypter')->encrypt($payload),'state'=>'pending','mode'=>$mode,'available_at'=>$now,'booking_id'=>$booking['id']??null,'booking_version'=>$booking['version']??null,'reminder_id'=>$booking['reminder_id']??null,'created_at'=>$now,'updated_at'=>$now]);
  },3);
 }
 public function process(int $id,?callable $transport=null):string {
  $business=app(Access::class)->business();if(!app(Access::class)->enabled('aireception'))abort(403);$db=app('db')->connection('tenant');
  $claimed=$db->transaction(function()use($db,$id,$business){
   $r=$db->table('aireception_outbox')->where('id',$id)->where('business_id',$business)->lockForUpdate()->first();if(!$r)abort(404);
   if($r->state==='sending' && $r->claimed_at<date('Y-m-d H:i:s',time()-120)){$this->finish($db,$id,'unknown','worker_interrupted');return ['state'=>'unknown'];}
   if(!in_array($r->state,['pending','retry'],true) || $r->available_at>date('Y-m-d H:i:s'))return ['state'=>$r->state];
   $c=$db->table('aireception_conversations')->where('id',$r->conversation_id)->first();$channel=app('db')->connection('system')->table('aireception_channels')->where('id',$r->channel_id)->where('website_id',$business)->where('enabled',true)->first();
   $link=$c?$db->table('aireception_contact_links')->where('channel_id',$r->channel_id)->where('sender_hash',$c->sender_hash)->where('person_id',$r->person_id)->first():null;
   $consent=$db->table('aireception_contact_consents')->where('person_id',$r->person_id)->where('channel','whatsapp')->first();
   if(!$channel || !$c || !$link || (int)$c->channel_id!==(int)$r->channel_id || (int)$c->person_id!==(int)$r->person_id){$this->finish($db,$id,'suppressed','channel_or_identity');return ['state'=>'suppressed'];}
   if(!$consent || !$consent->allowed || ($consent->expires_at && $consent->expires_at<=date('Y-m-d H:i:s'))){$this->finish($db,$id,'suppressed','no_consent');return ['state'=>'suppressed'];}
   if($r->booking_id){$b=$db->table('aireception_bookings')->where('id',$r->booking_id)->where('business_id',$business)->first();if(!$b || $b->status!=='confirmed' || (int)$b->version!==(int)$r->booking_version){$this->finish($db,$id,'cancelled','booking_changed');return ['state'=>'cancelled'];}}
   $payload=app('encrypter')->decrypt($r->encrypted_payload);if(!hash_equals($c->sender_hash,hash_hmac('sha256',$payload['sender_identity'],config('app.key').'|'.$business)))abort(403);
   $last=$db->table('aireception_inbound_messages')->where('channel_id',$r->channel_id)->where('sender_identity',$c->sender_hash)->max('provider_timestamp');$settings=app('encrypter')->decrypt($channel->encrypted_settings);
   if($payload['message']['type']==='text' && (!$last || $last>time() || time()-(int)$last>=86400)){$this->finish($db,$id,'suppressed','template_required');return ['state'=>'suppressed'];}
   if($payload['message']['type']==='template' && !in_array(($payload['message']['name']??'').':'.($payload['message']['language']??''),$settings['approved_templates']??[],true)){$this->finish($db,$id,'suppressed','template_unapproved');return ['state'=>'suppressed'];}
   if($r->mode==='sandbox' && (!config('aireception.external_connections_enabled',false) || !config('aireception.gateway_authorized',false)))return ['state'=>'disabled'];
   $db->table('aireception_outbox')->where('id',$id)->update(['state'=>'sending','claimed_at'=>date('Y-m-d H:i:s'),'attempts'=>$r->attempts+1]);
   return ['row'=>$r,'payload'=>$payload,'channel'=>$channel,'settings'=>$settings,'last'=>$last];
  },3);
  if(isset($claimed['state']))return $claimed['state'];$r=$claimed['row'];
  try{
   if($r->mode==='simulated'){$state='simulated_sent';$provider=null;}
   else {if(!$transport)$transport=function($v){$settings=$v['settings']+['enabled'=>true,'phone_number_id'=>$v['channel']->phone_id];$settings['request_key']='outbox-'.$v['row']->id;$settings['channel_id']=(int)$v['channel']->id;return (new \Modules\AiReception\ProvidersApi\WhatsAppClient(new \Modules\AiReception\ProvidersApi\GatewayTransport()))->send($settings,$v['payload']['recipient'],$v['payload']['message'],true,(int)$v['last'],time());};$provider=$transport($claimed);if(!is_string($provider) || !$provider || strlen($provider)>255)throw new \RuntimeException('Invalid provider response');$state='accepted';}
   $db->transaction(function()use($db,$id,$state,$provider){$db->table('aireception_outbox')->where('id',$id)->update(['provider_id'=>$provider]);$this->finish($db,$id,$state,null);},3);return $state;
  }catch(\Modules\AiReception\ProvidersApi\RetryableDelivery $e){$state=$r->attempts+1>=3?'failed':'retry';$db->transaction(function()use($db,$id,$state,$r){$this->finish($db,$id,$state,'provider_rate_rejected');$db->table('aireception_outbox')->where('id',$id)->update(['available_at'=>date('Y-m-d H:i:s',time()+30*(2**$r->attempts))]);},3);return $state;}catch(\Throwable $e){$this->finish($db,$id,'unknown','reconcile_required');return 'unknown';}
 }
 private function finish($db,int $id,string $state,?string $error):void {
  $db->table('aireception_outbox')->where('id',$id)->update(['state'=>$state,'last_error'=>$error,'updated_at'=>date('Y-m-d H:i:s')]);$db->table('aireception_outbox_attempts')->insert(['outbox_id'=>$id,'outcome'=>$state,'created_at'=>date('Y-m-d H:i:s')]);
  $row=$db->table('aireception_outbox')->where('id',$id)->first();if($row->reminder_id)$db->table('aireception_reminders')->where('id',$row->reminder_id)->where('booking_id',$row->booking_id)->update(['status'=>'outbox_'.$state,'attempts'=>$row->attempts,'last_error'=>$error,'processed_at'=>in_array($state,['simulated_sent','delivered','read','failed','cancelled','suppressed'],true)?date('Y-m-d H:i:s'):null]);
 }
 public function reconcile(int $id):string {
  $business=app(Access::class)->business();$db=app('db')->connection('tenant');
  return $db->transaction(function()use($db,$business,$id){$r=$db->table('aireception_outbox')->where('id',$id)->where('business_id',$business)->lockForUpdate()->first();if(!$r)abort(404);if(!$r->provider_id)return $r->state;
   $statuses=app('db')->connection('system')->table('aireception_delivery_receipts')->where('channel_id',$r->channel_id)->where('message_id',$r->provider_id)->pluck('status')->all();$next=$r->state;
   foreach(['failed','sent','delivered','read'] as $state)if(in_array($state,$statuses,true))$next=$state;
   if($next!==$r->state)$this->finish($db,$id,$next,null);return $next;
  },3);
 }
}