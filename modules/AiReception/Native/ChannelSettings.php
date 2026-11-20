<?php
namespace Modules\AiReception\Native;
final class ChannelSettings {
 public function save(array $input):int {
  $access=app(Access::class);$access->require('aireception','configure');$business=$access->business();
  $v=app('validator')->make($input,['phone_id'=>'required|regex:/^[0-9]{5,64}$/','label'=>'required|string|max:100','app_secret'=>'required|string|min:16|max:512','verify_token'=>'required|string|min:16|max:512','access_token'=>'nullable|string|max:2048','waba_id'=>'required|regex:/^[0-9]{5,64}$/','version'=>'required|regex:/^v[0-9]{2}\.0$/','enabled'=>'required|boolean'])->validate();
  app(ChannelOwnership::class)->claim($v['phone_id'],$business,'aireception');$db=app('db')->connection('system');
  return $db->transaction(function()use($db,$v,$business){
   $old=$db->table('aireception_channels')->where('phone_id',$v['phone_id'])->lockForUpdate()->first();if($old && (int)$old->website_id!==$business)abort(403);
   $values=['website_id'=>$business,'phone_id'=>$v['phone_id'],'label'=>$v['label'],'enabled'=>$v['enabled'],'encrypted_settings'=>app('encrypter')->encrypt(['mode'=>'sandbox','app_secret'=>$v['app_secret'],'verify_token'=>$v['verify_token'],'token'=>$v['access_token']??'','waba_id'=>$v['waba_id'],'version'=>$v['version']]),'updated_at'=>date('Y-m-d H:i:s')];
   if($old){$db->table('aireception_channels')->where('id',$old->id)->update($values);return (int)$old->id;}
   return (int)$db->table('aireception_channels')->insertGetId($values+['created_at'=>date('Y-m-d H:i:s')]);
  },3);
 }
}
