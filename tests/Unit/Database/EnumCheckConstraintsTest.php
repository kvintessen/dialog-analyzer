<?php

namespace Tests\Unit\Database;

use App\Models\AnalysisEvent;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The PHP enums (UserRole, DialogResult, MessageSender, EventSeverity) only
 * constrain values written through Eloquent. These tests prove the database
 * itself rejects an invalid value written any other way (raw SQL, a bug in
 * a future migration/import script, manual psql access).
 */
class EnumCheckConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_role_column_rejects_unknown_values(): void
    {
        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('users')->where('id', $user->id)->update(['role' => 'superadmin']);
    }

    public function test_dialogs_result_column_rejects_unknown_values(): void
    {
        $dialog = Dialog::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('dialogs')->where('id', $dialog->id)->update(['result' => 'maybe']);
    }

    public function test_messages_sender_column_rejects_unknown_values(): void
    {
        $message = Message::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('messages')->where('id', $message->id)->update(['sender' => 'bot']);
    }

    public function test_analysis_rules_severity_column_rejects_unknown_values(): void
    {
        $rule = AnalysisRule::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('analysis_rules')->where('id', $rule->id)->update(['severity' => 'critical']);
    }

    public function test_analysis_events_severity_column_rejects_unknown_values(): void
    {
        $event = AnalysisEvent::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('analysis_events')->where('id', $event->id)->update(['severity' => 'critical']);
    }
}
