<?php
require dirname(__DIR__).'/ProvidersApi/TranscriptionClient.php';
class AudioFake {public $calls=0;public function request($method,$url,$options){$this->calls++;if($url!=='https://api.openai.com/v1/audio/transcriptions' || $options['allow_redirects']!==false)throw new RuntimeException();return new class {public function getStatusCode(){return 200;}public function getBody(){return '{"text":"Solicitud ficticia"}';}};}}
$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
function bad($fn,$label){try{$fn();}catch(InvalidArgumentException $e){check(true,$label);return;}throw new RuntimeException($label);}
$data=str_repeat("\0",16000);$wav='RIFF'.pack('V',36+strlen($data)).'WAVEfmt '.pack('VvvVVvv',16,1,1,8000,16000,2,16).'data'.pack('V',strlen($data)).$data;
$c=['mode'=>'sandbox','enabled'=>true,'api_key'=>'fake-audio-key','model'=>'configured-transcription-model','max_seconds'=>120];$http=new AudioFake;$client=new Modules\AiReception\ProvidersApi\TranscriptionClient($http);$r=$client->transcribe($c,$wav);check($r['seconds']===1 || $r['seconds']===1.0,'duration derived from PCM bytes');check($r['untrusted_transcript']==='Solicitud ficticia','transcript is explicitly untrusted');
bad(function()use($client,$c){$client->transcribe($c,'OggS'.str_repeat('x',100));},'unverified compressed format rejected');
bad(function()use($client,$c,$wav){$client->transcribe($c,substr($wav,0,-1));},'truncated file rejected');
bad(function()use($client,$c,$wav){$c['max_seconds']=0;$client->transcribe($c,$wav);},'duration limit enforced');
bad(function()use($client,$c){$client->transcribe($c,str_repeat('x',2097153));},'size limit before provider');
check($http->calls===1,'invalid files never reach provider');echo "RESULT $n audio mock checks; no network.\n";
