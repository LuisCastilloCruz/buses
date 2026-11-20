<?php
namespace Modules\AiReception\Native;
final class ChannelOwnership {
 public function claim(string $phone,int $website,string $owner):void {
  if(!preg_match('/^[0-9]{5,64}$/D',$phone) || !in_array($owner,['aireception','tattoo_inbox'],true) || $website!==app(Access::class)->business())abort(403);
  $db=app('db')->connection('system');
  $db->transaction(function()use($db,$phone,$website,$owner){
   $db->statement('INSERT IGNORE INTO aireception_channel_ownership (phone_id,website_id,owner) VALUES (?,?,?)',[$phone,$website,$owner]);
   $claim=$db->table('aireception_channel_ownership')->where('phone_id',$phone)->lockForUpdate()->first();
   if((int)$claim->website_id!==$website || $claim->owner!==$owner)abort(409,'Canal asignado a otro receptor; requiere transferencia revisada.');
   if($owner==='aireception' && $db->getSchemaBuilder()->hasTable('tattoo_inbox_meta_channels') && $db->table('tattoo_inbox_meta_channels')->where('platform','whatsapp')->where('meta_identifier_type','whatsapp_phone_number_id')->where('meta_identifier_value',$phone)->where('is_active',true)->exists())abort(409,'Canal activo en TattooInbox; no se permite doble recepcion.');
  },3);
 }
}
