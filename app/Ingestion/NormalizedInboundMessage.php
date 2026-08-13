<?php

namespace App\Ingestion;

use App\Enums\MessageSender;
use Carbon\CarbonImmutable;

/**
 * Common shape every channel adapter parses its vendor-specific payload
 * into, so the webhook controller never needs to know which channel a
 * message came from.
 */
final readonly class NormalizedInboundMessage
{
    public function __construct(
        public string $externalThreadId,
        public string $externalMessageId,
        public MessageSender $sender,
        public string $body,
        public CarbonImmutable $sentAt,
        public ?string $clientName = null,
    ) {}
}
