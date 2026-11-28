<?php
namespace Modules\AiReception\Native;
/** Local model proposals share existing access, quotas and channel transport. */
final class LocalResponses {
 private $transport;
 public function __construct($transport=null){if($transport && (!app()->environment('local','testing') || config('aireception.v5_transport_mode','simulated')!=='simulated'))abort(403);$this->transport=$transport?:new \Modules\AiReception\ProvidersApi\GatewayTransport();}
 public function propose(array $messages,array $tools,string $key):array {
  $access=app(Access::class);$access->require('aireception','conversations');$business=$access->business();
  if(!app()->environment('local','testing') || !config('aireception.ollama_enabled',false))abort(403);
  $configuration=app(AgentSettings::class)->privateConfiguration();
  if(empty($configuration['enabled']) || $configuration['mode']!=='local' || !in_array($configuration['model'],['qwen3:1.7b','qwen3:4b'],true))abort(403);
  $db=app('db')->connection('tenant');$period=date('Y-m');
  if(!preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$key))abort(422);
  $plan=app('db')->connection('system')->table('clients as c')->join('hostnames as h','h.id','=','c.hostname_id')->join('aireception_plan_controls as p','p.plan_id','=','c.plan_id')->where('h.website_id',$business)->select('p.message_limit')->first();
  if(!$plan || (int)$plan->message_limit<1)abort(409,'Plan sin cuota local; atencion humana.');
  $payload=['model'=>$configuration['model'],'messages'=>$messages,'tools'=>$tools,'max_output_tokens'=>min(1024,$configuration['max_output_tokens']),'instructions'=>'Usa español del Perú sin voseo. Selecciona una herramienta autorizada; no confirmes operaciones. '.ReceptionProfile::context($configuration['profile']??[])];
  $hash=hash('sha256',json_encode(['actor'=>(int)auth('web')->id(),'payload'=>$payload]));// Local inference has a call quota, never an invented USD price.
  $entry=$db->transaction(function()use($db,$business,$period,$key,$hash,$plan,$configuration){
   $db->table('companies')->orderBy('id')->lockForUpdate()->first();
   $old=$db->table('aireception_local_usage')->where('business_id',$business)->where('period',$period)->where('request_key',$key)->first();
   if($old){if(!hash_equals($old->request_hash,$hash))abort(409,'Idempotencia incompatible.');if($old->state==='settled' && $old->encrypted_response)return ['replay'=>app('encrypter')->decrypt($old->encrypted_response)];abort(409,'Inferencia pendiente de conciliacion.');}
   if($db->table('aireception_local_usage')->where('business_id',$business)->where('period',$period)->count()>=(int)$plan->message_limit)abort(409,'Cuota local agotada; atencion humana.');
   $now=date('Y-m-d H:i:s');return ['id'=>$db->table('aireception_local_usage')->insertGetId(['business_id'=>$business,'period'=>$period,'request_key'=>$key,'request_hash'=>$hash,'model'=>$configuration['model'],'state'=>'pending','created_at'=>$now,'updated_at'=>$now])];
  },3);
  if(isset($entry['replay']))return $entry['replay'];
  $r=$this->transport->request('POST','ar://ollama',['headers'=>['X-AR-Request-Key'=>$key],'json'=>$payload]);
  if($r->getStatusCode()!==200)throw new \RuntimeException('Inferencia local no disponible.');
  $body=(string)$r->getBody();if(strlen($body)>262144)throw new \RuntimeException('Respuesta demasiado grande.');$response=json_decode($body,true);
  if(!is_array($response) || !isset($response['proposals'],$response['usage']) || !is_array($response['proposals']) || count($response['proposals'])>1)throw new \RuntimeException('Respuesta invalida.');
  foreach(['input_tokens','output_tokens'] as $k)if(!isset($response['usage'][$k]) || !is_int($response['usage'][$k]) || $response['usage'][$k]<0)throw new \RuntimeException('Uso invalido.');
  unset($response['unverified_text']);
  $db->table('aireception_local_usage')->where('id',$entry['id'])->where('business_id',$business)->update(['state'=>'settled','input_tokens'=>$response['usage']['input_tokens'],'output_tokens'=>$response['usage']['output_tokens'],'encrypted_response'=>app('encrypter')->encrypt($response),'updated_at'=>date('Y-m-d H:i:s')]);
  return $response;
 }
}
