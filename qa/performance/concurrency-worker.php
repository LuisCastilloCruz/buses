<?php
require dirname(__DIR__,2).'/native-lab/test-bootstrap.php';
$file=$argv[3];
$root=realpath(dirname(__DIR__,2).'/.native-runtime');
if (dirname(realpath($file))!==$root) throw new RuntimeException('Invalid input path');
$input=json_decode(file_get_contents($file),true);
echo "READY\n";flush();
if(trim(fgets(STDIN))!=='GO')exit(2);
$started=microtime(true);
try {
 $r=$engine->execute($actor,$business,$input['action']??'book',$input['data'],$input['key']);
 if(!empty($input['crash_after_commit']))exit(17);
 echo json_encode(['status'=>200,'replayed'=>$r['replayed'],'booking'=>$r['booking'],'ms'=>(microtime(true)-$started)*1000]);
} catch (Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
 echo json_encode(['status'=>$e->getStatusCode(),'ms'=>(microtime(true)-$started)*1000]);
} catch (Throwable $e) {
 echo json_encode(['status'=>500,'class'=>get_class($e),'ms'=>(microtime(true)-$started)*1000]);exit(1);
}
