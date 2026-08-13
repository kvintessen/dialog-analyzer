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
        return [];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        $lastMessage = $dialog->messages->last();

        if ($lastMessage === null || $lastMessage->sender !== MessageSender::Manager) {
            return [];
        }

        return [[
            'title' => 'Клиент перестал отвечать после сообщения менеджера',
            'description' => 'Последнее сообщение в диалоге принадлежит менеджеру, ответа клиента не последовало.',
            'evidence' => ['message_ids' => [$lastMessage->id]],
        ]];
    }
}
