<?php

namespace App\Analysis\Rules;

use App\Analysis\AnalysisRule;
use App\Enums\EventSeverity;
use App\Enums\MessageSender;
use App\Models\Dialog;

class SlowResponseRule implements AnalysisRule
{
    public static function key(): string
    {
        return 'slow_response';
    }

    public static function configSchema(): array
    {
        return [
            ['key' => 'threshold_minutes', 'label' => 'Порог ответа, мин', 'type' => 'integer', 'default' => 30],
        ];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        $threshold = (int) ($config['threshold_minutes'] ?? 30);
        $events = [];
        $pendingClientMessage = null;
        $messages = $dialog->messages->values();

        foreach ($messages as $index => $message) {
            if ($message->sender === MessageSender::Client) {
                $pendingClientMessage ??= $message;

                continue;
            }

            if ($pendingClientMessage !== null) {
                $gapMinutes = (int) $pendingClientMessage->sent_at->diffInMinutes($message->sent_at);
                // Another manager message right after, with no client reply in between,
                // means this is an outbound re-engagement follow-up, not a reply to the client.
                $nextMessage = $messages->get($index + 1);
                $isFollowUpBurst = $nextMessage !== null && $nextMessage->sender !== MessageSender::Client;

                if ($gapMinutes > $threshold && ! $isFollowUpBurst) {
                    $events[] = [
                        'title' => "Долгий ответ менеджера: {$gapMinutes} мин",
                        'description' => 'Клиент написал в '.$pendingClientMessage->sent_at->format('d.m.Y H:i').", менеджер ответил через {$gapMinutes} мин.",
                        'severity' => $gapMinutes >= $threshold * 2 ? EventSeverity::High->value : null,
                        'evidence' => [
                            'message_ids' => [$pendingClientMessage->id, $message->id],
                            'gap_minutes' => $gapMinutes,
                        ],
                    ];
                }

                $pendingClientMessage = null;
            }
        }

        return $events;
    }
}
