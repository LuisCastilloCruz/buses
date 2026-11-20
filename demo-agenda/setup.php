<?php

$app = (require __DIR__ . '/bootstrap.php');
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$db = app('db')->connection('agenda');
Modules\AiReception\Agenda\LabSafety::check($db);
$file = dirname(__DIR__) . '/modules/AiReception/Agenda/Database/2026_11_20_000001_create_aireception_agenda_v1.php';
if (!$db->getSchemaBuilder()->hasTable('aireception_migrations')) {
    require $file;
    (new CreateAiReceptionAgendaV1())->up();
}
$marker = $db->table('aireception_migrations')->first();
if (!$marker || $marker->sha256 !== hash_file('sha256', $file)) {
    throw new RuntimeException('Migration checksum mismatch; do not alter an applied migration.');
}
if ($db->table('aireception_businesses')->count()) {
    echo "Existing fictional dataset preserved.\n";
    exit;
}
$accounts = [];
$db->transaction(function () use($db, &$accounts) {
    $db->table('aireception_businesses')->insert([['id' => 1, 'name' => 'Tinta Sur - Estudio ficticio', 'kind' => 'tattoo', 'timezone' => 'America/Lima'], ['id' => 2, 'name' => 'Sonrisa Clara - Clinica ficticia', 'kind' => 'dental', 'timezone' => 'America/Lima']]);
    foreach ([[1, 'Administrador de demo', 'manager@example.test'], [2, 'Admin Tinta Sur', 'tattoo@example.test'], [3, 'Admin Sonrisa Clara', 'dental@example.test'], [4, 'Consulta sin edicion', 'viewer@example.test'], [5, 'Recepcion de prueba', 'reception@example.test']] as $u) {
        $password = bin2hex(random_bytes(8));
        $db->table('aireception_users')->insert(['id' => $u[0], 'name' => $u[1], 'email' => $u[2], 'password' => password_hash($password, PASSWORD_BCRYPT)]);
        $accounts[] = ['id' => $u[0], 'email' => $u[2], 'password' => $password];
    }
    foreach ([[1, 1, 'admin'], [2, 1, 'admin'], [1, 2, 'admin'], [2, 3, 'admin'], [1, 4, 'viewer'], [1, 5, 'reception']] as $m) {
        $db->table('aireception_memberships')->insert(['business_id' => $m[0], 'user_id' => $m[1], 'role' => $m[2], 'active' => true]);
    }
    $policy = ['timezone' => 'America/Lima', 'weekdays' => [], 'exceptions' => [], 'min_notice_minutes' => 0, 'slot_minutes' => 15, 'max_days_ahead' => 90];
    foreach (range(1, 6) as $day) {
        $policy['weekdays'][$day] = [['start' => '09:00', 'end' => '13:00'], ['start' => '14:00', 'end' => '18:00']];
    }
    foreach ([[1, 1, 'Alex - Tatuajes'], [2, 1, 'Sam - Linea fina'], [3, 2, 'Dra. Vega - Odontologia'], [4, 2, 'Dr. Sol - Odontologia']] as $p) {
        $db->table('aireception_professionals')->insert(['id' => $p[0], 'business_id' => $p[1], 'name' => $p[2], 'active' => true, 'policy' => json_encode($policy)]);
    }
    foreach ([[1, 1, 'Consulta de diseno', 30, 0], [2, 1, 'Sesion de tatuaje', 90, 15], [3, 2, 'Evaluacion dental', 30, 0], [4, 2, 'Limpieza dental', 45, 15]] as $s) {
        $db->table('aireception_services')->insert(['id' => $s[0], 'business_id' => $s[1], 'name' => $s[2], 'duration_minutes' => $s[3], 'buffer_minutes' => $s[4], 'active' => true]);
    }
    foreach ([[1, 1, 1], [1, 1, 2], [1, 2, 1], [1, 2, 2], [2, 3, 3], [2, 3, 4], [2, 4, 3], [2, 4, 4]] as $x) {
        $db->table('aireception_professional_services')->insert(['business_id' => $x[0], 'professional_id' => $x[1], 'service_id' => $x[2]]);
    }
});
file_put_contents(dirname(__DIR__) . '/.agenda-runtime/accounts.json', json_encode($accounts, JSON_PRETTY_PRINT));
$text = "ACCESO LOCAL FICTICIO - no publicar\nURL: http://127.0.0.1:8874\n\n";
foreach ($accounts as $a) {
    $text .= $a['email'] . ' : ' . $a['password'] . "\n";
}
file_put_contents(dirname(__DIR__) . '/.agenda-runtime/ACCESS.txt', $text);
echo "Agenda migration applied only to verified disposable MySQL; two fictional businesses seeded.\nPrivate login details: .agenda-runtime/ACCESS.txt\n";
