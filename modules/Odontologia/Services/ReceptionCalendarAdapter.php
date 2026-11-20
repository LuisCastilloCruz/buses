<?php

namespace Modules\Odontologia\Services;

use Modules\AiReception\Contracts\DomainCalendar;
class ReceptionCalendarAdapter implements DomainCalendar
{
    public function permits($db, int $business, int $source, string $day, int $duration, array $policy) : bool
    {
        return $db->table('odontologia_dentists')->where('id', $source)->where('active', true)->exists();
    }
}
