<?php

$app = (require dirname(__DIR__) . '/bootstrap.php');
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$db = app('db')->connection('agenda');
Modules\AiReception\Agenda\LabSafety::check($db);
$e = app(Modules\AiReception\Agenda\BookingService::class);
$n = 0;
function check($ok, $name)
{
    global $n;
    if (!$ok) {
        throw new RuntimeException('FAIL ' . $name);
    }
    $n++;
    echo "PASS {$name}\n";
}
function denied($fn, $status, $name)
{
    try {
        $fn();
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $x) {
        check($x->getStatusCode() === $status, $name);
        return;
    }
    throw new RuntimeException('Expected rejection ' . $name);
}
function keyid()
{
    return 'test-' . bin2hex(random_bytes(12));
}
$day = date('Y-m-d', strtotime('+10 days'));
while (date('N', strtotime($day)) == 7) {
    $day = date('Y-m-d', strtotime($day . ' +1 day'));
}
$policy = json_decode($db->table('aireception_professionals')->where('id', 1)->value('policy'), true);
$svc = $e->configure(2, 1, 'service', ['name' => 'QA isolated ' . keyid(), 'active' => true, 'duration_minutes' => 30, 'buffer_minutes' => 15])['id'];
$pro = $e->configure(2, 1, 'professional', ['name' => 'QA isolated ' . keyid(), 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]])['id'];
$base = ['professional_id' => $pro, 'service_id' => $svc, 'day' => $day, 'start' => '09:15', 'client_name' => 'Fictitious QA', 'status' => 'pending'];
check(count($e->catalogue(2, 1)['professionals']) >= 2 && count($e->catalogue(3, 2)['professionals']) === 2, 'separate catalogues');
denied(function () use($e) {
    $e->catalogue(2, 2);
}, 403, 'cross-business read forbidden');
denied(function () use($e, $base) {
    $e->execute(4, 1, 'book', $base, keyid());
}, 403, 'viewer cannot book');
denied(function () use($e, $day, $svc) {
    $e->availability(2, 1, 3, $svc, $day);
}, 404, 'foreign professional forbidden');
denied(function () use($e, $day, $pro) {
    $e->availability(2, 1, $pro, 3, $day);
}, 422, 'foreign service forbidden');
$slots = $e->availability(2, 1, $pro, $svc, $day);
check($slots[0] === ['start' => '09:15', 'end' => '09:45'], 'duration and opening margin');
check(!in_array('12:45', array_column($slots, 'start')) && !in_array('13:00', array_column($slots, 'start')), 'lunch and margin');
$sunday = date('Y-m-d', strtotime('next Sunday'));
check($e->availability(2, 1, $pro, $svc, $sunday) === [], 'closed Sunday');
$k = keyid();
$a = $e->execute(2, 1, 'book', $base, $k);
$id = $a['booking']['id'];
check($a['booking']['status'] === 'pending', 'pending reservation persists');
check($e->execute(2, 1, 'book', $base, $k)['replayed'] === true, 'idempotent retry');
denied(function () use($e, $base, $k) {
    $base['client_name'] = 'Other';
    $e->execute(2, 1, 'book', $base, $k);
}, 409, 'idempotency payload conflict');
$before = $db->table('aireception_events')->count();
denied(function () use($e, $base) {
    $e->execute(5, 1, 'book', $base, keyid());
}, 409, 'occupied rejected');
check($db->table('aireception_events')->count() === $before, 'failed command rolled back');
$slots = $e->availability(2, 1, $pro, $svc, $day);
check(!in_array('10:00', array_column($slots, 'start')) && in_array('10:15', array_column($slots, 'start')), 'sum of appointment margins');
$a = $e->execute(2, 1, 'confirm', ['booking_id' => $id, 'version' => 1], keyid());
check($a['booking']['status'] === 'confirmed', 'confirmation');
$e->configure(2, 1, 'service', ['id' => $svc, 'name' => 'QA changed service', 'active' => true, 'duration_minutes' => 60, 'buffer_minutes' => 0]);
check(in_array(['start' => '11:00', 'end' => '11:30'], $e->reschedulingAvailability(2, 1, $id, $day), true), 'reschedule availability uses booking snapshot');
$a = $e->execute(2, 1, 'reschedule', ['booking_id' => $id, 'version' => 2, 'day' => $day, 'start' => '11:00'], keyid());
check($a['booking']['duration_minutes'] === 30 && $a['booking']['end'] === '11:30', 'reschedule preserves explicit original duration');
denied(function () use($e, $id) {
    $e->execute(3, 2, 'cancel', ['booking_id' => $id, 'version' => 3], keyid());
}, 404, 'foreign cancellation forbidden');
denied(function () use($e, $id) {
    $e->execute(2, 1, 'cancel', ['booking_id' => $id, 'version' => 2], keyid());
}, 409, 'stale version forbidden');
$a = $e->execute(5, 1, 'cancel', ['booking_id' => $id, 'version' => 3], keyid());
check($a['booking']['status'] === 'cancelled' && count($e->events(2, 1, $id)) === 4, 'authorized cancellation and complete audit');
$policy['exceptions'][$day] = [];
$e->configure(2, 1, 'professional', ['id' => $pro, 'name' => 'QA exceptions', 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]]);
check($e->availability(2, 1, $pro, $svc, $day) === [], 'date exception closed');
unset($policy['exceptions'][$day]);
$e->configure(2, 1, 'professional', ['id' => $pro, 'name' => 'QA concurrency', 'active' => true, 'policy' => $policy, 'service_ids' => [$svc]]);
$db->table('aireception_legacy_blocks')->insert(['business_id' => 1, 'professional_id' => $pro, 'day' => $day, 'start' => '15:00', 'status' => 'pending', 'source_reference' => 'fictional-test']);
check($e->availability(2, 1, $pro, $svc, $day) === [], 'unknown legacy duration blocks day');
$db->table('aireception_legacy_blocks')->where('business_id', 1)->where('professional_id', $pro)->delete();
denied(function () use($e) {
    $e->configure(5, 1, 'service', ['name' => 'Denied', 'active' => true, 'duration_minutes' => 30, 'buffer_minutes' => 0]);
}, 403, 'reception cannot configure');
// Two different authenticated actors, independent PHP processes and MySQL connections.
$jobs = [];
$processes = [];
$at = microtime(true) + 3;
$race = $base;
$race['start'] = '09:00';
$race['status'] = 'confirmed';
foreach ([2, 5] as $i => $actor) {
    $path = dirname(__DIR__, 2) . '/.agenda-runtime/race-' . $i . '.json';
    file_put_contents($path, json_encode(['at' => $at, 'actor' => $actor, 'data' => $race, 'key' => keyid()]));
    $pipes = [];
    $proc = proc_open([PHP_BINARY, __DIR__ . '/worker.php', $path], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($proc)) {
        throw new RuntimeException('Cannot launch concurrent worker');
    }
    fclose($pipes[0]);
    $processes[] = [$proc, $pipes];
}
$statuses = [];
foreach ($processes as $p) {
    $out = stream_get_contents($p[1][1]);
    $err = stream_get_contents($p[1][2]);
    fclose($p[1][1]);
    fclose($p[1][2]);
    $exit = proc_close($p[0]);
    $r = json_decode($out, true);
    if (!$r) {
        throw new RuntimeException('Worker failure ' . $err . ' ' . $out);
    }
    $statuses[] = $r['status'];
}
sort($statuses);
check($statuses === [200, 409], 'real concurrent MySQL requests: one winner');
check($db->table('aireception_bookings')->where('professional_id', $pro)->where('status', 'confirmed')->count() === 1, 'one persisted concurrent booking');
// Simultaneous retries with one idempotency key must replay, not insert twice.
$processes = [];
$sameKey = keyid();
$race['start'] = '11:00';
$at = microtime(true) + 2;
foreach ([0, 1] as $i) {
    $path = dirname(__DIR__, 2) . '/.agenda-runtime/retry-' . $i . '.json';
    file_put_contents($path, json_encode(['at' => $at, 'actor' => 2, 'data' => $race, 'key' => $sameKey]));
    $pipes = [];
    $proc = proc_open([PHP_BINARY, __DIR__ . '/worker.php', $path], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    fclose($pipes[0]);
    $processes[] = [$proc, $pipes];
}
$replays = [];
$ids = [];
foreach ($processes as $p) {
    $out = stream_get_contents($p[1][1]);
    $err = stream_get_contents($p[1][2]);
    fclose($p[1][1]);
    fclose($p[1][2]);
    proc_close($p[0]);
    $r = json_decode($out, true);
    if (!$r || $r['status'] !== 200) {
        throw new RuntimeException('Retry worker failed ' . $err);
    }
    $replays[] = $r['result']['replayed'];
    $ids[] = $r['result']['booking']['id'];
}
sort($replays);
check($replays === [false, true] && $ids[0] === $ids[1], 'simultaneous idempotent retries replay one booking');
$ingress = new Modules\AiReception\Agenda\FutureMetaIngress();
$body = '{"object":"whatsapp_business_account","entry":[]}';
foreach ([['', ''], ['sha256=' . str_repeat('0', 64), 'fictional-secret']] as $case) {
    try {
        $ingress->receive($body, $case[0], $case[1], []);
        throw new RuntimeException('Unsigned input accepted');
    } catch (InvalidArgumentException $x) {
        check(true, 'future ingress rejects missing or invalid signature');
    }
}
check($ingress->receive($body, 'sha256=' . hash_hmac('sha256', $body, 'fictional-secret'), 'fictional-secret', []) === [], 'future ingress accepts valid simulated signature');
check(!in_array(Modules\AiReception\Providers\AiReceptionServiceProvider::class, config('app.providers'), true), 'original disabled provider not activated');
check(config('database.default') === 'agenda' && count(config('database.connections')) === 1, 'only disposable database configured');
// Delete only resources created by this run. Keep demonstration records untouched.
$db->transaction(function () use($db, $pro, $svc) {
    $db->table('aireception_commands')->where('business_id', 1)->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(result, '\$.professional_id')) = ?", [(string) $pro])->delete();
    $bookingIds = $db->table('aireception_bookings')->where('business_id', 1)->where('professional_id', $pro)->pluck('id')->all();
    $db->table('aireception_events')->where('business_id', 1)->whereIn('booking_id', $bookingIds)->delete();
    foreach ([['configure_professional', $pro], ['configure_service', $svc]] as $pair) {
        $db->table('aireception_events')->where('business_id', 1)->where('action', $pair[0])->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(details, '\$.id')) = ?", [(string) $pair[1]])->delete();
    }
    $db->table('aireception_bookings')->where('business_id', 1)->where('professional_id', $pro)->delete();
    $db->table('aireception_professional_services')->where('business_id', 1)->where('professional_id', $pro)->delete();
    $db->table('aireception_professionals')->where('business_id', 1)->where('id', $pro)->delete();
    $db->table('aireception_services')->where('business_id', 1)->where('id', $svc)->delete();
});
echo "RESULT {$n} assertions passed; actual MySQL " . $db->selectOne('SELECT VERSION() as v')->v . "\n";
