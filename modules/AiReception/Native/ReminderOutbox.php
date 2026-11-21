<?php
namespace Modules\AiReception\Native;
final class ReminderOutbox {
 public function enqueue(int $id):string {
  $business=app(Access::class)->business();if(!app(Access::class)->enabled('aireception'))abort(403);$db=app('db')->connection('tenant');
  return $db->transaction(function()use($db,$business,$id){
   $r=$db->table('aireception_reminders')->where('id',$id)->first();if(!$r)abort(404);
   $b=$db->table('aireception_bookings')->where('business_id',$business)->where('id',$r->booking_id)->lockForUpdate()->first();if(!$b)abort(404);
   $r=$db->table('aireception_reminders')->where('id',$id)->lockForUpdate()->first();if(!in_array($r->status,['simulated_pending','pending'],true))return $r->status;
   if($b->status!=='confirmed'){$db->table('aireception_reminders')->where('id',$id)->update(['status'=>'cancelled']);return 'cancelled';}
   $policy=app(ReminderPolicy::class)->get();if(!$policy['enabled'])return 'paused';$clock=date('H:i');if($clock<$policy['window_start']||$clock>$policy['window_end'])return 'outside_window';if($r->scheduled_at>date('Y-m-d H:i:s'))return 'not_due';
   $consent=$db->table('aireception_contact_consents')->where('person_id',$b->person_id)->where('channel','whatsapp')->first();if(!$consent||!$consent->allowed||($consent->expires_at&&$consent->expires_at<=date('Y-m-d H:i:s')))return 'suppressed_no_consent';
   $c=$db->table('aireception_conversations')->where('person_id',$b->person_id)->whereNotNull('channel_id')->orderByDesc('id')->first();if(!$c)return 'no_verified_channel';
   $template=config('aireception.reminder_templates.'.$business.'.'.$c->channel_id);if(!is_array($template))return 'template_unconfigured';
   $out=app(Outbox::class)->enqueue((int)$c->id,['type'=>'template','name'=>$template['name'],'language'=>$template['language']],'reminder-'.$id.'-version-'.$b->version,['id'=>(int)$b->id,'version'=>(int)$b->version,'reminder_id'=>$id]);
   $db->table('aireception_reminders')->where('id',$id)->update(['status'=>'outbox_queued']);$db->table('aireception_reminder_attempts')->insert(['reminder_id'=>$id,'outcome'=>'outbox_queued','created_at'=>date('Y-m-d H:i:s')]);return 'outbox_queued';
  },3);
 }
}