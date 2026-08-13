<?php

namespace Database\Factories;

use App\Enums\EventSeverity;
use App\Models\AnalysisRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisRule>
 */
class AnalysisRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'severity' => EventSeverity::Medium,
            'enabled' => true,
            'config' => [],
        ];
    }
}
