<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analysis_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dialog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analysis_rule_id')->constrained()->cascadeOnDelete();
            $table->string('severity');
            $table->string('title');
            $table->text('description')->nullable();
            $table->jsonb('evidence')->default('{}');
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index('dialog_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_events');
    }
};
