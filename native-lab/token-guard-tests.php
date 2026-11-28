<?php
require dirname(__DIR__).'/vendor/autoload.php';
class TokenTestProvider implements Illuminate\Contracts\Auth\UserProvider {
 public $calls=0;
 public function retrieveById($identifier){return null;}
 public function retrieveByToken($identifier,$token){return null;}
 public function updateRememberToken(Illuminate\Contracts\Auth\Authenticatable $user,$token){}
 public function retrieveByCredentials(array $credentials){$this->calls++;return ($credentials['api_token']??null)==='fixture-valid-token'?new Illuminate\Auth\GenericUser(['id'=>1]):null;}
 public function validateCredentials(Illuminate\Contracts\Auth\Authenticatable $user,array $credentials){return false;}
}
$class=$argv[1]??App\Auth\StrictTokenGuard::class;$checks=0;$failures=[];
foreach([true,false,1,1.5,['fixture-valid-token'],(object)['value'=>'fixture'],null,''] as $index=>$value){
 $provider=new TokenTestProvider;$request=Illuminate\Http\Request::create('/','POST',[],[],[],['CONTENT_TYPE'=>'application/json'],json_encode(['api_token'=>$value]));$guard=new $class($provider,$request);$user=$guard->user();$checks++;
 if($user!==null || $provider->calls!==0)$failures[]='non-string/empty input reached credential lookup: case '.$index;
 $provider=new TokenTestProvider;$guard=new $class($provider,$request);$valid=$guard->validate(['api_token'=>$value]);$checks++;if($valid || $provider->calls!==0)$failures[]='validate accepted untyped credential lookup: case '.$index;
}
foreach(['query','bearer','json'] as $carrier){$p=new TokenTestProvider;$r=Illuminate\Http\Request::create('/','GET',$carrier==='query'?['api_token'=>'fixture-valid-token']:[]);if($carrier==='bearer')$r->headers->set('Authorization','Bearer fixture-valid-token');if($carrier==='json')$r=Illuminate\Http\Request::create('/','POST',[],[],[],['CONTENT_TYPE'=>'application/json'],json_encode(['api_token'=>'fixture-valid-token']));$g=new $class($p,$r);$checks++;if(!$g->user() || $p->calls!==1)$failures[]='valid string token failed: '.$carrier;}
foreach($failures as $failure)echo 'FAIL '.$failure.PHP_EOL;
echo 'RESULT '.$checks.' token guard checks; failures '.count($failures).PHP_EOL;exit($failures?1:0);
