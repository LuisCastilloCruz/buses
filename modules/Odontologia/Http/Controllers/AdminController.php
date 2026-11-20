<?php

namespace Modules\Odontologia\Http\Controllers;

use Illuminate\Http\Request;
use Modules\AiReception\Native\Access;
class AdminController extends \Illuminate\Routing\Controller
{
    public function index()
    {
        $db = app('db')->connection('tenant');
        return view('odontologia::index', ['dentists' => $db->table('odontologia_dentists as d')->join('aireception_professionals as p', 'p.id', '=', 'd.professional_id')->join('odontologia_specialties as s', 's.id', '=', 'd.specialty_id')->select('d.*', 'p.name', 's.name as specialty')->get(), 'specialties' => $db->table('odontologia_specialties')->get(), 'chairs' => $db->table('odontologia_chairs as c')->join('aireception_resources as r', 'r.id', '=', 'c.resource_id')->select('c.*', 'r.name', 'r.active')->get(), 'patients' => app(Access::class)->allows('odontologia', 'patients') ? $db->table('persons')->where('type', 'customers')->get() : collect(), 'canManage' => app(Access::class)->allows('odontologia', 'manage')]);
    }
    public function store(Request $r, $kind)
    {
        $db = app('db')->connection('tenant');
        $business = app(Access::class)->business();
        $r->validate(['name' => 'required|string|max:100']);
        $db->transaction(function () use($db, $r, $kind, $business) {
            if ($kind === 'specialty') {
                $db->table('odontologia_specialties')->updateOrInsert(['name' => $r->input('name')], ['active' => true]);
                return;
            }
            if ($kind === 'chair') {
                $r->validate(['room' => 'required|string|max:100']);
                $id = $db->table('aireception_resources')->insertGetId(['business_id' => $business, 'name' => $r->input('name'), 'active' => true]);
                $db->table('odontologia_chairs')->insert(['resource_id' => $id, 'room' => $r->input('room')]);
                return;
            }
            if ($kind !== 'dentist') {
                abort(422);
            }
            $r->validate(['specialty_id' => 'required|integer', 'chair_id' => 'required|integer', 'registration' => 'nullable|string|max:50']);
            $specialty = $db->table('odontologia_specialties')->where('id', $r->input('specialty_id'))->where('active', true)->first();
            $chair = $db->table('odontologia_chairs')->where('id', $r->input('chair_id'))->first();
            if (!$specialty || !$chair) {
                abort(422);
            }
            $db->table('aireception_resources')->where('id', $chair->resource_id)->lockForUpdate()->first();
            $policy = ['weekdays' => [], 'exceptions' => [], 'min_notice_minutes' => 0, 'slot_minutes' => 15, 'max_days_ahead' => 90];
            foreach (range(1, 5) as $day) {
                $policy['weekdays'][$day] = [['start' => '09:00', 'end' => '13:00'], ['start' => '14:00', 'end' => '18:00']];
            }
            $pro = $db->table('aireception_professionals')->insertGetId(['business_id' => $business, 'name' => $r->input('name'), 'active' => true, 'policy' => json_encode($policy)]);
            $id = $db->table('odontologia_dentists')->insertGetId(['professional_id' => $pro, 'specialty_id' => $specialty->id, 'registration' => $r->input('registration'), 'active' => true]);
            $db->table('aireception_professional_resources')->insert(['professional_id' => $pro, 'resource_id' => $chair->resource_id]);
            $db->table('aireception_professional_links')->insert(['professional_id' => $pro, 'domain' => 'dental', 'source_id' => $id]);
        });
        return redirect('/odontologia');
    }
}
