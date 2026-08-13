<?php

namespace Tests\Feature;

use App\Enums\MessageSender;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundMessageWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $messageId = 'msg-1', string $chatId = 'chat-1'): array
    {
        return [
            'messages' => [[
                'messageId' => $messageId,
                'chatId' => $chatId,
                'dateTime' => '2026-08-14T10:00:00Z',
                'text' => 'Здравствуйте, интересует ваш продукт',
                'isEcho' => false,
                'contact' => ['name' => 'Новый клиент'],
            ]],
        ];
    }

    private function withManager(): User
    {
        $manager = User::factory()->manager()->create();
        config(['inbound_channels.wazzup.manager_id' => $manager->id]);
        config(['inbound_channels.wazzup.webhook_secret' => 'secret']);

        return $manager;
    }

    public function test_it_creates_a_dialog_and_message_from_a_valid_webhook(): void
    {
        $manager = $this->withManager();

        $this->postJson('/webhooks/wazzup', $this->payload(), ['X-Wazzup-Secret' => 'secret'])
            ->assertNoContent();

        $this->assertDatabaseHas('dialogs', [
            'source' => 'wazzup',
            'external_thread_id' => 'chat-1',
            'manager_id' => $manager->id,
            'client_name' => 'Новый клиент',
        ]);
        $this->assertDatabaseHas('messages', [
            'external_message_id' => 'msg-1',
            'sender' => MessageSender::Client->value,
        ]);
    }

    public function test_it_rejects_a_request_with_a_wrong_secret(): void
    {
        $this->withManager();

        $this->postJson('/webhooks/wazzup', $this->payload(), ['X-Wazzup-Secret' => 'wrong'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_it_returns_404_for_an_unknown_channel(): void
    {
        $this->postJson('/webhooks/does-not-exist', [], ['X-Wazzup-Secret' => 'secret'])
            ->assertNotFound();
    }

    public function test_it_is_idempotent_for_a_repeated_external_message_id(): void
    {
        $this->withManager();

        $this->postJson('/webhooks/wazzup', $this->payload(), ['X-Wazzup-Secret' => 'secret'])
            ->assertNoContent();
        $this->postJson('/webhooks/wazzup', $this->payload(), ['X-Wazzup-Secret' => 'secret'])
            ->assertNoContent();

        $this->assertDatabaseCount('messages', 1);
    }

    public function test_a_second_message_in_the_same_chat_is_appended_to_the_same_dialog(): void
    {
        $this->withManager();

        $this->postJson('/webhooks/wazzup', $this->payload('msg-1', 'chat-1'), ['X-Wazzup-Secret' => 'secret']);
        $this->postJson('/webhooks/wazzup', $this->payload('msg-2', 'chat-1'), ['X-Wazzup-Secret' => 'secret']);

        $this->assertDatabaseCount('dialogs', 1);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_new_messages_from_the_webhook_still_trigger_analysis(): void
    {
        $this->withManager();

        $this->postJson('/webhooks/wazzup', $this->payload(), ['X-Wazzup-Secret' => 'secret']);

        $dialog = Dialog::where('source', 'wazzup')->where('external_thread_id', 'chat-1')->firstOrFail();
        $message = Message::where('external_message_id', 'msg-1')->firstOrFail();

        $this->assertTrue($message->dialog->is($dialog));
    }
}
