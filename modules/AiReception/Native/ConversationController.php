<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class ConversationController extends \Illuminate\Routing\Controller {
 public function action(Request $r,$id,$action){
  app(Access::class)->require('aireception','conversations');
  if(!in_array($action,['resume','assign','reply'],true))abort(404);$db=app('db')->connection('tenant');$business=app(Access::class)->business();$actor=(int)auth('web')->id();
  $db->transaction(function()use($db,$r,$id,$action,$business,$actor){
   $c=$db->table('aireception_conversations')->where('id',(int)$id)->lockForUpdate()->first();if(!$c)abort(404);
   if($action==='reply'){$r->validate(['text'=>'required|string|max:2000']);if($c->channel_id)$r->validate(['request_key'=>'required|regex:/^[a-f0-9]{32}$/']);$outboxId=null;if($c->channel_id)$outboxId=app(Outbox::class)->enqueue((int)$c->id,['type'=>'text','body'=>$r->input('text')],'manual-'.$r->input('request_key'));if($outboxId&&$db->table('aireception_messages')->where('channel_id',$c->channel_id)->where('external_id','outbox-'.$outboxId)->exists())return;$db->table('aireception_messages')->insert(['conversation_id'=>$c->id,'channel_id'=>$c->channel_id,'external_id'=>$outboxId?'outbox-'.$outboxId:null,'direction'=>$c->channel_id?'outgoing_queued':'outgoing_simulation','body'=>app('encrypter')->encrypt($r->input('text')),'is_encrypted'=>true,'created_at'=>date('Y-m-d H:i:s')]);}
   elseif($action==='resume'){$db->table('aireception_conversations')->where('id',$c->id)->update(['status'=>'automated','updated_at'=>date('Y-m-d H:i:s')]);}
   else{$r->validate(['user_id'=>'required|integer|min:1']);$target=\App\Models\Tenant\User::find($r->input('user_id'));$allowed=$target && !$target->aireception_suspended && $target->modules()->where('value','aireception')->exists() && $db->table('module_level_user as p')->join('module_levels as l','l.id','=','p.module_level_id')->where('p.user_id',$r->input('user_id'))->where('l.value','aireception_conversations')->exists();if(!$allowed)abort(422,'Responsable sin permiso de conversaciones.');$db->table('aireception_conversations')->where('id',$c->id)->update(['assigned_user_id'=>$r->input('user_id'),'status'=>'human','updated_at'=>date('Y-m-d H:i:s')]);}
   $db->table('aireception_events')->insert(['business_id'=>$business,'actor_id'=>$actor,'booking_id'=>null,'action'=>'conversation_'.$action,'details'=>json_encode(['conversation_id'=>$c->id]),'created_at'=>date('Y-m-d H:i:s')]);
  },3);
  return redirect('/aireception/conversations?selected='.(int)$id);
 }
}
