<?php

namespace Tests\Unit\Jobs;

use App\Analysis\AnalysisRunner;
use App\Jobs\AnalyzeDialogJob;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Tests\TestCase;

class AnalyzeDialogJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_is_queueable(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new AnalyzeDialogJob(Dialog::factory()->make()));
    }

    public function test_it_prevents_overlapping_runs_for_the_same_dialog(): void
    {
        $dialog = Dialog::factory()->create();

        $middleware = (new AnalyzeDialogJob($dialog))->middleware();

        $this->assertCount(1, $middleware);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[0]);
        $this->assertSame((string) $dialog->id, $middleware[0]->key);
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

    public function test_it_backs_off_between_retries(): void
    {
        $job = new AnalyzeDialogJob(Dialog::factory()->make());

        $this->assertSame([10, 30], $job->backoff());
    }

    public function test_failed_marks_the_dialog_as_failed(): void
    {
        $dialog = Dialog::factory()->create();

        (new AnalyzeDialogJob($dialog))->failed(new \RuntimeException('boom'));

        $this->assertNotNull($dialog->fresh()->analysis_failed_at);
    }
}
