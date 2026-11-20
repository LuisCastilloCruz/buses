<?php

namespace Modules\AiReception\Contracts;

/** Domain adapters contribute restrictions; only BookingService persists reservations. */
interface CalendarGuard
{
    public function lock($db, int $business, int $professional) : void;
    public function permits($db, int $business, int $professional, string $day, int $duration, array $policy) : bool;
    public function intervals($db, int $business, int $professional, string $day, ?int $exclude) : array;
}
