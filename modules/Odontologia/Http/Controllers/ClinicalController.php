<?php
namespace Modules\Odontologia\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Odontologia\Services\ClinicalRecords;
class ClinicalController extends \Illuminate\Routing\Controller {
    public function index(Request $r){
        abort_unless(config('odontologia.clinical_records_enabled',false),403);
        $r->validate(['person_id'=>'nullable|integer|min:1']);
        $people=app('db')->connection('tenant')->table('persons')->where('type','customers')->select('id','name')->limit(500)->get();
        $person=(int)$r->query('person_id',0);$records=$person?app(ClinicalRecords::class)->list($person):[];
        return response()->view('odontologia::clinical',compact('people','person','records'))->header('Cache-Control','no-store, private');
    }
    public function store(Request $r){
        $r->validate(['person_id'=>'required|integer|min:1','kind'=>'required|string','note'=>'nullable|string|max:12000','tooth'=>'nullable|integer','surface'=>'nullable|string','id'=>'nullable|integer|min:1','version'=>'nullable|integer|min:1']);
        $content=$r->input('kind')==='odontogram'?['teeth'=>[['tooth'=>$r->input('tooth'),'surface'=>$r->input('surface'),'note'=>$r->input('note')]]]:['note'=>$r->input('note')];
        $id=app(ClinicalRecords::class)->save((int)$r->input('person_id'),$r->input('kind'),$content,$r->input('id')?(int)$r->input('id'):null,$r->input('version')?(int)$r->input('version'):null);
        return redirect('/odontologia/clinical?person_id='.(int)$r->input('person_id'));
    }
}
