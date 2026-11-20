<?php
require __DIR__.'/test-bootstrap.php';
$n=0;
function check($ok,$label){global $n;if(!$ok)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
function denied($fn,$code,$label){try{$fn();}catch(\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===$code,$label);return;}throw new RuntimeException('Expected rejection '.$label);}
check($host==='dental','fixture dental context');
$level=$db->table('module_levels')->where('value','odontologia_clinical')->value('id');
check((bool)$level,'independent clinical permission exists');
// Explicit opt-in for this fictional administrator only; never grant existing customers automatically.
$db->table('module_level_user')->updateOrInsert(['user_id'=>1,'module_level_id'=>$level],[]);
$person=(int)$db->table('persons')->where('type','customers')->value('id');
$service=app(Modules\Odontologia\Services\ClinicalRecords::class);
$id=$service->save($person,'history',['note'=>'FICTICIO antecedente de prueba V4']);
$row=$db->table('odontologia_clinical_records')->where('id',$id)->first();
check(strpos($row->encrypted_content,'antecedente')===false,'clinical content encrypted at rest');
$rows=$service->list($person);check(count(array_filter($rows,function($r)use($id){return $r['id']===$id && $r['content']['note']==='FICTICIO antecedente de prueba V4';}))===1,'authorized clinical decryption');
$service->save($person,'history',['note'=>'FICTICIO version 2'],$id,1);
denied(function()use($service,$person,$id){$service->save($person,'history',['note'=>'stale'],$id,1);},409,'clinical optimistic concurrency');
check($db->table('odontologia_clinical_audit')->where('record_id',$id)->where('action','previous_version')->exists(),'encrypted clinical version audit');
$tooth=$service->save($person,'odontogram',['teeth'=>[['tooth'=>11,'surface'=>'mesial','note'=>'Observacion ficticia profesional']]]);check($tooth>0,'FDI odontogram persistence');
denied(function()use($service,$person){$service->save($person,'odontogram',['teeth'=>[['tooth'=>99,'surface'=>'mesial','note'=>'x']]]);},422,'invalid tooth rejected');
config(['odontologia.clinical_records_enabled'=>false]);denied(function()use($service,$person){$service->list($person);},403,'clinical global disable');config(['odontologia.clinical_records_enabled'=>true]);
auth('web')->setUser(App\Models\Tenant\User::findOrFail(3));denied(function()use($service,$person){$service->list($person);},403,'nonclinical role cannot read');auth('web')->setUser(App\Models\Tenant\User::findOrFail(1));
$budget=new Modules\AiReception\Native\UsageBudget($db);$period='2039-12';
// Fresh budget fixture by assigning a new future period on each repeat, without deleting historical usage.
for($y=2039;$y<2200;$y++){ $period=$y.'-12';if(!$db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->exists())break;}
$db->table('aireception_budgets')->insert(['business_id'=>$business,'period'=>$period,'limit_units'=>100]);
$key='budget-'.bin2hex(random_bytes(12));$hash=hash('sha256','fictional input');
$r=$budget->reserve($business,$period,$key,$hash,'openai',80);check(!$r['replayed'],'budget reservation persisted');
check($budget->reserve($business,$period,$key,$hash,'openai',80)['replayed'],'budget idempotent replay');
denied(function()use($budget,$business,$period,$hash){$budget->reserve($business,$period,'another-key-123',$hash,'openai',30);},409,'reserved budget cannot be overspent');
$budget->settle($business,$r['id'],50);$budget->settle($business,$r['id'],50);
$b=$db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->first();check((int)$b->used_units===50 && (int)$b->reserved_units===0,'settlement exactly once');
denied(function()use($budget,$business,$r){$budget->settle($business+1,$r['id'],50);},403,'budget rejects other business');
check(is_array($engine->calendar(1,$business,date('Y-m-01'),(int)date('t'))),'monthly calendar real MySQL');
$cat=$engine->catalogue(1,$business);$pro=$cat['professionals'][0]->id;$svc=$cat['services'][0]->id;
$day=date('Y-m-d',strtotime('+25 days'));if(date('N',strtotime($day))==7)$day=date('Y-m-d',strtotime($day.' +1 day'));
$slots=$engine->availability(1,$business,$pro,$svc,$day);check(count($slots)>0,'real available slot');
$b=$engine->execute(1,$business,'book',['professional_id'=>$pro,'service_id'=>$svc,'person_id'=>$person,'client_name'=>'Atencion ficticia V4','day'=>$day,'start'=>$slots[0]['start'],'status'=>'confirmed'],'v4-'.bin2hex(random_bytes(12)))['booking'];
$b=$engine->execute(1,$business,'attend',['booking_id'=>$b['id'],'version'=>$b['version']],'v4-'.bin2hex(random_bytes(12)))['booking'];check($b['status']==='attended','attended state persisted');
denied(function()use($engine,$business,$b){$engine->execute(1,$business,'cancel',['booking_id'=>$b['id'],'version'=>$b['version']],'v4-'.bin2hex(random_bytes(12)));},409,'attended record cannot be cancelled');
echo "RESULT $n V4 MySQL assertions.\n";
