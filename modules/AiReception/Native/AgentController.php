<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class AgentController extends \Illuminate\Routing\Controller {
 public function index(){
  $settings=app('db')->connection('tenant')->table('aireception_agent_settings')->where('id',1)->select('enabled','mode','model','max_output_tokens')->first();$profile=app(AgentSettings::class)->publicProfile();return view('aireception::agent-settings',compact('settings','profile'));
 }
 public function save(Request $r){app(AgentSettings::class)->save($r->all());return redirect('/aireception/agent-settings');}
 public function overview(){
  $db=app('db')->connection('tenant');$business=app(Access::class)->business();$period=date('Y-m');
  $counts=$db->table('aireception_bookings')->where('business_id',$business)->where('day','>=',$period.'-01')->where('day','<',date('Y-m-01',strtotime('first day of next month')))->selectRaw('status,COUNT(*) total')->groupBy('status')->get();
  $budget=$db->table('aireception_budgets')->where('business_id',$business)->where('period',$period)->first();
  $usage=$db->table('aireception_usage as u')->join('aireception_budgets as b','b.id','=','u.budget_id')->where('b.business_id',$business)->where('b.period',$period)->select('u.kind','u.state','u.model','u.input_tokens','u.output_tokens','u.used_units','u.created_at')->orderByDesc('u.id')->limit(100)->get();
  $audit=$db->table('aireception_events')->where('business_id',$business)->select('action','actor_id','created_at')->orderByDesc('id')->limit(30)->get();
  $localUsage=$db->table('aireception_local_usage')->where('business_id',$business)->where('period',$period)->selectRaw('COUNT(*) calls, SUM(input_tokens) input_tokens, SUM(output_tokens) output_tokens')->first();return view('aireception::overview',compact('counts','budget','usage','audit','period','localUsage'));
 }
}
