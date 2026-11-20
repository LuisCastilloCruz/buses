<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class SystemController extends \Illuminate\Routing\Controller {
 private function db(){abort_if(app(\Hyn\Tenancy\Environment::class)->tenant(),403);abort_unless(auth('admin')->check(),403);return app('db')->connection('system');}
 public function index(){ $db=$this->db();$plans=$db->table('plans')->get();$clients=$db->table('clients')->select('id','name','plan_id')->get();$controls=$db->table('aireception_plan_controls')->get()->keyBy('plan_id');$subscriptions=$db->table('aireception_subscriptions')->get()->keyBy('client_id');return view('aireception::system',compact('plans','clients','controls','subscriptions')); }
 public function save(Request $r,string $kind){
  $db=$this->db();if($kind==='plan'){$v=$r->validate(['plan_id'=>'required|integer|min:1','allow_reception'=>'required|boolean','allow_dental'=>'required|boolean','addon_price'=>'required|numeric|min:0|max:9999999','monthly_units'=>'required|integer|min:0|max:1000000000','professional_limit'=>'required|integer|min:0|max:10000','message_limit'=>'required|integer|min:0|max:10000000']);$table='aireception_plan_controls';$key='plan_id';abort_unless($db->table('plans')->where('id',$v[$key])->exists(),404);}
  elseif($kind==='subscription'){$v=$r->validate(['client_id'=>'required|integer|min:1','status'=>'required|in:active,suspended,expired','expires_at'=>'nullable|date_format:Y-m-d H:i:s']);$table='aireception_subscriptions';$key='client_id';abort_unless($db->table('clients')->where('id',$v[$key])->exists(),404);}
  else abort(404);
  $db->transaction(function()use($db,$v,$table,$key,$kind){$old=$db->table($table)->where($key,$v[$key])->lockForUpdate()->first();$db->table($table)->updateOrInsert([$key=>$v[$key]],$v+['created_at'=>$old?$old->created_at:date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);$db->table('aireception_system_audit')->insert(['actor_id'=>auth('admin')->id(),'action'=>'configure_'.$kind,'subject_id'=>$v[$key],'details'=>json_encode(['before'=>$old,'after'=>$v]),'created_at'=>date('Y-m-d H:i:s')]);});
  return $r->expectsJson()?response()->json(['success'=>true]):redirect('/aireception-management');
 }
}
