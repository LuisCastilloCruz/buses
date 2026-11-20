<?php

namespace Modules\AiReception\Native;

class CalendarGuard implements \Modules\AiReception\Contracts\CalendarGuard
{
    private $adapters;
    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }
    public function lock($db, int $business, int $professional) : void
    {
        $ids = $db->table('aireception_professional_resources')->where('professional_id', $professional)->pluck('resource_id')->all();
        if ($ids) {
            $db->table('aireception_resources')->where('business_id', $business)->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        }
    }
    public function permits($db, int $business, int $professional, string $day, int $duration, array $policy) : bool
    {
        $link = $db->table('aireception_professional_links')->where('professional_id', $professional)->first();
        if (!$link) {
            return true;
        }
        if (!isset($this->adapters[$link->domain])) {
            return false;
        }
        return $this->adapters[$link->domain]->permits($db, $business, (int) $link->source_id, $day, $duration, $policy);
    }
    public function intervals($db, int $business, int $professional, string $day, ?int $exclude) : array
    {
        $resources = $db->table('aireception_professional_resources')->where('professional_id', $professional)->pluck('resource_id')->all();
        if (!$resources) {
            return [];
        }
        if ($db->table('aireception_resources')->whereIn('id', $resources)->where('active', false)->exists()) {
            return [['business_id' => (string) $business, 'professional_id' => (string) $professional, 'day' => $day, 'start' => '00:00', 'end' => '23:59', 'status' => 'confirmed']];
        }
        $others = $db->table('aireception_professional_resources')->whereIn('resource_id', $resources)->where('professional_id', '!=', $professional)->pluck('professional_id')->all();
        $rows = $db->table('aireception_bookings')->where('business_id', $business)->whereIn('professional_id', $others)->where('day', $day)->where('status', '!=', 'cancelled')->get();
        $out = [];
        foreach ($rows as $r) {
            $start = max(0, (int) substr($r->start, 0, 2) * 60 + (int) substr($r->start, 3, 2) - (int) $r->buffer_minutes);
            $end = min(1439, (int) substr($r->end, 0, 2) * 60 + (int) substr($r->end, 3, 2) + (int) $r->buffer_minutes);
            $out[] = ['business_id' => (string) $business, 'professional_id' => (string) $professional, 'day' => $day, 'start' => sprintf('%02d:%02d', intdiv($start, 60), $start % 60), 'end' => sprintf('%02d:%02d', intdiv($end, 60), $end % 60), 'status' => $r->status];
        }
        return $out;
    }
}
