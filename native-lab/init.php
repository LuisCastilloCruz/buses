<?php

$root = str_replace('\\', '/', realpath(dirname(__DIR__)));
$runtime = $root . '/.native-runtime';
if (is_file($runtime . '/settings.json')) {
    echo "Native settings already exist.\n";
    exit;
}
$p = new PDO('mysql:host=127.0.0.1;port=13321;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$r = $p->query('SELECT @@port port,@@datadir datadir')->fetch(PDO::FETCH_ASSOC);
if ((int) $r['port'] !== 13321 || strtolower(str_replace('\\', '/', $r['datadir'])) !== strtolower($runtime . '/mysql-data/')) {
    throw new RuntimeException('Not native disposable MySQL.');
}
$p->exec('SET GLOBAL restrict_fk_on_non_standard_key=OFF');
$pass = bin2hex(random_bytes(24));
$admin = bin2hex(random_bytes(24));
$p->exec("CREATE USER 'mf_v6'@'127.0.0.1' IDENTIFIED BY '{$pass}'");
foreach (['mf_v6_system', 'mf_v6_tattoo', 'mf_v6_dental', 'mf_v6_commerce'] as $name) {
    $p->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $p->exec("GRANT ALL ON `{$name}`.* TO 'mf_v6'@'127.0.0.1'");
}
$p->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY '{$admin}'");
file_put_contents($runtime . '/settings.json', json_encode(['root' => $root, 'purpose' => 'multifactura-native-v6', 'user' => 'mf_v6', 'password' => $pass, 'key' => 'base64:' . base64_encode(random_bytes(32))], JSON_PRETTY_PRINT));
file_put_contents($runtime . '/admin.json', json_encode(['user' => 'root', 'password' => $admin]));
echo "Independent native MySQL initialized; four empty databases; private credentials only.\n";
