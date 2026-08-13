<?php

namespace App\Http\Resources;

use App\Models\AnalysisEvent;
use App\Models\Dialog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Dialog
 */
class DialogSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'manager_name' => $this->manager->name,
            'result' => $this->result->value,
            'messages_count' => $this->messages_count,
            'last_message_at' => $this->last_message_at ? Carbon::parse($this->last_message_at) : null,
            'events_count' => $this->events->count(),
            'max_severity' => $this->maxSeverity(),
        ];
    }

    private function maxSeverity(): ?string
    {
        if ($this->events->isEmpty()) {
            return null;
        }

        return $this->events
            ->sortBy(fn (AnalysisEvent $event) => $event->severity->rank())
            ->last()
            ->severity
            ->value;
    }
}
