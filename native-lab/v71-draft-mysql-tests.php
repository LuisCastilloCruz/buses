<?php
require __DIR__.'/test-bootstrap.php';
use Modules\AiReception\Native\DialogueSession;
use Modules\AiReception\Native\BookingProposals;
$n=0;function verifyDraft($ok,$label){global $n;if(!$ok)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
verifyDraft($db->table('migrations')->where('migration','2026_11_28_000006_bind_booking_draft_conversation_v71')->exists(),'binding migration marker present');
$person=(int)$db->table('persons')->where('type','customers')->value('id');$now=date('Y-m-d H:i:s');
$cid=$db->table('aireception_conversations')->insertGetId(['person_id'=>$person,'status'=>'automated','created_at'=>$now,'updated_at'=>$now]);
$cat=$engine->catalogue(1,$business);$pro=(int)$cat['professionals'][0]->id;$svc=(int)$cat['services'][0]->id;
$day=date('Y-m-d',strtotime('+70 days'));if(date('N',strtotime($day))==7)$day=date('Y-m-d',strtotime($day.' +1 day'));
$slots=$engine->availability(1,$business,$pro,$svc,$day);if(!$slots)throw new RuntimeException('No fixture availability');
$selection=['service_id'=>$svc,'professional_id'=>$pro,'day'=>$day,'start'=>$slots[0]['start']];$state=app(DialogueSession::class);$drafts=app(BookingProposals::class);
$state->begin($cid,$person,'Quiero una nueva cita',$selection);$draft=$drafts->prepare($selection+['person_id'=>$person]);$state->result($cid,$person,'prepare_booking',['proposal'=>$draft]);
verifyDraft((int)$db->table('aireception_booking_drafts')->where('token_hash',hash('sha256',$draft['confirmation_token']))->value('conversation_id')===$cid,'draft bound to authorized conversation');
$before=$db->table('aireception_bookings')->count();$state->begin($cid,$person,'Mejor otra fecha',array_replace($selection,['day'=>date('Y-m-d',strtotime($day.' +1 day'))]));
try{$drafts->confirm($draft['confirmation_token'],true);throw new LogicException('Stale token accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){verifyDraft($e->getStatusCode()===409,'stale token rejected after preferences change');}
verifyDraft($db->table('aireception_bookings')->count()===$before,'stale confirmation created no booking');
$state->begin($cid,$person,'Quiero una nueva cita',$selection);$draft=$drafts->prepare($selection+['person_id'=>$person]);$state->result($cid,$person,'prepare_booking',['proposal'=>$draft]);
$result=$drafts->confirm($draft['confirmation_token'],true);$stored=$state->load($cid,$person);
verifyDraft($stored['phase']==='confirmed'&&$stored['booking_id']===$result['booking']['id'],'native confirmation persists actual booking state');
$again=$drafts->confirm($draft['confirmation_token'],true);verifyDraft(!empty($again['replayed'])&&$db->table('aireception_bookings')->count()===$before+1,'native confirmation replay is idempotent');
$engine->execute(1,$business,'cancel',['booking_id'=>(int)$result['booking']['id'],'version'=>(int)$result['booking']['version']],'v71-draft-cleanup-'.$cid);
echo "RESULT $n draft binding assertions\n";
