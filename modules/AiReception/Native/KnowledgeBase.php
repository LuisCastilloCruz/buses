<?php
namespace Modules\AiReception\Native;
/** Lexical retrieval over explicitly approved administrative documents, never clinical records. */
final class KnowledgeBase {
 public static function terms(string $text):array {
  $text=mb_strtolower($text,'UTF-8');preg_match_all('/[\p{L}\p{N}]{3,40}/u',$text,$matches);return array_slice(array_values(array_unique($matches[0])),0,4096);
 }
 private function hashes(string $text):array {return array_map(function($term){return hash_hmac('sha256',$term,config('app.key'));},self::terms($text));}
 public function save(array $input):int {
  $access=app(Access::class);$access->require('aireception','configure');$business=$access->business();
  $v=app('validator')->make($input,['source'=>'required|regex:/^[a-z0-9_-]{3,80}$/','title'=>'required|string|max:160','body'=>'required|string|max:12000','approved'=>'required|accepted','classification'=>'required|in:administrative'])->validate();
  if(preg_match('/sk-[A-Za-z0-9]{15}|BEGIN .*PRIVATE KEY|historia clinica|diagn[oó]stico de paciente|prescripci[oó]n|contrase[nñ]a\s*[:=]/iu',$v['body']))abort(422,'No se permiten secretos ni registros clinicos.');
  $db=app('db')->connection('tenant');return $db->transaction(function()use($db,$v,$business){
   $db->table('companies')->orderBy('id')->lockForUpdate()->first();
   if($db->table('aireception_knowledge_documents')->where('business_id',$business)->where('active',true)->where('source','<>',$v['source'])->count()>=100)abort(422,'Limite documental del laboratorio.');
   $current=$db->table('aireception_knowledge_documents')->where('business_id',$business)->where('source',$v['source'])->where('active',true)->first();if($current && is_array(json_decode($current->term_hashes,true)) && hash_equals($current->content_hash,hash('sha256',$v['body'])) && $current->title===$v['title'])return (int)$current->id;
   $version=1+(int)$db->table('aireception_knowledge_documents')->where('business_id',$business)->where('source',$v['source'])->max('version');
   $db->table('aireception_knowledge_documents')->where('business_id',$business)->where('source',$v['source'])->update(['active'=>false]);$now=date('Y-m-d H:i:s');
   $id=$db->table('aireception_knowledge_documents')->insertGetId(['business_id'=>$business,'source'=>$v['source'],'version'=>$version,'title'=>$v['title'],'content_hash'=>hash('sha256',$v['body']),'encrypted_body'=>app('encrypter')->encrypt($v['body']),'term_hashes'=>json_encode($this->hashes($v['title'].' '.$v['body'])),'active'=>true,'actor_id'=>(int)auth('web')->id(),'created_at'=>$now,'updated_at'=>$now]);
   $this->audit('knowledge_update',$v['source'],$version);return $id;
  },3);
 }
 public function remove(string $source):void {
  $a=app(Access::class);$a->require('aireception','configure');$db=app('db')->connection('tenant');$db->transaction(function()use($db,$a,$source){$db->table('companies')->orderBy('id')->lockForUpdate()->first();$n=$db->table('aireception_knowledge_documents')->where('business_id',$a->business())->where('source',$source)->update(['active'=>false,'encrypted_body'=>null,'term_hashes'=>'[]','updated_at'=>date('Y-m-d H:i:s')]);if(!$n)abort(404);$this->audit('knowledge_remove',$source,0);},3);
 }
 private function audit(string $action,string $source,int $version):void {app('db')->connection('tenant')->table('aireception_events')->insert(['business_id'=>app(Access::class)->business(),'actor_id'=>(int)auth('web')->id(),'booking_id'=>null,'action'=>$action,'details'=>json_encode(['source'=>$source,'version'=>$version]),'created_at'=>date('Y-m-d H:i:s')]);}
 public function search(string $query):array {
  $a=app(Access::class);$a->require('aireception','conversations');if(strlen($query)>200||strlen(trim($query))<2)abort(422);$terms=$this->hashes($query);$matches=[];
  foreach(app('db')->connection('tenant')->table('aireception_knowledge_documents')->where('business_id',$a->business())->where('active',true)->limit(100)->get() as $row){$index=json_decode($row->term_hashes,true);if(!is_array($index))throw new \RuntimeException('Knowledge index requires rebuild.');$score=count(array_intersect($terms,$index));if(!$score||!$row->encrypted_body)continue;$body=app('encrypter')->decrypt($row->encrypted_body);$positions=[];foreach(self::terms($query) as $term){$position=mb_stripos($body,$term,0,'UTF-8');if($position!==false)$positions[]=$position;}$offset=$positions?max(0,min($positions)-250):0;$matches[]=['score'=>$score,'source'=>$row->source,'version'=>(int)$row->version,'title'=>$row->title,'excerpt'=>mb_substr($body,$offset,800,'UTF-8')];}
  usort($matches,function($a,$b){return $b['score']<=>$a['score'];});return array_slice($matches,0,3);
 }
}
