<?php

namespace Tests\Unit\Ingestion;

use App\Enums\MessageSender;
use App\Ingestion\Adapters\WazzupAdapter;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Tests\TestCase;

class WazzupAdapterTest extends TestCase
{
    public function test_parse_maps_an_incoming_client_message(): void
    {
        $normalized = (new WazzupAdapter())->parse([
            'messages' => [[
                'messageId' => 'msg-1',
                'chatId' => 'chat-42',
                'dateTime' => '2026-08-14T10:00:00Z',
                'text' => 'Здравствуйте, интересует тариф для команды',
                'isEcho' => false,
                'contact' => ['name' => 'Ирина Соколова'],
            ]],
        ]);

        $this->assertSame('chat-42', $normalized->externalThreadId);
        $this->assertSame('msg-1', $normalized->externalMessageId);
        $this->assertSame(MessageSender::Client, $normalized->sender);
        $this->assertSame('Здравствуйте, интересует тариф для команды', $normalized->body);
        $this->assertSame('2026-08-14 10:00:00', $normalized->sentAt->format('Y-m-d H:i:s'));
        $this->assertSame('Ирина Соколова', $normalized->clientName);
    }

    public function test_parse_maps_an_outgoing_manager_message(): void
    {
        $normalized = (new WazzupAdapter())->parse([
            'messages' => [[
                'messageId' => 'msg-2',
                'chatId' => 'chat-42',
                'dateTime' => '2026-08-14T10:05:00Z',
                'text' => 'Добрый день! Расскажу подробнее.',
                'isEcho' => true,
            ]],
        ]);

        $this->assertSame(MessageSender::Manager, $normalized->sender);
        $this->assertNull($normalized->clientName);
    }

    public function test_parse_rejects_a_payload_without_messages(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new WazzupAdapter())->parse([]);
    }

    public function test_verify_accepts_the_configured_secret(): void
    {
        config(['inbound_channels.wazzup.webhook_secret' => 'correct-secret']);

        $request = Request::create('/webhooks/wazzup', 'POST', server: ['HTTP_X_WAZZUP_SECRET' => 'correct-secret']);

        $this->assertTrue((new WazzupAdapter())->verify($request));
    }

    public function test_verify_rejects_a_wrong_secret(): void
    {
        config(['inbound_channels.wazzup.webhook_secret' => 'correct-secret']);

        $request = Request::create('/webhooks/wazzup', 'POST', server: ['HTTP_X_WAZZUP_SECRET' => 'wrong-secret']);

        $this->assertFalse((new WazzupAdapter())->verify($request));
    }

    public function test_verify_rejects_when_no_secret_is_configured(): void
    {
        config(['inbound_channels.wazzup.webhook_secret' => null]);

        $request = Request::create('/webhooks/wazzup', 'POST');

        $this->assertFalse((new WazzupAdapter())->verify($request));
    }
}
