<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exercise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExerciseSeeder extends Seeder
{
    /**
     * Seed the exercises table with ML training data.
     *
     * This imports the exact dataset used to train the ML models:
     * - Content-Based Filtering Model
     * - Random Forest Prediction Model
     * - Hybrid Recommendation Model
     */
    public function run(): void
    {
        // Path to the CSV file used in ML training
        $csvPath = storage_path('app/ml_data/fitnease_exercises_final.csv');

        // Check if file exists
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            $this->command->info("Please copy fitnease_exercises_final.csv to storage/app/ml_data/");
            return;
        }

        $this->command->info("🔄 Loading exercises from ML training dataset...");

        // Clear existing exercises
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Exercise::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Read and parse CSV
        $csv = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csv); // Remove header row

        $exerciseCount = 0;
        $batchSize = 50;
        $exercises = [];

        foreach ($csv as $row) {
            $data = array_combine($header, $row);

            $exercises[] = [
                'exercise_id' => (int) $data['exercise_id'],
                'exercise_name' => trim($data['exercise_name']),
                'description' => trim($data['instructions']), // Use instructions as description
                'difficulty_level' => (int) $data['difficulty_level'],
                'target_muscle_group' => trim($data['target_muscle_group']),
                'default_duration_seconds' => (int) $data['default_duration_seconds'],
                'default_rest_duration_seconds' => (int) $data['default_rest_duration_seconds'],
                'instructions' => trim($data['instructions']),
                'safety_tips' => trim($data['safety_tips'] ?? ''),
                'calories_burned_per_minute' => (float) $data['calories_burned_per_minute'],
                'equipment_needed' => trim($data['equipment_needed']),
                'exercise_category' => trim($data['exercise_category']),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $exerciseCount++;

            // Insert in batches for performance
            if (count($exercises) >= $batchSize) {
                Exercise::insert($exercises);
                $exercises = [];
                $this->command->info("✅ Inserted {$exerciseCount} exercises...");
            }
        }

        // Insert remaining exercises
        if (!empty($exercises)) {
            Exercise::insert($exercises);
        }

        $this->command->info("🎉 Successfully imported {$exerciseCount} exercises from ML training dataset!");
        $this->command->info("📊 Dataset source: fitnease_exercises_final.csv (400 exercises)");
        $this->command->info("🤖 Ready for ML model integration!");
    }
}