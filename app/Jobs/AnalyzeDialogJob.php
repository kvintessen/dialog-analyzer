<?php

namespace App\Jobs;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzeDialogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Dialog $dialog) {}

    public function handle(AnalysisRunner $runner): void
    {
        $runner->analyze($this->dialog);
    }

    /**
     * Serializes runs per dialog: several messages arriving in quick
     * succession each dispatch their own job, but running them concurrently
     * could let an older job's results overwrite a newer one's. An
     * overlapping job is released back onto the queue rather than dropped,
     * so it still runs once the earlier one finishes.
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping((string) $this->dialog->id))->releaseAfter(2)->expireAfter(60),
        ];
    }

    /**
     * Seconds to wait before each retry (docker/docker-compose.yml runs the
     * worker with --tries=3, so two backoff values cover both retries).
     */
    public function backoff(): array
    {
        return [10, 30];
    }

    public function failed(Throwable $exception): void
    {
        $this->dialog->forceFill(['analysis_failed_at' => now()])->save();
    }
}
