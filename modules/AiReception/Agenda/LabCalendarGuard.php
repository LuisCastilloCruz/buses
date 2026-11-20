<?php

namespace Modules\AiReception\Agenda;

use Modules\AiReception\Contracts\CalendarGuard;
class LabCalendarGuard implements CalendarGuard
{
    public function lock($db, int $business, int $professional) : void
    {
    }
    public function permits($db, int $business, int $professional, string $day, int $duration, array $policy) : bool
    {
        return !$db->table('aireception_legacy_blocks')->where('business_id', $business)->where('professional_id', $professional)->where('status', '!=', 'cancelled')->whereBetween('day', [(new \DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d'), $day])->exists();
    }
    public function intervals($db, int $business, int $professional, string $day, ?int $exclude) : array
    {
        return [];
    }
}
