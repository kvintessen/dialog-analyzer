<?php

namespace App\Analysis\Rules;

use App\Analysis\AnalysisRule;
use App\Enums\MessageSender;
use App\Models\Dialog;

class ClientSilenceAfterManagerRule implements AnalysisRule
{
    public static function key(): string
    {
        return 'client_silence_after_manager';
    }

    public static function configSchema(): array
    {
        return [
            ['key' => 'threshold_minutes', 'label' => 'Порог молчания клиента, мин', 'type' => 'integer', 'default' => 60],
        ];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        $threshold = (int) ($config['threshold_minutes'] ?? 60);
        $lastMessage = $dialog->messages->last();

        if ($lastMessage === null || $lastMessage->sender !== MessageSender::Manager) {
            return [];
        }

        $silenceMinutes = (int) $lastMessage->sent_at->diffInMinutes(now());

        if ($silenceMinutes < $threshold) {
            return [];
        }

        return [[
            'title' => 'Клиент перестал отвечать после сообщения менеджера',
            'description' => "Последнее сообщение в диалоге принадлежит менеджеру, ответа клиента не было {$silenceMinutes} мин.",
            'evidence' => ['message_ids' => [$lastMessage->id], 'silence_minutes' => $silenceMinutes],
        ]];
    }
}
