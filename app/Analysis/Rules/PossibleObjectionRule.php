<?php

namespace App\Analysis\Rules;

use App\Analysis\AnalysisRule;
use App\Enums\MessageSender;
use App\Models\Dialog;

class PossibleObjectionRule implements AnalysisRule
{
    private const DEFAULT_KEYWORDS = [
        'дорого', 'дешевле', 'подума', 'не интересно', 'не уверен',
        'сомнева', 'конкурент', 'не сейчас',
    ];

    public static function key(): string
    {
        return 'possible_objection';
    }

    public static function configSchema(): array
    {
        return [
            ['key' => 'keywords', 'label' => 'Ключевые слова возражений', 'type' => 'string_list', 'default' => self::DEFAULT_KEYWORDS],
        ];
    }

    public function evaluate(Dialog $dialog, array $config): array
    {
        $keywords = $config['keywords'] ?? self::DEFAULT_KEYWORDS;
        $events = [];

        foreach ($dialog->messages as $message) {
            if ($message->sender !== MessageSender::Client) {
                continue;
            }

            $body = mb_strtolower($message->body);

            foreach ($keywords as $keyword) {
                if ($keyword === '') {
                    continue;
                }

                $keywordLower = mb_strtolower($keyword);
                $pattern = '/(?<![a-zа-яё0-9])'.preg_quote($keywordLower, '/').'/u';

                if (preg_match($pattern, $body) === 1) {
                    $events[] = [
                        'title' => 'Обнаружено возможное возражение клиента',
                        'description' => "Сообщение клиента содержит слово «{$keyword}».",
                        'evidence' => ['message_ids' => [$message->id], 'keyword' => $keyword],
                    ];

                    break;
                }
            }
        }

        return $events;
    }
}
