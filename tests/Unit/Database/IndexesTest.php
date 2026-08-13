<?php

namespace Tests\Unit\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Postgres does not automatically index foreign key columns (unlike MySQL).
 * These columns are filtered on in hot paths (Dialog::visibleTo() scopes by
 * manager_id; analysis_events is looked up by analysis_rule_id when a rule
 * is edited/disabled), so they need an explicit index.
 */
class IndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dialogs_manager_id_is_indexed(): void
    {
        $this->assertTrue($this->hasLeadingIndexOn('dialogs', 'manager_id'));
    }

    public function test_analysis_events_analysis_rule_id_is_indexed(): void
    {
        $this->assertTrue($this->hasLeadingIndexOn('analysis_events', 'analysis_rule_id'));
    }

    private function hasLeadingIndexOn(string $table, string $column): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['columns'][0] ?? null) === $column) {
                return true;
            }
        }

        return false;
    }
}
