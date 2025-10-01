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
        $csvPath = storage_path('app/ml_data/bodyweight_exercises_complete.csv');

        // Check if file exists
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            $this->command->info("Please copy bodyweight_exercises_complete.csv to storage/app/ml_data/");
            return;
        }

        $this->command->info("🔄 Loading exercises from ML training dataset...");

        // Clear existing exercises
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Exercise::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Difficulty level mapping from CSV to database integer (1-5)
        $difficultyMap = [
            'beginner' => 1,        // beginner
            'intermediate' => 3,    // intermediate
            'advanced' => 5,        // expert
        ];

        // Muscle group mapping from CSV to database enum
        $muscleGroupMap = [
            'abdominals' => 'core',
            'abductors' => 'lower_body',
            'adductors' => 'lower_body',
            'back' => 'upper_body',
            'biceps' => 'upper_body',
            'calves' => 'lower_body',
            'chest' => 'upper_body',
            'forearms' => 'upper_body',
            'glutes' => 'lower_body',
            'hamstrings' => 'lower_body',
            'hips' => 'lower_body',
            'lats' => 'upper_body',
            'lower_back' => 'core',
            'middle_back' => 'upper_body',
            'neck' => 'upper_body',
            'quadriceps' => 'lower_body',
            'shoulders' => 'upper_body',
            'traps' => 'upper_body',
            'triceps' => 'upper_body',
            'core' => 'core',
            'upper_body' => 'upper_body',
            'lower_body' => 'lower_body',
        ];

        // Read and parse CSV with proper handling of quoted fields
        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file);

        $exerciseCount = 0;
        $batchSize = 50;
        $exercises = [];

        while (($row = fgetcsv($file)) !== false) {
            // Skip rows that don't have the correct number of columns
            if (count($row) !== count($header)) {
                continue;
            }

            $data = array_combine($header, $row);

            $muscleGroup = strtolower(trim($data['target_muscle_group']));

            $exercises[] = [
                'exercise_id' => (int) $data['exercise_id'],
                'exercise_name' => trim($data['exercise_name']),
                'description' => trim($data['description']),
                'difficulty_level' => $difficultyMap[strtolower(trim($data['difficulty_level']))] ?? 1,
                'target_muscle_group' => $muscleGroupMap[$muscleGroup] ?? 'core',
                'default_duration_seconds' => (int) $data['default_duration_seconds'],
                'default_rest_duration_seconds' => 30, // Default value
                'instructions' => null, // Leave empty as per user request
                'safety_tips' => null, // Not available in CSV
                'calories_burned_per_minute' => (float) $data['estimated_calories_per_min'],
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

        fclose($file);

        $this->command->info("🎉 Successfully imported {$exerciseCount} exercises from ML training dataset!");
        $this->command->info("📊 Dataset source: bodyweight_exercises_complete.csv (1,140 exercises)");
        $this->command->info("🤖 Ready for ML model integration!");
    }
}