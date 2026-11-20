<?php
namespace Modules\AiReception\Native;
use Illuminate\Http\Request;
class ProposalController extends \Illuminate\Routing\Controller {
    public function prepare(Request $r){return response()->json(app(BookingProposals::class)->prepare($r->all()));}
    public function confirm(Request $r){$r->validate(['confirmation_token'=>'required|string|size:64','confirmed'=>'required|boolean']);return response()->json(app(BookingProposals::class)->confirm($r->input('confirmation_token'),$r->input('confirmed')===true));}
}
