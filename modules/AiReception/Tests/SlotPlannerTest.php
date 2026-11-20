<?php

declare(strict_types=1);

require __DIR__ . '/../Services/SlotPlanner.php';

use Modules\AiReception\Services\SlotPlanner;

$checks = 0;
function checkSlot(bool $condition, string $description): void
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException('FAILED: ' . $description);
    }
}
function badSlot(callable $callback): bool
{
    try {
        $callback();
    } catch (InvalidArgumentException $exception) {
        return true;
    }
    return false;
}
$policy = [
    'timezone' => 'America/Lima',
    'weekdays' => [
        6 => [ ['start' => '09:00', 'end' => '12:00'], ['start' => '13:00', 'end' => '15:00'] ],
    ],
    'exceptions' => [],
    'min_notice_minutes' => 0,
    'slot_minutes' => 30,
    'buffer_minutes' => 0,
    'max_days_ahead' => 90,
];
$now = new DateTimeImmutable('2026-11-20 09:00:00', new DateTimeZone('America/Lima'));
$day = '2026-11-21'; // Saturday.
$all = SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $policy, [], $now);
checkSlot(count($all) === 10, 'Two opening windows return ten half-hour slots');
checkSlot($all[0]['start'] === '09:00', 'First opening time');
checkSlot($all[9]['start'] === '14:30', 'Last opening time');
checkSlot(!in_array('12:00', array_column($all, 'start'), true), 'Lunch break excluded');

$bookings = [
    ['business_id' => 'tenant-A', 'professional_id' => 'dentist-1', 'day' => $day,
        'start' => '09:30', 'end' => '10:30', 'status' => 'confirmed'],
];
$slots = SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $policy, $bookings, $now);
checkSlot(count($slots) === 8, 'One-hour appointment blocks two slots');
checkSlot(!in_array('09:30', array_column($slots, 'start'), true), 'Overlapping interval excluded');
checkSlot(!in_array('10:00', array_column($slots, 'start'), true), 'Partial overlap excluded');
checkSlot(in_array('10:30', array_column($slots, 'start'), true), 'Adjacent slot permitted');
checkSlot(count(SlotPlanner::slots('tenant-B', 'dentist-1', $day, 30, $policy, $bookings, $now)) === 10,
    'Other business appointments cannot block tenant');
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-2', $day, 30, $policy, $bookings, $now)) === 10,
    'Other professional appointments cannot block calendar');
$bookings[0]['status'] = 'cancelled';
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $policy, $bookings, $now)) === 10,
    'Cancelled bookings do not reserve capacity');
$bookings[0]['status'] = 'pending';
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $policy, $bookings, $now)) === 8,
    'Pending bookings reserve capacity');

$withBuffer = $policy;
$withBuffer['buffer_minutes'] = 15;
$buffered = SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $withBuffer, $bookings, $now);
checkSlot(count($buffered) === 6, 'Buffers exclude neighboring half-hour slots');

$closure = $policy;
$closure['exceptions'][$day] = [];
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $closure, [], $now)) === 0,
    'Business holiday overrides weekly hours');
$special = $policy;
$special['exceptions'][$day] = [['start' => '16:00', 'end' => '17:00']];
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $special, [], $now)) === 2,
    'Special hours override weekday hours');
$hour = SlotPlanner::slots('tenant-A', 'dentist-1', $day, 60, $policy, [], $now);
checkSlot(count($hour) === 8, 'Service duration respected');

$sameDay = $policy;
$sameDay['weekdays'][5] = [['start' => '09:00', 'end' => '11:00']];
$sameDay['min_notice_minutes'] = 60;
$today = SlotPlanner::slots('tenant-A', 'dentist-1', '2026-11-20', 30, $sameDay, [], $now);
checkSlot(count($today) === 2 && $today[0]['start'] === '10:00', 'Minimum notice applied');
checkSlot(SlotPlanner::slots('tenant-A', 'dentist-1', '2026-11-19', 30, $sameDay, [], $now) === [],
    'Past date returns empty');
checkSlot(SlotPlanner::slots('tenant-A', 'dentist-1', '2027-03-21', 30, $policy, [], $now) === [],
    'Max advance window enforced');
checkSlot(badSlot(static function () use ($policy, $now) {
    SlotPlanner::slots('tenant-A', 'dentist-1', '2026-02-30', 30, $policy, [], $now);
}), 'Invalid date rejected');
checkSlot(badSlot(static function () use ($policy, $now, $day) {
    SlotPlanner::slots('tenant-A', 'dentist-1', $day, 0, $policy, [], $now);
}), 'Invalid duration rejected');
checkSlot(badSlot(static function () use ($policy, $now, $day) {
    SlotPlanner::slots('', 'dentist-1', $day, 30, $policy, [], $now);
}), 'Missing tenant rejected');
checkSlot(badSlot(static function () use ($policy, $now, $day) {
    $bad = $policy;
    $bad['slot_minutes'] = 0;
    SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $bad, [], $now);
}), 'Invalid slot interval rejected');
checkSlot(badSlot(static function () use ($policy, $now, $day) {
    $bad = $policy;
    $bad['exceptions'][$day] = [['start' => '18:00', 'end' => '08:00']];
    SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $bad, [], $now);
}), 'Overnight window must be explicitly implemented');
checkSlot(badSlot(static function () use ($policy, $now, $day) {
    $bad = $policy;
    $bad['timezone'] = 'Mars/Olympus';
    SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $bad, [], $now);
}), 'Invalid timezone rejected');

$overlappingWindows = $policy;
$overlappingWindows['exceptions'][$day] = [
    ['start' => '09:00', 'end' => '10:00'],
    ['start' => '09:30', 'end' => '10:30'],
];
checkSlot(count(SlotPlanner::slots('tenant-A', 'dentist-1', $day, 30, $overlappingWindows, [], $now)) === 3,
    'Overlapping windows deduplicated');

fwrite(STDOUT, "OK: {$checks} comprobaciones SlotPlanner.\n");
