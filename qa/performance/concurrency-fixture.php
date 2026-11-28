<?php
$action=$argv[3]??'';
$file=$argv[4]??'';
if (!preg_match('/^[a-f0-9]{24}$/', $file)) throw new RuntimeException('Invalid run identifier');
require dirname(__DIR__,2).'/native-lab/test-bootstrap.php';
$path=dirname(__DIR__,2).'/.native-runtime/concurrency-'.$file.'.json';
if ($action==='prepare') {
    if (is_file($path)) throw new RuntimeException('Existing fixture must be inspected, not recreated');
    $policy=['weekdays'=>[], 'exceptions'=>[], 'slot_minutes'=>15,'min_notice_minutes'=>0,'max_days_ahead'=>90];
    foreach(range(1,7) as $day) $policy['weekdays'][$day]=[['start'=>'09:00','end'=>'18:00']];
    $svc=$engine->configure(1,$business,'service',['name'=>'V6 concurrency '.$file,'active'=>true,'duration_minutes'=>30,'buffer_minutes'=>0])['id'];
    $pro=$engine->configure(1,$business,'professional',['name'=>'V6 concurrency '.$file,'active'=>true,'policy'=>$policy,'service_ids'=>[$svc]])['id'];
    $data=['professional_id'=>$pro,'service_id'=>$svc,'day'=>date('Y-m-d',strtotime('+30 days')),'start'=>'09:00','client_name'=>'FICTICIO concurrency V6','status'=>'confirmed'];
    file_put_contents($path,json_encode(['data'=>$data,'business'=>$business,'run'=>$file]));
    echo json_encode(['prepared'=>true]);
} elseif ($action==='verify') {
    $f=json_decode(file_get_contents($path),true);
    $rows=$db->table('aireception_bookings')->where('business_id',$business)->where('professional_id',$f['data']['professional_id'])->get(['id','day','start','end','status']);
    echo json_encode(['rows'=>$rows]);
} else throw new RuntimeException('Unknown action');
