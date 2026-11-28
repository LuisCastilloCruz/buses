<?php
namespace Modules\AiReception\Native;
/** Channel-derived context only. No actor, tenant or confirmation supplied by a model. */
final class ChannelReception {
 public function process(int $inbound,?callable $provider=null):array {
  $access=app(Access::class);$business=$access->business();if(!$access->enabled('aireception'))abort(403);$db=app('db')->connection('tenant');
  $m=$db->table('aireception_inbound_messages')->where('id',$inbound)->first();if(!$m)abort(404);
  $channel=app('db')->connection('system')->table('aireception_channels')->where('id',$m->channel_id)->where('website_id',$business)->where('enabled',true)->first();if(!$channel)abort(403);
  $c=$db->table('aireception_conversations')->where('channel_id',$channel->id)->where('sender_hash',$m->sender_identity)->first();if(!$c)return ['state'=>'awaiting_identity'];
  $link=$db->table('aireception_contact_links')->where('channel_id',$channel->id)->where('sender_hash',$m->sender_identity)->where('person_id',$c->person_id)->first();if(!$link)abort(403);
  $actor=(int)config('aireception.channel_actors.'.$business.'.'.$channel->id,0);if(!$actor)return ['state'=>'actor_unconfigured'];
  $user=\App\Models\Tenant\User::find($actor);if(!$user || $user->aireception_suspended)abort(403);$guard=auth('web');$previous=$guard->user();$guard->setUser($user);
  try{
   foreach(['conversations','calendar','book'] as $level)$access->require('aireception',$level);
   $claim=$db->transaction(function()use($db,$c,$m){
    $current=$db->table('aireception_conversations')->where('id',$c->id)->lockForUpdate()->first();
    $old=$db->table('aireception_channel_turns')->where('channel_id',$m->channel_id)->where('message_id',$m->message_id)->first();
    if($old)return $old->encrypted_result?['replay'=>app('encrypter')->decrypt($old->encrypted_result)]:['replay'=>['state'=>'reconcile_required']];
    if($current->status!=='automated')return ['replay'=>['state'=>'paused']];
    if($db->table('aireception_channel_turns')->where('conversation_id',$c->id)->where('state','pending')->exists())return ['replay'=>['state'=>'busy']];
    $now=date('Y-m-d H:i:s');return ['id'=>$db->table('aireception_channel_turns')->insertGetId(['channel_id'=>$m->channel_id,'conversation_id'=>$c->id,'message_id'=>$m->message_id,'state'=>'pending','created_at'=>$now,'updated_at'=>$now])];
   },3);
   if(isset($claim['replay']))return $claim['replay'];
   $content=app('encrypter')->decrypt($m->encrypted_content);$text=$content['text'];$reply=['reply'=>'Recepcion humana revisara tu solicitud.','handoff'=>true];
   try{
    if($m->type==='audio'){$transcription=app(BudgetedAudio::class)->process((int)$m->id);$text=$transcription['untrusted_transcript'];}elseif($m->type!=='text')throw new \RuntimeException('Unsupported input.');
    if(!is_string($text) || strlen($text)>2000)throw new \RuntimeException('Invalid input.');
    $pending=$db->table('aireception_pending_confirmations')->where('conversation_id',$c->id)->first();
    if($m->type==='text' && preg_match('/^CONFIRMAR ([A-F0-9]{8})$/D',trim($text),$match)){
     if(!$pending || !hash_equals($pending->code,$match[1]) || (int)$pending->actor_id!==$actor || $pending->expires_at<=date('Y-m-d H:i:s'))throw new \RuntimeException('Confirmation expired or mismatched.');
     $token=app('encrypter')->decrypt($pending->encrypted_token);$result=app(BookingProposals::class)->confirm($token,true,function()use($db,$c){$current=$db->table('aireception_conversations')->where('id',$c->id)->lockForUpdate()->first();if(!$current || $current->status!=='automated')abort(409,'Conversacion pausada.');});
     $reply=['reply'=>'Cita #'.$result['booking']['id'].': '.$result['booking']['day'].' '.$result['booking']['start'].' / '.$result['booking']['status'].'.','booking'=>$result['booking']];
    }elseif(ReceptionTools::clinical($text))$reply=app(ReceptionTools::class)->simulated($text,(int)$c->person_id);
    else{
     $tools=app(ReceptionTools::class);$history=$db->table('aireception_messages')->where('conversation_id',$c->id)->orderByDesc('id')->limit(12)->get()->reverse();$messages=[];
     foreach($history as $h){$body=$h->is_encrypted?app('encrypter')->decrypt($h->body):$h->body;if(ReceptionTools::clinical($body))throw new \RuntimeException('Clinical history.');$messages[]=['role'=>$h->direction==='incoming'?'user':'assistant','content'=>substr($body,0,2000)];}
     $catalog=['services'=>$db->table('aireception_services')->where('business_id',$business)->where('active',true)->select('id','name','duration_minutes')->limit(20)->get()->toArray(),'professionals'=>$db->table('aireception_professionals')->where('business_id',$business)->where('active',true)->select('id','name')->limit(20)->get()->toArray()];array_unshift($messages,['role'=>'assistant','content'=>'Catalogo verificado para seleccionar identificadores: '.json_encode($catalog)]);
     $memoryService=app(ConversationMemory::class);$memoryService->rememberRequest((int)$c->id,(int)$c->person_id,$text);$memory=app(ConversationMemory::class)->load((int)$c->id,(int)$c->person_id);array_unshift($messages,['role'=>'assistant','content'=>'Seleccion previa verificada, no reserva confirmada: '.json_encode($memory)]);
     if($m->type==='audio')$messages[]=['role'=>'user','content'=>$text];
     if($provider){if(!app()->environment('local','testing') || config('aireception.v5_transport_mode','simulated')!=='simulated')throw new \RuntimeException('Fake provider forbidden.');$response=$provider($messages,$tools->definitions());}
     elseif(config('aireception.ollama_enabled',false))$response=app(LocalResponses::class)->propose($messages,$tools->definitions(),'channel-turn-'.$claim['id']);
     elseif(config('aireception.external_connections_enabled',false))$response=app(BudgetedResponses::class)->propose($messages,$tools->definitions(),'channel-turn-'.$claim['id']);
     else throw new \RuntimeException('Provider disabled.');
     if(count($response['proposals']??[])!==1)throw new \RuntimeException('Verified tool required.');$call=$response['proposals'][0];$reply=$tools->run($call['name'],$call['arguments'],(int)$c->person_id,(int)$c->id);app(ConversationMemory::class)->record((int)$c->id,(int)$c->person_id,$call['name'],$call['arguments']);
     if(isset($reply['proposal'])){$p=$reply['proposal'];$code=strtoupper(substr(hash('sha256',$p['confirmation_token']),0,8));$db->table('aireception_pending_confirmations')->updateOrInsert(['conversation_id'=>$c->id],['encrypted_token'=>app('encrypter')->encrypt($p['confirmation_token']),'code'=>$code,'expires_at'=>$p['expires_at'],'actor_id'=>$actor]);unset($reply['proposal']);$reply['reply'].=' Responde exactamente CONFIRMAR '.$code.' antes de 5 minutos.';}
     elseif($pending)$db->table('aireception_pending_confirmations')->where('id',$pending->id)->delete();
    }
   }catch(\Throwable $e){$reply=['reply'=>'No se ha confirmado una nueva operacion. Recepcion humana revisara tu solicitud.','handoff'=>true];}
   return $db->transaction(function()use($db,$c,$claim,$reply){
    $current=$db->table('aireception_conversations')->where('id',$c->id)->lockForUpdate()->first();
    if($current->status!=='automated' && empty($reply['booking']))$reply=['reply'=>'La conversacion esta en atencion humana.','handoff'=>true];
    if(!empty($reply['handoff']))$db->table('aireception_conversations')->where('id',$c->id)->update(['status'=>'human']);
    // Persist response and outbox atomically, even if delivery is subsequently suppressed.
    $out=app(Outbox::class)->enqueue((int)$c->id,['type'=>'text','body'=>$reply['reply']],'channel-response-'.$claim['id']);
    $db->table('aireception_messages')->insert(['conversation_id'=>$c->id,'channel_id'=>$c->channel_id,'direction'=>'outgoing_queued','body'=>app('encrypter')->encrypt($reply['reply']),'is_encrypted'=>true,'created_at'=>date('Y-m-d H:i:s')]);
    $reply['outbox_id']=$out;$reply['state']='completed';$db->table('aireception_channel_turns')->where('id',$claim['id'])->update(['state'=>'completed','encrypted_result'=>app('encrypter')->encrypt($reply),'updated_at'=>date('Y-m-d H:i:s')]);return $reply;
   },3);
  }finally{if($previous)$guard->setUser($previous);else $guard->forgetUser();}
 }
}