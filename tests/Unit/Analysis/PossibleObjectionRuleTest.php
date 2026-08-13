<?php

namespace Tests\Unit\Analysis;

use App\Analysis\Rules\PossibleObjectionRule;
use App\Models\Dialog;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PossibleObjectionRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flags_client_messages_matching_keywords(): void
    {
        $dialog = Dialog::factory()->create();
        $objection = Message::factory()->for($dialog)->fromClient()->create(['body' => 'Это слишком дорого для нас', 'sent_at' => now()]);
        Message::factory()->for($dialog)->fromManager()->create(['body' => 'Понимаю, есть рассрочка', 'sent_at' => now()->addMinute()]);
        $dialog->load('messages');

        $events = (new PossibleObjectionRule())->evaluate($dialog, []);

        $this->assertCount(1, $events);
        $this->assertSame([$objection->id], $events[0]['evidence']['message_ids']);
    }

    public function test_it_ignores_manager_messages_and_non_matching_text(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['body' => 'Отлично, беру!', 'sent_at' => now()]);
        Message::factory()->for($dialog)->fromManager()->create(['body' => 'Это дорого стоит производство', 'sent_at' => now()->addMinute()]);
        $dialog->load('messages');

        $this->assertSame([], (new PossibleObjectionRule())->evaluate($dialog, []));
    }

    public function test_it_does_not_flag_keyword_embedded_in_unrelated_word(): void
    {
        $dialog = Dialog::factory()->create();
        Message::factory()->for($dialog)->fromClient()->create(['body' => 'О, у вас тут совсем недорого!', 'sent_at' => now()]);
        $dialog->load('messages');

        $this->assertSame([], (new PossibleObjectionRule())->evaluate($dialog, []));
    }

    public function test_it_still_matches_keyword_stem_followed_by_suffix(): void
    {
        $dialog = Dialog::factory()->create();
        $message = Message::factory()->for($dialog)->fromClient()->create(['body' => 'Я подумаю над этим предложением', 'sent_at' => now()]);
        $dialog->load('messages');

        $events = (new PossibleObjectionRule())->evaluate($dialog, []);

        $this->assertCount(1, $events);
        $this->assertSame($message->id, $events[0]['evidence']['message_ids'][0]);
    }

    public function test_it_respects_custom_keyword_list_from_config(): void
    {
        $dialog = Dialog::factory()->create();
        $message = Message::factory()->for($dialog)->fromClient()->create(['body' => 'А что скажет наш юрист?', 'sent_at' => now()]);
        $dialog->load('messages');

        $events = (new PossibleObjectionRule())->evaluate($dialog, ['keywords' => ['юрист']]);

        $this->assertCount(1, $events);
        $this->assertSame($message->id, $events[0]['evidence']['message_ids'][0]);
    }
}
