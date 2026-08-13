<?php

namespace Database\Seeders;

use App\Analysis\Rules\ClientSilenceAfterManagerRule;
use App\Analysis\Rules\LongEffortNoSaleRule;
use App\Analysis\Rules\PossibleObjectionRule;
use App\Analysis\Rules\SlowResponseRule;
use App\Enums\EventSeverity;
use App\Models\AnalysisRule;
use Illuminate\Database\Seeder;

class AnalysisRuleSeeder extends Seeder
{
    public function run(): void
    {
        AnalysisRule::updateOrCreate(
            ['key' => SlowResponseRule::key()],
            [
                'name' => 'Долгий ответ менеджера',
                'description' => 'Клиент написал, а менеджер ответил позже установленного порога.',
                'severity' => EventSeverity::Medium,
                'enabled' => true,
                'config' => ['threshold_minutes' => 30],
            ]
        );

        AnalysisRule::updateOrCreate(
            ['key' => ClientSilenceAfterManagerRule::key()],
            [
                'name' => 'Клиент перестал отвечать',
                'description' => 'Последнее сообщение в диалоге — от менеджера, ответа клиента не последовало.',
                'severity' => EventSeverity::High,
                'enabled' => true,
                'config' => [],
            ]
        );

        AnalysisRule::updateOrCreate(
            ['key' => PossibleObjectionRule::key()],
            [
                'name' => 'Возможное возражение клиента',
                'description' => 'Сообщение клиента содержит одно из типичных слов-маркеров возражения.',
                'severity' => EventSeverity::Low,
                'enabled' => true,
                'config' => [
                    'keywords' => [
                        'дорого', 'дешевле', 'подума', 'не интересно',
                        'не уверен', 'сомнева', 'конкурент', 'не сейчас',
                    ],
                ],
            ]
        );

        AnalysisRule::updateOrCreate(
            ['key' => LongEffortNoSaleRule::key()],
            [
                'name' => 'Длинный диалог без продажи',
                'description' => 'В диалоге много сообщений, но он не завершился покупкой — стоит разобрать вручную.',
                'severity' => EventSeverity::Medium,
                'enabled' => true,
                'config' => ['min_messages' => 10],
            ]
        );
    }
}
