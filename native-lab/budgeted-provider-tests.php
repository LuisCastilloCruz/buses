<?php
require __DIR__.'/test-bootstrap.php';
$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
class BudgetFakeHttp {
 public $counts=0;public $generations=0;public $fail=false;
 public function request($method,$url,$options){
  if(substr($url,-12)==='input_tokens'){$this->counts++;$body=['object'=>'response.input_tokens','input_tokens'=>10];}
  else{$this->generations++;if($this->fail)throw new RuntimeException('fake provider timeout');$body=['status'=>'completed','output'=>[['type'=>'function_call','name'=>'list_services','arguments'=>'{}']],'usage'=>['input_tokens'=>10,'output_tokens'=>4]];}
  return new class($body){private $body;public function __construct($b){$this->body=$b;}public function getStatusCode(){return 200;}public function getBody(){return json_encode($this->body);}};
 }
}
$settings=$db->table('aireception_agent_settings')->where('id',1)->first();$system=app('db')->connection('system');$plan=$system->table('aireception_plan_controls')->where('plan_id',1)->first();
$model='fake-budget-model-v4';$system->table('aireception_model_rates')->updateOrInsert(['model'=>$model],['input_micro_usd_per_million'=>1000000,'output_micro_usd_per_million'=>1000000,'valid_until'=>date('Y-m-d H:i:s',time()+3600),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
$http=new BudgetFakeHttp;$service=new Modules\AiReception\Native\BudgetedResponses(new Modules\AiReception\ProvidersApi\ResponsesClient($http));$messages=[['role'=>'user','content'=>'Solicitud ficticia']];$tools=[['type'=>'function','name'=>'list_services','strict'=>true,'parameters'=>['type'=>'object','properties'=>[],'required'=>[],'additionalProperties'=>false]]];
try{
 app(Modules\AiReception\Native\AgentSettings::class)->save(['enabled'=>true,'mode'=>'sandbox','model'=>$model,'max_output_tokens'=>128,'api_key'=>'fake-budget-api-key-v4']);
 $stored=$db->table('aireception_agent_settings')->where('id',1)->value('encrypted_credentials');check(strpos($stored,'fake-budget-api-key-v4')===false,'provider credential encrypted');
 try{$service->propose($messages,$tools,'disabled-'.bin2hex(random_bytes(8)));throw new RuntimeException('External guard bypass');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===403 && $http->counts===0,'global external disable prevents all provider calls');}
 config(['aireception.external_connections_enabled'=>true]);
 $current=$db->table('aireception_budgets')->where('business_id',$business)->where('period',date('Y-m'))->first();$already=$current?(int)$current->used_units+(int)$current->reserved_units:0;
 $system->table('aireception_plan_controls')->where('plan_id',1)->update(['monthly_units'=>$already+200]);
 $key='budget-api-'.bin2hex(random_bytes(8));$result=$service->propose($messages,$tools,$key);check($result['proposals'][0]['name']==='list_services','counted generation returns proposal only');
 $again=$service->propose($messages,$tools,$key);check($http->counts===1 && $http->generations===1,'idempotent retry uses encrypted response without API');
 $entry=$db->table('aireception_usage')->where('request_key',$key)->first();check((int)$entry->used_units===14 && (int)$entry->input_tokens===10 && (int)$entry->output_tokens===4,'actual usage settled from explicit fake tariff');
 $http->fail=true;$failedKey='budget-timeout-'.bin2hex(random_bytes(8));try{$service->propose($messages,$tools,$failedKey);throw new LogicException('Expected timeout');}catch(RuntimeException $e){check($e->getMessage()==='Provider unavailable; manual attention required.','provider failure sanitized');}
 $failed=$db->table('aireception_usage')->where('request_key',$failedKey)->first();check($failed->state==='reserved' && (int)$failed->reserved_units===138,'ambiguous paid request retains budget reservation');
 try{$service->propose($messages,$tools,$failedKey);throw new LogicException('Expected dedup rejection');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===409 && $http->generations===2,'ambiguous generation never retried automatically');}
 try{$service->propose($messages,$tools,'exhausted-'.bin2hex(random_bytes(8)));throw new LogicException('Expected budget rejection');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===409 && $http->generations===2,'budget exhausted blocks further generation');}
}finally{
 config(['aireception.external_connections_enabled'=>false]);$system->table('aireception_plan_controls')->where('plan_id',1)->update(['monthly_units'=>$plan->monthly_units]);
 if($settings)$db->table('aireception_agent_settings')->where('id',1)->update((array)$settings);else $db->table('aireception_agent_settings')->where('id',1)->update(['enabled'=>false,'mode'=>'simulated']);
}
echo "RESULT $n budgeted provider MySQL/mock checks; zero external traffic.\n";
