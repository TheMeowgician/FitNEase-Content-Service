<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseInstruction;
use App\Services\CommsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public function index(): JsonResponse
    {
        $exercises = Exercise::with(['muscleGroups', 'videos', 'instructions'])
            ->orderBy('exercise_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $exercises
        ]);
    }

    public function show($id): JsonResponse
    {
        $exercise = Exercise::with(['muscleGroups', 'videos', 'instructions'])
            ->find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $exercise
        ]);
    }

    public function getExerciseAttributes($id): JsonResponse
    {
        $exercise = Exercise::with(['muscleGroups'])
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
            ->find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $attributes = [
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

        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exercise_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'difficulty_level' => ['required', Rule::in(['beginner', 'medium', 'expert'])],
            'target_muscle_group' => ['required', Rule::in(['core', 'upper_body', 'lower_body'])],
            'default_duration_seconds' => 'integer|min:1',
            'default_rest_duration_seconds' => 'integer|min:1',
            'instructions' => 'nullable|string',
            'safety_tips' => 'nullable|string',
            'calories_burned_per_minute' => 'nullable|numeric|min:0',
            'equipment_needed' => 'nullable|string|max:255',
            'exercise_category' => 'nullable|string|max:50',
        ]);

        $exercise = Exercise::create($validated);

        // Demonstrate service-to-service communication: notify user of new exercise
        $token = $request->bearerToken();
        $userId = $request->attributes->get('user_id');

        if ($token && $userId) {
            $commsService = new CommsService();
            $commsService->sendAchievementNotification($token, $userId, [
                'title' => 'New Exercise Created!',
                'message' => "You've successfully created a new exercise: {$exercise->exercise_name}"
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Exercise created successfully',
            'data' => $exercise->load(['muscleGroups', 'videos', 'instructions'])
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $validated = $request->validate([
            'exercise_name' => 'string|max:100',
            'description' => 'nullable|string',
            'difficulty_level' => Rule::in(['beginner', 'medium', 'expert']),
            'target_muscle_group' => Rule::in(['core', 'upper_body', 'lower_body']),
            'default_duration_seconds' => 'integer|min:1',
            'default_rest_duration_seconds' => 'integer|min:1',
            'instructions' => 'nullable|string',
            'safety_tips' => 'nullable|string',
            'calories_burned_per_minute' => 'nullable|numeric|min:0',
            'equipment_needed' => 'nullable|string|max:255',
            'exercise_category' => 'nullable|string|max:50',
        ]);

        $exercise->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Exercise updated successfully',
            'data' => $exercise->load(['muscleGroups', 'videos', 'instructions'])
        ]);
    }
}
