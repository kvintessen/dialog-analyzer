<?php

namespace Tests\Feature;

use App\Models\AnalysisEvent;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $dialog = Dialog::factory()->create();

        $this->get(route('dialogs.index'))->assertRedirect(route('login'));
        $this->get(route('dialogs.show', $dialog))->assertRedirect(route('login'));
    }

    public function test_index_lists_dialogs_with_summary_data(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create(['manager_id' => $user->id, 'client_name' => 'Иван Тестов']);
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()]);

        $this->actingAs($user)
            ->get(route('dialogs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dialogs/Index')
                ->has('dialogs', 1)
                ->where('dialogs.0.client_name', 'Иван Тестов')
                ->where('dialogs.0.messages_count', 1)
            );
    }

    public function test_show_returns_messages_and_events_for_a_dialog(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create(['manager_id' => $user->id]);
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()]);
        $rule = AnalysisRule::factory()->slowResponse()->create();
        AnalysisEvent::factory()->for($dialog)->for($rule, 'rule')->create([
            'severity' => 'medium',
            'title' => 'Тестовое событие',
        ]);

        $this->actingAs($user)
            ->get(route('dialogs.show', $dialog))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dialogs/Show')
                ->has('messages', 1)
                ->has('events', 1)
                ->where('events.0.rule_name', $rule->name)
            );
    }

    public function test_show_returns_404_for_unknown_dialog(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dialogs/999999')->assertNotFound();
    }

    public function test_index_reports_the_highest_severity_among_a_dialogs_events(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create(['manager_id' => $user->id]);
        $rule = AnalysisRule::factory()->slowResponse()->create();

        foreach (['low', 'high', 'medium'] as $severity) {
            AnalysisEvent::factory()->for($dialog)->for($rule, 'rule')->create([
                'severity' => $severity,
                'title' => "Event {$severity}",
            ]);
        }

        $this->actingAs($user)
            ->get(route('dialogs.index'))
            ->assertInertia(fn ($page) => $page
                ->where('dialogs.0.max_severity', 'high')
                ->where('dialogs.0.events_count', 3)
            );
    }

    public function test_show_orders_events_by_severity_descending(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create(['manager_id' => $user->id]);
        $rule = AnalysisRule::factory()->slowResponse()->create();

        foreach (['low', 'high', 'medium'] as $severity) {
            AnalysisEvent::factory()->for($dialog)->for($rule, 'rule')->create([
                'severity' => $severity,
                'title' => "Event {$severity}",
            ]);
        }

        $this->actingAs($user)
            ->get(route('dialogs.show', $dialog))
            ->assertInertia(fn ($page) => $page
                ->where('events.0.title', 'Event high')
                ->where('events.1.title', 'Event medium')
                ->where('events.2.title', 'Event low')
            );
    }

    public function test_index_serializes_last_message_at_like_other_timestamps(): void
    {
        $user = User::factory()->create();
        $dialog = Dialog::factory()->create(['manager_id' => $user->id]);
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()]);

        // Assert on the actual JSON the client receives (not the raw PHP prop
        // array), so Carbon's __toString() formatting can't hide a mismatch
        // between last_message_at and other, natively-cast timestamps.
        $this->actingAs($user)
            ->get(route('dialogs.index'))
            ->assertInertia(fn ($page) => $page
                ->where(
                    'dialogs.0.last_message_at',
                    fn (string $value) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/', $value)
                )
            );
    }

    public function test_manager_only_sees_their_own_dialogs_in_index(): void
    {
        $manager = User::factory()->manager()->create();
        $ownDialog = Dialog::factory()->create(['manager_id' => $manager->id, 'client_name' => 'Свой клиент']);
        Dialog::factory()->create(['client_name' => 'Чужой клиент']);

        $this->actingAs($manager)
            ->get(route('dialogs.index'))
            ->assertInertia(fn ($page) => $page
                ->has('dialogs', 1)
                ->where('dialogs.0.id', $ownDialog->id)
            );
    }

    public function test_analyst_sees_all_dialogs_in_index(): void
    {
        $analyst = User::factory()->analyst()->create();
        Dialog::factory()->count(2)->create();

        $this->actingAs($analyst)
            ->get(route('dialogs.index'))
            ->assertInertia(fn ($page) => $page->has('dialogs', 2));
    }

    public function test_manager_cannot_view_another_managers_dialog(): void
    {
        $manager = User::factory()->manager()->create();
        $dialog = Dialog::factory()->create();

        $this->actingAs($manager)
            ->get(route('dialogs.show', $dialog))
            ->assertForbidden();
    }

    public function test_analyst_can_view_any_dialog(): void
    {
        $analyst = User::factory()->analyst()->create();
        $dialog = Dialog::factory()->create();

        $this->actingAs($analyst)
            ->get(route('dialogs.show', $dialog))
            ->assertOk();
    }
}
