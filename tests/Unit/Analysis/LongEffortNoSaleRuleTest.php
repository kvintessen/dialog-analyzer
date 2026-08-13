<?php

namespace Tests\Unit\Analysis;

use App\Analysis\Rules\LongEffortNoSaleRule;
use App\Enums\DialogResult;
use App\Enums\MessageSender;
use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LongEffortNoSaleRuleTest extends TestCase
{
    use RefreshDatabase;

    private function createMessages(Dialog $dialog, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $dialog->messages()->create([
                'sender' => $i % 2 === 0 ? MessageSender::Client : MessageSender::Manager,
                'body' => "Сообщение {$i}",
                'sent_at' => now()->addMinutes($i),
            ]);
        }
    }

    public function test_it_fires_for_long_unsuccessful_dialog(): void
    {
        $dialog = Dialog::factory()->create(['result' => DialogResult::NotPurchased]);
        $this->createMessages($dialog, 12);
        $dialog->load('messages');

        $events = (new LongEffortNoSaleRule())->evaluate($dialog, ['min_messages' => 10]);

        $this->assertCount(1, $events);
        $this->assertSame(12, $events[0]['evidence']['message_count']);
    }

    public function test_it_does_not_fire_for_short_dialogs(): void
    {
        $dialog = Dialog::factory()->create(['result' => DialogResult::NotPurchased]);
        $this->createMessages($dialog, 4);
        $dialog->load('messages');

        $this->assertSame([], (new LongEffortNoSaleRule())->evaluate($dialog, ['min_messages' => 10]));
    }

    public function test_it_does_not_fire_when_dialog_resulted_in_purchase(): void
    {
        $dialog = Dialog::factory()->create(['result' => DialogResult::Purchased]);
        $this->createMessages($dialog, 12);
        $dialog->load('messages');

        $this->assertSame([], (new LongEffortNoSaleRule())->evaluate($dialog, ['min_messages' => 10]));
    }
}
