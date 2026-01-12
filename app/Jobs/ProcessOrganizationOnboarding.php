<?php

namespace App\Jobs;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessOrganizationOnboarding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum retry attempts
     */
    public int $tries = 3;

    /**
     * Backoff delays in seconds
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Max execution time per job (seconds)
     */
    public int $timeout = 120;

    public function __construct(public int $organizationId) {}

    /**
     * Prevent concurrent processing of same organization
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->organizationId),
        ];
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        $org = Organization::find($this->organizationId);

        // 🔹 Idempotency & safety guards
        if (! $org) {
            return;
        }

        if ($org->status === 'completed') {
            return;
        }

        if ($org->status === 'processing') {
            return;
        }

        // Mark as processing
        $org->update([
            'status' => 'processing',
        ]);

        try {
            /**
             * 🔹 Simulated heavy onboarding logic
             * (API calls, provisioning, DNS, etc.)
             */
            sleep(1);

            // Mark success
            $org->update([
                'status'       => 'completed',
                'processed_at' => now(),
            ]);

            Log::info('Organization onboarding completed', [
                'organization_id' => $org->id,
                'batch_id'        => $org->batch_id,
                'status'          => 'completed',
            ]);

        } catch (Throwable $e) {

            Log::error('Organization onboarding attempt failed', [
                'organization_id' => $org->id,
                'batch_id'        => $org->batch_id,
                'attempt'         => $this->attempts(),
                'error'           => $e->getMessage(),
            ]);

            // Rethrow → Laravel will retry with backoff
            throw $e;
        }
    }

    /**
     * Runs after all retries are exhausted
     */
    public function failed(Throwable $exception): void
    {
        Organization::where('id', $this->organizationId)->update([
            'status'        => 'failed',
            'failed_reason' => $exception->getMessage(),
        ]);

        Log::critical('Organization onboarding permanently failed', [
            'organization_id' => $this->organizationId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
