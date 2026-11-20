<?php
namespace Modules\AiReception\Native;
final class ReminderPolicy {
 public function get():array {$row=app('db')->connection('tenant')->table('aireception_reminder_policy')->where('id',1)->first();return $row?['offsets'=>json_decode($row->offsets,true),'window_start'=>$row->window_start,'window_end'=>$row->window_end,'enabled'=>(bool)$row->enabled]:['offsets'=>[1440,120],'window_start'=>'00:00','window_end'=>'23:59','enabled'=>true];}
 public function save(array $input):void {
  app(Access::class)->require('aireception','configure');$v=app('validator')->make($input,['offsets'=>'required|string|max:100','window_start'=>'required|date_format:H:i','window_end'=>'required|date_format:H:i','enabled'=>'required|boolean'])->validate();
  $offsets=explode(',',$v['offsets']);if(count($offsets)>5 || $v['window_start']>$v['window_end'])abort(422);
  foreach($offsets as &$offset){$offset=trim($offset);if(!ctype_digit($offset) || (int)$offset<1 || (int)$offset>43200)abort(422);$offset=(int)$offset;}unset($offset);$offsets=array_values(array_unique($offsets));rsort($offsets);$v['offsets']=json_encode($offsets);
  $db=app('db')->connection('tenant');$db->transaction(function()use($db,$v){$db->table('companies')->orderBy('id')->lockForUpdate()->first();$old=$db->table('aireception_reminder_policy')->where('id',1)->first();$db->table('aireception_reminder_policy')->updateOrInsert(['id'=>1],$v+['created_at'=>$old?$old->created_at:date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);$db->table('aireception_events')->insert(['business_id'=>app(Access::class)->business(),'actor_id'=>auth('web')->id(),'action'=>'reminder_policy','details'=>json_encode($v),'created_at'=>date('Y-m-d H:i:s')]);},3);
 }
}
