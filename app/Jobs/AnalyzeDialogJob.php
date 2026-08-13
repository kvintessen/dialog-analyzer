<?php

namespace App\Jobs;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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
