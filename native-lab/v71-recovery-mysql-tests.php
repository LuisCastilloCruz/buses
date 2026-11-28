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

$service=app(ChannelReception::class);
$r=$service->process(incoming('Quiero una cita'),function(){abort(409,'Fixture budget exhausted');});check(!empty($r['handoff']),'provider budget rejection goes to human, not repeated clarification');
$db->table('aireception_conversations')->where('id',$cid)->update(['status'=>'automated']);
$before=$db->table('aireception_bookings')->count();$r=$service->process(incoming('Quiero una cita'),function(){return ['proposals'=>[['name'=>'prepare_booking','arguments'=>['professional_id'=>999,'service_id'=>999,'day'=>'2090-01-01','start'=>'09:00']]]];});check(!empty($r['needs_clarification'])&&empty($r['handoff']),'incomplete invented selection rejected without abandoning conversation');check($db->table('aireception_bookings')->count()===$before,'rejected proposal creates zero appointments');
$r=$service->process(incoming('Que servicios tienen'),function(){return ['proposals'=>[['name'=>'list_services','arguments'=>[]]]];});check(empty($r['handoff'])&&strpos($r['reply'],'min)')!==false,'conversation continues safely after rejected proposal');
echo "RESULT $n channel recovery assertions; model mocked explicitly.\n";
