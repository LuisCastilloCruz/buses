<?php
namespace Modules\Odontologia\Services;
final class ClinicalAttachments {
 private function context(int $record){
  $a=app(\Modules\AiReception\Native\Access::class);$a->require('odontologia','clinical');abort_unless(config('odontologia.clinical_records_enabled',false),403);
  $db=app('db')->connection('tenant');$row=$db->table('odontologia_clinical_records')->where('id',$record)->first();if(!$row)abort(404);
  return [$db,$row,$a->business()];
 }
 private function folder(int $business):string {
  $root=storage_path('app/clinical/'.$business);if(!is_dir($root) && !mkdir($root,0700,true))throw new \RuntimeException('Private storage unavailable.');return $root;
 }
 public function upload(int $record,\Illuminate\Http\UploadedFile $file):int {
  list($db,$row,$business)=$this->context($record);
  if(!$file->isValid() || $file->getSize()<1 || $file->getSize()>2097152)abort(422,'Adjunto invalido o superior a 2 MB.');
  $bytes=file_get_contents($file->getRealPath());$mime=(new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
  if(!in_array($mime,['application/pdf','image/png','image/jpeg'],true))abort(422,'Solo PDF, PNG o JPEG.');
  $key=bin2hex(random_bytes(32));$path=$this->folder($business).DIRECTORY_SEPARATOR.$key;
  $encrypted=app('encrypter')->encrypt(['business'=>$business,'record'=>$record,'bytes'=>$bytes]);
  if(file_put_contents($path,$encrypted,LOCK_EX)!==strlen($encrypted))throw new \RuntimeException('Private storage write failed.');
  try{return $db->transaction(function()use($db,$row,$business,$record,$key,$mime,$bytes){
   $id=$db->table('odontologia_clinical_attachments')->insertGetId(['record_id'=>$record,'storage_key'=>$key,'mime'=>$mime,'size'=>strlen($bytes),'sha256'=>hash('sha256',$bytes),'actor_id'=>auth('web')->id(),'created_at'=>date('Y-m-d H:i:s')]);
   $db->table('odontologia_clinical_audit')->insert(['record_id'=>$record,'person_id'=>$row->person_id,'actor_id'=>auth('web')->id(),'action'=>'upload_attachment','created_at'=>date('Y-m-d H:i:s')]);return (int)$id;
  });}catch(\Throwable $e){if(is_file($path))unlink($path);throw new \RuntimeException('Attachment could not be recorded.');}
 }
 public function download(int $id):array {
  app(\Modules\AiReception\Native\Access::class)->require('odontologia','clinical');
  $db=app('db')->connection('tenant');$file=$db->table('odontologia_clinical_attachments')->where('id',$id)->first();if(!$file)abort(404);
  list($db,$row,$business)=$this->context((int)$file->record_id);
  if(!preg_match('/^[a-f0-9]{64}$/D',$file->storage_key))abort(404);
  $path=$this->folder($business).DIRECTORY_SEPARATOR.$file->storage_key;if(!is_file($path))abort(404);
  $data=app('encrypter')->decrypt(file_get_contents($path));
  if($data['business']!==$business || $data['record']!==(int)$file->record_id || !hash_equals($file->sha256,hash('sha256',$data['bytes'])))abort(403);
  $db->table('odontologia_clinical_audit')->insert(['record_id'=>$file->record_id,'person_id'=>$row->person_id,'actor_id'=>auth('web')->id(),'action'=>'download_attachment','created_at'=>date('Y-m-d H:i:s')]);
  return ['bytes'=>$data['bytes'],'mime'=>$file->mime,'filename'=>'documento-'.$id.'.'.['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg'][$file->mime]];
 }
}
