<?php
namespace Modules\AiReception\Native;
final class ReminderSchedule {
 public function sync(array $booking):void {
  $db=app('db')->connection('tenant');if($db->transactionLevel()<1)throw new \LogicException('Schedule reminders in booking transaction.');
  $db->table('aireception_reminders')->where('booking_id',$booking['id'])->where('status','simulated_pending')->update(['status'=>'cancelled']);
  if($booking['status']!=='confirmed')return;
  $date=new \DateTimeImmutable($booking['day'].' '.$booking['start'],new \DateTimeZone(config('app.timezone','America/Lima')));
  $policy=app(ReminderPolicy::class)->get();if(!$policy['enabled'])return;
  foreach($policy['offsets'] as $minutes){
   $when=$date->modify('-'.$minutes.' minutes');if($when<=new \DateTimeImmutable('now',$date->getTimezone()))continue;
   $key=['booking_id'=>$booking['id'],'scheduled_at'=>$when->format('Y-m-d H:i:s')];$old=$db->table('aireception_reminders')->where($key)->first();
   if(!$old || $old->processed_at===null)$db->table('aireception_reminders')->updateOrInsert($key,['status'=>'simulated_pending']);
  }
 }
}
