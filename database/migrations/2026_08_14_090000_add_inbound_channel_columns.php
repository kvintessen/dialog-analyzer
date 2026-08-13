<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dialogs', function (Blueprint $table) {
            $table->string('source')->nullable()->after('analysis_failed_at');
            $table->string('external_thread_id')->nullable()->after('source');

            $table->unique(['source', 'external_thread_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('external_message_id')->nullable()->unique()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('dialogs', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_thread_id']);
            $table->dropColumn(['source', 'external_thread_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('external_message_id');
        });
    }
};
