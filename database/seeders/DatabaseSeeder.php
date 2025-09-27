<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info("🚀 Starting FitNEase Content Service Database Seeding...");

        // Import ML training data for exercises
        $this->call([
            ExerciseSeeder::class,
        ]);

        $this->command->info("✅ Content service database seeding completed!");
        $this->command->info("🤖 Ready for ML model integration!");
    }
}
