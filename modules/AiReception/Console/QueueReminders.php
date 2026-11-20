<?php
namespace Modules\AiReception\Console;
class QueueReminders extends \Illuminate\Console\Command {
 protected $signature='aireception:reminders {website : Trusted website ID} {--simulate}';
 protected $description='Queue due reminders in explicit simulation mode for one licensed tenant';
 public function handle(){
  if(!$this->option('simulate') || !config('aireception.reminders_simulation_enabled',false))throw new \RuntimeException('Simulation flag required; no external delivery implemented.');
  $environment=app(\Hyn\Tenancy\Environment::class);$previous=$environment->tenant();
  try{
   $website=\Hyn\Tenancy\Models\Website::findOrFail((int)$this->argument('website'));$environment->tenant($website);
   $access=app(\Modules\AiReception\Native\Access::class);$access->business();if(!$access->enabled('aireception'))throw new \RuntimeException('Unlicensed tenant.');
   $rows=app('db')->connection('tenant')->table('aireception_reminders')->where('status','simulated_pending')->where('scheduled_at','<=',date('Y-m-d H:i:s'))->orderBy('id')->limit(100)->get();
   foreach($rows as $r)\Modules\AiReception\Jobs\ProcessReminder::dispatch((int)$website->id,(int)$r->id);
   $this->info('Queued '.count($rows).' simulated reminders. Duplicate jobs are harmless; processor is idempotent.');
  }finally{$environment->tenant($previous);}
 }
}
