<?php
namespace Modules\AiReception\Native;
use Modules\AiReception\ProvidersApi\ResponsesClient;
final class BudgetedResponses {
 private $provider;public function __construct(ResponsesClient $provider){$this->provider=$provider;}
 private function cost(int $tokens,int $rate):int{return (int)ceil(($tokens*$rate)/1000000);}
 public function propose(array $messages,array $tools,string $key):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();$actor=(int)auth('web')->id();
  if(!app()->environment('local','testing') || !config('aireception.external_connections_enabled',false))abort(403,'Conexiones externas desactivadas.');
  if(!preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$key))abort(422);
  $configuration=app(AgentSettings::class)->privateConfiguration();if(empty($configuration['enabled']) || $configuration['mode']!=='sandbox')abort(403);
  $system=app('db')->connection('system');$plan=$system->table('clients as c')->join('hostnames as h','h.id','=','c.hostname_id')->join('aireception_plan_controls as p','p.plan_id','=','c.plan_id')->where('h.website_id',$business)->select('p.monthly_units')->first();
  $rate=$system->table('aireception_model_rates')->where('model',$configuration['model'])->where('valid_until','>',date('Y-m-d H:i:s'))->first();
  if(!$plan || !$rate || (int)$plan->monthly_units<1)abort(409,'Presupuesto o tarifa verificada no disponible.');
  $db=app('db')->connection('tenant');$period=date('Y-m');
  $db->transaction(function()use($db,$business,$period,$plan){$db->table('companies')->orderBy('id')->lockForUpdate()->first();$old=$db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->lockForUpdate()->first();if(!$old)$db->table('aireception_budgets')->insert(['business_id'=>$business,'period'=>$period,'limit_units'=>(int)$plan->monthly_units]);else $db->table('aireception_budgets')->where('id',$old->id)->update(['limit_units'=>(int)$plan->monthly_units]);},3);
  $hash=hash('sha256',json_encode(['actor'=>$actor,'model'=>$configuration['model'],'messages'=>$messages,'tools'=>$tools,'max_output_tokens'=>$configuration['max_output_tokens']]));
  $b=$db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->first();
  $prior=$db->table('aireception_usage')->where('budget_id',$b->id)->where('request_key',$key)->first();
  if($prior){if(!hash_equals($prior->request_hash,$hash))abort(409);if($prior->state==='settled' && $prior->encrypted_response)return app('encrypter')->decrypt($prior->encrypted_response);abort(409,'Solicitud pendiente de conciliacion; no se repite el consumo.');}
  if((int)$b->limit_units<=(int)$b->used_units+(int)$b->reserved_units)abort(409,'Presupuesto agotado; atencion humana.');
  $input=$this->provider->countInput($configuration,$messages,$tools);
  $maximum=max(1,$this->cost($input,(int)$rate->input_micro_usd_per_million)+$this->cost($configuration['max_output_tokens'],(int)$rate->output_micro_usd_per_million));
  $budget=new UsageBudget($db);$entry=$budget->reserve($business,$period,$key,$hash,'openai',$maximum);if($entry['replayed'])abort(409,'Otra solicitud ya proceso esta clave.');
  $db->table('aireception_usage')->where('id',$entry['id'])->update(['model'=>$configuration['model']]);
  // On timeout/unknown result retain the reservation. Never retry paid generation automatically.
  $response=$this->provider->propose($configuration,$messages,$tools);
  if($response['usage']['input_tokens']>$input || $response['usage']['output_tokens']>$configuration['max_output_tokens'])throw new \RuntimeException('Provider usage exceeds verified bounds; reconcile manually.');
  $actual=$this->cost($response['usage']['input_tokens'],(int)$rate->input_micro_usd_per_million)+$this->cost($response['usage']['output_tokens'],(int)$rate->output_micro_usd_per_million);
  $db->transaction(function()use($db,$budget,$business,$entry,$actual,$response){$budget->settle($business,$entry['id'],$actual);$db->table('aireception_usage')->where('id',$entry['id'])->update(['input_tokens'=>$response['usage']['input_tokens'],'output_tokens'=>$response['usage']['output_tokens'],'encrypted_response'=>app('encrypter')->encrypt($response)]);},3);
  return $response;
 }
}
