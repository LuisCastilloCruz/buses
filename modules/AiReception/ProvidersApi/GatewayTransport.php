<?php
namespace Modules\AiReception\ProvidersApi;
/** Guzzle-compatible adapter: PHP can reach only the loopback internal gateway. */
final class GatewayTransport {
 public function request($method,$url,$options){
  if(!config('aireception.external_connections_enabled',false) || !config('aireception.gateway_authorized',false))throw new \RuntimeException('Gateway disabled.');
  $business=app(\Modules\AiReception\Native\Access::class)->business();$cfg=config('aireception.gateway',[]);$channel=(int)($options['headers']['X-AR-Channel']??config('aireception.gateway_channels.'.$business,0));$trusted=app('db')->connection('system')->table('aireception_channels')->where('id',$channel)->where('website_id',$business)->where('enabled',true)->first();if(!$trusted)throw new \RuntimeException('Channel context mismatch.');
  if(!$channel || empty($cfg['secret']) || strlen($cfg['secret'])<32 || !preg_match('/^[A-Za-z0-9_-]{1,40}$/D',$cfg['client']??'') || !is_int($cfg['port']??null) || $cfg['port']<1024 || $cfg['port']>65535)throw new \RuntimeException('Gateway unconfigured.');
  if($method!=='POST')throw new \RuntimeException('Method forbidden.');
  if($url==='ar://ingress_pull')$operation='ingress_pull';elseif($url==='ar://ingress_ack')$operation='ingress_ack';elseif($url==='ar://transcription')$operation='transcription';elseif($url==='https://api.openai.com/v1/responses')$operation='responses';elseif($url==='https://api.openai.com/v1/responses/input_tokens')$operation='input_tokens';elseif(preg_match('#^https://graph\.facebook\.com/v[0-9]{2}\.0/[0-9]{5,30}/messages$#D',$url))$operation='whatsapp';else throw new \RuntimeException('Destination forbidden.');
  $id=$options['headers']['X-AR-Request-Key']??null;if(!is_string($id)||!preg_match('/^[A-Za-z0-9_-]{8,80}$/D',$id))throw new \RuntimeException('Stable request key required.');
  $body=json_encode(['tenant'=>$business,'channel'=>$channel,'operation'=>$operation,'id'=>$id,'payload'=>$options['json']]);$stamp=(string)time();$nonce=bin2hex(random_bytes(16));$signature=hash_hmac('sha256',$cfg['client']."\n".$stamp."\n".$nonce."\n".$body,$cfg['secret']);
  $response=(new \GuzzleHttp\Client())->request('POST','http://127.0.0.1:'.$cfg['port'].'/internal/v1/execute',['headers'=>['Content-Type'=>'application/json','X-AR-Client'=>$cfg['client'],'X-AR-Time'=>$stamp,'X-AR-Nonce'=>$nonce,'X-AR-Signature'=>$signature],'body'=>$body,'connect_timeout'=>2,'timeout'=>30,'allow_redirects'=>false,'http_errors'=>false,'proxy'=>'']);
  if(in_array($response->getStatusCode(),[200,429],true) && !hash_equals(hash_hmac('sha256',$nonce."\n".(string)$response->getBody(),$cfg['secret']),$response->getHeaderLine('X-AR-Response-Signature')))throw new \RuntimeException('Gateway response signature invalid.');return $response;
 }
}