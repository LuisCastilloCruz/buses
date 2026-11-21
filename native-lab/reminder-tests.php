<?php
require __DIR__.'/test-bootstrap.php';
$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$booking=$db->table('aireception_bookings')->where('business_id',$business)->where('status','confirmed')->first();
if(!$booking)throw new RuntimeException('Run existing booking regression fixtures first.');
$person=(int)$booking->person_id;
if(!$person){$person=(int)$db->table('persons')->where('type','customers')->value('id');$db->table('aireception_bookings')->where('id',$booking->id)->update(['person_id'=>$person]);}
$when=date('Y-m-d H:i:s',time()-60);$id=$db->table('aireception_reminders')->insertGetId(['booking_id'=>$booking->id,'scheduled_at'=>$when,'status'=>'simulated_pending']);
$db->table('aireception_contact_consents')->updateOrInsert(['person_id'=>$person,'channel'=>'whatsapp'],['allowed'=>false,'evidence'=>'Consentimiento FICTICIO prueba negativa','actor_id'=>1,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
$p=app(Modules\AiReception\Native\ReminderProcessor::class);check($p->process($id)==='suppressed_no_consent','no consent suppresses reminder');check($p->process($id)==='suppressed_no_consent' && (int)$db->table('aireception_reminders')->where('id',$id)->value('attempts')===1,'repeat processing is idempotent');
$db->table('aireception_contact_consents')->where('person_id',$person)->update(['allowed'=>true]);
$id=$db->table('aireception_reminders')->insertGetId(['booking_id'=>$booking->id,'scheduled_at'=>date('Y-m-d H:i:s',time()-120),'status'=>'simulated_pending']);
Modules\AiReception\Jobs\ProcessReminder::dispatch($business,$id)->onQueue('aireception-reminder-tests');Modules\AiReception\Jobs\ProcessReminder::dispatch($business,$id)->onQueue('aireception-reminder-tests');
check(app('db')->connection('system')->table('aireception_jobs')->where('queue','aireception-reminder-tests')->count()>=2,'duplicate real MySQL queue jobs persisted');
file_put_contents(dirname(__DIR__).'/.native-runtime/reminder-fixture.json',json_encode(['business'=>$business,'reminder'=>$id]));
echo "RESULT $n reminder setup assertions; worker verification follows.\n";
