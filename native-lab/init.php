<?php

$root = str_replace('\\', '/', realpath(dirname(__DIR__)));
$runtime = $root . '/.native-runtime';
if (is_file($runtime . '/settings.json')) {
    echo "Native settings already exist.\n";
    exit;
}
$p = new PDO('mysql:host=127.0.0.1;port=13320;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$r = $p->query('SELECT @@port port,@@datadir datadir')->fetch(PDO::FETCH_ASSOC);
if ((int) $r['port'] !== 13320 || strtolower(str_replace('\\', '/', $r['datadir'])) !== strtolower($runtime . '/mysql-data/')) {
    throw new RuntimeException('Not native disposable MySQL.');
}
$p->exec('SET GLOBAL restrict_fk_on_non_standard_key=OFF');
$pass = bin2hex(random_bytes(24));
$admin = bin2hex(random_bytes(24));
$p->exec("CREATE USER 'mf_v5'@'127.0.0.1' IDENTIFIED BY '{$pass}'");
foreach (['mf_v5_system', 'mf_v5_tattoo', 'mf_v5_dental', 'mf_v5_commerce'] as $name) {
    $p->exec('CREATE DATABASE `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $p->exec("GRANT ALL ON `{$name}`.* TO 'mf_v5'@'127.0.0.1'");
}
$p->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY '{$admin}'");
file_put_contents($runtime . '/settings.json', json_encode(['root' => $root, 'purpose' => 'multifactura-native-v5', 'user' => 'mf_v5', 'password' => $pass, 'key' => 'base64:' . base64_encode(random_bytes(32))], JSON_PRETTY_PRINT));
file_put_contents($runtime . '/admin.json', json_encode(['user' => 'root', 'password' => $admin]));
echo "Independent native MySQL initialized; four empty databases; private credentials only.\n";
