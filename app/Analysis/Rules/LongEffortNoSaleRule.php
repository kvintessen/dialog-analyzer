<?php

namespace App\Analysis\Rules;

use App\Analysis\AnalysisRule;
use App\Enums\DialogResult;
use App\Models\Dialog;

class LongEffortNoSaleRule implements AnalysisRule
{
    public static function key(): string
    {
        return 'long_effort_no_sale';
    }

    public static function configSchema(): array
    {
        return [
            ['key' => 'min_messages', 'label' => 'Порог числа сообщений', 'type' => 'integer', 'default' => 10],
        ];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        $minMessages = (int) ($config['min_messages'] ?? 10);
        $messages = $dialog->messages;

        if ($messages->count() < $minMessages || $dialog->result === DialogResult::Purchased) {
            return [];
        }

        return [[
            'title' => "Длинный диалог без продажи: {$messages->count()} сообщений",
            'description' => 'Менеджер потратил много сообщений на диалог, который не завершился покупкой — стоит разобрать вручную.',
            'evidence' => [
                'message_ids' => $messages->pluck('id')->all(),
                'message_count' => $messages->count(),
            ],
        ]];
    }
}
