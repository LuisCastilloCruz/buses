<?php

namespace Modules\TattooInbox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\TattooInbox\Services\MetaInboxService;

class ProcessMetaWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onQueue(config('tattooinbox.queue', 'default'));
    }

    public function handle(MetaInboxService $service)
    {
        $result = $service->processWebhook($this->payload);

        Log::info('[TATTOOINBOX][WEBHOOK_JOB] processed', [
            'entry_count' => is_array($this->payload['entry'] ?? null) ? count($this->payload['entry']) : 0,
            'processed_count' => is_array($result) ? count($result) : 0,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('[TATTOOINBOX][WEBHOOK_JOB] failed', [
            'message' => $exception->getMessage(),
            'entry_count' => is_array($this->payload['entry'] ?? null) ? count($this->payload['entry']) : 0,
        ]);
    }
}
