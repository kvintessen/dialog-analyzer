<?php

namespace Tests\Feature;

use App\Models\AnalysisRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisRuleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('analysis-rules.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_rules_with_config_schema(): void
    {
        $user = User::factory()->manager()->create();
        AnalysisRule::factory()->slowResponse()->create(['name' => 'Долгий ответ']);

        $this->actingAs($user)
            ->get(route('analysis-rules.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('AnalysisRules/Index')
                ->has('rules', 1)
                ->where('rules.0.name', 'Долгий ответ')
                ->has('rules.0.config_schema')
            );
    }

    public function test_it_can_toggle_a_rule_off(): void
    {
        $user = User::factory()->analyst()->create();
        $rule = AnalysisRule::factory()->create(['enabled' => true]);

        $this->actingAs($user)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => $rule->name,
                'description' => $rule->description,
                'severity' => 'medium',
                'enabled' => false,
                'config' => [],
            ])
            ->assertRedirect();

        $this->assertFalse($rule->fresh()->enabled);
    }

    public function test_it_validates_severity(): void
    {
        $user = User::factory()->analyst()->create();
        $rule = AnalysisRule::factory()->create();

        $this->actingAs($user)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => 'X',
                'severity' => 'critical',
                'enabled' => true,
                'config' => [],
            ])
            ->assertSessionHasErrors('severity');
    }

    public function test_guests_cannot_update_rules(): void
    {
        $rule = AnalysisRule::factory()->create();

        $this->patch(route('analysis-rules.update', $rule), [])
            ->assertRedirect(route('login'));
    }

    public function test_manager_cannot_update_rules(): void
    {
        $manager = User::factory()->manager()->create();
        $rule = AnalysisRule::factory()->create(['enabled' => true]);

        $this->actingAs($manager)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => $rule->name,
                'description' => $rule->description,
                'severity' => 'medium',
                'enabled' => false,
                'config' => [],
            ])
            ->assertForbidden();

        $this->assertTrue($rule->fresh()->enabled);
    }

    public function test_it_rejects_a_non_array_value_for_a_string_list_config_field(): void
    {
        $user = User::factory()->analyst()->create();
        $rule = AnalysisRule::factory()->create(['key' => 'possible_objection']);

        $this->actingAs($user)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => $rule->name,
                'severity' => 'medium',
                'enabled' => true,
                'config' => ['keywords' => 'дорого'],
            ])
            ->assertSessionHasErrors('config.keywords');
    }

    public function test_it_rejects_a_non_integer_value_for_an_integer_config_field(): void
    {
        $user = User::factory()->analyst()->create();
        $rule = AnalysisRule::factory()->slowResponse()->create();

        $this->actingAs($user)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => $rule->name,
                'severity' => 'medium',
                'enabled' => true,
                'config' => ['threshold_minutes' => 'many'],
            ])
            ->assertSessionHasErrors('config.threshold_minutes');
    }

    public function test_it_accepts_config_matching_the_rules_schema(): void
    {
        $user = User::factory()->analyst()->create();
        $rule = AnalysisRule::factory()->slowResponse()->create();

        $this->actingAs($user)
            ->patch(route('analysis-rules.update', $rule), [
                'name' => $rule->name,
                'severity' => 'medium',
                'enabled' => true,
                'config' => ['threshold_minutes' => 45],
            ])
            ->assertRedirect();

        $this->assertSame(45, $rule->fresh()->config['threshold_minutes']);
    }
}
