<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
final class KnowledgeController extends \Illuminate\Routing\Controller {
 public function index(){app(Access::class)->require('aireception','configure');$documents=app('db')->connection('tenant')->table('aireception_knowledge_documents')->where('business_id',app(Access::class)->business())->where('active',true)->select('source','title','version','updated_at')->get();return view('aireception::knowledge',compact('documents'));}
 public function save(Request $r){app(KnowledgeBase::class)->save($r->all());return redirect('/aireception/knowledge');}
 public function remove(Request $r){app(KnowledgeBase::class)->remove((string)$r->input('source'));return redirect('/aireception/knowledge');}
}
