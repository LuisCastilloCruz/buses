<?php

require __DIR__ . '/test-bootstrap.php';
if ($host !== 'tattoo') {
    throw new RuntimeException('Tattoo fixture required');
}
$n = 0;
function check($ok, $label)
{
    global $n;
    if (!$ok) {
        throw new RuntimeException('FAIL ' . $label);
    }
    echo 'PASS ' . ++$n . ' ' . $label . PHP_EOL;
}
$link = $db->table('aireception_professional_links')->where('domain', 'tattoo')->first();
$pro = (int) $link->professional_id;
$svc = (int) $db->table('aireception_professional_services')->where('professional_id', $pro)->value('service_id');
$person = (int) $db->table('persons')->where('name', 'like', 'Paciente ficticio%')->value('id');
$day = date('Y-m-d', strtotime('+25 days'));
while (date('N', strtotime($day)) == 7) {
    $day = date('Y-m-d', strtotime($day . ' +1 day'));
}
$legacy = $db->transaction(function () use($db, $link, $person, $day) {
    Modules\Tattoo\Services\ReceptionWriteGuard::check((int) $link->source_id, $day);
    return $db->table('tattoo_appointments')->insertGetId(['person_id' => $person, 'tattoo_artist_id' => $link->source_id, 'appointment_date' => $day, 'appointment_time' => '09:00', 'status' => 'pending']);
});
try {
    check($engine->availability(1, $business, $pro, $svc, $day) === [], 'legacy unknown duration closes entire day');
    $next = date('Y-m-d', strtotime($day . ' +1 day'));
    check($engine->availability(1, $business, $pro, $svc, $next) === [], 'unknown previous-day duration cannot leak availability');
} finally {
    $db->table('tattoo_appointments')->where('id', $legacy)->update(['status' => 'cancelled']);
}
$slots = $engine->availability(1, $business, $pro, $svc, $day);
check(count($slots) > 0, 'legacy cancellation releases native availability');
$b = $engine->execute(1, $business, 'book', ['professional_id' => $pro, 'service_id' => $svc, 'day' => $day, 'start' => $slots[0]['start'], 'client_name' => 'Fictitious legacy guard QA'], 'legacy-' . bin2hex(random_bytes(8)))['booking'];
try {
    try {
        $db->transaction(function () use($link, $day) {
            Modules\Tattoo\Services\ReceptionWriteGuard::check((int) $link->source_id, $day);
        });
        throw new RuntimeException('Legacy writer did not reject');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
        check($e->getStatusCode() === 409, 'legacy writer rejects canonical occupied day under lock');
    }
} finally {
    $engine->execute(1, $business, 'cancel', ['booking_id' => $b['id'], 'version' => 1], 'legacy-' . bin2hex(random_bytes(8)));
}
check($db->table('tattoo_artists')->where('id', $link->source_id)->exists(), 'original artist retained');
check($db->getSchemaBuilder()->hasTable('tattoo_inbox_conversations'), 'original TattooInbox schema retained');
echo 'RESULT ' . $n . ' real legacy compatibility checks' . PHP_EOL;
