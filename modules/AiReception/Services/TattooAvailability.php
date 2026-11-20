<?php

declare(strict_types=1);

namespace Modules\AiReception\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use Modules\AiReception\Contracts\TattooCalendarSource;
use RuntimeException;

/** No writes, no AI calls. Policy and authorization come from trusted application code. */
final class TattooAvailability
{
    private $source;
    private $authorize;

    public function __construct(TattooCalendarSource $source, callable $authorize)
    {
        $this->source = $source;
        $this->authorize = $authorize;
    }

    public function slots(string $businessId, string $professionalId, string $day, int $minutes, array $policy, ?DateTimeImmutable $now = null): array
    {
        if (($this->authorize)($businessId, $professionalId) !== true) {
            throw new RuntimeException('Calendar access denied.');
        }
        // Validate input before allowing any database read.
        SlotPlanner::slots($businessId, $professionalId, $day, $minutes, $policy, [], $now);
        $snapshot = $this->source->snapshot($businessId, $professionalId, $day);
        if (($snapshot['business_id'] ?? null) !== $businessId
            || ($snapshot['professional_id'] ?? null) !== $professionalId
            || ($snapshot['day'] ?? null) !== $day) {
            throw new RuntimeException('Calendar scope mismatch.');
        }
        $bookings = [];
        $previousDay = (new DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d');
        foreach (['appointments' => 'appointment', 'sessions' => 'session'] as $collection => $prefix) {
            if (!isset($snapshot[$collection]) || !is_array($snapshot[$collection])) {
                throw new RuntimeException('Incomplete calendar snapshot.');
            }
            foreach ($snapshot[$collection] as $row) {
                if (!is_array($row)) throw new RuntimeException('Invalid calendar record.');
                if (($row['status'] ?? null) === 'cancelled') continue;
                $recordDay = $row[$prefix . '_date'] ?? null;
                if ($recordDay !== $day && $recordDay !== $previousDay) {
                    throw new RuntimeException('Calendar date mismatch.');
                }
                $time = $row[$prefix . '_time'] ?? null;
                $duration = $policy['legacy_duration_minutes'] ?? null;
                // Existing Tattoo schema has no duration. Never invent one or advertise false availability.
                if ($time === null || $time === '' || $duration === null) return [];
                if (!is_int($duration) || $duration < 5 || $duration > 720) {
                    throw new InvalidArgumentException('Explicit legacy duration must be 5..720 minutes.');
                }
                if (!is_string($time) || !preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])(?::00)?$/D', $time, $parts)) {
                    throw new InvalidArgumentException('Invalid legacy appointment time.');
                }
                $end = (int) $parts[1] * 60 + (int) $parts[2] + $duration;
                $start = substr($time, 0, 5);
                if ($recordDay === $previousDay) {
                    if ($end <= 1440) continue;
                    $start = '00:00';
                    $end -= 1440;
                } elseif ($end >= 1440) {
                    return []; // Do not advertise slots on a day with unsupported overnight occupation.
                }
                $bookings[] = [
                    'business_id' => $businessId, 'professional_id' => $professionalId,
                    'day' => $day, 'start' => $start,
                    'end' => sprintf('%02d:%02d', intdiv($end, 60), $end % 60),
                    'status' => 'confirmed',
                ];
            }
        }
        return SlotPlanner::slots($businessId, $professionalId, $day, $minutes, $policy, $bookings, $now);
    }
}
