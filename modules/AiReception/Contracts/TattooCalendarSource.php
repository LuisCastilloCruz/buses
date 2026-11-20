<?php

namespace Modules\AiReception\Contracts;

interface TattooCalendarSource
{
    /** Read-only snapshot; implementors must validate the active tenant before querying. */
    public function snapshot(string $businessId, string $professionalId, string $day): array;
}
