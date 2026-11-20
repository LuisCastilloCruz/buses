<?php

namespace Modules\AiReception\Contracts;

interface DomainCalendar
{
    public function permits($db, int $business, int $source, string $day, int $duration, array $policy) : bool;
}
