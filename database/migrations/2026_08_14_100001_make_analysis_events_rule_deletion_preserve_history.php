<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analysis_events', function (Blueprint $table) {
            $table->dropForeign(['analysis_rule_id']);
        });

        DB::statement('ALTER TABLE analysis_events ALTER COLUMN analysis_rule_id DROP NOT NULL');

        Schema::table('analysis_events', function (Blueprint $table) {
            $table->foreign('analysis_rule_id')->references('id')->on('analysis_rules')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_events', function (Blueprint $table) {
            $table->dropForeign(['analysis_rule_id']);
        });

        DB::statement('ALTER TABLE analysis_events ALTER COLUMN analysis_rule_id SET NOT NULL');

        Schema::table('analysis_events', function (Blueprint $table) {
            $table->foreign('analysis_rule_id')->references('id')->on('analysis_rules')->cascadeOnDelete();
        });
    }
};
