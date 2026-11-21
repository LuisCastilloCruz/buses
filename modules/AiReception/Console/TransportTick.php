<?php
namespace Modules\AiReception\Console;
class TransportTick extends \Illuminate\Console\Command {
 protected $signature='aireception:transport {website} {--simulate} {--sandbox}';protected $description='Process bounded AiReception outbox and reminders for one authorized tenant';
 public function handle(){
  if(!app()->environment('local','testing'))throw new \RuntimeException('Local sandbox only.');$mode=config('aireception.v5_transport_mode','simulated');if($mode==='simulated'&&!$this->option('simulate'))throw new \RuntimeException('Explicit simulation required.');if($mode!=='simulated'&&($mode!=='sandbox'||!$this->option('sandbox')||!config('aireception.gateway_authorized',false)||!config('aireception.external_connections_enabled',false)))throw new \RuntimeException('Authorized sandbox required.');
  $env=app(\Hyn\Tenancy\Environment::class);$previous=$env->tenant();
  try{$env->tenant(\Hyn\Tenancy\Models\Website::findOrFail((int)$this->argument('website')));if(!app(\Modules\AiReception\Native\Access::class)->enabled('aireception'))throw new \RuntimeException('Unlicensed tenant.');$db=app('db')->connection('tenant');
   foreach($db->table('aireception_reminders')->whereIn('status',['pending','simulated_pending'])->where('scheduled_at','<=',date('Y-m-d H:i:s'))->limit(100)->pluck('id') as $id)app(\Modules\AiReception\Native\ReminderOutbox::class)->enqueue((int)$id);
   foreach($db->table('aireception_outbox')->whereIn('state',['pending','retry','sending','accepted','sent'])->limit(100)->pluck('id') as $id){app(\Modules\AiReception\Native\Outbox::class)->process((int)$id);app(\Modules\AiReception\Native\Outbox::class)->reconcile((int)$id);}
  }finally{$env->tenant($previous);}return 0;
 }
}