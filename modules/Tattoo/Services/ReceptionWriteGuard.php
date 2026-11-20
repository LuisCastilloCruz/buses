<?php

namespace Modules\Tattoo\Services;

class ReceptionWriteGuard
{
    public static function check(int $artist, ?string $day) : void
    {
        if (!config('aireception.native_enabled', false) || !$day) {
            return;
        }
        $access = app(\Modules\AiReception\Native\Access::class);
        if (!$access->enabled('aireception')) {
            return;
        }
        $db = app('db')->connection('tenant');
        if ($db->transactionLevel() === 0) {
            throw new \LogicException('Legacy agenda guard requires transaction.');
        }
        $link = $db->table('aireception_professional_links')->where('domain', 'tattoo')->where('source_id', $artist)->first();
        if (!$link) {
            return;
        }
        (new \Modules\AiReception\Native\CalendarGuard())->lock($db, $access->business(), (int) $link->professional_id);
        $db->table('aireception_professionals')->where('id', $link->professional_id)->lockForUpdate()->first();
        $next = (new \DateTimeImmutable($day))->modify('+1 day')->format('Y-m-d');
        // A locking read sees the latest committed rows even under legacy REPEATABLE READ.
        $occupied = $db->table('aireception_bookings')->where('professional_id', $link->professional_id)->where('business_id', $access->business())->where('status', '!=', 'cancelled')->whereBetween('day', [$day, $next])->lockForUpdate()->get();
        if ($occupied->isNotEmpty()) {
            abort(409, 'Existe una reserva en la agenda compartida. La cita heredada no tiene duracion verificada.');
        }
    }
}
