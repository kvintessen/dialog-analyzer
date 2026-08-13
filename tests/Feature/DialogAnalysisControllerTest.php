<?php

namespace Tests\Feature;

use App\Enums\MessageSender;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialogAnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_trigger_analysis(): void
    {
        $dialog = Dialog::factory()->create();

        $this->post(route('dialogs.analyze', $dialog))->assertRedirect(route('login'));
    }

    public function test_it_reruns_analysis_and_replaces_events(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create();
        $dialog->messages()->create(['sender' => MessageSender::Client, 'body' => 'Привет', 'sent_at' => now()->subHour()]);
        $dialog->messages()->create(['sender' => MessageSender::Manager, 'body' => 'Здравствуйте', 'sent_at' => now()]);
        AnalysisRule::factory()->create(['key' => 'slow_response', 'enabled' => true, 'config' => ['threshold_minutes' => 30]]);

        $this->actingAs($user)->post(route('dialogs.analyze', $dialog))
            ->assertRedirect(route('dialogs.show', $dialog));

        $this->assertSame(1, $dialog->events()->count());

        $this->actingAs($user)->post(route('dialogs.analyze', $dialog));

        $this->assertSame(1, $dialog->events()->count());
    }
}
