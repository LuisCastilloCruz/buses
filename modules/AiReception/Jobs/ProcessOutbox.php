<?php
namespace Modules\AiReception\Jobs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
class ProcessOutbox implements ShouldQueue {
 use Queueable,Dispatchable,InteractsWithQueue;
 public $tries=3;public $timeout=40;public $websiteId;public $outboxId;
 public function __construct(int $website,int $id){$this->websiteId=$website;$this->outboxId=$id;$this->onConnection('aireception');$this->onQueue('aireception');}
 public function handle(){
  $env=app(\Hyn\Tenancy\Environment::class);$previous=$env->tenant();
  try{$env->tenant(\Hyn\Tenancy\Models\Website::findOrFail($this->websiteId));$service=app(\Modules\AiReception\Native\Outbox::class);$service->process($this->outboxId);$service->reconcile($this->outboxId);}finally{$env->tenant($previous);}
 }
}