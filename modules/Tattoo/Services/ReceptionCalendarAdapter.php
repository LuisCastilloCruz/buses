<?php

namespace Modules\Tattoo\Services;

use Modules\AiReception\Contracts\DomainCalendar;
use Modules\AiReception\Services\TattooAvailability;
use Hyn\Tenancy\Environment;
class ReceptionCalendarAdapter implements DomainCalendar
{
    public function permits($db, int $business, int $source, string $day, int $duration, array $policy) : bool
    {
        unset($policy['legacy_duration_minutes']);
        $environment = app(Environment::class);
        $adapter = new TattooAvailability(new HynTattooCalendarSource($environment), function ($businessId, $professionalId) use($environment, $business, $source) {
            $tenant = $environment->tenant();
            return $tenant && (string) $tenant->id === $businessId && $businessId === (string) $business && $professionalId === (string) $source;
        });
        try {
            return count($adapter->slots((string) $business, (string) $source, $day, $duration, $policy)) > 0;
        } catch (\RuntimeException $e) {
            return false;
        }
        // Unknown/inactive source or unverifiable legacy snapshot fails closed.
    }
}
