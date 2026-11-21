<?php
namespace Modules\AiReception\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
class ProcessInbound implements ShouldQueue {
 use Queueable,Dispatchable,InteractsWithQueue;
 public $tries=3;public $timeout=30;public $eventId;
 public function __construct(int $id){$this->eventId=$id;$this->onConnection('aireception');$this->onQueue('aireception');}
 public function handle(){
  $system=app('db')->connection('system');$event=$system->table('aireception_webhook_events')->where('id',$this->eventId)->first();if(!$event || $event->state==='processed')return;
  $channel=$system->table('aireception_channels')->where('id',$event->channel_id)->where('enabled',true)->first();if(!$channel)throw new \RuntimeException('Channel disabled.');
  $environment=app(\Hyn\Tenancy\Environment::class);$previous=$environment->tenant();
  try{
   $environment->tenant(\Hyn\Tenancy\Models\Website::findOrFail($channel->website_id));$access=app(\Modules\AiReception\Native\Access::class);$business=$access->business();if(!$access->enabled('aireception'))throw new \RuntimeException('Unlicensed channel business.');
   $message=app('encrypter')->decrypt($event->encrypted_payload);if((int)$message['business_id']!==$business || $message['phone_number_id']!==$channel->phone_id)throw new \RuntimeException('Channel context mismatch.');
   $db=app('db')->connection('tenant');$db->transaction(function()use($db,$message,$channel){
    $db->table('companies')->orderBy('id')->lockForUpdate()->first();if($db->table('aireception_inbound_messages')->where('channel_id',$channel->id)->where('message_id',$message['message_id'])->exists())return;
    $id=$db->table('aireception_inbound_messages')->insertGetId(['channel_id'=>$channel->id,'message_id'=>$message['message_id'],'sender_identity'=>hash_hmac('sha256',$message['sender_identity'],config('app.key').'|'.$channel->website_id),'type'=>$message['type'],'encrypted_content'=>app('encrypter')->encrypt(['sender_identity'=>$message['sender_identity'],'text'=>$message['text'],'media_id'=>$message['media_id']]),'provider_timestamp'=>$message['timestamp']!==''?(int)$message['timestamp']:null,'state'=>$message['type']==='audio'?'manual_audio':'awaiting_identity','created_at'=>date('Y-m-d H:i:s')]);
    $row=$db->table('aireception_inbound_messages')->where('id',$id)->first();
    $link=$db->table('aireception_contact_links')->where('channel_id',$channel->id)->where('sender_hash',$row->sender_identity)->first();
    if($link)app(\Modules\AiReception\Native\ContactLinker::class)->attach($db,$row,(int)$link->person_id);
   },3);
   if(config('aireception.channel_processing_enabled',false)){$received=$db->table('aireception_inbound_messages')->where('channel_id',$channel->id)->where('message_id',$message['message_id'])->first();$outcome=app(\Modules\AiReception\Native\ChannelReception::class)->process((int)$received->id);if(($outcome['state']??'')==='busy')throw new \RuntimeException('Conversation busy; retry after current turn.');}
   $system->table('aireception_webhook_events')->where('id',$event->id)->update(['state'=>'processed','updated_at'=>date('Y-m-d H:i:s')]);
  }finally{$environment->tenant($previous);}
 }
}
