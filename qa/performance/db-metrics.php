<?php
$argv[1]='mf_v6_system';require dirname(__DIR__,2).'/native-lab/schema-bootstrap.php';
$meta=$db->selectOne('SELECT @@port port,DATABASE() name,@@max_connections max_connections');$stats=[];
foreach($db->select("SHOW GLOBAL STATUS WHERE Variable_name IN ('Threads_connected','Threads_running','Max_used_connections','Innodb_row_lock_waits','Innodb_row_lock_time','Innodb_deadlocks','Connections')") as $row)$stats[$row->Variable_name]=(int)$row->Value;
echo json_encode(['port'=>(int)$meta->port,'database'=>$meta->name,'max_connections'=>(int)$meta->max_connections,'status'=>$stats,'queue_depth'=>$db->table('aireception_jobs')->count(),'failed_jobs'=>$db->table('aireception_failed_jobs')->count()]);
