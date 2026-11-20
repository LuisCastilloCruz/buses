<?php

namespace Modules\AiReception\Agenda;

use Modules\AiReception\Contracts\BookingGateway;
use Modules\AiReception\Services\SlotPlanner;
use Modules\AiReception\Services\TattooAvailability;
/** All writes require a trusted actor and explicit business membership; no AI or HTTP clients. */
class BookingService implements BookingGateway
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function authorize(int $actor, int $business, bool $write = false, bool $lock = false)
    {
        $q = $this->db->table('aireception_memberships')->where('business_id', $business)->where('user_id', $actor)->where('active', true);
        if ($lock) {
            $q->lockForUpdate();
        }
        $member = $q->first();
        if (!$member || !in_array($member->role, $write ? ['admin', 'reception'] : ['admin', 'reception', 'viewer'], true)) {
            abort(403, 'No tienes permiso para este negocio.');
        }
        return $member;
    }
    public function catalogue(int $actor, int $business) : array
    {
        $member = $this->authorize($actor, $business);
        return ['business' => $this->db->table('aireception_businesses')->where('id', $business)->first(), 'role' => $member->role, 'professionals' => $this->db->table('aireception_professionals')->where('business_id', $business)->get(), 'services' => $this->db->table('aireception_services')->where('business_id', $business)->get(), 'assignments' => $this->db->table('aireception_professional_services')->where('business_id', $business)->get()];
    }
    public function calendar(int $actor, int $business, string $day, int $days = 1, ?int $professional = null) : array
    {
        $this->authorize($actor, $business);
        $this->validate(['day' => $day], ['day' => 'required|date_format:Y-m-d']);
        if (!in_array($days, [1, 7], true)) {
            abort(422, 'Vista invalida.');
        }
        if ($professional) {
            $this->professional($business, $professional);
        }
        $end = (new \DateTimeImmutable($day))->modify('+' . ($days - 1) . ' days')->format('Y-m-d');
        $q = $this->db->table('aireception_bookings')->where('business_id', $business)->whereBetween('day', [$day, $end]);
        if ($professional) {
            $q->where('professional_id', $professional);
        }
        return $q->orderBy('day')->orderBy('start')->orderBy('id')->get()->all();
    }
    public function events(int $actor, int $business, int $id) : array
    {
        $this->authorize($actor, $business);
        $this->booking($business, $id);
        return $this->db->table('aireception_events')->where('business_id', $business)->where('booking_id', $id)->orderBy('id')->get()->all();
    }
    public function availability(int $actor, int $business, int $professional, int $service, string $day) : array
    {
        $this->authorize($actor, $business);
        return $this->slots($business, $professional, $service, $day);
    }
    public function reschedulingAvailability(int $actor, int $business, int $id, string $day) : array
    {
        $this->authorize($actor, $business, true);
        $b = $this->booking($business, $id);
        if ($b->status === 'cancelled') {
            abort(409, 'La reserva ya esta cancelada.');
        }
        return $this->slots($business, (int) $b->professional_id, (int) $b->service_id, $day, $id, ['duration_minutes' => (int) $b->duration_minutes, 'buffer_minutes' => (int) $b->buffer_minutes]);
    }
    private function professional(int $business, int $id, bool $lock = false)
    {
        $q = $this->db->table('aireception_professionals')->where('business_id', $business)->where('id', $id);
        if ($lock) {
            $q->lockForUpdate();
        }
        $row = $q->first();
        if (!$row) {
            abort(404, 'Profesional no disponible.');
        }
        return $row;
    }
    private function booking(int $business, int $id, bool $lock = false)
    {
        $q = $this->db->table('aireception_bookings')->where('business_id', $business)->where('id', $id);
        if ($lock) {
            $q->lockForUpdate();
        }
        $row = $q->first();
        if (!$row) {
            abort(404, 'Reserva no disponible.');
        }
        return $row;
    }
    private function service(int $business, int $professional, int $id)
    {
        $row = $this->db->table('aireception_services')->where('business_id', $business)->where('id', $id)->where('active', true)->first();
        $link = $this->db->table('aireception_professional_services')->where('business_id', $business)->where('professional_id', $professional)->where('service_id', $id)->exists();
        if (!$row || !$link) {
            abort(422, 'Servicio no habilitado para este profesional.');
        }
        return $row;
    }
    private function clock(int $n) : string
    {
        return sprintf('%02d:%02d', intdiv($n, 60), $n % 60);
    }
    private function minute(string $s) : int
    {
        return (int) substr($s, 0, 2) * 60 + (int) substr($s, 3, 2);
    }
    private function slots(int $business, int $professional, int $service, string $day, ?int $exclude = null, ?array $snapshot = null) : array
    {
        $this->validate(['day' => $day], ['day' => 'required|date_format:Y-m-d']);
        $pro = $this->professional($business, $professional);
        if (!$pro->active) {
            return [];
        }
        $svc = $this->service($business, $professional, $service);
        if ($snapshot) {
            $svc->duration_minutes = $snapshot['duration_minutes'];
            $svc->buffer_minutes = $snapshot['buffer_minutes'];
        }
        $policy = json_decode($pro->policy, true);
        $tenant = $this->db->table('aireception_businesses')->where('id', $business)->first();
        $policy['timezone'] = $tenant->timezone;
        $policy['buffer_minutes'] = 0;
        // A shadow with unknown duration closes availability. No inference from the requested service.
        $legacy = new TattooAvailability(new LegacyCalendarSource($this->db), function ($b, $p) use($business, $professional) {
            return $b === (string) $business && $p === (string) $professional;
        });
        unset($policy['legacy_duration_minutes']);
        if (!$legacy->slots((string) $business, (string) $professional, $day, (int) $svc->duration_minutes, $policy)) {
            return [];
        }
        $margin = (int) $svc->buffer_minutes;
        foreach (['weekdays', 'exceptions'] as $collection) {
            foreach ($policy[$collection] ?? [] as $key => $windows) {
                $adjusted = [];
                foreach ($windows as $w) {
                    $a = $this->minute($w['start']) + $margin;
                    $z = $this->minute($w['end']) - $margin;
                    if ($z > $a) {
                        $adjusted[] = ['start' => $this->clock($a), 'end' => $this->clock($z)];
                    }
                }
                $policy[$collection][$key] = $adjusted;
            }
        }
        $policy['buffer_minutes'] = $margin;
        $q = $this->db->table('aireception_bookings')->where('business_id', $business)->where('professional_id', $professional)->where('day', $day)->where('status', '!=', 'cancelled');
        if ($exclude) {
            $q->where('id', '!=', $exclude);
        }
        $occupied = [];
        foreach ($q->get() as $r) {
            $occupied[] = ['business_id' => (string) $business, 'professional_id' => (string) $professional, 'day' => $day, 'start' => $this->clock(max(0, $this->minute($r->start) - (int) $r->buffer_minutes)), 'end' => $this->clock(min(1439, $this->minute($r->end) + (int) $r->buffer_minutes)), 'status' => $r->status];
        }
        return SlotPlanner::slots((string) $business, (string) $professional, $day, (int) $svc->duration_minutes, $policy, $occupied);
    }
    private function validate(array $data, array $rules) : array
    {
        return app('validator')->make($data, $rules)->validate();
    }
    public function execute(int $actor, int $business, string $action, array $input, string $key) : array
    {
        if (!in_array($action, ['book', 'confirm', 'cancel', 'reschedule'], true)) {
            abort(422, 'Accion invalida.');
        }
        if (!preg_match('/^[A-Za-z0-9_-]{8,80}$/D', $key)) {
            abort(422, 'Identificador de idempotencia invalido.');
        }
        $allowed = $action === 'book' ? ['professional_id', 'service_id', 'day', 'start', 'client_name', 'status'] : ['booking_id', 'version', 'day', 'start'];
        $data = array_intersect_key($input, array_flip($allowed));
        $rules = $action === 'book' ? ['professional_id' => 'required|integer|min:1', 'service_id' => 'required|integer|min:1', 'day' => 'required|date_format:Y-m-d', 'start' => 'required|date_format:H:i', 'client_name' => 'required|string|max:100', 'status' => 'sometimes|in:pending,confirmed'] : ['booking_id' => 'required|integer|min:1', 'version' => 'required|integer|min:1'];
        if ($action === 'reschedule') {
            $rules += ['day' => 'required|date_format:Y-m-d', 'start' => 'required|date_format:H:i'];
        }
        $data = $this->validate($data, $rules);
        ksort($data);
        $hash = hash('sha256', json_encode([$action, $data]));
        return $this->db->transaction(function () use($actor, $business, $action, $data, $key, $hash) {
            $this->authorize($actor, $business, true, true);
            // Locking reads before the professional lock avoid a stale REPEATABLE READ snapshot.
            $prior = $this->db->table('aireception_commands')->where('business_id', $business)->where('actor_id', $actor)->where('request_key', $key)->lockForUpdate()->first();
            if ($prior) {
                if (!hash_equals($prior->request_hash, $hash)) {
                    abort(409, 'La clave ya fue usada con otros datos.');
                }
                return ['booking' => json_decode($prior->result, true), 'replayed' => true];
            }
            $old = null;
            if ($action !== 'book') {
                $old = $this->booking($business, (int) $data['booking_id'], true);
                if ((int) $old->version !== (int) $data['version']) {
                    abort(409, 'La reserva cambio. Actualiza el calendario.');
                }
            }
            $proId = $old ? (int) $old->professional_id : (int) $data['professional_id'];
            $this->professional($business, $proId, true);
            // Single serialization point for every write to this calendar.
            if ($old && $old->status === 'cancelled') {
                abort(409, 'La reserva ya esta cancelada.');
            }
            if ($action === 'confirm' && $old->status !== 'pending') {
                abort(409, 'Solo se confirman reservas pendientes.');
            }
            $now = date('Y-m-d H:i:s');
            $values = [];
            if ($action !== 'cancel') {
                $serviceId = $old ? (int) $old->service_id : (int) $data['service_id'];
                $day = $action === 'confirm' ? $old->day : $data['day'];
                $start = $action === 'confirm' ? $old->start : $data['start'];
                $free = $this->slots($business, $proId, $serviceId, $day, $old ? (int) $old->id : null, $old ? ['duration_minutes' => (int) $old->duration_minutes, 'buffer_minutes' => (int) $old->buffer_minutes] : null);
                $chosen = null;
                foreach ($free as $slot) {
                    if ($slot['start'] === $start) {
                        $chosen = $slot;
                    }
                }
                if (!$chosen) {
                    abort(409, 'Horario ocupado, cerrado o bloqueado. Selecciona otra opcion.');
                }
                $svc = $this->service($business, $proId, $serviceId);
                $values = ['day' => $day, 'start' => $chosen['start'], 'end' => $chosen['end'], 'duration_minutes' => $old ? (int) $old->duration_minutes : (int) $svc->duration_minutes, 'buffer_minutes' => $old ? (int) $old->buffer_minutes : (int) $svc->buffer_minutes];
            }
            if ($action === 'book') {
                $values += ['business_id' => $business, 'professional_id' => $proId, 'service_id' => (int) $data['service_id'], 'client_name' => $data['client_name'], 'status' => $data['status'] ?? 'confirmed', 'version' => 1, 'created_at' => $now, 'updated_at' => $now];
                $id = $this->db->table('aireception_bookings')->insertGetId($values);
            } else {
                $id = (int) $old->id;
                $values += ['status' => $action === 'cancel' ? 'cancelled' : ($action === 'confirm' ? 'confirmed' : $old->status), 'version' => (int) $old->version + 1, 'updated_at' => $now];
                $this->db->table('aireception_bookings')->where('business_id', $business)->where('id', $id)->update($values);
            }
            $record = (array) $this->booking($business, $id);
            $this->db->table('aireception_events')->insert(['business_id' => $business, 'actor_id' => $actor, 'booking_id' => $id, 'action' => $action, 'details' => json_encode(['before' => $old, 'after' => $record]), 'created_at' => $now]);
            $this->db->table('aireception_commands')->insert(['business_id' => $business, 'actor_id' => $actor, 'request_key' => $key, 'request_hash' => $hash, 'result' => json_encode($record), 'created_at' => $now]);
            return ['booking' => $record, 'replayed' => false];
        }, 3);
    }
    public function configure(int $actor, int $business, string $kind, array $data) : array
    {
        if (!in_array($kind, ['professional', 'service'], true)) {
            abort(422);
        }
        $rules = ['id' => 'nullable|integer|min:1', 'name' => 'required|string|max:100', 'active' => 'required|boolean'];
        if ($kind === 'service') {
            $rules += ['duration_minutes' => 'required|integer|min:5|max:720', 'buffer_minutes' => 'required|integer|min:0|max:120'];
        } else {
            $rules += ['policy' => 'required|array', 'service_ids' => 'required|array', 'service_ids.*' => 'integer|min:1'];
        }
        $v = $this->validate($data, $rules);
        if ($kind === 'professional') {
            $p = $v['policy'];
            $p['timezone'] = 'America/Lima';
            unset($p['legacy_duration_minutes']);
            foreach (range(0, 6) as $i) {
                SlotPlanner::slots('1', '1', date('Y-m-d', strtotime('+' . $i . ' days')), 30, $p, []);
            }
            foreach (array_keys($p['exceptions'] ?? []) as $day) {
                SlotPlanner::slots('1', '1', $day, 30, $p, [], new \DateTimeImmutable($day . ' 00:00:00'));
            }
            $v['policy'] = json_encode($p);
        }
        return $this->db->transaction(function () use($actor, $business, $kind, $v) {
            $m = $this->authorize($actor, $business, true, true);
            if ($m->role !== 'admin') {
                abort(403, 'Solo administradores.');
            }
            // Same professional locks as booking writes, including service/assignment changes.
            $this->db->table('aireception_professionals')->where('business_id', $business)->orderBy('id')->lockForUpdate()->get();
            $table = $kind === 'service' ? 'aireception_services' : 'aireception_professionals';
            $id = (int) ($v['id'] ?? 0);
            $values = $v;
            unset($values['id'], $values['service_ids']);
            if ($id) {
                if (!$this->db->table($table)->where('business_id', $business)->where('id', $id)->exists()) {
                    abort(404);
                }
                $this->db->table($table)->where('business_id', $business)->where('id', $id)->update($values);
            } else {
                $id = $this->db->table($table)->insertGetId($values + ['business_id' => $business]);
            }
            if ($kind === 'professional') {
                foreach ($v['service_ids'] as $service) {
                    if (!$this->db->table('aireception_services')->where('business_id', $business)->where('id', $service)->exists()) {
                        abort(422, 'Servicio de otro negocio.');
                    }
                }
                $this->db->table('aireception_professional_services')->where('business_id', $business)->where('professional_id', $id)->delete();
                foreach (array_unique($v['service_ids']) as $service) {
                    $this->db->table('aireception_professional_services')->insert(['business_id' => $business, 'professional_id' => $id, 'service_id' => $service]);
                }
            }
            $this->db->table('aireception_events')->insert(['business_id' => $business, 'actor_id' => $actor, 'booking_id' => null, 'action' => 'configure_' . $kind, 'details' => json_encode(['id' => $id, 'values' => $values]), 'created_at' => date('Y-m-d H:i:s')]);
            return ['id' => $id];
        }, 3);
    }
    public function book(array $command) : void
    {
        $this->execute($command['actor_id'], $command['business_id'], 'book', $command, $command['request_key']);
    }
    public function cancel(array $command) : void
    {
        $this->execute($command['actor_id'], $command['business_id'], 'cancel', $command, $command['request_key']);
    }
    public function reschedule(array $command) : void
    {
        $this->execute($command['actor_id'], $command['business_id'], 'reschedule', $command, $command['request_key']);
    }
}
