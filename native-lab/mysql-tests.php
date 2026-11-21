<?php

require __DIR__ . '/test-bootstrap.php';
$n = 0;
function check($ok, $label)
{
    global $n;
    if (!$ok) {
        throw new RuntimeException('FAIL ' . $label);
    }
    echo 'PASS ' . ++$n . ' ' . $label . PHP_EOL;
}
function denied($fn, $status, $label)
{
    try {
        $fn();
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
        check($e->getStatusCode() === $status, $label);
        return;
    }
    throw new RuntimeException('FAIL expected denial ' . $label);
}
function keyid()
{
    return 'native-' . bin2hex(random_bytes(12));
}
function user($id)
{
    auth('web')->setUser(App\Models\Tenant\User::findOrFail($id));
}
foreach ([2, 3] as $id) {
    if (!$db->table('users')->where('id', $id)->exists()) {
        $db->table('users')->insert(['id' => $id, 'name' => 'QA ficticio ' . $id, 'email' => 'qa' . $id . '@example.test', 'password' => password_hash(bin2hex(random_bytes(20)), PASSWORD_BCRYPT), 'type' => 'seller', 'establishment_id' => 1]);
        foreach ($db->table('module_user')->where('user_id', 1)->get() as $m) {
            $db->table('module_user')->insert(['module_id' => $m->module_id, 'user_id' => $id]);
        }
        foreach ($db->table('module_level_user')->where('user_id', 1)->get() as $l) {
            $value = $db->table('module_levels')->where('id', $l->module_level_id)->value('value');
            if ($id === 2 || $value === 'aireception_calendar') {
                $db->table('module_level_user')->insert(['module_level_id' => $l->module_level_id, 'user_id' => $id]);
            }
        }
    }
}
$policy = ['weekdays' => [], 'exceptions' => [], 'slot_minutes' => 15, 'min_notice_minutes' => 0, 'max_days_ahead' => 90];
foreach (range(1, 6) as $d) {
    $policy['weekdays'][$d] = [['start' => '09:00', 'end' => '13:00'], ['start' => '14:00', 'end' => '18:00']];
}
$day = date('Y-m-d', strtotime('+15 days'));
while (date('N', strtotime($day)) == 7) {
    $day = date('Y-m-d', strtotime($day . ' +1 day'));
}
$svc = $engine->configure(1, $business, 'service', ['name' => 'QA ficticio ' . keyid(), 'active' => true, 'duration_minutes' => 30, 'buffer_minutes' => 15])['id'];
$pro = $engine->configure(1, $business, 'professional', ['name' => 'QA ficticio ' . keyid(), 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]])['id'];
$base = ['professional_id' => $pro, 'service_id' => $svc, 'day' => $day, 'start' => '09:15', 'client_name' => 'Paciente ficticio QA', 'status' => 'pending'];
check(count($engine->availability(1, $business, $pro, $svc, $day)) > 0, 'availability respects configured duration');
check(!in_array('13:00', array_column($engine->availability(1, $business, $pro, $svc, $day), 'start'), true), 'break excluded');
$sunday = date('Y-m-d', strtotime('next Sunday'));
check($engine->availability(1, $business, $pro, $svc, $sunday) === [], 'closed Sunday');
$key = keyid();
$b = $engine->execute(1, $business, 'book', $base, $key)['booking'];
check($b['status'] === 'pending' && $b['end'] === '09:45', 'pending persisted with duration snapshot');
check($engine->execute(1, $business, 'book', $base, $key)['replayed'], 'idempotent retry returns original');
denied(function () use($engine, $business, $base, $key) {
    $x = $base;
    $x['start'] = '10:15';
    $engine->execute(1, $business, 'book', $x, $key);
}, 409, 'idempotency payload mismatch');
denied(function () use($engine, $business, $base) {
    $engine->execute(1, $business, 'book', $base, keyid());
}, 409, 'occupied slot rejected');
$b = $engine->execute(1, $business, 'confirm', ['booking_id' => $b['id'], 'version' => 1], keyid())['booking'];
check($b['status'] === 'confirmed' && $b['version'] === 2, 'confirmation persists');
$b = $engine->execute(1, $business, 'reschedule', ['booking_id' => $b['id'], 'version' => 2, 'day' => $day, 'start' => '10:15'], keyid())['booking'];
check($b['start'] === '10:15' && $b['duration_minutes'] === 30, 'reschedule uses duration snapshot');
denied(function () use($engine, $business, $b) {
    $engine->execute(1, $business, 'cancel', ['booking_id' => $b['id'], 'version' => 1], keyid());
}, 409, 'stale version rejected');
$b = $engine->execute(1, $business, 'cancel', ['booking_id' => $b['id'], 'version' => 3], keyid())['booking'];
check($b['status'] === 'cancelled', 'cancellation persists');
check(count($engine->events(1, $business, $b['id'])) === 4, 'complete write audit');
user(3);
denied(function () use($engine, $business, $base) {
    $engine->execute(3, $business, 'book', $base, keyid());
}, 403, 'viewer cannot write');
check(count($engine->calendar(3, $business, $day)) >= 1, 'viewer can read');
user(1);
denied(function () use($engine, $business) {
    $engine->catalogue(1, $business + 100);
}, 403, 'cross-company context rejected');
denied(function () use($engine, $business, $base) {
    $x = $base;
    $x['professional_id'] = 2147483647;
    $engine->execute(1, $business, 'book', $x, keyid());
}, 404, 'foreign or missing professional rejected');
function race($left, $right, $sameKey = false)
{
    global $host, $n;
    $at = microtime(true) + 4;
    $key = keyid();
    $jobs = [];
    foreach ([$left, $right] as $i => $data) {
        $f = dirname(__DIR__) . '/.native-runtime/race-' . keyid() . '.json';
        file_put_contents($f, json_encode(['at' => $at, 'key' => $sameKey ? $key : keyid(), 'data' => $data]));
        $actor = $sameKey ? 1 : $i + 1;
        $pipes = [];
        $process = proc_open([PHP_BINARY, __DIR__ . '/worker.php', $host, (string) $actor, $f], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), null, ['bypass_shell' => true]);
        fclose($pipes[0]);
        $jobs[] = [$process, $pipes, $f];
    }
    $out = [];
    foreach ($jobs as [$process, $pipes, $f]) {
        $text = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        $value = json_decode(trim($text), true);
        if (!$value) {
            throw new RuntimeException('Worker failed: ' . substr($text . $err, 0, 400));
        }
        $out[] = $value;
        unlink($f);
    }
    return $out;
}
$x = $base;
$x['start'] = '11:15';
$race = race($x, $x);
$statuses = array_column($race, 'status');
sort($statuses);
check($statuses === [200, 409], 'two real MySQL workers: one professional, exactly one booking');
$x['start'] = '14:15';
$race = race($x, $x, true);
check(array_column($race, 'status') === [200, 200] && count(array_filter(array_column($race, 'replayed'))) === 1, 'simultaneous same idempotency key creates once');
$other = $engine->configure(1, $business, 'professional', ['name' => 'QA shared chair ' . keyid(), 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]])['id'];
$resource = $db->table('aireception_resources')->insertGetId(['business_id' => $business, 'name' => 'QA shared chair', 'active' => true]);
foreach ([$pro, $other] as $pid) {
    $db->table('aireception_professional_resources')->insert(['professional_id' => $pid, 'resource_id' => $resource]);
}
$x['start'] = '16:15';
$y = $x;
$y['professional_id'] = $other;
$race = race($x, $y);
$statuses = array_column($race, 'status');
sort($statuses);
check($statuses === [200, 409], 'two real MySQL workers: different professionals, one chair');
$system = app('db')->connection('system');
$entitlement = $system->table('client_module_entitlements as e')->join('clients as c', 'c.id', '=', 'e.client_id')->join('hostnames as h', 'h.id', '=', 'c.hostname_id')->join('modules as m', 'm.id', '=', 'e.module_id')->where('h.website_id', $business)->where('m.value', 'aireception')->select('e.*')->first();
try {
    $system->table('client_module_entitlements')->where('client_id', $entitlement->client_id)->where('module_id', $entitlement->module_id)->update(['enabled' => false]);
    denied(function () use($engine, $business) {
        $engine->catalogue(1, $business);
    }, 403, 'disabled company denied despite assigned user permissions');
} finally {
    $system->table('client_module_entitlements')->where('client_id', $entitlement->client_id)->where('module_id', $entitlement->module_id)->update(['enabled' => true]);
}
check(!config('aireception.enabled') && !config('aireception.external_connections_enabled'), 'external AI and Meta remain disabled');
try {
    $db->select('SELECT COUNT(*) FROM mf_v5_tattoo.aireception_bookings');
    throw new RuntimeException('Unexpected cross-database access');
} catch (\Illuminate\Database\QueryException $e) {
    check(in_array((int) ($e->errorInfo[1] ?? 0), [1044, 1142], true), 'Hyn database credentials cannot read another tenant');
}
$engine->configure(1, $business, 'service', ['id' => $svc, 'name' => 'QA duration policy', 'active' => true, 'duration_minutes' => 60, 'buffer_minutes' => 15]);
$free = $engine->availability(1, $business, $other, $svc, $day);
check(count($free) > 0 && strtotime($free[0]['end']) - strtotime($free[0]['start']) === 3600, 'different service duration changes offered slots');
$closed = $policy;
$closed['exceptions'][$day] = [];
$engine->configure(1, $business, 'professional', ['id' => $other, 'name' => 'QA exception policy', 'active' => true, 'policy' => $closed, 'service_ids' => [$svc]]);
check($engine->availability(1, $business, $other, $svc, $day) === [], 'dated exception closes availability');
$engine->configure(1, $business, 'professional', ['id' => $other, 'name' => 'QA restored policy', 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]]);
echo 'RESULT ' . $n . ' real native Laravel/MySQL checks' . PHP_EOL;
