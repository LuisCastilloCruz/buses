<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class AutomationController extends \Illuminate\Routing\Controller {
 public function index(){
  $db=app('db')->connection('tenant');$business=app(Access::class)->business();
  $reminders=$db->table('aireception_reminders as r')->join('aireception_bookings as b','b.id','=','r.booking_id')->where('b.business_id',$business)->select('r.*','b.client_name')->orderByDesc('r.id')->limit(100)->get();
  $people=$db->table('persons')->where('type','customers')->select('id','name')->limit(500)->get();
  return view('aireception::automations',compact('reminders','people'));
 }
 public function consent(Request $r){
  $r->validate(['person_id'=>'required|integer|min:1','allowed'=>'required|boolean','evidence'=>'required|string|max:255']);$db=app('db')->connection('tenant');
  abort_unless($db->table('persons')->where('id',$r->input('person_id'))->where('type','customers')->exists(),404);
  $db->table('aireception_contact_consents')->updateOrInsert(['person_id'=>$r->input('person_id'),'channel'=>'whatsapp'],['allowed'=>(bool)$r->input('allowed'),'evidence'=>$r->input('evidence'),'actor_id'=>auth('web')->id(),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);
  return redirect('/aireception/automations');
 }
 public function process($id){app(ReminderProcessor::class)->process((int)$id);return redirect('/aireception/automations');}
}
