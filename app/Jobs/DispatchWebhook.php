<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public string $url,
        public string $event,
        public array $data,
        public int $tenantId
    ) {}

    public function handle(): void
    {
        try {
            $response = Http::timeout(10)
                ->post($this->url, [
                    'event' => $this->event,
                    'data' => $this->data,
                    'timestamp' => now()->toIso8601String(),
                    'tenant_id' => $this->tenantId,
                ]);

            if ($response->failed()) {
                Log::warning('Webhook failed', [
                    'url' => $this->url,
                    'event' => $this->event,
                    'status' => $response->status(),
                    'tenant_id' => $this->tenantId,
                ]);

                throw new \Exception("Webhook failed with status {$response->status()}");
            }

            Log::info('Webhook dispatched successfully', [
                'url' => $this->url,
                'event' => $this->event,
                'tenant_id' => $this->tenantId,
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'url' => $this->url,
                'event' => $this->event,
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId,
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook failed permanently after retries', [
            'url' => $this->url,
            'event' => $this->event,
            'error' => $exception->getMessage(),
            'tenant_id' => $this->tenantId,
        ]);
    }
}
