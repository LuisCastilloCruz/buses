<?php
namespace Modules\AiReception\ProvidersApi;
/** Bounded canonical PCM WAV only. Compressed voice notes require a separately validated decoder. */
final class TranscriptionClient {
 private $http;public function __construct($http){$this->http=$http;}
 public function transcribe(array $config,string $wav):array {
  if(empty($config['enabled']) || ($config['mode']??'')!=='sandbox' || empty($config['api_key']) || empty($config['model']))throw new \RuntimeException('Transcription disabled.');
  $length=strlen($wav);
  if($length<46 || $length>2097152 || substr($wav,0,4)!=='RIFF' || substr($wav,8,8)!=='WAVEfmt ' || substr($wav,36,4)!=='data')throw new \InvalidArgumentException('Canonical PCM WAV required.');
  $h=unpack('Vriff/a4wave/a4fmt/VfmtSize/vformat/vchannels/Vrate/VbyteRate/valign/vbits/a4data/VdataSize',substr($wav,4,40));
  if($h['riff']!==$length-8 || $h['fmtSize']!==16 || $h['format']!==1 || !in_array($h['channels'],[1,2],true) || $h['bits']!==16 || $h['rate']<8000 || $h['rate']>48000 || $h['align']!==$h['channels']*2 || $h['byteRate']!==$h['rate']*$h['align'] || $h['dataSize']!==$length-44 || $h['dataSize']%$h['align']!==0)throw new \InvalidArgumentException('Invalid PCM header.');
  $seconds=$h['dataSize']/$h['byteRate'];
  if($seconds<=0 || $seconds>min(300,(int)($config['max_seconds']??120)))throw new \InvalidArgumentException('Audio duration limit.');
  try{
   $r=$this->http->request('POST','https://api.openai.com/v1/audio/transcriptions',['headers'=>['Authorization'=>'Bearer '.$config['api_key']],'multipart'=>[['name'=>'model','contents'=>$config['model']],['name'=>'file','contents'=>$wav,'filename'=>'authorized-audio.wav','headers'=>['Content-Type'=>'audio/wav']]],'connect_timeout'=>5,'timeout'=>30,'allow_redirects'=>false,'http_errors'=>false]);
   if($r->getStatusCode()!==200)throw new \RuntimeException();$body=(string)$r->getBody();if(strlen($body)>65536)throw new \RuntimeException();$data=json_decode($body,true);
   if(!is_string($data['text']??null) || strlen($data['text'])>16000)throw new \RuntimeException();
   return ['untrusted_transcript'=>$data['text'],'seconds'=>$seconds];
  }catch(\Throwable $e){throw new \RuntimeException('Transcription unavailable; manual attention required.');}
 }
}
