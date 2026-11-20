<?php
require __DIR__.'/test-bootstrap.php';
$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
function denied($fn,$code,$label){try{$fn();}catch(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===$code,$label);return;}throw new RuntimeException('Expected denial '.$label);}
$p=app(Modules\AiReception\Native\BookingProposals::class);$cat=$engine->catalogue(1,$business);$pro=(int)$cat['professionals'][0]->id;$svc=(int)$cat['services'][0]->id;$person=(int)$db->table('persons')->where('type','customers')->value('id');
$day=date('Y-m-d',strtotime('+26 days'));if(date('N',strtotime($day))==7)$day=date('Y-m-d',strtotime($day.' +1 day'));
$slots=$engine->availability(1,$business,$pro,$svc,$day);
$data=['professional_id'=>$pro,'service_id'=>$svc,'person_id'=>$person,'day'=>$day,'start'=>$slots[0]['start']];
$before=$db->table('aireception_bookings')->count();$draft=$p->prepare($data);check($db->table('aireception_bookings')->count()===$before && !$draft['confirmed'],'proposal does not reserve');
denied(function()use($p,$draft){$p->confirm($draft['confirmation_token'],false);},422,'explicit confirmation required');
denied(function()use($p,$data){$p->prepare($data+['business_id'=>999]);},422,'tenant injection rejected');
auth('web')->setUser(App\Models\Tenant\User::findOrFail(2));denied(function()use($p,$draft){$p->confirm($draft['confirmation_token'],true);},404,'proposal bound to authenticated actor');auth('web')->setUser(App\Models\Tenant\User::findOrFail(1));
$r=$p->confirm($draft['confirmation_token'],true);check($r['booking']['id']>0 && $r['booking']['status']==='confirmed','confirmed only after real backend write');check($p->confirm($draft['confirmation_token'],true)['replayed'],'confirmation retry idempotent');
$engine->execute(1,$business,'cancel',['booking_id'=>$r['booking']['id'],'version'=>$r['booking']['version']],'proposal-test-'.bin2hex(random_bytes(8)));
$draft=$p->prepare($data);$competing=$engine->execute(1,$business,'book',$data+['client_name'=>'Competidor ficticio'],'proposal-test-'.bin2hex(random_bytes(8)))['booking'];
denied(function()use($p,$draft){$p->confirm($draft['confirmation_token'],true);},409,'availability revalidated at confirmation');
$engine->execute(1,$business,'cancel',['booking_id'=>$competing['id'],'version'=>$competing['version']],'proposal-test-'.bin2hex(random_bytes(8)));
$draft=$p->prepare($data);$db->table('aireception_booking_drafts')->where('token_hash',hash('sha256',$draft['confirmation_token']))->update(['expires_at'=>'2000-01-01 00:00:00']);denied(function()use($p,$draft){$p->confirm($draft['confirmation_token'],true);},409,'expired proposal rejected');

$draft=$p->prepare($data);$r=$p->confirm($draft['confirmation_token'],true);$booking=$r['booking'];
check($db->table('aireception_bookings')->where('id',$booking['id'])->value('origin')==='assisted','assisted booking origin stored');
$other=(int)$db->table('persons')->where('type','customers')->where('id','<>',$person)->value('id');
denied(function()use($p,$booking,$other){$p->prepareChange('cancel',$other,['booking_id'=>$booking['id']]);},404,'AI cancellation cannot target another customer');
$target=date('Y-m-d',strtotime($day.' +1 day'));if(date('N',strtotime($target))==7)$target=date('Y-m-d',strtotime($target.' +1 day'));
$slots=$engine->reschedulingAvailability(1,$business,$booking['id'],$target);$change=$p->prepareChange('reschedule',$person,['booking_id'=>$booking['id'],'day'=>$target,'start'=>$slots[0]['start']]);
check($db->table('aireception_bookings')->where('id',$booking['id'])->value('day')===$day,'rescheduling proposal does not change booking');
$moved=$p->confirm($change['confirmation_token'],true)['booking'];check($moved['day']===$target,'confirmed proposal reschedules through booking engine');
$cancel=$p->prepareChange('cancel',$person,['booking_id'=>$booking['id']]);check($db->table('aireception_bookings')->where('id',$booking['id'])->value('status')==='confirmed','cancellation proposal waits for confirmation');
$cancelled=$p->confirm($cancel['confirmation_token'],true);check($cancelled['booking']['status']==='cancelled' && $p->confirm($cancel['confirmation_token'],true)['replayed'],'confirmed cancellation idempotent');
denied(function()use($p,$change){$p->confirm($change['confirmation_token'],true);},409,'old successful proposal cannot report stale state');
echo "RESULT $n proposal MySQL checks.\n";
