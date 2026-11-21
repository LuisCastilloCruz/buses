<?php

$app = (require __DIR__ . '/schema-bootstrap.php');
$runtime = dirname(__DIR__) . '/.native-runtime';
$accounts = is_file($runtime . '/accounts.json') ? json_decode(file_get_contents($runtime . '/accounts.json'), true) : [];
$system = $app['db']->connection('system');
if ($name === 'mf_v5_system') {
    if (!$system->table('plans')->exists()) {
        $system->table('plans')->insert(['name' => 'Laboratorio ficticio', 'pricing' => 0, 'limit_users' => 20, 'limit_documents' => 99999, 'plan_documents' => '["01","03","80"]']);
    }
    if (!$system->table('users')->exists()) {
        $pass = bin2hex(random_bytes(12));
        $system->table('users')->insert(['name' => 'Superadministrador ficticio', 'email' => 'superadmin@example.test', 'password' => password_hash($pass, PASSWORD_BCRYPT)]);
        $accounts['system'] = ['url' => 'http://localhost:8877', 'email' => 'superadmin@example.test', 'password' => $pass];
        file_put_contents($runtime . '/accounts.json', json_encode($accounts, JSON_PRETTY_PRINT));
    }
    $admin = json_decode(file_get_contents($runtime . '/admin.json'), true);
    $pdo = new PDO('mysql:host=127.0.0.1;port=13320', $admin['user'], $admin['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    foreach (['tattoo' => 'mf_v5_tattoo', 'dental' => 'mf_v5_dental', 'comercio' => 'mf_v5_commerce'] as $host => $database) {
        $id = $system->table('websites')->where('uuid', $database)->value('id');
        if (!$id) {
            $id = $system->table('websites')->insertGetId(['uuid' => $database, 'created_at' => date('Y-m-d H:i:s')]);
        }
        $hid = $system->table('hostnames')->where('fqdn', $host . '.localhost')->value('id');
        if (!$hid) {
            $hid = $system->table('hostnames')->insertGetId(['fqdn' => $host . '.localhost', 'website_id' => $id, 'force_https' => false, 'created_at' => date('Y-m-d H:i:s')]);
        }
        if (!$system->table('clients')->where('hostname_id', $hid)->exists()) {
            $system->table('clients')->insert(['hostname_id' => $hid, 'number' => '2000000000' . $id, 'name' => ucfirst($host) . ' FICTICIO', 'email' => $host . '@example.test', 'token' => bin2hex(random_bytes(24)), 'plan_id' => $system->table('plans')->value('id'), 'locked' => false, 'locked_tenant' => false, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        }
        $password = md5($s['key'] . '.' . $id);
        $pdo->exec("CREATE USER IF NOT EXISTS '{$database}'@'127.0.0.1' IDENTIFIED BY '{$password}'");
        $pdo->exec("GRANT ALL ON `{$database}`.* TO '{$database}'@'127.0.0.1'");
    }
    echo "PASS native system fixtures and separate Hyn database users.\n";
    exit;
}
$website = $system->table('websites')->where('uuid', $name)->first();
if (!$website) {
    throw new RuntimeException('Seed system first.');
}
$biz = (int) $website->id;
$host = ['mf_v5_tattoo' => 'tattoo', 'mf_v5_dental' => 'dental', 'mf_v5_commerce' => 'comercio'][$name];
if ($db->table('companies')->exists()) {
    echo "SKIP already seeded {$name}\n";
    exit;
}
$pass = bin2hex(random_bytes(12));
$db->transaction(function () use($db, $host, $biz, $pass, $system, $website) {
    $db->table('companies')->insert(['id' => 1, 'identity_document_type_id' => '6', 'number' => '2000000000' . $biz, 'name' => ucfirst($host) . ' FICTICIO', 'trade_name' => ucfirst($host) . ' / DEMO LOCAL', 'soap_type_id' => '01']);
    $db->table('establishments')->insert(['id' => 1, 'description' => 'Sede ficticia', 'country_id' => 'PE', 'department_id' => '15', 'province_id' => '1501', 'district_id' => '150101', 'address' => 'Direccion ficticia de laboratorio', 'telephone' => '000000000', 'code' => '0000']);
    $uid = $db->table('users')->insertGetId(['id' => 1, 'name' => 'Administrador ' . ucfirst($host), 'email' => $host . '@example.test', 'password' => password_hash($pass, PASSWORD_BCRYPT), 'type' => 'admin', 'establishment_id' => 1, 'created_at' => date('Y-m-d H:i:s')]);
    $selected = [];
    foreach ($db->table('modules')->get() as $m) {
        if (in_array($m->value, ['aireception', 'odontologia'], true)) {
            if ($host === 'comercio' || $m->value === 'odontologia' && $host !== 'dental') {
                continue;
            }
        } elseif (stripos($m->value, 'tattoo') !== false && $host !== 'tattoo') {
            continue;
        }
        $selected[] = $m->value;
        $db->table('module_user')->insert(['module_id' => $m->id, 'user_id' => $uid]);
        foreach ($db->table('module_levels')->where('module_id', $m->id)->get() as $l) {
            $db->table('module_level_user')->insert(['module_level_id' => $l->id, 'user_id' => $uid]);
        }
    }
    $client = $system->table('clients')->join('hostnames', 'hostnames.id', '=', 'clients.hostname_id')->where('hostnames.website_id', $biz)->select('clients.*')->first();
    \Modules\AiReception\Native\Catalog::syncEntitlements($client, $selected);
    $person = $db->table('persons')->insertGetId(['type' => 'customers', 'identity_document_type_id' => '1', 'number' => '0000000' . $biz, 'name' => 'Paciente ficticio ' . ucfirst($host), 'country_id' => 'PE']);
    $item = $db->table('items')->insertGetId(['item_type_id' => '02', 'unit_type_id' => 'ZZ', 'currency_type_id' => 'PEN', 'description' => $host === 'dental' ? 'Limpieza dental ficticia' : 'Servicio ficticio ' . $host, 'sale_unit_price' => 100, 'sale_affectation_igv_type_id' => '10', 'purchase_affectation_igv_type_id' => '10']);
    if ($host === 'comercio') {
        return;
    }
    $policy = ['weekdays' => [], 'exceptions' => [], 'min_notice_minutes' => 0, 'slot_minutes' => 15, 'max_days_ahead' => 90];
    foreach (range(1, 6) as $d) {
        $policy['weekdays'][$d] = [['start' => '09:00', 'end' => '13:00'], ['start' => '14:00', 'end' => '18:00']];
    }
    $svc = $db->table('aireception_services')->insertGetId(['business_id' => $biz, 'item_id' => $item, 'name' => $host === 'dental' ? 'Limpieza dental ficticia' : 'Sesion tatuaje ficticia', 'duration_minutes' => $host === 'dental' ? 30 : 60, 'buffer_minutes' => 15, 'active' => true]);
    if ($host === 'dental') {
        $spec = $db->table('odontologia_specialties')->insertGetId(['name' => 'Odontologia general', 'active' => true]);
        $resource = $db->table('aireception_resources')->insertGetId(['business_id' => $biz, 'name' => 'Sillon ficticio A', 'active' => true]);
        $db->table('odontologia_chairs')->insert(['resource_id' => $resource, 'room' => 'Consultorio ficticio A']);
    }
    foreach ([1, 2] as $n) {
        $pro = $db->table('aireception_professionals')->insertGetId(['business_id' => $biz, 'name' => ($host === 'dental' ? 'Odontologo' : 'Tatuador') . ' ficticio ' . $n, 'active' => true, 'policy' => json_encode($policy)]);
        $db->table('aireception_professional_services')->insert(['business_id' => $biz, 'professional_id' => $pro, 'service_id' => $svc]);
        if ($host === 'dental') {
            $source = $db->table('odontologia_dentists')->insertGetId(['professional_id' => $pro, 'specialty_id' => $spec, 'registration' => 'DEMO-' . $n, 'active' => true]);
            $db->table('aireception_professional_resources')->insert(['professional_id' => $pro, 'resource_id' => $resource]);
        } else {
            $source = $db->table('tattoo_artists')->insertGetId(['stage_name' => 'Tatuador ficticio ' . $n, 'active' => true]);
        }
        $db->table('aireception_professional_links')->insert(['professional_id' => $pro, 'domain' => $host === 'dental' ? 'dental' : 'tattoo', 'source_id' => $source]);
    }
});
$accounts[$host] = ['url' => 'http://' . $host . '.localhost:8877', 'email' => $host . '@example.test', 'password' => $pass];
file_put_contents($runtime . '/accounts.json', json_encode($accounts, JSON_PRETTY_PRINT));
echo "PASS {$name}: original company, user, catalogs and native fixtures.\n";
