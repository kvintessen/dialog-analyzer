<?php

namespace Tests\Feature;

use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
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
        $dialog = Dialog::factory()->create(['manager_id' => $user->id]);
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);
        AnalysisRule::factory()->slowResponse()->create();

        $this->actingAs($user)->post(route('dialogs.analyze', $dialog))
            ->assertRedirect(route('dialogs.show', $dialog));

        $this->assertSame(1, $dialog->events()->count());

        $this->actingAs($user)->post(route('dialogs.analyze', $dialog));

        $this->assertSame(1, $dialog->events()->count());
    }

    public function test_manager_cannot_analyze_another_managers_dialog(): void
    {
        $manager = User::factory()->manager()->create();
        $dialog = Dialog::factory()->create();

        $this->actingAs($manager)
            ->post(route('dialogs.analyze', $dialog))
            ->assertForbidden();
    }

    public function test_analyst_can_analyze_any_dialog(): void
    {
        $analyst = User::factory()->analyst()->create();
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()]);
        AnalysisRule::factory()->slowResponse()->create();

        $this->actingAs($analyst)
            ->post(route('dialogs.analyze', $dialog))
            ->assertRedirect(route('dialogs.show', $dialog));
    }
}
