<?php
require __DIR__.'/test-bootstrap.php';
use Modules\AiReception\Native\ChannelReception;
use Modules\AiReception\Native\Outbox;
$n=0;function check($ok,$label){global $n;if(!$ok)throw new RuntimeException('FAIL '.$label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$system=app('db')->connection('system');$person=(int)$db->table('persons')->where('type','customers')->value('id');$phone='99000'.str_pad((string)$business,5,'0',STR_PAD_LEFT);$sender='phone:51'.random_int(900000000,999999999);$now=date('Y-m-d H:i:s');
$channel=$system->table('aireception_channels')->where('phone_id',$phone)->first();
if(!$channel){$id=$system->table('aireception_channels')->insertGetId(['website_id'=>$business,'phone_id'=>$phone,'label'=>'V5 ficticio','enabled'=>true,'encrypted_settings'=>app('encrypter')->encrypt(['mode'=>'sandbox','version'=>'v99.0','token'=>'fixture-only','approved_templates'=>['reminder:es']]),'created_at'=>$now,'updated_at'=>$now]);$channel=$system->table('aireception_channels')->where('id',$id)->first();}
$hash=hash_hmac('sha256',$sender,config('app.key').'|'.$business);$db->table('aireception_contact_links')->updateOrInsert(['channel_id'=>$channel->id,'sender_hash'=>$hash],['person_id'=>$person,'verified_by'=>1,'evidence'=>'Fixture identity verified locally','verified_at'=>$now]);
$db->table('aireception_contact_consents')->updateOrInsert(['person_id'=>$person,'channel'=>'whatsapp'],['allowed'=>true,'expires_at'=>null,'evidence'=>'Fictional sandbox consent','actor_id'=>1,'created_at'=>$now,'updated_at'=>$now]);
$c=$db->table('aireception_conversations')->where('channel_id',$channel->id)->where('sender_hash',$hash)->first();$cid=$c?$c->id:$db->table('aireception_conversations')->insertGetId(['person_id'=>$person,'channel_id'=>$channel->id,'sender_hash'=>$hash,'status'=>'automated','created_at'=>$now,'updated_at'=>$now]);$db->table('aireception_conversations')->where('id',$cid)->update(['status'=>'automated']);
config(['aireception.channel_actors.'.$business.'.'.$channel->id=>1,'aireception.v5_transport_mode'=>'simulated']);
function incoming($text,$type='text'){
 global $db,$channel,$system,$cid,$sender;$id='v5-'.bin2hex(random_bytes(12));$secret='fixture-signed-secret-v5';
 $settings=app('encrypter')->decrypt($channel->encrypted_settings);$settings['app_secret']=$secret;$settings['waba_id']='99888166';$system->table('aireception_channels')->where('id',$channel->id)->update(['encrypted_settings'=>app('encrypter')->encrypt($settings)]);
 $message=['id'=>$id,'from'=>substr($sender,6),'timestamp'=>(string)time(),'type'=>$type];if($type==='text')$message['text']=['body'=>$text];else $message['audio']=['id'=>'12345678'];
 $raw=json_encode(['object'=>'whatsapp_business_account','entry'=>[['id'=>'99888166','changes'=>[['field'=>'messages','value'=>['metadata'=>['phone_number_id'=>$channel->phone_id],'messages'=>[$message]]]]]]]);
 $request=Illuminate\Http\Request::create('/internal','POST',[],[],[],['HTTP_X_HUB_SIGNATURE_256'=>'sha256='.hash_hmac('sha256',$raw,$secret)],$raw);
 app(Modules\AiReception\Native\WebhookController::class)->receive($request,$channel->id);$event=$system->table('aireception_webhook_events')->where('channel_id',$channel->id)->where('message_id',$id)->first();
 (new Modules\AiReception\Jobs\ProcessInbound((int)$event->id))->handle();return (int)$db->table('aireception_inbound_messages')->where('channel_id',$channel->id)->where('message_id',$id)->value('id');
}

if(($argv[3]??'')==='prepare'){echo "Fictional channel and verified identity prepared.\n";exit;}
$cfg=json_decode(file_get_contents(dirname(__DIR__).'/.native-runtime/ollama-gateway.json'),true);if(!$cfg)throw new RuntimeException('Configure private local gateway after fixture channels exist.');
config(['aireception.ollama_enabled'=>true,'aireception.gateway_authorized'=>true,'aireception.gateway'=>['client'=>'nativeV7','secret'=>$cfg['keys']['nativeV7']['secret'],'port'=>8899,'node_binary'=>'C:/Program Files/nodejs/node.exe'],'aireception.gateway_channels.'.$business=>(int)$channel->id]);
$old=$db->table('aireception_agent_settings')->where('id',1)->first();$transcript=[];
function askReal($text){global $service,$transcript;$mid=incoming($text);$r=$service->process($mid);$transcript[]=['user'=>$text,'reply'=>$r];if(!empty($r['handoff']))throw new RuntimeException('Unexpected handoff; inspect fictional transcript');return [$mid,$r];}
try{
 app(Modules\AiReception\Native\AgentSettings::class)->save(['enabled'=>true,'mode'=>'local','model'=>'qwen3:1.7b','max_output_tokens'=>256]);
 $service=app(ChannelReception::class);$cat=$engine->catalogue(1,$business);$pro=(int)$cat['professionals'][0]->id;$svc=(int)$cat['services'][0]->id;$day=date('Y-m-d',strtotime('+42 days'));if(date('N',strtotime($day))==7)$day=date('Y-m-d',strtotime($day.' +1 day'));$slots=$engine->availability(1,$business,$pro,$svc,$day);if(!$slots)throw new RuntimeException('Fixture has no availability');$start=$slots[0]['start'];
 [$mid,$r]=askReal('Quiero '.$cat['services'][0]->name.' con '.$cat['professionals'][0]->name.' el '.$day);check(!empty($r['slots']),'real Ollama chooses actual MySQL availability');
 $before=$db->table('aireception_bookings')->count();[$mid,$r]=askReal('Elijo el horario '.$start.'. Prepara mi solicitud de cita');check($db->table('aireception_bookings')->count()===$before,'model cannot write booking before confirmation');check(preg_match('/CONFIRMAR ([A-F0-9]{8})/',$r['reply'],$match)===1,'backend returns explicit confirmation challenge');check($service->process($mid)===$r,'duplicate inbound does not infer or prepare again');
 [$confirm,$r]=askReal('CONFIRMAR '.$match[1]);check(isset($r['booking'])&&$r['booking']['status']==='confirmed','explicit confirmation creates real booking');$booking=$r['booking'];check($service->process($confirm)===$r&&$db->table('aireception_bookings')->count()===$before+1,'duplicate confirmation is idempotent');check($db->table('aireception_bookings')->where('id',$booking['id'])->where('person_id',$person)->where('business_id',$business)->exists(),'booking ownership verified directly in MySQL');check(app(Outbox::class)->process($r['outbox_id'])==='simulated_sent','real booking response uses simulated durable outbox');
 $newDay=date('Y-m-d',strtotime($day.' +2 days'));if(date('N',strtotime($newDay))==7)$newDay=date('Y-m-d',strtotime($newDay.' +1 day'));$newSlots=$engine->reschedulingAvailability(1,$business,(int)$booking['id'],$newDay);$newStart=$newSlots[0]['start'];
 [$mid,$r]=askReal('Quiero reprogramar cita #'.$booking['id'].' al '.$newDay.' a las '.$newStart);check(preg_match('/CONFIRMAR ([A-F0-9]{8})/',$r['reply'],$match)===1,'rescheduling proposal waits for explicit confirmation');check($db->table('aireception_bookings')->where('id',$booking['id'])->value('day')===$day,'pending rescheduling leaves old day unchanged');
 [$confirm,$r]=askReal('CONFIRMAR '.$match[1]);check($r['booking']['day']===$newDay,'rescheduling committed through BookingService');check($service->process($confirm)===$r,'rescheduling confirmation replay unchanged');
 [$mid,$r]=askReal('Quiero cancelar cita #'.$booking['id']);check(preg_match('/CONFIRMAR ([A-F0-9]{8})/',$r['reply'],$match)===1,'cancellation proposal waits for explicit confirmation');check($db->table('aireception_bookings')->where('id',$booking['id'])->value('status')==='confirmed','pending cancellation does not change appointment');
 [$confirm,$r]=askReal('CONFIRMAR '.$match[1]);check($db->table('aireception_bookings')->where('id',$booking['id'])->value('status')==='cancelled','cancellation physically verified in MySQL');check($service->process($confirm)===$r,'cancellation replay idempotent');check(!config('aireception.external_connections_enabled'),'all external providers disabled');
 echo "RESULT $n real Ollama end-to-end assertions; host $host.\n";
}finally{
 if($old)$db->table('aireception_agent_settings')->where('id',1)->update((array)$old);else $db->table('aireception_agent_settings')->where('id',1)->delete();
 $dir=dirname(__DIR__).'/audit/native-v7-1';if(!is_dir($dir))mkdir($dir,0777,true);file_put_contents($dir.'/E2E_'.$host.'_'.date('Ymd_His').'.json',json_encode(['fictional'=>true,'model'=>'real qwen3:1.7b','assertions'=>$n,'transcript'=>$transcript],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
