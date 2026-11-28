<?php
namespace Modules\AiReception\Native;
/** A bounded verified-selection summary, never a substitute for booking authorization. */
final class ConversationMemory {
 public static function merge(array $old,array $changes):array {
  $allowed=['service_id','professional_id','day','start'];foreach($changes as $k=>$v){if(!in_array($k,$allowed,true))throw new \InvalidArgumentException('Untrusted memory field.');if(substr($k,-3)==='_id' && (!is_int($v)||$v<1))throw new \InvalidArgumentException('Invalid memory identity.');if($k==='day' && (!is_string($v)||!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$v)))throw new \InvalidArgumentException('Invalid memory date.');if($k==='start' && (!is_string($v)||!preg_match('/^\d{2}:\d{2}$/D',$v)))throw new \InvalidArgumentException('Invalid memory time.');}
  foreach(['service_id','professional_id','day'] as $k)if(isset($changes[$k]) && ($old[$k]??null)!==$changes[$k])unset($old['start']);
  return array_intersect_key(array_replace($old,$changes),array_flip($allowed));
 }
 private function authorize(int $conversation,int $person):void {
  $a=app(Access::class);$a->require('aireception','conversations');if(!app('db')->connection('tenant')->table('aireception_conversations')->where('id',$conversation)->where('person_id',$person)->exists())abort(404);
 }
 public function load(int $conversation,int $person):array {
  $this->authorize($conversation,$person);$row=app('db')->connection('tenant')->table('aireception_conversation_memory')->where('conversation_id',$conversation)->where('business_id',app(Access::class)->business())->where('person_id',$person)->first();
  return $row && $row->expires_at>date('Y-m-d H:i:s')?app('encrypter')->decrypt($row->encrypted_state):[];
 }
 public function record(int $conversation,int $person,string $tool,array $arguments):void {
  if(!in_array($tool,['availability','prepare_booking'],true))return;$this->authorize($conversation,$person);$db=app('db')->connection('tenant');$business=app(Access::class)->business();
  $db->transaction(function()use($db,$business,$conversation,$person,$arguments){$db->table('aireception_conversations')->where('id',$conversation)->lockForUpdate()->first();$old=$db->table('aireception_conversation_memory')->where('conversation_id',$conversation)->first();$valid=$old && $old->expires_at>date('Y-m-d H:i:s');$state=$valid?app('encrypter')->decrypt($old->encrypted_state):[];$state=self::merge($state,array_intersect_key($arguments,array_flip(['service_id','professional_id','day','start'])));$now=date('Y-m-d H:i:s');$db->table('aireception_conversation_memory')->updateOrInsert(['conversation_id'=>$conversation],['business_id'=>$business,'person_id'=>$person,'encrypted_state'=>app('encrypter')->encrypt($state),'expires_at'=>$valid?$old->expires_at:date('Y-m-d H:i:s',time()+86400),'created_at'=>$valid?$old->created_at:$now,'updated_at'=>$now]);},3);
 }
 public function purgeExpired():int {app(Access::class)->require('aireception','configure');return app('db')->connection('tenant')->table('aireception_conversation_memory')->where('business_id',app(Access::class)->business())->where('expires_at','<=',date('Y-m-d H:i:s'))->delete();}
}
