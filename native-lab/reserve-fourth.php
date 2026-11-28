<?php
// CLI installer, never included by the web application. Only one new empty reservation.
if (PHP_SAPI !== 'cli') exit(1);
$root=str_replace('\\','/',realpath(__DIR__.'/..'));$runtime=$root.'/.native-runtime';
if(is_file($runtime.'/provision.json')){echo "Reservation already exists.\n";exit;}
$s=json_decode(file_get_contents($runtime.'/settings.json'),true);
if($s['root']!==$root || $s['purpose']!=='multifactura-native-v7')throw new RuntimeException('Wrong runtime');
$a=json_decode(file_get_contents($runtime.'/admin.json'),true);
$p=new PDO('mysql:host=127.0.0.1;port=13323',$a['user'],$a['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$r=$p->query('SELECT @@port port,@@datadir datadir')->fetch(PDO::FETCH_ASSOC);
if((int)$r['port']!==13323 || strtolower(str_replace('\\','/',$r['datadir']))!==strtolower($runtime.'/mysql-data/'))throw new RuntimeException('Wrong server');
$pass=bin2hex(random_bytes(24));
$p->exec('CREATE DATABASE mf_v7_cuarta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$p->exec("CREATE USER 'mf_v7_cuarta'@'127.0.0.1' IDENTIFIED BY '$pass'");
$p->exec("GRANT ALL ON mf_v7_cuarta.* TO 'mf_v7_cuarta'@'127.0.0.1'");
file_put_contents($runtime.'/provision.json',json_encode(['database'=>'mf_v7_cuarta','password'=>$pass]));
echo "PASS empty fourth database and restricted user reserved; no tenant or business seeded.\n";
