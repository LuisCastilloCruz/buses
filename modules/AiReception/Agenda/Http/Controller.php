<?php

namespace Modules\AiReception\Agenda\Http;

use Illuminate\Http\Request;
use Modules\AiReception\Agenda\BookingService;
class Controller extends \Illuminate\Routing\Controller
{
    private $engine;
    public function __construct(BookingService $engine)
    {
        $this->engine = $engine;
    }
    private function actor() : int
    {
        if (!auth()->check()) {
            abort(401, 'Inicia sesion.');
        }
        return (int) auth()->id();
    }
    private function business(Request $r) : int
    {
        $actor = $this->actor();
        $id = (int) $r->session()->get('agenda_business');
        $this->engine->authorize($actor, $id);
        return $id;
    }
    public function loginForm()
    {
        return view('login');
    }
    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (!auth()->attempt($data)) {
            abort(422, 'Credenciales incorrectas.');
        }
        $r->session()->regenerate();
        $member = app('db')->table('aireception_memberships')->where('user_id', auth()->id())->where('active', true)->orderBy('business_id')->first();
        if (!$member) {
            auth()->logout();
            abort(403);
        }
        $r->session()->put('agenda_business', (int) $member->business_id);
        return redirect('/calendar');
    }
    public function logout(Request $r)
    {
        auth()->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/login');
    }
    public function page(Request $r)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        $this->business($r);
        return view('app', ['page' => trim($r->path(), '/') ?: 'calendar']);
    }
    public function bootstrap(Request $r)
    {
        $actor = $this->actor();
        $business = $this->business($r);
        return response()->json($this->engine->catalogue($actor, $business) + ['user' => ['name' => auth()->user()->name, 'id' => $actor], 'businesses' => app('db')->table('aireception_memberships as m')->join('aireception_businesses as b', 'b.id', '=', 'm.business_id')->where('m.user_id', $actor)->where('m.active', true)->select('b.id', 'b.name')->get(), 'today' => date('Y-m-d')]);
    }
    public function switchBusiness(Request $r)
    {
        $actor = $this->actor();
        $r->validate(['business_id' => 'required|integer|min:1']);
        $id = (int) $r->input('business_id');
        $this->engine->authorize($actor, $id);
        $r->session()->put('agenda_business', $id);
        $r->session()->regenerate();
        return response()->json(['business_id' => $id]);
    }
    public function calendar(Request $r)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d', 'days' => 'sometimes|in:1,7', 'professional_id' => 'nullable|integer|min:1']);
        return response()->json($this->engine->calendar($this->actor(), $this->business($r), $r->query('day'), (int) $r->query('days', 1), $r->query('professional_id') ? (int) $r->query('professional_id') : null));
    }
    public function availability(Request $r)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d', 'professional_id' => 'required|integer|min:1', 'service_id' => 'required|integer|min:1']);
        return response()->json($this->engine->availability($this->actor(), $this->business($r), (int) $r->query('professional_id'), (int) $r->query('service_id'), $r->query('day')));
    }
    public function rescheduling(Request $r, $id)
    {
        $r->validate(['day' => 'required|date_format:Y-m-d']);
        return response()->json($this->engine->reschedulingAvailability($this->actor(), $this->business($r), (int) $id, $r->query('day')));
    }
    public function events(Request $r, $id)
    {
        return response()->json($this->engine->events($this->actor(), $this->business($r), (int) $id));
    }
    public function command(Request $r, $action)
    {
        return response()->json($this->engine->execute($this->actor(), $this->business($r), $action, $r->all(), (string) $r->header('Idempotency-Key')));
    }
    public function configure(Request $r, $kind)
    {
        return response()->json($this->engine->configure($this->actor(), $this->business($r), $kind, $r->all()));
    }
    public function agent(Request $r)
    {
        $catalog = $this->engine->catalogue($this->actor(), $this->business($r));
        $r->validate(['text' => 'required|string|max:1000']);
        $text = mb_strtolower($r->input('text'));
        $reply = 'Soy el asistente de demostracion de ' . $catalog['business']->name . '. Elige un profesional, servicio y fecha para ver horarios reales del laboratorio.';
        if (strpos($text, 'cancel') !== false || strpos($text, 'reprogram') !== false) {
            $reply = 'Puedes cancelar o reprogramar desde Calendario. Cada cambio requiere tus permisos y queda registrado.';
        }
        return response()->json(['reply' => $reply, 'simulated' => true, 'provider_calls' => 0]);
    }
}
