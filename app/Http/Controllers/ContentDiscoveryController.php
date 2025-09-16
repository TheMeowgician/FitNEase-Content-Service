<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ContentDiscoveryController extends Controller
{
    public function getExercisesByMuscleGroup($group): JsonResponse
    {
        if (!in_array($group, ['core', 'upper_body', 'lower_body'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid muscle group'
            ], 400);
        }

        $exercises = Exercise::with(['muscleGroups', 'videos'])
            ->where('target_muscle_group', $group)
            ->orderBy('difficulty_level')
            ->orderBy('exercise_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $exercises
        ]);
    }

    public function getExercisesByDifficulty($level): JsonResponse
    {
        if (!in_array($level, ['beginner', 'medium', 'expert'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid difficulty level'
            ], 400);
        }

        $exercises = Exercise::with(['muscleGroups', 'videos'])
            ->where('difficulty_level', $level)
            ->orderBy('target_muscle_group')
            ->orderBy('exercise_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $exercises
        ]);
    }

    public function searchContent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|max:255',
            'type' => 'nullable|in:exercises,workouts,both',
            'difficulty' => 'nullable|in:beginner,medium,expert',
            'muscle_group' => 'nullable|in:core,upper_body,lower_body',
            'equipment' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $searchTerm = $validated['q'];
        $type = $validated['type'] ?? 'both';
        $limit = $validated['limit'] ?? 20;
        $results = [];

        // Search exercises
        if ($type === 'exercises' || $type === 'both') {
            $exerciseQuery = Exercise::with(['muscleGroups', 'videos'])
                ->where(function ($q) use ($searchTerm) {
                    $q->where('exercise_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('exercise_category', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('equipment_needed', 'LIKE', '%' . $searchTerm . '%');
                });

            if (!empty($validated['difficulty'])) {
                $exerciseQuery->where('difficulty_level', $validated['difficulty']);
            }

            if (!empty($validated['muscle_group'])) {
                $exerciseQuery->where('target_muscle_group', $validated['muscle_group']);
            }

            if (!empty($validated['equipment'])) {
                $exerciseQuery->where('equipment_needed', 'LIKE', '%' . $validated['equipment'] . '%');
            }

            $exercises = $exerciseQuery->limit($limit)->get();
            $results['exercises'] = $exercises;
        }

        // Search workouts
        if ($type === 'workouts' || $type === 'both') {
            $workoutQuery = Workout::with(['exercises'])
                ->where('is_public', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('workout_name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
                });

            if (!empty($validated['difficulty'])) {
                $workoutQuery->where('difficulty_level', $validated['difficulty']);
            }

            if (!empty($validated['muscle_group'])) {
                $workoutQuery->whereRaw('FIND_IN_SET(?, target_muscle_groups)', [$validated['muscle_group']]);
            }

            $workouts = $workoutQuery->limit($limit)->get();
            $results['workouts'] = $workouts;
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'search_term' => $searchTerm
        ]);
    }
}
