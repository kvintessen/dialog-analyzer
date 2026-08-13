<?php

namespace App\Ingestion;

use Illuminate\Http\Request;

/**
 * A channel is anything that can deliver a manager/client message from the
 * outside (a messenger aggregator webhook, a CRM, in theory a file import).
 * Adding a new channel means writing a class that implements this interface
 * and registering it in config/inbound_channels.php — the webhook
 * controller and the rest of the analysis pipeline don't change.
 */
interface InboundChannelAdapter
{
    public static function key(): string;

    /**
     * Verify the request actually came from this channel's provider
     * (shared secret, signature header, etc.) before any payload is parsed.
     */
    public function verify(Request $request): bool;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parse(array $payload): NormalizedInboundMessage;
}
