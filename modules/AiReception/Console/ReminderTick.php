<?php
namespace Modules\AiReception\Console;
/** Dedicated scheduler entry point; never runs unrelated legacy scheduled tasks. */
class ReminderTick extends \Illuminate\Console\Command {
 protected $signature='aireception:tick {--simulate}';protected $description='Enqueue licensed tenant reminders in explicit local simulation';
 public function handle(){
  if(!$this->option('simulate') || !app()->environment('local','testing') || !config('aireception.reminders_simulation_enabled',false))throw new \RuntimeException('Local simulation required.');
  $env=app(\Hyn\Tenancy\Environment::class);$previous=$env->tenant();$failures=0;
  try{foreach(\Hyn\Tenancy\Models\Website::orderBy('id')->get() as $website){try{$env->tenant($website);if(app(\Modules\AiReception\Native\Access::class)->enabled('aireception'))$this->call('aireception:reminders',['website'=>$website->id,'--simulate'=>true]);}catch(\Throwable $e){$failures++;$this->error('Tenant '.$website->id.' requires review; details withheld.');}}}finally{$env->tenant($previous);}
  return $failures?1:0;
 }
}
