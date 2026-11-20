<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class InboxController extends \Illuminate\Routing\Controller {
 public function index(){
  $db=app('db')->connection('tenant');$rows=$db->table('aireception_inbound_messages')->orderByDesc('id')->limit(100)->get();
  foreach($rows as $row){$content=app('encrypter')->decrypt($row->encrypted_content);$row->text=$content['text'];$row->sender=$content['sender_identity'];}
  $people=$db->table('persons')->where('type','customers')->select('id','name')->limit(500)->get();
  return response()->view('aireception::inbox',compact('rows','people'))->header('Cache-Control','no-store, private');
 }
 public function link(Request $r,$id){$r->validate(['person_id'=>'required|integer|min:1','evidence'=>'required|string|min:10|max:255']);app(ContactLinker::class)->link((int)$id,(int)$r->input('person_id'),$r->input('evidence'));return redirect('/aireception/inbox');}
 public function channels(){ $business=app(Access::class)->business();$channels=app('db')->connection('system')->table('aireception_channels')->where('website_id',$business)->select('id','label','phone_id','enabled')->get();return view('aireception::channels',compact('channels')); }
 public function saveChannel(Request $r){app(ChannelSettings::class)->save($r->all());return redirect('/aireception/channels');}
}
