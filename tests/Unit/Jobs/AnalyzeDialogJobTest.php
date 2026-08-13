<?php

namespace Tests\Unit\Jobs;

use App\Analysis\AnalysisRunner;
use App\Jobs\AnalyzeDialogJob;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyzeDialogJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_queueable(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new AnalyzeDialogJob(Dialog::factory()->make()));
    }

    public function test_handle_runs_analysis_for_the_given_dialog(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);
        AnalysisRule::factory()->slowResponse()->create();

        (new AnalyzeDialogJob($dialog))->handle(app(AnalysisRunner::class));

        $this->assertSame(1, $dialog->events()->count());
    }
}
