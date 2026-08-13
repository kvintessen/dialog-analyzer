<?php

namespace App\Ingestion;

class ChannelAdapterRegistry
{
    /**
     * @return array<string, class-string<InboundChannelAdapter>>
     */
    public static function all(): array
    {
        return config('inbound_channels.channels', []);
    }

    /**
     * @return class-string<InboundChannelAdapter>|null
     */
    public static function resolve(string $key): ?string
    {
        return static::all()[$key] ?? null;
    }
}
