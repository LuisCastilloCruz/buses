<?php

declare(strict_types=1);

namespace Modules\AiReception\Services;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Pure, read-only slot calculator for an independently resolved business and professional.
 * Does not query Laravel, modify reservations or call any external provider.
 *
 * The caller MUST validate user authorization to access the business and professional.
 * A transaction and DB constraints are still necessary when booking a returned slot.
 */
final class SlotPlanner
{
    /**
     * @param array<string,mixed> $policy Business rules and working windows.
     * @param array<int,array<string,mixed>> $bookings Booking records. Cross-business records are ignored.
     * @return array<int,array<string,string>> Available HH:MM start/end pairs.
     */
    public static function slots(
        string $businessId,
        string $professionalId,
        string $day,
        int $serviceMinutes,
        array $policy,
        array $bookings,
        ?DateTimeImmutable $now = null
    ): array {
        if (trim($businessId) === '' || trim($professionalId) === '') {
            throw new InvalidArgumentException('Business and professional IDs are required.');
        }
        if ($serviceMinutes < 5 || $serviceMinutes > 720) {
            throw new InvalidArgumentException('Service duration must be 5..720 minutes.');
        }
        $tzName = $policy['timezone'] ?? 'America/Lima';
        if (!is_string($tzName)) {
            throw new InvalidArgumentException('Invalid timezone.');
        }
        try {
            $timezone = new DateTimeZone($tzName);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid timezone.', 0, $e);
        }
        if (!is_string($day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day)) {
            throw new InvalidArgumentException('Day must be YYYY-MM-DD.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $day, $timezone);
        if (!$date || $date->format('Y-m-d') !== $day) {
            throw new InvalidArgumentException('Invalid calendar day.');
        }
        $now = ($now ?: new DateTimeImmutable('now', $timezone))->setTimezone($timezone);
        $today = $now->setTime(0, 0);
        $maxDays = self::integerRule($policy, 'max_days_ahead', 90, 1, 365);
        if ($date < $today || $date > $today->modify('+' . $maxDays . ' days')) {
            return [];
        }
        $notice = self::integerRule($policy, 'min_notice_minutes', 30, 0, 10080);
        $step = self::integerRule($policy, 'slot_minutes', 30, 5, 120);
        $buffer = self::integerRule($policy, 'buffer_minutes', 0, 0, 180);
        $weekdays = $policy['weekdays'] ?? [];
        $exceptions = $policy['exceptions'] ?? [];
        if (!is_array($weekdays) || !is_array($exceptions)) {
            throw new InvalidArgumentException('Weekday and exception schedules must be arrays.');
        }
        $weekday = (int) $date->format('N');
        $windows = array_key_exists($day, $exceptions) ? $exceptions[$day] : ($weekdays[$weekday] ?? []);
        if (!is_array($windows)) {
            throw new InvalidArgumentException('Working windows must be an array.');
        }
        $cutoff = $now->modify('+' . $notice . ' minutes');
        $occupied = [];
        foreach ($bookings as $booking) {
            if (!is_array($booking) || ($booking['business_id'] ?? null) !== $businessId
                || ($booking['professional_id'] ?? null) !== $professionalId
                || ($booking['day'] ?? null) !== $day
                || !in_array(($booking['status'] ?? ''), ['confirmed', 'pending'], true)) {
                continue;
            }
            $occupied[] = [
                self::toMinutes($booking['start'] ?? null),
                self::toMinutes($booking['end'] ?? null),
            ];
        }
        $available = [];
        foreach ($windows as $window) {
            if (!is_array($window)) {
                throw new InvalidArgumentException('Malformed working window.');
            }
            $open = self::toMinutes($window['start'] ?? null);
            $close = self::toMinutes($window['end'] ?? null);
            if ($close <= $open) {
                throw new InvalidArgumentException('Overnight/empty windows are not supported.');
            }
            for ($minute = $open; $minute + $serviceMinutes <= $close; $minute += $step) {
                $start = self::clock($minute);
                $end = self::clock($minute + $serviceMinutes);
                $candidate = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $day . ' ' . $start, $timezone);
                if (!$candidate || $candidate < $cutoff) {
                    continue;
                }
                $free = true;
                foreach ($occupied as $appointment) {
                    // Buffers are reserved before and after an existing appointment.
                    if ($minute < $appointment[1] + $buffer
                        && $appointment[0] - $buffer < $minute + $serviceMinutes) {
                        $free = false;
                        break;
                    }
                }
                if ($free) {
                    $available[$start] = ['start' => $start, 'end' => $end];
                }
            }
        }
        ksort($available);
        return array_values($available);
    }

    /** @param array<string,mixed> $rules */
    private static function integerRule(array $rules, string $key, int $default, int $min, int $max): int
    {
        $value = $rules[$key] ?? $default;
        if (!is_int($value) || $value < $min || $value > $max) {
            throw new InvalidArgumentException('Invalid scheduling rule: ' . $key);
        }
        return $value;
    }

    /** @param mixed $clock */
    private static function toMinutes($clock): int
    {
        if (!is_string($clock) || !preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/D', $clock, $parts)) {
            // 24:00 is a valid closing boundary only; not needed for initial release.
            throw new InvalidArgumentException('Time must be HH:MM between 00:00 and 23:59.');
        }
        return ((int) $parts[1]) * 60 + (int) $parts[2];
    }

    private static function clock(int $minute): string
    {
        return sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
    }
}
