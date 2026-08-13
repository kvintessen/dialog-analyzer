<?php

namespace Tests\Feature;

use App\Enums\DialogResult;
use App\Enums\UserRole;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_manager_sees_stats_and_recent_dialogs_scoped_to_their_own_dialogs(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $otherManager = User::factory()->create(['role' => UserRole::Manager]);

        $purchased = Dialog::factory()->create([
            'manager_id' => $manager->id,
            'result' => DialogResult::Purchased,
        ]);
        Message::factory()->create(['dialog_id' => $purchased->id, 'sent_at' => now()]);

        $notPurchased = Dialog::factory()->create([
            'manager_id' => $manager->id,
            'result' => DialogResult::NotPurchased,
        ]);
        Message::factory()->create(['dialog_id' => $notPurchased->id, 'sent_at' => now()->subDay()]);

        $rule = AnalysisRule::factory()->create();
        $notPurchased->events()->create([
            'analysis_rule_id' => $rule->id,
            'severity' => 'high',
            'title' => 'Пропущен вопрос клиента',
            'evidence' => [],
            'detected_at' => now(),
        ]);

        Dialog::factory()->create(['manager_id' => $otherManager->id]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.total_dialogs', 2)
                ->where('stats.purchased_count', 1)
                ->where('stats.high_severity_events_count', 1)
                ->has('recentDialogs', 2)
                ->where('recentDialogs.0.client_name', $purchased->client_name)
            );
    }

    public function test_analyst_sees_dialogs_from_all_managers(): void
    {
        $analyst = User::factory()->create(['role' => UserRole::Analyst]);
        $managerOne = User::factory()->create(['role' => UserRole::Manager]);
        $managerTwo = User::factory()->create(['role' => UserRole::Manager]);

        Dialog::factory()->create(['manager_id' => $managerOne->id]);
        Dialog::factory()->create(['manager_id' => $managerTwo->id]);

        $this->actingAs($analyst)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.total_dialogs', 2)
            );
    }

    public function test_empty_state_reports_zero_stats(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.total_dialogs', 0)
                ->where('stats.purchased_count', 0)
                ->where('stats.high_severity_events_count', 0)
                ->has('recentDialogs', 0)
            );
    }
}
