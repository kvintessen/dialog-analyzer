<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Иван Кузнецов',
            'email' => 'ivan@example.com',
        ]);

        User::factory()->create([
            'name' => 'Анна Смирнова',
            'email' => 'anna@example.com',
        ]);

        User::factory()->create([
            'name' => 'Игорь Петров',
            'email' => 'igor@example.com',
        ]);
    }
}
