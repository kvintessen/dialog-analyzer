<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeDialogJob;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
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

        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()]);

        Bus::assertDispatched(AnalyzeDialogJob::class, fn (AnalyzeDialogJob $job) => $job->dialog->is($dialog));
    }

    public function test_creating_a_message_triggers_analysis_without_a_manual_rerun(): void
    {
        $dialog = Dialog::factory()->create();
        AnalysisRule::factory()->slowResponse()->create();

        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);

        $this->assertSame(1, $dialog->events()->count());
    }
}
