<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrossServiceCommunication
{
    /**
     * Get instructional videos from media service
     * As specified in the requirements document
     */
    public function getExerciseVideos($exerciseId): array
    {
        try {
            $mediaServiceUrl = env('MEDIA_SERVICE_URL');

            if (!$mediaServiceUrl) {
                Log::warning('Media service URL not configured');
                return [];
            }

            $response = Http::timeout(10)->get($mediaServiceUrl . '/media/videos/' . $exerciseId);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::error('Failed to fetch exercise videos from media service', [
                'exercise_id' => $exerciseId,
                'status' => $response->status(),
                'error' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch exercise videos', [
                'exercise_id' => $exerciseId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Provide exercise data for ML service
     * As specified in the requirements document
     */
    public function getExerciseAttributesForML($exerciseId = null): \Illuminate\Support\Collection
    {
        $query = Exercise::with(['muscleGroups'])
            ->select([
                'exercise_id',
                'exercise_name',
                'difficulty_level',
                'target_muscle_group',
                'default_duration_seconds',
                'calories_burned_per_minute',
                'equipment_needed',
                'exercise_category'
            ]);

        if ($exerciseId) {
            $query->where('exercise_id', $exerciseId);
        }

        return $query->get()->map(function($exercise) {
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
    }

    /**
     * Link exercises with instructional videos
     * As specified in the requirements document
     */
    public function linkExerciseWithVideo($exerciseId, array $videoData): Video
    {
        // Validate exercise exists
        $exercise = Exercise::find($exerciseId);
        if (!$exercise) {
            throw new \Exception('Exercise not found');
        }

        // Create video record linked to media service
        $video = Video::create([
            'exercise_id' => $exerciseId,
            'video_title' => $videoData['title'],
            'video_url' => $videoData['url'],
            'video_description' => $videoData['description'] ?? null,
            'duration_seconds' => $videoData['duration'] ?? null,
            'video_type' => $videoData['type'] ?? null,
            'video_quality' => $videoData['quality'] ?? '720p',
            'thumbnail_url' => $videoData['thumbnail_url'] ?? null,
            'file_size_mb' => $videoData['file_size_mb'] ?? null
        ]);

        return $video;
    }

    /**
     * Validate user permissions via auth service
     */
    public function validateUserPermissions($userId, $permission = 'content:read'): bool
    {
        try {
            $authServiceUrl = env('AUTH_SERVICE_URL');

            if (!$authServiceUrl) {
                Log::warning('Auth service URL not configured');
                return false;
            }

            $response = Http::timeout(5)->get($authServiceUrl . '/auth/validate', [
                'user_id' => $userId,
                'permission' => $permission
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['valid'] ?? false;
            }

            Log::warning('Auth service validation failed', [
                'user_id' => $userId,
                'permission' => $permission,
                'status' => $response->status()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to validate user permissions', [
                'user_id' => $userId,
                'permission' => $permission,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send data to ML service for processing
     */
    public function sendToMLService($endpoint, $data): ?array
    {
        try {
            $mlServiceUrl = env('ML_SERVICE_URL');

            if (!$mlServiceUrl) {
                Log::warning('ML service URL not configured');
                return null;
            }

            $response = Http::timeout(30)->post($mlServiceUrl . $endpoint, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ML service request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'error' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to communicate with ML service', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Notify planning service about new workout templates
     */
    public function notifyPlanningService($workoutId): bool
    {
        try {
            $planningServiceUrl = env('PLANNING_SERVICE_URL');

            if (!$planningServiceUrl) {
                Log::warning('Planning service URL not configured');
                return false;
            }

            $response = Http::timeout(10)->post($planningServiceUrl . '/planning/workout-template-updated', [
                'workout_id' => $workoutId,
                'service' => 'content'
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to notify planning service', [
                'workout_id' => $workoutId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}