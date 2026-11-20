<?php

namespace Modules\AiReception\Agenda;

use Modules\AiReception\Contracts\BookingAuthority;
/** Compatibility adapter used exclusively by the V1 regression laboratory. */
class LabAuthority implements BookingAuthority
{
    public function authorize($db, int $actor, int $business, bool $write, bool $lock)
    {
        $q = $db->table('aireception_memberships')->where('business_id', $business)->where('user_id', $actor)->where('active', true);
        if ($lock) {
            $q->lockForUpdate();
        }
        $m = $q->first();
        if (!$m || !in_array($m->role, $write ? ['admin', 'reception'] : ['admin', 'reception', 'viewer'], true)) {
            abort(403, 'No tienes permiso para este negocio.');
        }
        return $m;
    }
    public function business($db, int $business)
    {
        return $db->table('aireception_businesses')->where('id', $business)->first();
    }
}
