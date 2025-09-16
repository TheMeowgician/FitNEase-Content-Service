<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class WorkoutController extends Controller
{
    public function getFilteredWorkouts($difficulty, $muscleGroup): JsonResponse
    {
        $query = Workout::with(['exercises'])
            ->where('is_public', true);

        if ($difficulty !== 'all') {
            $query->where('difficulty_level', $difficulty);
        }

        if ($muscleGroup !== 'all') {
            $query->whereRaw('FIND_IN_SET(?, target_muscle_groups)', [$muscleGroup]);
        }

        $workouts = $query->orderBy('workout_name')->get();

        return response()->json([
            'success' => true,
            'data' => $workouts
        ]);
    }

    public function searchWorkouts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'difficulty' => 'nullable|in:beginner,medium,expert',
            'muscle_groups' => 'nullable|array',
            'muscle_groups.*' => 'in:core,upper_body,lower_body',
            'workout_type' => 'nullable|in:individual,group,both',
            'duration_min' => 'nullable|integer|min:1',
            'duration_max' => 'nullable|integer|min:1',
            'search_term' => 'nullable|string|max:255'
        ]);

        $query = Workout::with(['exercises'])
            ->where('is_public', true);

        if (!empty($validated['difficulty'])) {
            $query->where('difficulty_level', $validated['difficulty']);
        }

        if (!empty($validated['muscle_groups'])) {
            foreach ($validated['muscle_groups'] as $muscleGroup) {
                $query->whereRaw('FIND_IN_SET(?, target_muscle_groups)', [$muscleGroup]);
            }
        }

        if (!empty($validated['workout_type'])) {
            $query->where('workout_type', $validated['workout_type']);
        }

        if (!empty($validated['duration_min'])) {
            $query->where('total_duration_minutes', '>=', $validated['duration_min']);
        }

        if (!empty($validated['duration_max'])) {
            $query->where('total_duration_minutes', '<=', $validated['duration_max']);
        }

        if (!empty($validated['search_term'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('workout_name', 'LIKE', '%' . $validated['search_term'] . '%')
                  ->orWhere('description', 'LIKE', '%' . $validated['search_term'] . '%');
            });
        }

        $workouts = $query->orderBy('workout_name')->get();

        return response()->json([
            'success' => true,
            'data' => $workouts
        ]);
    }

    public function show($id): JsonResponse
    {
        $workout = Workout::with(['exercises', 'workoutExercises.exercise'])
            ->find($id);

        if (!$workout) {
            return response()->json([
                'success' => false,
                'message' => 'Workout not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $workout
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workout_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'difficulty_level' => ['required', Rule::in(['beginner', 'medium', 'expert'])],
            'target_muscle_groups' => 'nullable|array',
            'target_muscle_groups.*' => 'in:core,upper_body,lower_body',
            'workout_type' => ['nullable', Rule::in(['individual', 'group', 'both'])],
            'created_by' => 'nullable|integer',
            'is_public' => 'boolean',
            'is_system_generated' => 'boolean',
            'exercises' => 'required|array|min:1',
            'exercises.*.exercise_id' => 'required|exists:exercises,exercise_id',
            'exercises.*.order_sequence' => 'required|integer|min:1',
            'exercises.*.custom_duration_seconds' => 'nullable|integer|min:1',
            'exercises.*.custom_rest_duration_seconds' => 'nullable|integer|min:1',
            'exercises.*.sets_count' => 'nullable|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            // Create workout
            $workoutData = collect($validated)->except(['exercises'])->toArray();
            $workoutData['total_exercises'] = count($validated['exercises']);

            $workout = Workout::create($workoutData);

            // Add exercises to workout
            foreach ($validated['exercises'] as $exerciseData) {
                $exerciseData['workout_id'] = $workout->workout_id;
                WorkoutExercise::create($exerciseData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workout created successfully',
                'data' => $workout->load(['exercises', 'workoutExercises.exercise'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create workout: ' . $e->getMessage()
            ], 500);
        }
    }
}
