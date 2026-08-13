<?php

namespace Tests\Unit\Enums;

use App\Enums\EventSeverity;
use Tests\TestCase;

class EventSeverityTest extends TestCase
{
    public function test_rank_orders_high_above_medium_above_low(): void
    {
        $this->assertGreaterThan(EventSeverity::Medium->rank(), EventSeverity::High->rank());
        $this->assertGreaterThan(EventSeverity::Low->rank(), EventSeverity::Medium->rank());
    }
}
