<?php

namespace Modules\AiReception\Agenda;

use Modules\AiReception\Contracts\TattooCalendarSource;
class LegacyCalendarSource implements TattooCalendarSource
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function snapshot(string $businessId, string $professionalId, string $day) : array
    {
        $rows = $this->db->table('aireception_legacy_blocks')->where('business_id', $businessId)->where('professional_id', $professionalId)->whereBetween('day', [(new \DateTimeImmutable($day))->modify('-1 day')->format('Y-m-d'), $day])->get();
        $appointments = [];
        foreach ($rows as $r) {
            $appointments[] = ['appointment_date' => $r->day, 'appointment_time' => $r->start, 'status' => $r->status];
        }
        return ['business_id' => $businessId, 'professional_id' => $professionalId, 'day' => $day, 'appointments' => $appointments, 'sessions' => []];
    }
}
