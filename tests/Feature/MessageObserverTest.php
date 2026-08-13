<?php

namespace Tests\Feature;

use App\Enums\MessageSender;
use App\Jobs\AnalyzeDialogJob;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MessageObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_message_dispatches_an_analysis_job_for_its_dialog(): void
    {
        Bus::fake();

        $dialog = Dialog::factory()->create();

        $dialog->messages()->create(['sender' => MessageSender::Client, 'body' => 'Привет', 'sent_at' => now()]);

        Bus::assertDispatched(AnalyzeDialogJob::class, fn (AnalyzeDialogJob $job) => $job->dialog->is($dialog));
    }

    public function test_creating_a_message_triggers_analysis_without_a_manual_rerun(): void
    {
        $dialog = Dialog::factory()->create();
        AnalysisRule::factory()->create(['key' => 'slow_response', 'enabled' => true, 'config' => ['threshold_minutes' => 30]]);

        $dialog->messages()->create(['sender' => MessageSender::Client, 'body' => 'Привет', 'sent_at' => now()->subHour()]);
        $dialog->messages()->create(['sender' => MessageSender::Manager, 'body' => 'Здравствуйте', 'sent_at' => now()]);

        $this->assertSame(1, $dialog->events()->count());
    }
}
