<?php

namespace Tests\Unit\Models;

use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DialogTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_relation_orders_by_sent_at_then_id_as_a_tie_breaker(): void
    {
        $dialog = Dialog::factory()->make();

        $orders = $dialog->messages()->toBase()->orders;

        $this->assertSame(
            [
                ['column' => 'sent_at', 'direction' => 'asc'],
                ['column' => 'id', 'direction' => 'asc'],
            ],
            $orders
        );
    }
}
