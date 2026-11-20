<?php

$root = str_replace('\\', '/', realpath(dirname(__DIR__)));
$runtime = $root . '/.agenda-runtime';
if (is_file($runtime . '/settings.json')) {
    echo "Private configuration already exists; not replaced.\n";
    exit;
}
$db = new PDO('mysql:host=127.0.0.1;port=13317;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$r = $db->query('SELECT @@port port, @@datadir datadir')->fetch(PDO::FETCH_ASSOC);
if ((int) $r['port'] !== 13317 || strtolower(str_replace('\\', '/', $r['datadir'])) !== strtolower($runtime . '/mysql-data/')) {
    throw new RuntimeException('Not the disposable MySQL instance.');
}
$pass = bin2hex(random_bytes(24));
$admin = bin2hex(random_bytes(24));
$db->exec('CREATE DATABASE aireception_lab_v1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$db->exec("CREATE USER 'agenda_lab'@'127.0.0.1' IDENTIFIED BY '{$pass}'");
$db->exec("GRANT ALL PRIVILEGES ON aireception_lab_v1.* TO 'agenda_lab'@'127.0.0.1'");
$db->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY '{$admin}'");
file_put_contents($runtime . '/settings.json', json_encode(['purpose' => 'disposable-aireception-lab-v1', 'root' => $root, 'db_user' => 'agenda_lab', 'db_password' => $pass, 'app_key' => 'base64:' . base64_encode(random_bytes(32))], JSON_PRETTY_PRINT));
file_put_contents($runtime . '/mysql-admin.json', json_encode(['user' => 'root', 'password' => $admin]));
echo "Dedicated database and restricted lab user ready; credentials stored privately.\n";
