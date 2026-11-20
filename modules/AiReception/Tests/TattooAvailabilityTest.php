<?php

declare(strict_types=1);
require_once __DIR__ . '/../Contracts/TattooCalendarSource.php';
require_once __DIR__ . '/../Contracts/BookingGateway.php';
require_once __DIR__ . '/../Services/DisabledBookingGateway.php';
require_once __DIR__ . '/../Services/SlotPlanner.php';
require_once __DIR__ . '/../Services/TattooAvailability.php';

use Modules\AiReception\Contracts\TattooCalendarSource;
use Modules\AiReception\Services\DisabledBookingGateway;
use Modules\AiReception\Services\SlotPlanner;
use Modules\AiReception\Services\TattooAvailability;

final class FixtureCalendar implements TattooCalendarSource
{
    public $reads = 0;
    public $data = [];
    public function snapshot(string $businessId, string $professionalId, string $day): array
    {
        $this->reads++;
        return $this->data ?: ['business_id' => $businessId, 'professional_id' => $professionalId,
            'day' => $day, 'appointments' => [], 'sessions' => []];
    }
}
$checks = 0;
function calendarCheck(bool $condition, string $label): void {
    global $checks;
    if (!$condition) throw new RuntimeException($label);
    $checks++;
}
function calendarReject(callable $operation): bool {
    try { $operation(); } catch (RuntimeException $e) { return true; } catch (InvalidArgumentException $e) { return true; }
    return false;
}
$source = new FixtureCalendar();
$service = new TattooAvailability($source, function ($business, $professional) { return $business === '1' && $professional === '7'; });
$policy = ['timezone' => 'America/Lima', 'weekdays' => [6 => [['start' => '09:00', 'end' => '12:00']]],
    'min_notice_minutes' => 0, 'slot_minutes' => 30];
$now = new DateTimeImmutable('2026-11-20T09:00:00-05:00');
$slots = function () use ($service, &$policy, $now) { return $service->slots('1', '7', '2026-11-21', 30, $policy, $now); };
calendarCheck(count($slots()) === 6, 'Empty authorized calendar');
$before = $source->reads;
calendarCheck(calendarReject(function () use ($service, $policy, $now) {
    $service->slots('2', '7', '2026-11-21', 30, $policy, $now);
}), 'Other business denied');
calendarCheck(calendarReject(function () use ($service, $policy, $now) {
    $service->slots('1', '8', '2026-11-21', 30, $policy, $now);
}), 'Other professional denied');
calendarCheck($source->reads === $before, 'Authorization precedes reads');
$source->data = ['business_id' => '2', 'professional_id' => '7', 'day' => '2026-11-21', 'appointments' => [], 'sessions' => []];
calendarCheck(calendarReject($slots), 'Mismatched source scope rejected');
$source->data['business_id'] = '1';
$source->data['appointments'] = [['appointment_date' => '2026-11-21', 'appointment_time' => '09:30:00', 'status' => 'confirmed']];
calendarCheck($slots() === [], 'Unknown legacy duration closes availability');
$policy['legacy_duration_minutes'] = 60;
calendarCheck(count($slots()) === 4, 'Explicit duration blocks overlap');
$source->data['appointments'][0]['status'] = 'in_progress';
calendarCheck(count($slots()) === 4, 'In-progress appointment blocks overlap');
$source->data['appointments'][0]['status'] = 'cancelled';
calendarCheck(count($slots()) === 6, 'Cancelled appointment releases availability');
$source->data['sessions'] = [['session_date' => '2026-11-21', 'session_time' => '10:00:00', 'status' => 'pending']];
calendarCheck(count($slots()) === 4, 'Existing session blocks artist availability');
$source->data['sessions'][0]['session_time'] = '11:00:00';
$moved = array_column($slots(), 'start');
calendarCheck(in_array('10:00', $moved, true) && !in_array('11:00', $moved, true), 'Read snapshot reflects rescheduling');
$source->data['sessions'][0]['session_time'] = null;
calendarCheck($slots() === [], 'Undated time blocks rather than guesses');
$source->data['sessions'][0]['session_time'] = '23:30:00';
calendarCheck($slots() === [], 'Overnight legacy booking closes availability');
$source->data['sessions'][0]['session_date'] = '2026-11-20';
$policy['legacy_duration_minutes'] = 720;
calendarCheck(count($slots()) === 1, 'Previous-day overnight session blocks morning until 11:30');
$policy['legacy_duration_minutes'] = 60;
$bad = [['business_id' => '1', 'professional_id' => '7', 'day' => '2026-11-21',
    'start' => '11:00', 'end' => '10:00', 'status' => 'confirmed']];
calendarCheck(calendarReject(function () use ($bad, $policy, $now) {
    SlotPlanner::slots('1', '7', '2026-11-21', 30, $policy, $bad, $now);
}), 'Reversed occupied interval rejected');
$writer = new DisabledBookingGateway();
foreach (['book', 'cancel', 'reschedule'] as $action) {
    calendarCheck(calendarReject(function () use ($writer, $action) { $writer->$action(['business_id' => '1']); }), $action . ' remains disabled');
}
echo "OK: $checks Tattoo adapter checks (fixtures; no database or network).\n";
