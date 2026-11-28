<?php
namespace Modules\AiReception\Native;
final class ConversationCoordinator {
 public function turn(int $person,string $text,?int $conversation,string $key):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();$actor=(int)auth('web')->id();$db=app('db')->connection('tenant');
  if(strlen(trim($text))<1 || strlen($text)>2000 || !preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$key))abort(422);
  if(!$db->table('persons')->where('id',$person)->where('type','customers')->exists())abort(404);
  $hash=hash('sha256',json_encode([$person,$text,$conversation]));
  $turn=$db->transaction(function()use($db,$actor,$person,$conversation,$text,$key,$hash){
   $db->table('users')->where('id',$actor)->lockForUpdate()->first();$old=$db->table('aireception_agent_turns')->where('actor_id',$actor)->where('request_key',$key)->first();
   if($old){if(!hash_equals($old->request_hash,$hash))abort(409);if(!$old->encrypted_result)abort(409,'Turno pendiente de conciliacion.');return ['replay'=>app('encrypter')->decrypt($old->encrypted_result)];}
   $c=$conversation?$db->table('aireception_conversations')->where('id',$conversation)->where('person_id',$person)->lockForUpdate()->first():null;if($conversation && !$c)abort(404);
   if($c && $db->table('aireception_agent_turns')->where('conversation_id',$c->id)->where('state','pending')->exists())abort(409,'Otra solicitud de esta conversacion esta en curso.');
   $now=date('Y-m-d H:i:s');$id=$c?$c->id:$db->table('aireception_conversations')->insertGetId(['person_id'=>$person,'status'=>'automated','created_at'=>$now,'updated_at'=>$now]);
   $turnId=$db->table('aireception_agent_turns')->insertGetId(['actor_id'=>$actor,'person_id'=>$person,'conversation_id'=>$id,'request_key'=>$key,'request_hash'=>$hash,'state'=>'pending','created_at'=>$now,'updated_at'=>$now]);
   $db->table('aireception_messages')->insert(['conversation_id'=>$id,'direction'=>'incoming','body'=>app('encrypter')->encrypt($text),'is_encrypted'=>true,'created_at'=>$now]);
   return ['id'=>$turnId,'conversation_id'=>$id,'human'=>$c && $c->status==='human'];
  },3);
  if(isset($turn['replay']))return $turn['replay'];
  $tools=app(ReceptionTools::class);$reply=['reply'=>'Recepcion humana revisara tu solicitud.','handoff'=>true];
  try{
   if(!$turn['human']){
    $settings=app(AgentSettings::class)->privateConfiguration();
    if(ReceptionTools::clinical($text))$reply=$tools->simulated($text,$person);
    elseif(!in_array($settings['mode']??'simulated',['sandbox','local'],true)){$reply=$tools->simulated($text,$person);$reply['reply']='Simulacion local: '.$reply['reply'].(isset($reply['reply_hint'])?' '.$reply['reply_hint']:'');}
    elseif(!empty($settings['enabled'])){
     $history=$db->table('aireception_messages')->where('conversation_id',$turn['conversation_id'])->orderByDesc('id')->limit(20)->get()->reverse();$messages=[];
     foreach($history as $m){$body=$m->is_encrypted?app('encrypter')->decrypt($m->body):$m->body;if(ReceptionTools::clinical($body))throw new \RuntimeException('Clinical conversation requires human review.');$messages[]=['role'=>$m->direction==='incoming'?'user':'assistant','content'=>$body];}
     $catalog=['services'=>$db->table('aireception_services')->where('business_id',$business)->where('active',true)->select('id','name','duration_minutes')->limit(20)->get()->toArray(),'professionals'=>$db->table('aireception_professionals')->where('business_id',$business)->where('active',true)->select('id','name')->limit(20)->get()->toArray()];
     array_unshift($messages,['role'=>'assistant','content'=>'Catalogo verificado: '.json_encode($catalog)]);
     $memory=app(ConversationMemory::class)->load((int)$turn['conversation_id'],$person);array_unshift($messages,['role'=>'assistant','content'=>'Seleccion previa verificada, no reserva confirmada: '.json_encode($memory)]);
     $response=app($settings['mode']==='local'?LocalResponses::class:BudgetedResponses::class)->propose($messages,$tools->definitions(),'conversation-turn-'.$turn['id']);
     if(count($response['proposals'])!==1)throw new \RuntimeException('Verified tool proposal required.');
     $proposal=$response['proposals'][0];$reply=$tools->run($proposal['name'],$proposal['arguments'],$person);app(ConversationMemory::class)->record((int)$turn['conversation_id'],$person,$proposal['name'],$proposal['arguments']);
    }
   }
  }catch(\Throwable $e){$reply=['reply'=>'No puedo completar la solicitud automaticamente. La he derivado a recepcion humana; no se ha confirmado ninguna cita.','handoff'=>true];}
  $reply['conversation_id']=$turn['conversation_id'];
  $reply=$db->transaction(function()use($db,$turn,$reply){
   $current=$db->table('aireception_conversations')->where('id',$turn['conversation_id'])->lockForUpdate()->first();
   if($current->status==='human' && empty($reply['handoff']))$reply=['reply'=>'La conversacion esta pausada para atencion humana. No se ha confirmado ninguna cita.','handoff'=>true,'conversation_id'=>$turn['conversation_id']];
   $db->table('aireception_messages')->insert(['conversation_id'=>$turn['conversation_id'],'direction'=>'simulation','body'=>app('encrypter')->encrypt($reply['reply']),'is_encrypted'=>true,'created_at'=>date('Y-m-d H:i:s')]);
   if(!empty($reply['handoff']))$db->table('aireception_conversations')->where('id',$turn['conversation_id'])->update(['status'=>'human','updated_at'=>date('Y-m-d H:i:s')]);
   $db->table('aireception_agent_turns')->where('id',$turn['id'])->update(['state'=>'completed','encrypted_result'=>app('encrypter')->encrypt($reply),'updated_at'=>date('Y-m-d H:i:s')]);return $reply;
  },3);
  return $reply;
 }
}
