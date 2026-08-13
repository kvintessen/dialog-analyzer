<?php

namespace Database\Factories;

use App\Enums\EventSeverity;
use App\Models\AnalysisEvent;
use App\Models\AnalysisRule;
use App\Models\Dialog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisEvent>
 */
class AnalysisEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dialog_id' => Dialog::factory(),
            'analysis_rule_id' => AnalysisRule::factory(),
            'severity' => EventSeverity::Medium,
            'title' => fake()->sentence(),
            'description' => fake()->sentence(),
            'evidence' => [],
            'detected_at' => now(),
        ];
    }
}
