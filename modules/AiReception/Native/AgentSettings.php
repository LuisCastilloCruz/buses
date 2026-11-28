<?php
namespace Modules\AiReception\Native;
final class AgentSettings {
 public function save(array $input):void {
  $a=app(Access::class);$a->require('aireception','configure');
  $v=app('validator')->make($input,['enabled'=>'required|boolean','mode'=>'required|in:simulated,sandbox,local','model'=>'nullable|string|max:100|regex:/^[A-Za-z0-9._:-]+$/','max_output_tokens'=>'required|integer|min:128|max:4096','api_key'=>'nullable|string|min:16|max:1024'])->validate();
  if($v['mode']==='local' && (!in_array($v['model'],['qwen3:1.7b','qwen3:4b'],true) || $v['max_output_tokens']>1024))abort(422,'Modelo local o limite no autorizado.');
  try{$profile=isset($input['profile'])?ReceptionProfile::validate((array)$input['profile']):null;}catch(\InvalidArgumentException $e){abort(422,'Perfil administrativo invalido.');}
  $options=app('validator')->make($input,['temperature'=>'nullable|numeric|min:0|max:0.7','context'=>'nullable|integer|in:2048,4096'])->validate();
  $db=app('db')->connection('tenant');$db->transaction(function()use($db,$v,$a,$profile,$options){
   $db->table('companies')->orderBy('id')->lockForUpdate()->first();$old=$db->table('aireception_agent_settings')->where('id',1)->first();$private=$old?app('encrypter')->decrypt($old->encrypted_credentials):['api_key'=>''];
   if($profile!==null)$private['profile']=$profile;
   if(isset($options['temperature']))$private['local_options']['temperature']=(float)$options['temperature'];if(isset($options['context']))$private['local_options']['context']=(int)$options['context'];
   if(!empty($v['api_key']))$private['api_key']=$v['api_key'];unset($v['api_key']);
   if($v['mode']==='sandbox' && $v['enabled'] && (empty($private['api_key']) || empty($v['model'])))abort(422,'Configura modelo y credencial de pruebas.');
   $db->table('aireception_agent_settings')->updateOrInsert(['id'=>1],$v+['encrypted_credentials'=>app('encrypter')->encrypt($private),'created_at'=>$old?$old->created_at:date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
   if($profile!==null)foreach(['faq'=>'Preguntas frecuentes aprobadas','policies'=>'Politicas administrativas aprobadas'] as $field=>$title){if(array_key_exists($field,$profile)){if(trim($profile[$field])!=='')app(KnowledgeBase::class)->save(['source'=>'profile-'.$field,'title'=>$title,'body'=>$profile[$field],'classification'=>'administrative','approved'=>1]);elseif($db->table('aireception_knowledge_documents')->where('business_id',$a->business())->where('source','profile-'.$field)->where('active',true)->exists())app(KnowledgeBase::class)->remove('profile-'.$field);}}
   $db->table('aireception_events')->insert(['business_id'=>$a->business(),'actor_id'=>auth('web')->id(),'booking_id'=>null,'action'=>'configure_agent','details'=>json_encode($v),'created_at'=>date('Y-m-d H:i:s')]);
  },3);
 }
 public function publicProfile():array {
  app(Access::class)->require('aireception','configure');$row=app('db')->connection('tenant')->table('aireception_agent_settings')->where('id',1)->first();return $row?(app('encrypter')->decrypt($row->encrypted_credentials)['profile']??[]):[];
 }
 public function localOptions():array {app(Access::class)->require('aireception','configure');$row=app('db')->connection('tenant')->table('aireception_agent_settings')->where('id',1)->first();return $row?(app('encrypter')->decrypt($row->encrypted_credentials)['local_options']??[]):[];}
 public function privateConfiguration():array {
  app(Access::class)->require('aireception','conversations');$row=app('db')->connection('tenant')->table('aireception_agent_settings')->where('id',1)->first();
  if(!$row)return ['enabled'=>false,'mode'=>'simulated'];
  return ['enabled'=>(bool)$row->enabled,'mode'=>$row->mode,'model'=>$row->model,'max_output_tokens'=>(int)$row->max_output_tokens]+app('encrypter')->decrypt($row->encrypted_credentials);
 }
}
