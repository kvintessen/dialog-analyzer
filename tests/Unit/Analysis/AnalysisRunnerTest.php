<?php

namespace Tests\Unit\Analysis;

use App\Analysis\AnalysisRule as AnalysisRuleContract;
use App\Analysis\AnalysisRunner;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ThrowingRule implements AnalysisRuleContract
{
    public static function key(): string
    {
        return 'throwing_rule';
    }

    public static function configSchema(): array
    {
        return [];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        throw new RuntimeException('boom');
    }
}

class AnalysisRunnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_rules_do_not_produce_events(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);

        AnalysisRule::factory()->slowResponse()->create(['enabled' => false]);

        (new AnalysisRunner())->analyze($dialog);

        $this->assertSame(0, $dialog->events()->count());
    }

    public function test_unresolvable_rule_key_is_skipped_without_error(): void
    {
        $dialog = Dialog::factory()->create();
        AnalysisRule::factory()->create(['key' => 'nonexistent_rule', 'enabled' => true]);

        (new AnalysisRunner())->analyze($dialog);

        $this->assertSame(0, $dialog->events()->count());
    }

    public function test_it_persists_events_from_enabled_rules(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);

        $rule = AnalysisRule::factory()->slowResponse()->create();

        (new AnalysisRunner())->analyze($dialog);

        $this->assertSame(1, $dialog->events()->count());
        $this->assertSame($rule->id, $dialog->events()->first()->analysis_rule_id);
    }

    public function test_re_running_analysis_replaces_previous_events_instead_of_duplicating(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);

        AnalysisRule::factory()->slowResponse()->create();

        $runner = new AnalysisRunner();
        $runner->analyze($dialog);
        $runner->analyze($dialog);

        $this->assertSame(1, $dialog->events()->count());
    }

    public function test_a_throwing_rule_rolls_back_and_keeps_previous_events(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['sent_at' => now()->subHour()]);
        Message::factory()->for($dialog)->fromManager()->create(['sent_at' => now()]);

        $slowResponseRule = AnalysisRule::factory()->slowResponse()->create();
        (new AnalysisRunner())->analyze($dialog);
        $originalEvent = $dialog->events()->first();
        $this->assertNotNull($originalEvent);

        // Disable the working rule so only the throwing one runs — otherwise a
        // coincidentally-matching recreated row could hide a lost-data bug.
        $slowResponseRule->update(['enabled' => false]);
        config(['analysis_rules.rules.throwing_rule' => ThrowingRule::class]);
        AnalysisRule::factory()->create(['key' => 'throwing_rule', 'enabled' => true]);

        try {
            (new AnalysisRunner())->analyze($dialog);
            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertSame(1, $dialog->events()->count());
        $this->assertSame($originalEvent->id, $dialog->events()->first()->id);
    }
}
