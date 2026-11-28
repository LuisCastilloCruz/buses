<?php
namespace Modules\AiReception\ProvidersApi;
final class LoopbackGatewayClient {
 public function request(int $port,array $headers,string $body){
  $node=config('aireception.gateway.node_binary');$script=realpath(__DIR__.'/../../../gateway/loopback-client.mjs');
  if(!is_string($node)||!is_file($node)||!$script||strlen($body)>65536)throw new \RuntimeException('Local bridge unconfigured.');
  $pipes=[];$process=proc_open([$node,$script,(string)$port],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);if(!is_resource($process))throw new \RuntimeException('Local bridge unavailable.');
  fwrite($pipes[0],json_encode(['headers'=>$headers,'body'=>$body]));fclose($pipes[0]);$output=stream_get_contents($pipes[1],524289);fclose($pipes[1]);$errors=stream_get_contents($pipes[2],1024);fclose($pipes[2]);$status=proc_close($process);
  if($status!==0||strlen($output)>524288)throw new \RuntimeException('Local bridge failed.');$result=json_decode($output,true);if(!is_array($result)||!is_int($result['status']??null)||!is_string($result['body']??null)||!is_string($result['signature']??null))throw new \RuntimeException('Invalid bridge response.');
  return new \GuzzleHttp\Psr7\Response($result['status'],['X-AR-Response-Signature'=>$result['signature']],$result['body']);
 }
}
