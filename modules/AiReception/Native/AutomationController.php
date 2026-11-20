<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class AutomationController extends \Illuminate\Routing\Controller {
 public function index(){
  $db=app('db')->connection('tenant');$business=app(Access::class)->business();
  $reminders=$db->table('aireception_reminders as r')->join('aireception_bookings as b','b.id','=','r.booking_id')->where('b.business_id',$business)->select('r.*','b.client_name')->orderByDesc('r.id')->limit(100)->get();
  $people=$db->table('persons')->where('type','customers')->select('id','name')->limit(500)->get();
  $policy=app(ReminderPolicy::class)->get();return view('aireception::automations',compact('reminders','people','policy'));
 }
 public function policy(Request $r){app(ReminderPolicy::class)->save($r->all());return redirect('/aireception/automations');}
 public function consent(Request $r){
  $r->validate(['person_id'=>'required|integer|min:1','allowed'=>'required|boolean','evidence'=>'required|string|max:255']);$db=app('db')->connection('tenant');
  abort_unless($db->table('persons')->where('id',$r->input('person_id'))->where('type','customers')->exists(),404);
  $db->transaction(function()use($db,$r){
   $db->table('companies')->orderBy('id')->lockForUpdate()->first();$old=$db->table('aireception_contact_consents')->where('person_id',$r->input('person_id'))->where('channel','whatsapp')->first();
   $db->table('aireception_contact_consents')->updateOrInsert(['person_id'=>$r->input('person_id'),'channel'=>'whatsapp'],['allowed'=>(bool)$r->input('allowed'),'evidence'=>$r->input('evidence'),'actor_id'=>auth('web')->id(),'created_at'=>$old?$old->created_at:date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
   $db->table('aireception_events')->insert(['business_id'=>app(Access::class)->business(),'actor_id'=>auth('web')->id(),'action'=>'contact_consent','details'=>json_encode(['person_id'=>(int)$r->input('person_id'),'allowed'=>(bool)$r->input('allowed'),'evidence'=>$r->input('evidence'),'previous_allowed'=>$old?(bool)$old->allowed:null]),'created_at'=>date('Y-m-d H:i:s')]);
  },3);
  return redirect('/aireception/automations');
 }
 public function process($id){app(ReminderProcessor::class)->process((int)$id);return redirect('/aireception/automations');}
}
