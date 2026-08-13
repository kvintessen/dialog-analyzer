<?php

namespace Tests\Unit\Analysis;

use App\Analysis\Rules\SlowResponseRule;
use App\Enums\MessageSender;
use App\Models\Dialog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlowResponseRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fires_when_manager_reply_gap_exceeds_threshold(): void
    {
        $dialog = Dialog::factory()->create();
        $client = $dialog->messages()->create([
            'sender' => MessageSender::Client,
            'body' => 'Здравствуйте, расскажите про тариф',
            'sent_at' => now()->subMinutes(60),
        ]);
        $manager = $dialog->messages()->create([
            'sender' => MessageSender::Manager,
            'body' => 'Добрый день! Сейчас расскажу.',
            'sent_at' => now()->subMinutes(10),
        ]);
        $dialog->load('messages');

        $events = (new SlowResponseRule())->evaluate($dialog, ['threshold_minutes' => 30]);

        $this->assertCount(1, $events);
        $this->assertSame([$client->id, $manager->id], $events[0]['evidence']['message_ids']);
        $this->assertSame(50, $events[0]['evidence']['gap_minutes']);
    }

    public function test_it_does_not_fire_within_threshold(): void
    {
        $dialog = Dialog::factory()->create();
        $dialog->messages()->create([
            'sender' => MessageSender::Client,
            'body' => 'Привет',
            'sent_at' => now()->subMinutes(20),
        ]);
        $dialog->messages()->create([
            'sender' => MessageSender::Manager,
            'body' => 'Здравствуйте!',
            'sent_at' => now()->subMinutes(10),
        ]);
        $dialog->load('messages');

        $events = (new SlowResponseRule())->evaluate($dialog, ['threshold_minutes' => 30]);

        $this->assertCount(0, $events);
    }

    public function test_it_escalates_severity_for_very_long_gaps(): void
    {
        $dialog = Dialog::factory()->create();
        $dialog->messages()->create([
            'sender' => MessageSender::Client,
            'body' => 'Привет',
            'sent_at' => now()->subMinutes(120),
        ]);
        $dialog->messages()->create([
            'sender' => MessageSender::Manager,
            'body' => 'Здравствуйте!',
            'sent_at' => now(),
        ]);
        $dialog->load('messages');

        $events = (new SlowResponseRule())->evaluate($dialog, ['threshold_minutes' => 30]);

        $this->assertSame('high', $events[0]['severity']);
    }

    public function test_it_does_not_flag_manager_follow_up_burst_after_client_silence(): void
    {
        $dialog = Dialog::factory()->create();
        $dialog->messages()->create([
            'sender' => MessageSender::Client,
            'body' => 'Обсужу с командой и вернусь.',
            'sent_at' => now()->subDays(2),
        ]);
        $dialog->messages()->create([
            'sender' => MessageSender::Manager,
            'body' => 'Добрый день! Актуально ли ещё предложение?',
            'sent_at' => now()->subDay(),
        ]);
        $dialog->messages()->create([
            'sender' => MessageSender::Manager,
            'body' => 'Если демо было полезно — дайте знать.',
            'sent_at' => now(),
        ]);
        $dialog->load('messages');

        $events = (new SlowResponseRule())->evaluate($dialog, ['threshold_minutes' => 30]);

        $this->assertSame([], $events);
    }

    public function test_it_does_not_fail_on_empty_dialog(): void
    {
        $dialog = Dialog::factory()->create();
        $dialog->load('messages');

        $events = (new SlowResponseRule())->evaluate($dialog, []);

        $this->assertSame([], $events);
    }
}
