<?php

namespace Tests\Unit\Jobs;

use App\Analysis\AnalysisRunner;
use App\Enums\MessageSender;
use App\Jobs\AnalyzeDialogJob;
use App\Models\AnalysisRule;
use App\Models\Dialog;
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
        $dialog->messages()->create(['sender' => MessageSender::Client, 'body' => 'Привет', 'sent_at' => now()->subHour()]);
        $dialog->messages()->create(['sender' => MessageSender::Manager, 'body' => 'Здравствуйте', 'sent_at' => now()]);
        AnalysisRule::factory()->create(['key' => 'slow_response', 'enabled' => true, 'config' => ['threshold_minutes' => 30]]);

        (new AnalyzeDialogJob($dialog))->handle(app(AnalysisRunner::class));

        $this->assertSame(1, $dialog->events()->count());
    }
}
