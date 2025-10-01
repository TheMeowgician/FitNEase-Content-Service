<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MLDataController extends Controller
{
    public function getAllExercises(): JsonResponse
    {
        $exercises = Exercise::with(['muscleGroups'])
            ->select([
                'exercise_id',
                'exercise_name',
                'difficulty_level',
                'target_muscle_group',
                'default_duration_seconds',
                'calories_burned_per_minute',
                'equipment_needed',
                'exercise_category'
            ])
            ->get()
            ->map(function($exercise) {
                return [
                    'exercise_id' => $exercise->exercise_id,
                    'features' => [
                        'difficulty_level' => $exercise->difficulty_level,
                        'muscle_groups' => $exercise->muscleGroups->pluck('group_name')->toArray(),
                        'duration' => $exercise->default_duration_seconds,
                        'intensity' => $exercise->calories_burned_per_minute,
                        'equipment' => explode(',', $exercise->equipment_needed ?? ''),
                        'category' => $exercise->exercise_category
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $exercises
        ]);
    }

    public function getExerciseAttributes(): JsonResponse
    {
        $exercises = Exercise::select([
            'exercise_id',
            'exercise_name',
            'difficulty_level',
            'target_muscle_group',
            'default_duration_seconds',
            'calories_burned_per_minute',
            'equipment_needed',
            'exercise_category'
        ])->get();

        return response()->json([
            'success' => true,
            'exercises' => $exercises->map(function($exercise) {
                return [
                    'exercise_id' => $exercise->exercise_id,
                    'exercise_name' => $exercise->exercise_name,
                    'difficulty_level' => $exercise->difficulty_level,
                    'target_muscle_group' => $exercise->target_muscle_group,
                    'default_duration_seconds' => $exercise->default_duration_seconds,
                    'estimated_calories_burned' => $exercise->calories_burned_per_minute * ($exercise->default_duration_seconds / 60),
                    'equipment_needed' => $exercise->equipment_needed,
                    'exercise_category' => $exercise->exercise_category
                ];
            })
        ]);
    }

    public function getExerciseById($id): JsonResponse
    {
        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'exercise_id' => $exercise->exercise_id,
            'exercise_name' => $exercise->exercise_name,
            'difficulty_level' => $exercise->difficulty_level,
            'target_muscle_group' => $exercise->target_muscle_group,
            'default_duration_seconds' => $exercise->default_duration_seconds,
            'calories_burned_per_minute' => $exercise->calories_burned_per_minute,
            'equipment_needed' => $exercise->equipment_needed,
            'exercise_category' => $exercise->exercise_category,
            'description' => $exercise->description,
        ]);
    }

    public function getExerciseFeatures($id): JsonResponse
    {
        $exercise = Exercise::with(['muscleGroups', 'instructions'])->find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $featureVector = $this->generateExerciseFeatureVector($exercise);

        return response()->json([
            'success' => true,
            'data' => [
                'exercise_id' => $exercise->exercise_id,
                'feature_vector' => $featureVector
            ]
        ]);
    }

    public function getExerciseSimilarityData(): JsonResponse
    {
        $exercises = Exercise::with(['muscleGroups', 'instructions'])->get();

        $similarityData = $exercises->map(function($exercise) {
            return [
                'exercise_id' => $exercise->exercise_id,
                'exercise_name' => $exercise->exercise_name,
                'feature_vector' => $this->generateExerciseFeatureVector($exercise)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $similarityData
        ]);
    }

    private function generateExerciseFeatureVector($exercise): array
    {
        return [
            'difficulty_numeric' => $this->mapDifficultyToNumeric($exercise->difficulty_level),
            'muscle_groups_vector' => $this->encodeMuscleGroups($exercise->muscleGroups),
            'duration_normalized' => $exercise->default_duration_seconds / 120, // Normalize to 0-1
            'intensity_score' => ($exercise->calories_burned_per_minute ?? 0) / 20, // Normalize to 0-1
            'equipment_requirements' => $this->encodeEquipment($exercise->equipment_needed),
            'instruction_complexity' => $exercise->instructions()->count(),
            'category_encoding' => $this->encodeCategoryOneHot($exercise->exercise_category)
        ];
    }

    private function mapDifficultyToNumeric($difficulty): int
    {
        return match($difficulty) {
            'beginner' => 1,
            'medium' => 2,
            'expert' => 3,
            default => 1
        };
    }

    private function encodeMuscleGroups($muscleGroups): array
    {
        $encoding = ['core' => 0, 'upper_body' => 0, 'lower_body' => 0];

        foreach ($muscleGroups as $group) {
            if (isset($encoding[$group->group_name])) {
                $encoding[$group->group_name] = 1;
            }
        }

        return array_values($encoding);
    }

    private function encodeEquipment($equipment): array
    {
        $equipmentList = explode(',', $equipment ?? '');
        $equipmentList = array_map('trim', $equipmentList);
        $equipmentList = array_filter($equipmentList);

        // Common equipment categories
        $equipmentCategories = [
            'bodyweight' => ['none', 'bodyweight', ''],
            'weights' => ['dumbbells', 'barbells', 'kettlebells', 'weights'],
            'bands' => ['resistance bands', 'bands', 'elastic'],
            'cardio' => ['treadmill', 'bike', 'elliptical'],
            'other' => []
        ];

        $encoding = ['bodyweight' => 0, 'weights' => 0, 'bands' => 0, 'cardio' => 0, 'other' => 0];

        foreach ($equipmentList as $item) {
            $item = strtolower($item);
            $categorized = false;

            foreach ($equipmentCategories as $category => $keywords) {
                if (in_array($item, $keywords) || empty($item)) {
                    $encoding[$category] = 1;
                    $categorized = true;
                    break;
                }
            }

            if (!$categorized) {
                $encoding['other'] = 1;
            }
        }

        return array_values($encoding);
    }

    private function encodeCategoryOneHot($category): array
    {
        $categories = ['strength', 'cardio', 'flexibility', 'balance', 'endurance', 'other'];
        $encoding = array_fill(0, count($categories), 0);

        $categoryIndex = array_search(strtolower($category ?? 'other'), $categories);
        if ($categoryIndex !== false) {
            $encoding[$categoryIndex] = 1;
        } else {
            $encoding[array_search('other', $categories)] = 1;
        }

        return $encoding;
    }

    public function calculateExerciseSimilarity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exercise_id_1' => 'required|exists:exercises,exercise_id',
            'exercise_id_2' => 'required|exists:exercises,exercise_id'
        ]);

        $exercise1 = Exercise::with(['muscleGroups', 'instructions'])->find($validated['exercise_id_1']);
        $exercise2 = Exercise::with(['muscleGroups', 'instructions'])->find($validated['exercise_id_2']);

        $features1 = $this->generateExerciseFeatureVector($exercise1);
        $features2 = $this->generateExerciseFeatureVector($exercise2);

        $similarity = $this->calculateCosineSimilarity($features1, $features2);

        return response()->json([
            'success' => true,
            'data' => [
                'exercise_1' => ['id' => $exercise1->exercise_id, 'name' => $exercise1->exercise_name],
                'exercise_2' => ['id' => $exercise2->exercise_id, 'name' => $exercise2->exercise_name],
                'similarity_score' => $similarity
            ]
        ]);
    }

    private function calculateCosineSimilarity($features1, $features2): float
    {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        // Flatten arrays for calculation
        $flat1 = $this->flattenFeatures($features1);
        $flat2 = $this->flattenFeatures($features2);

        $maxLength = max(count($flat1), count($flat2));

        for ($i = 0; $i < $maxLength; $i++) {
            $value1 = $flat1[$i] ?? 0;
            $value2 = $flat2[$i] ?? 0;

            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 * $value1;
            $magnitude2 += $value2 * $value2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    private function flattenFeatures($features): array
    {
        $flat = [];

        foreach ($features as $key => $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, $value);
            } else {
                $flat[] = $value;
            }
        }

        return $flat;
    }
}
