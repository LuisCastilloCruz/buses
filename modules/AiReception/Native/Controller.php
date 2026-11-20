<?php

namespace Modules\AiReception\Native;

use Illuminate\Http\Request;
class Controller extends \Illuminate\Routing\Controller
{
    private function module(Request $r) : string
    {
        return $r->segment(1);
    }
    private function engine(Request $r)
    {
        return app(EngineFactory::class)->make($this->module($r));
    }
    private function db()
    {
        return app('db')->connection('tenant');
    }
    private function actor() : int
    {
        return (int) auth('web')->id();
    }
    private function business() : int
    {
        return app(Access::class)->business();
    }
    public function page(Request $r)
    {
        $page = in_array($r->segment(2), ['professionals', 'services', 'settings'], true) ? 'settings' : ($r->segment(2) === 'agent' ? 'agent' : 'calendar');
        return view('aireception::agenda', compact('page') + ['module' => $this->module($r)]);
    }
    public function bootstrap(Request $r)
    {
        $data = $this->engine($r)->catalogue($this->actor(), $this->business());
        $data['user'] = ['id' => $this->actor(), 'name' => auth()->user()->name];
        $data['today'] = date('Y-m-d');
        if ($this->module($r) === 'aireception') $data['capabilities'] = app(DomainRegistry::class)->capabilities();
        $data['persons'] = app(Access::class)->allows($this->module($r), 'book') ? $this->db()->table('persons')->where('type', 'customers')->select('id', 'name')->limit(1000)->get() : collect();
        $data['items'] = $this->db()->table('items')->where('item_type_id', '02')->select('id', 'description as name')->limit(1000)->get();
        return response()->json($data);
    }
    public function calendar(Request $r)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d', 'days' => 'sometimes|in:1,7,28,29,30,31', 'professional_id' => 'nullable|integer|min:1']);
        return response()->json($this->engine($r)->calendar($this->actor(), $this->business(), $r->query('day'), (int) $r->query('days', 1), $r->query('professional_id') ? (int) $r->query('professional_id') : null));
    }
    public function availability(Request $r)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d', 'professional_id' => 'required|integer|min:1', 'service_id' => 'required|integer|min:1']);
        return response()->json($this->engine($r)->availability($this->actor(), $this->business(), (int) $r->query('professional_id'), (int) $r->query('service_id'), $r->query('day')));
    }
    public function rescheduling(Request $r, $id)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d']);
        return response()->json($this->engine($r)->reschedulingAvailability($this->actor(), $this->business(), (int) $id, $r->query('day')));
    }
    public function events(Request $r, $id)
    {
        return response()->json($this->engine($r)->events($this->actor(), $this->business(), (int) $id));
    }
    public function command(Request $r, $action)
    {
        $data = $r->all();
        if ($action === 'book') {
            $r->validate(['person_id' => 'required|integer|min:1']);
            $p = $this->db()->table('persons')->where('id', $r->input('person_id'))->where('type', 'customers')->first();
            if (!$p) {
                abort(404);
            }
            $data['client_name'] = $p->name;
        }
        $engine = $this->engine($r);
        return $this->db()->transaction(function () use($engine, $r, $action, $data) {
            $result = $engine->execute($this->actor(), $this->business(), $action, $data, (string) $r->header('Idempotency-Key'));
            if (!$result['replayed']) {
                $booking = $result['booking'];
                app(ReminderSchedule::class)->sync($booking);
            }
            return response()->json($result);
        });
    }
    public function configure(Request $r, $kind)
    {
        $data = $r->all();
        $item = null;
        if ($kind === 'service') {
            $r->validate(['item_id' => 'required|integer|min:1']);
            $item = $this->db()->table('items')->where('id', $r->input('item_id'))->where('item_type_id', '02')->first();
            if (!$item) {
                abort(422, 'Selecciona un servicio del catalogo existente.');
            }
            $data['name'] = $item->description;
        }
        $engine = $this->engine($r);
        return $this->db()->transaction(function () use($r, $kind, $data, $item, $engine) {
            if ($kind === 'professional' && empty($data['id'])) app(ProfessionalQuota::class)->requireCapacity();
            $result = $engine->configure($this->actor(), $this->business(), $kind, $data);
            if ($item) {
                $this->db()->table('aireception_services')->where('business_id', $this->business())->where('id', $result['id'])->update(['item_id' => $item->id]);
            }
            return response()->json($result);
        });
    }
    public function agent(Request $r)
    {
        $r->validate(['text' => 'required|string|max:2000']);
        return response()->json(['reply' => 'Simulacion local: consulta los servicios y elige un horario disponible. La reserva requiere tu confirmacion.']);
    }
    public function conversations()
    {
        return view('aireception::conversations', ['conversations' => $this->db()->table('aireception_conversations as c')->join('persons as p', 'p.id', '=', 'c.person_id')->select('c.*', 'p.name')->get(), 'persons' => $this->db()->table('persons')->where('type', 'customers')->get()]);
    }
    public function message(Request $r)
    {
        $r->validate(['person_id' => 'required|integer', 'text' => 'required|string|max:2000', 'conversation_id' => 'nullable|integer']);
        $db = $this->db();
        $person = $db->table('persons')->where('id', $r->input('person_id'))->where('type', 'customers')->first();
        if (!$person) {
            abort(404);
        }
        $id = $db->transaction(function () use($db, $r, $person) {
            $id = (int) $r->input('conversation_id');
            if ($id) {
                $c = $db->table('aireception_conversations')->where('id', $id)->where('person_id', $person->id)->lockForUpdate()->first();
                if (!$c) {
                    abort(404);
                }
            } else {
                $id = $db->table('aireception_conversations')->insertGetId(['person_id' => $person->id, 'status' => 'automated', 'created_at' => now(), 'updated_at' => now()]);
                $c = (object) ['status' => 'automated'];
            }
            $db->table('aireception_messages')->insert(['conversation_id' => $id, 'direction' => 'incoming', 'body' => $r->input('text'), 'created_at' => now()]);
            if ($c->status === 'automated') {
                $db->table('aireception_messages')->insert(['conversation_id' => $id, 'direction' => 'simulation', 'body' => 'Solicitud recibida en simulacion. Consulta el calendario para elegir y confirmar un horario.', 'created_at' => now()]);
            }
            return $id;
        });
        return redirect('/aireception/conversations?selected=' . $id);
    }
    public function handoff(Request $r, $id)
    {
        $db = $this->db();
        $db->transaction(function () use($db, $id) {
            $c = $db->table('aireception_conversations')->where('id', (int) $id)->lockForUpdate()->first();
            if (!$c) {
                abort(404);
            }
            $db->table('aireception_conversations')->where('id', $c->id)->update(['status' => 'human', 'assigned_user_id' => $this->actor(), 'updated_at' => now()]);
            $db->table('aireception_messages')->insert(['conversation_id' => $c->id, 'direction' => 'system', 'body' => 'Derivada a recepcion humana. Respuestas automaticas detenidas.', 'created_at' => now()]);
        });
        return redirect('/aireception/conversations?selected=' . (int) $id);
    }
}
