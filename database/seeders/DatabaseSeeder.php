<?php

namespace Database\Seeders;

use App\Analysis\AnalysisRunner;
use App\Models\Dialog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AnalysisRuleSeeder::class,
            DialogSeeder::class,
        ]);

        $runner = app(AnalysisRunner::class);

        Dialog::all()->each(fn (Dialog $dialog) => $runner->analyze($dialog));
    }
}
