<?php
require __DIR__.'/test-bootstrap.php';
$n=0;function check($x,$label){global $n;if(!$x)throw new RuntimeException($label);echo 'PASS '.++$n.' '.$label.PHP_EOL;}
$record=(int)$db->table('odontologia_clinical_records')->value('id');$service=app(Modules\Odontologia\Services\ClinicalAttachments::class);
$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');$path=dirname(__DIR__).'/.native-runtime/fictitious-pixel.png';file_put_contents($path,$png);
$id=$service->upload($record,new Illuminate\Http\UploadedFile($path,'fictional.png','image/png',null,true));$r=$db->table('odontologia_clinical_attachments')->where('id',$id)->first();
check($id>0,'clinical attachment metadata persisted');$stored=file_get_contents(storage_path('app/clinical/'.$business.'/'.$r->storage_key));check(strpos($stored,$png)===false,'attachment encrypted outside public');
$download=$service->download($id);check($download['bytes']===$png && $download['mime']==='image/png','authorized attachment decrypted');
check($db->table('odontologia_clinical_audit')->where('record_id',$record)->where('action','download_attachment')->exists(),'download audited');
auth('web')->setUser(App\Models\Tenant\User::findOrFail(3));try{$service->download($id);throw new RuntimeException('Unauthorized attachment leaked');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===403,'nonclinical role denied attachment');}auth('web')->setUser(App\Models\Tenant\User::findOrFail(1));
file_put_contents($path,'<?php echo "not an image";');try{$service->upload($record,new Illuminate\Http\UploadedFile($path,'fake.png','image/png',null,true));throw new RuntimeException('Invalid MIME accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e){check($e->getStatusCode()===422,'content MIME verified, filename not trusted');}
unlink($path);echo "RESULT $n private attachment MySQL checks.\n";
