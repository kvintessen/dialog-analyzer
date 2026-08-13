<?php

use App\Enums\DialogResult;
use App\Enums\EventSeverity;
use App\Enums\MessageSender;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The corresponding PHP enums (UserRole, DialogResult, MessageSender,
 * EventSeverity) only guard values written through Eloquent. These
 * check constraints make the database itself the source of truth, so a
 * value written any other way (raw SQL, a bad import script, manual psql
 * access) can't silently drift from what the app enums expect.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addCheck('users', 'users_role_check', 'role', UserRole::cases());
        $this->addCheck('dialogs', 'dialogs_result_check', 'result', DialogResult::cases());
        $this->addCheck('messages', 'messages_sender_check', 'sender', MessageSender::cases());
        $this->addCheck('analysis_rules', 'analysis_rules_severity_check', 'severity', EventSeverity::cases());
        $this->addCheck('analysis_events', 'analysis_events_severity_check', 'severity', EventSeverity::cases());
    }

    public function down(): void
    {
        DB::statement('alter table users drop constraint users_role_check');
        DB::statement('alter table dialogs drop constraint dialogs_result_check');
        DB::statement('alter table messages drop constraint messages_sender_check');
        DB::statement('alter table analysis_rules drop constraint analysis_rules_severity_check');
        DB::statement('alter table analysis_events drop constraint analysis_events_severity_check');
    }

    /**
     * @param  array<int, \UnitEnum&\BackedEnum>  $cases
     */
    private function addCheck(string $table, string $constraint, string $column, array $cases): void
    {
        $values = collect($cases)->map(fn ($case) => "'{$case->value}'")->implode(', ');

        DB::statement("alter table {$table} add constraint {$constraint} check ({$column} in ({$values}))");
    }
};
