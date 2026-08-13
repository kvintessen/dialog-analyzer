<?php

namespace Tests\Unit\Analysis;

use App\Analysis\Rules\ClientSilenceAfterManagerRule;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSilenceAfterManagerRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fires_when_last_message_is_from_manager(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['body' => 'Привет', 'sent_at' => now()->subDay()]);
        $manager = Message::factory()->for($dialog)->fromManager()->create(['body' => 'Ждём вашего решения', 'sent_at' => now()]);
        $dialog->load('messages');

        $events = (new ClientSilenceAfterManagerRule())->evaluate($dialog, []);

        $this->assertCount(1, $events);
        $this->assertSame([$manager->id], $events[0]['evidence']['message_ids']);
    }

    public function test_it_does_not_fire_when_client_replied_last(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromManager()->create(['body' => 'Здравствуйте', 'sent_at' => now()->subDay()]);
        Message::factory()->for($dialog)->fromClient()->create(['body' => 'Спасибо, куплю', 'sent_at' => now()]);
        $dialog->load('messages');

        $events = (new ClientSilenceAfterManagerRule())->evaluate($dialog, []);

        $this->assertCount(0, $events);
    }

    public function test_it_does_not_fail_on_empty_dialog(): void
    {
        $dialog = Dialog::factory()->create();
        $dialog->load('messages');

        $this->assertSame([], (new ClientSilenceAfterManagerRule())->evaluate($dialog, []));
    }
}
