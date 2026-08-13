<?php

namespace Tests\Unit\Analysis;

use App\Analysis\Rules\SlowResponseRule;
use App\Analysis\RuleRegistry;
use RuntimeException;
use Tests\TestCase;

class NotAnAnalysisRule
{
}

class RuleRegistryTest extends TestCase
{
    public function test_resolve_returns_null_for_an_unknown_key(): void
    {
        $this->assertNull(RuleRegistry::resolve('nonexistent_rule'));
    }

    public function test_resolve_returns_the_configured_class_for_a_known_key(): void
    {
        $this->assertSame(SlowResponseRule::class, RuleRegistry::resolve(SlowResponseRule::key()));
    }

    public function test_resolve_throws_when_the_configured_class_does_not_implement_the_rule_interface(): void
    {
        config(['analysis_rules.rules.broken_rule' => NotAnAnalysisRule::class]);

        $this->expectException(RuntimeException::class);

        RuleRegistry::resolve('broken_rule');
    }
}
