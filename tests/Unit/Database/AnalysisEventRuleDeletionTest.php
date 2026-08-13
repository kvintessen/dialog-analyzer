<?php

namespace Tests\Unit\Database;

use App\Models\AnalysisEvent;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * analysis_events is the audit trail of what was detected in a dialog.
 * Deleting the rule that produced an event must not delete the event itself
 * (it already carries its own title/description/evidence/severity) — only
 * the link to the now-gone rule is cleared.
 */
class AnalysisEventRuleDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_rule_keeps_its_past_events_with_the_link_cleared(): void
    {
        $rule = AnalysisRule::factory()->slowResponse()->create();
        $dialog = Dialog::factory()->create();
        $event = AnalysisEvent::factory()->for($dialog)->create(['analysis_rule_id' => $rule->id]);

        $rule->delete();

        $this->assertNotNull($event->fresh());
        $this->assertNull($event->fresh()->analysis_rule_id);
    }
}
