<?php
namespace Modules\AiReception\Native;
final class BudgetedAudio {
 public function process(int $id,?callable $transport=null):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();$db=app('db')->connection('tenant');$m=$db->table('aireception_inbound_messages')->where('id',$id)->first();if(!$m || $m->type!=='audio')abort(404);
  $channel=app('db')->connection('system')->table('aireception_channels')->where('id',$m->channel_id)->where('website_id',$business)->where('enabled',true)->first();if(!$channel)abort(403);
  $link=$db->table('aireception_contact_links')->where('channel_id',$m->channel_id)->where('sender_hash',$m->sender_identity)->first();if(!$link)abort(403);$consent=$db->table('aireception_contact_consents')->where('person_id',$link->person_id)->where('channel','whatsapp')->first();if(!$consent||!$consent->allowed||($consent->expires_at&&$consent->expires_at<=date('Y-m-d H:i:s')))abort(403);
  $cfg=config('aireception.audio.'.$business,[]);if(empty($cfg['enabled']) || !is_int($cfg['max_units']??null) || $cfg['max_units']<1 || !is_int($cfg['units_per_second']??null) || $cfg['units_per_second']<1 || ($cfg['valid_until']??'')<=date('Y-m-d H:i:s'))abort(403);
  if($cfg['max_units']<120*$cfg['units_per_second'])abort(409,'Reserva insuficiente para duracion maxima.');
  if(!$transport && (!config('aireception.external_connections_enabled',false)||!config('aireception.gateway_authorized',false)))abort(403);
  if($transport && (!app()->environment('local','testing')||config('aireception.v5_transport_mode','simulated')!=='simulated'))abort(403);
  $content=app('encrypter')->decrypt($m->encrypted_content);$key='audio-'.$m->channel_id.'-'.$m->id;$hash=hash('sha256',json_encode([$content['media_id'],$cfg]));$budget=new UsageBudget($db);
  $entry=$budget->reserve($business,date('Y-m'),$key,$hash,'transcription',$cfg['max_units']);
  if($entry['replayed']){$old=$db->table('aireception_usage')->where('id',$entry['id'])->first();if($old->state==='settled' && $old->encrypted_response)return app('encrypter')->decrypt($old->encrypted_response);abort(409,'Audio pendiente de conciliacion.');}
  if($transport)$result=$transport($content['media_id']);else{$response=(new \Modules\AiReception\ProvidersApi\GatewayTransport())->request('POST','ar://transcription',['headers'=>['X-AR-Request-Key'=>$key,'X-AR-Channel'=>(int)$channel->id],'json'=>['media_id'=>$content['media_id']]]);if($response->getStatusCode()!==200)throw new \RuntimeException('Audio unavailable.');$result=json_decode((string)$response->getBody(),true);}
  if(!is_string($result['untrusted_transcript']??null)||strlen($result['untrusted_transcript'])>2000||!is_numeric($result['seconds']??null)||$result['seconds']<=0||$result['seconds']>120)throw new \RuntimeException('Invalid transcription.');
  $actual=(int)ceil($result['seconds']*$cfg['units_per_second']);
  $db->transaction(function()use($db,$budget,$business,$entry,$actual,$result,$m){$budget->settle($business,$entry['id'],$actual);$db->table('aireception_usage')->where('id',$entry['id'])->update(['encrypted_response'=>app('encrypter')->encrypt($result)]);$db->table('aireception_inbound_messages')->where('id',$m->id)->update(['state'=>'transcribed_review']);},3);
  return $result;
 }
}