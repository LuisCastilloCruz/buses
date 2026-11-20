<?php
namespace Modules\AiReception\Native;
final class ProfessionalQuota {
 public function requireCapacity():void {
  $business=app(Access::class)->business();$db=app('db')->connection('tenant');if($db->transactionLevel()<1)throw new \LogicException('Quota check requires transaction.');
  // Stable tenant row serializes both universal and dental professional creation, including an empty calendar.
  $db->table('companies')->orderBy('id')->lockForUpdate()->first();
  $system=app('db')->connection('system');$plan=$system->table('clients as c')->join('hostnames as h','h.id','=','c.hostname_id')->join('aireception_plan_controls as p','p.plan_id','=','c.plan_id')->where('h.website_id',$business)->select('p.professional_limit')->first();
  if($plan && $db->table('aireception_professionals')->where('business_id',$business)->count()>=(int)$plan->professional_limit)abort(409,'Limite de profesionales del plan alcanzado.');
 }
}
