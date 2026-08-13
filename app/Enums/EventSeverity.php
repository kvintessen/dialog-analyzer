<?php

namespace App\Enums;

enum EventSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    /**
     * Single source of truth for severity ordering — higher is more severe.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Low => 0,
            self::Medium => 1,
            self::High => 2,
        };
    }
}
