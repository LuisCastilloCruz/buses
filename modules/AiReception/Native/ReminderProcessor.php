<?php
namespace Modules\AiReception\Native;
/** Sandbox processor. Simulated outcomes are NEVER described as external delivery. */
final class ReminderProcessor
{
 public function process(int $id):string {
  $access=app(Access::class);$business=$access->business();if(!$access->enabled('aireception'))abort(403);
  if(!config('aireception.reminders_simulation_enabled',false))abort(403,'Procesador simulado desactivado.');
  $db=app('db')->connection('tenant');
  return $db->transaction(function()use($db,$business,$id){
   $candidate=$db->table('aireception_reminders')->where('id',$id)->first();if(!$candidate)abort(404);
   // Same booking-first ordering as controller cancellation.
   $booking=$db->table('aireception_bookings')->where('business_id',$business)->where('id',$candidate->booking_id)->lockForUpdate()->first();if(!$booking)abort(404);
   $r=$db->table('aireception_reminders')->where('id',$id)->lockForUpdate()->first();
   if($r->status!=='simulated_pending')return $r->status;
   if($r->scheduled_at>date('Y-m-d H:i:s'))return 'not_due';
   $consent=$db->table('aireception_contact_consents')->where('person_id',$booking->person_id)->where('channel','whatsapp')->first();
   $status=$booking->status!=='confirmed'?'cancelled':(!$consent || !$consent->allowed || ($consent->expires_at && $consent->expires_at<=date('Y-m-d H:i:s'))?'suppressed_no_consent':'simulated_sent');
   $db->table('aireception_reminders')->where('id',$id)->update(['status'=>$status,'attempts'=>(int)$r->attempts+1,'processed_at'=>date('Y-m-d H:i:s')]);
   $db->table('aireception_reminder_attempts')->insert(['reminder_id'=>$id,'outcome'=>$status,'created_at'=>date('Y-m-d H:i:s')]);
   return $status;
  },3);
 }
}
