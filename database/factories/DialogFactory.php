<?php

namespace Database\Factories;

use App\Enums\DialogResult;
use App\Models\Dialog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dialog>
 */
class DialogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manager_id' => User::factory(),
            'client_name' => fake()->name(),
            'result' => DialogResult::Undecided,
        ];
    }
}
