<?php
namespace Modules\AiReception\Console;
/** Pull ingress from internal gateway; reuse the exact V4 signature/ownership/dedup path. */
class GatewayPull extends \Illuminate\Console\Command {
 protected $signature='aireception:gateway-pull {website} {channel}';protected $description='Pull one signed sandbox webhook through authenticated internal transport';
 public function handle(){
  if(!app()->environment('local','testing')||!config('aireception.gateway_authorized',false)||!config('aireception.external_connections_enabled',false))throw new \RuntimeException('Authorized sandbox required.');
  $env=app(\Hyn\Tenancy\Environment::class);$previous=$env->tenant();
  try{
   $env->tenant(\Hyn\Tenancy\Models\Website::findOrFail((int)$this->argument('website')));$business=app(\Modules\AiReception\Native\Access::class)->business();$channel=(int)$this->argument('channel');if(!app(\Modules\AiReception\Native\Access::class)->enabled('aireception'))throw new \RuntimeException('Unlicensed tenant.');
   $transport=new \Modules\AiReception\ProvidersApi\GatewayTransport();$headers=['X-AR-Channel'=>$channel,'X-AR-Request-Key'=>'pull-'.bin2hex(random_bytes(16))];$r=$transport->request('POST','ar://ingress_pull',['headers'=>$headers,'json'=>new \stdClass()]);if($r->getStatusCode()!==200)throw new \RuntimeException('Gateway unavailable.');$payload=json_decode((string)$r->getBody(),true);if(empty($payload['event']))return 0;$event=$payload['event'];
   $request=\Illuminate\Http\Request::create('/internal','POST',[],[],[],['CONTENT_TYPE'=>'application/json','HTTP_X_HUB_SIGNATURE_256'=>$event['signature']],$event['body']);
   app(\Modules\AiReception\Native\WebhookController::class)->receive($request,$channel);
   $ack=$transport->request('POST','ar://ingress_ack',['headers'=>['X-AR-Channel'=>$channel,'X-AR-Request-Key'=>'ack-'.bin2hex(random_bytes(16))],'json'=>['id'=>$event['id'],'owner'=>$event['owner']]]);if($ack->getStatusCode()!==200)throw new \RuntimeException('Ack pending; replay is deduplicated.');
  }finally{$env->tenant($previous);}return 0;
 }
}