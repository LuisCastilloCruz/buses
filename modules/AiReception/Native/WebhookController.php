<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
use Modules\AiReception\Services\MetaWebhook;
class WebhookController extends \Illuminate\Routing\Controller {
 private function channel($id):array {
  abort_unless(config('aireception.meta_sandbox_enabled',false),403);
  $c=app('db')->connection('system')->table('aireception_channels')->where('id',(int)$id)->where('enabled',true)->first();if(!$c)abort(403);
  $s=app('encrypter')->decrypt($c->encrypted_settings);if(($s['mode']??'')!=='sandbox')abort(403);return [$c,$s];
 }
 public function verify(Request $r,$id){list($c,$s)=$this->channel($id);try{return response(MetaWebhook::challenge((string)$r->query('hub_mode',$r->query('hub.mode')),(string)$r->query('hub_verify_token',$r->query('hub.verify_token')),(string)$r->query('hub_challenge',$r->query('hub.challenge')),$s['verify_token']));}catch(\InvalidArgumentException $e){abort(403);}}
 public function receive(Request $r,$id){
  list($channel,$settings)=$this->channel($id);
  try{$messages=MetaWebhook::verifiedMessages($r->getContent(),(string)$r->header('X-Hub-Signature-256'),$settings['app_secret'],[$channel->phone_id=>(string)$channel->website_id]);}catch(\InvalidArgumentException $e){abort(403);}
  $payload=json_decode($r->getContent(),true);
  foreach($payload['entry']??[] as $entry){
   if((string)($entry['id']??'')!==$settings['waba_id'])abort(403);
   foreach($entry['changes']??[] as $change)if(($change['field']??'')==='messages' && (string)($change['value']['metadata']['phone_number_id']??'')!==$channel->phone_id)abort(403);
  }
  $db=app('db')->connection('system');$accepted=0;
  foreach($messages as $message){
   $db->transaction(function()use($db,$channel,$message,&$accepted){
    // Channel lock serializes deduplication and avoids job/event split across transactions.
    $db->table('aireception_channels')->where('id',$channel->id)->lockForUpdate()->first();
    if($db->table('aireception_webhook_events')->where('channel_id',$channel->id)->where('message_id',$message['message_id'])->exists())return;
    $now=date('Y-m-d H:i:s');$id=$db->table('aireception_webhook_events')->insertGetId(['channel_id'=>$channel->id,'message_id'=>$message['message_id'],'encrypted_payload'=>app('encrypter')->encrypt($message),'state'=>'pending','created_at'=>$now,'updated_at'=>$now]);
    \Modules\AiReception\Jobs\ProcessInbound::dispatch((int)$id);$accepted++;
   },3);
  }
  return response()->json(['accepted'=>$accepted]);
 }
}
