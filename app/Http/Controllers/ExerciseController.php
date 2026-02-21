<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseInstruction;
use App\Services\CommsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Build a cache key from the query parameters
        $cacheKey = 'exercises:' . md5($request->fullUrl());

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Exercise::with(['muscleGroups', 'videos', 'instructions']);

            // Filter by difficulty if provided
            if ($request->has('difficulty')) {
                $difficulty = $request->input('difficulty');
                // Map difficulty string to integer (1=beginner, 2=intermediate, 3=advanced)
                $difficultyMap = [
                    'beginner' => 1,
                    'intermediate' => 2,
                    'medium' => 2,
                    'advanced' => 3,
                    'expert' => 3
                ];
                if (isset($difficultyMap[$difficulty])) {
                    $query->where('difficulty_level', $difficultyMap[$difficulty]);
                }
            }

            // Filter by muscle groups if provided (comma-separated)
            if ($request->has('muscle_groups')) {
                $muscleGroups = explode(',', $request->input('muscle_groups'));
                $query->whereIn('target_muscle_group', $muscleGroups);
            }

            // Order by ID for consistent, fast results
            $query->orderBy('exercise_id');

            // Apply limit if provided, default to 100 to prevent unbounded queries
            $limit = min((int) $request->input('limit', 100), 200);
            $query->limit($limit);

            return $query->get();
        });

        return response()->json([
            'success' => true,
            'data' => $data
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

    public function library(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 30), 50);
        $search = $request->input('search');
        $difficulty = $request->input('difficulty');
        $muscleGroup = $request->input('muscle_group');

        // Build filtered query
        $query = Exercise::select([
            'exercise_id',
            'exercise_name',
            'difficulty_level',
            'target_muscle_group',
            'default_duration_seconds',
            'calories_burned_per_minute',
            'equipment_needed',
            'exercise_category',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('exercise_name', 'LIKE', "%{$search}%")
                  ->orWhere('target_muscle_group', 'LIKE', "%{$search}%")
                  ->orWhere('exercise_category', 'LIKE', "%{$search}%");
            });
        }

        if ($difficulty) {
            $query->where('difficulty_level', (int) $difficulty);
        }

        if ($muscleGroup) {
            $query->where('target_muscle_group', $muscleGroup);
        }

        $query->orderBy('exercise_name', 'asc');

        $paginated = $query->paginate($perPage);

        // Get aggregate counts for filter chips (unfiltered totals)
        $stats = [
            'total' => Exercise::count(),
            'beginner' => Exercise::where('difficulty_level', 1)->count(),
            'intermediate' => Exercise::where('difficulty_level', 2)->count(),
            'advanced' => Exercise::where('difficulty_level', 3)->count(),
            'upper_body' => Exercise::where('target_muscle_group', 'upper_body')->count(),
            'lower_body' => Exercise::where('target_muscle_group', 'lower_body')->count(),
            'core' => Exercise::where('target_muscle_group', 'core')->count(),
            'full_body' => Exercise::where('target_muscle_group', 'full_body')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'stats' => $stats,
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
