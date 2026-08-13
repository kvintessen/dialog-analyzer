<?php

namespace App\Ingestion\Adapters;

use App\Enums\MessageSender;
use App\Ingestion\InboundChannelAdapter;
use App\Ingestion\NormalizedInboundMessage;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Illustrative adapter for a Wazzup-shaped webhook payload — approximates
 * the real API's field names (messages[].messageId/chatId/dateTime/text/
 * isEcho/contact.name) closely enough to demonstrate the adapter contract,
 * but hasn't been verified against a live Wazzup account. Treat this as a
 * template for wiring a real aggregator, not a certified integration —
 * check the payload shape against the vendor's current docs before
 * pointing a real webhook at it.
 */
class WazzupAdapter implements InboundChannelAdapter
{
    public static function key(): string
    {
        return 'wazzup';
    }

    public function verify(Request $request): bool
    {
        $expected = config('inbound_channels.wazzup.webhook_secret');

        if (blank($expected)) {
            return false;
        }

        return hash_equals((string) $expected, (string) $request->header('X-Wazzup-Secret'));
    }

    public function parse(array $payload): NormalizedInboundMessage
    {
        $message = $payload['messages'][0]
            ?? throw new InvalidArgumentException('Wazzup payload is missing "messages".');

        return new NormalizedInboundMessage(
            externalThreadId: (string) $message['chatId'],
            externalMessageId: (string) $message['messageId'],
            sender: ($message['isEcho'] ?? false) ? MessageSender::Manager : MessageSender::Client,
            body: (string) ($message['text'] ?? ''),
            sentAt: CarbonImmutable::parse($message['dateTime']),
            clientName: $message['contact']['name'] ?? null,
        );
    }
}
