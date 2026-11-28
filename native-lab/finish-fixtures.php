<?php

$app = (require __DIR__ . '/schema-bootstrap.php');
if ($name === 'mf_v6_system') {
    throw new RuntimeException('Tenant only');
}
if (!$db->table('format_templates')->exists()) {
    require_once dirname(__DIR__) . '/database/seeds/tenants/TenancyDatabaseSeeder.php';
    (new TenancyDatabaseSeeder())->run();
}
if (!$db->table('warehouses')->exists()) {
    $db->table('warehouses')->insert(['establishment_id' => 1, 'description' => 'Almacen ficticio', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
}
if (!$db->table('series')->exists()) {
    foreach ([['01', 'F001'], ['03', 'B001'], ['80', 'NV01']] as $series) {
        $db->table('series')->insert(['establishment_id' => 1, 'document_type_id' => $series[0], 'number' => $series[1]]);
    }
}
$accounts = json_decode(file_get_contents(dirname(__DIR__) . '/.native-runtime/accounts.json'), true);
$lines = ['MULTIFACTURA NATIVO / SOLO DATOS FICTICIOS', 'No compartir ni publicar este archivo.', ''];
foreach ($accounts as $a) {
    $lines[] = $a['url'];
    $lines[] = 'Usuario: ' . $a['email'];
    $lines[] = 'Clave: ' . $a['password'];
    $lines[] = '';
}
file_put_contents(dirname(__DIR__) . '/.native-runtime/ACCESS.txt', implode(PHP_EOL, $lines));
echo "PASS original reference fixtures {$name}\n";
