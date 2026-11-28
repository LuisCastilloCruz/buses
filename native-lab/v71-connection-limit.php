<?php
$root=dirname(__DIR__);$s=json_decode(file_get_contents($root.'/.native-runtime/settings.json'),true);$a=json_decode(file_get_contents($root.'/.native-runtime/admin.json'),true);
if($s['purpose']!=='multifactura-native-v7-2'||$s['root']!==str_replace('\\','/',realpath($root)))throw new RuntimeException('Wrong lab');
$n=(int)($argv[1]??0);if(!in_array($n,[20,64],true))throw new RuntimeException('Unsupported bounded limit');
$p=new PDO('mysql:host=127.0.0.1;port=13327;dbname=mf_v72_system','root',$a['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);$r=$p->query('SELECT @@port port,@@datadir dir')->fetch(PDO::FETCH_ASSOC);
if((int)$r['port']!==13327||strtolower(str_replace('\\','/',$r['dir']))!==strtolower($s['root'].'/.native-runtime/mysql-data/'))throw new RuntimeException('Wrong server');
$p->exec('SET GLOBAL max_connections='.$n);echo 'Isolated lab connection ceiling '.$n.PHP_EOL;
