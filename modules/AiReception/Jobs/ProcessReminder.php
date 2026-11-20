<?php
namespace Modules\AiReception\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
class ProcessReminder implements ShouldQueue {
 use Queueable,Dispatchable,InteractsWithQueue;
 public $tries=3;public $timeout=30;public $websiteId;public $reminderId;
 public function __construct(int $websiteId,int $reminderId){$this->websiteId=$websiteId;$this->reminderId=$reminderId;$this->onConnection('aireception');$this->onQueue('aireception');}
 public function handle(){
  if(!config('aireception.reminders_simulation_enabled',false))throw new \RuntimeException('Reminder simulation disabled.');
  $environment=app(\Hyn\Tenancy\Environment::class);$previous=$environment->tenant();
  try{
   $website=\Hyn\Tenancy\Models\Website::findOrFail($this->websiteId);$environment->tenant($website);
   app(\Modules\AiReception\Native\ReminderProcessor::class)->process($this->reminderId);
  }finally{$environment->tenant($previous);}
 }
}
