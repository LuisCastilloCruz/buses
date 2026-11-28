<?php
// Read-only canonical row digests for the verified V6 source or isolated restore.
$root=str_replace('\\','/',realpath(dirname(__DIR__)));
$settings=json_decode(file_get_contents($root.'/.native-runtime/settings.json'),true);
if ($settings['purpose']!=='multifactura-native-v7-1' || $settings['root']!==$root) throw new RuntimeException('Wrong lab');
$config=json_decode(file_get_contents($argv[1]),true);
if (!in_array($config['port'],[13325,13326],true)) throw new RuntimeException('Unsafe recovery port');
$pdo=new PDO('mysql:host=127.0.0.1;port='.$config['port'].';charset=utf8mb4',$config['user'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$meta=$pdo->query('SELECT @@port port,@@datadir dir')->fetch(PDO::FETCH_ASSOC);
if ((int)$meta['port']!==$config['port'] || strtolower(str_replace('\\','/',$meta['dir']))!==strtolower(rtrim(str_replace('\\','/',$config['datadir']),'/').'/')) throw new RuntimeException('Wrong recovery datadir');
$result=[];
foreach(['mf_v71_system','mf_v71_tattoo','mf_v71_dental','mf_v71_commerce','mf_v71_cuarta'] as $database){
 if($database==='mf_v71_cuarta' && !$pdo->query("SHOW DATABASES LIKE 'mf_v71_cuarta'")->fetchColumn())continue;
 $pdo->exec('USE `'.$database.'`');$tables=$pdo->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
 foreach($tables as $table){
  $name=$table[0];$rows=[];$statement=$pdo->query('SELECT * FROM `'.str_replace('`','``',$name).'`');
  while($row=$statement->fetch(PDO::FETCH_NUM))$rows[]=hash('sha256',serialize($row));
  sort($rows,SORT_STRING);$result[$database][$name]=['count'=>count($rows),'sha256'=>hash('sha256',implode('', $rows))];
 }
 ksort($result[$database]);
}
echo json_encode($result,JSON_PRETTY_PRINT);
