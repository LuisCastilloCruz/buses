<?php

declare(strict_types=1);

namespace Modules\AiReception\Services;

use Hyn\Tenancy\Environment;
use Modules\AiReception\Contracts\TattooCalendarSource;
use Modules\Tattoo\Models\TattooAppointment;
use Modules\Tattoo\Models\TattooArtist;
use Modules\Tattoo\Models\TattooSession;
use RuntimeException;

/** Uses verified existing fields only. Never selects a tenant from client/AI input. */
final class HynTattooCalendarSource implements TattooCalendarSource
{
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function snapshot(string $businessId, string $professionalId, string $day): array
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $day);
        if (!$date || $date->format('Y-m-d') !== $day) throw new RuntimeException('Invalid calendar day.');
        $previousDay = $date->modify('-1 day')->format('Y-m-d');
        $tenant = $this->environment->tenant();
        if (!$tenant || (string) $tenant->id !== $businessId) {
            throw new RuntimeException('An explicitly resolved matching tenant is required.');
        }
        if (!ctype_digit($professionalId) || (int) $professionalId < 1) {
            throw new RuntimeException('Invalid professional.');
        }
        if (!TattooArtist::query()->where('id', $professionalId)->where('active', true)->exists()) {
            throw new RuntimeException('Professional unavailable in this tenant.');
        }
        $appointments = TattooAppointment::query()
            ->where('tattoo_artist_id', $professionalId)
            ->whereDate('appointment_date', '>=', $previousDay)->whereDate('appointment_date', '<=', $day)
            ->get(['id', 'appointment_date', 'appointment_time', 'status'])->toArray();
        $sessions = TattooSession::query()->whereDate('session_date', '>=', $previousDay)->whereDate('session_date', '<=', $day)
            ->whereHas('appointment', function ($query) use ($professionalId) {
                $query->where('tattoo_artist_id', $professionalId);
            })->get(['id', 'session_date', 'session_time', 'status'])->toArray();
        return [
            'business_id' => $businessId, 'professional_id' => $professionalId, 'day' => $day,
            'appointments' => $appointments, 'sessions' => $sessions,
        ];
    }
}
