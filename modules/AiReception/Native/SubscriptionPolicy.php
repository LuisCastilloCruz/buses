<?php
namespace Modules\AiReception\Native;
final class SubscriptionPolicy {
 public function allows($client,string $module):bool {
  $db=app('db')->connection('system');
  // Absence means preserve existing explicit module entitlements; never activate a module here.
  if(!$db->getSchemaBuilder()->hasTable('aireception_plan_controls'))return true;
  $plan=$db->table('aireception_plan_controls')->where('plan_id',$client->plan_id)->first();
  if($plan && !(bool)($module==='odontologia'?$plan->allow_dental:$plan->allow_reception))return false;
  $subscription=$db->table('aireception_subscriptions')->where('client_id',$client->id)->first();
  if(!$subscription)return true;
  return $subscription->status==='active' && (!$subscription->expires_at || $subscription->expires_at>date('Y-m-d H:i:s'));
 }
}
