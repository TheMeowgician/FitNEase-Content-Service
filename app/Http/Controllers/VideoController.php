<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function getExerciseVideos($exerciseId): JsonResponse
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        // Get videos from local database
        $localVideos = Video::where('exercise_id', $exerciseId)
            ->where('is_active', true)
            ->orderBy('video_type')
            ->get();

        // Get videos from media service
        $mediaServiceVideos = $this->getVideosFromMediaService($exerciseId);

        return response()->json([
            'success' => true,
            'data' => [
                'exercise' => $exercise->only(['exercise_id', 'exercise_name']),
                'local_videos' => $localVideos,
                'media_service_videos' => $mediaServiceVideos
            ]
        ]);
    }

    public function linkExerciseWithVideo(Request $request, $exerciseId): JsonResponse
    {
        $exercise = Exercise::find($exerciseId);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }

        $validated = $request->validate([
            'video_title' => 'required|string|max:255',
            'video_url' => 'required|url|max:500',
            'video_description' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:1',
            'video_type' => [
                'nullable',
                Rule::in(['instruction', 'form_guide', 'demonstration', 'tips'])
            ],
            'thumbnail_url' => 'nullable|url|max:500',
            'video_quality' => [
                'nullable',
                Rule::in(['720p', '1080p', '480p'])
            ],
            'file_size_mb' => 'nullable|numeric|min:0'
        ]);

        $validated['exercise_id'] = $exerciseId;
        $validated['video_quality'] = $validated['video_quality'] ?? '720p';

        $video = Video::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Video linked successfully',
            'data' => $video
        ], 201);
    }

    public function updateVideo(Request $request, $videoId): JsonResponse
    {
        $video = Video::find($videoId);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        $validated = $request->validate([
            'video_title' => 'nullable|string|max:255',
            'video_url' => 'nullable|url|max:500',
            'video_description' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:1',
            'video_type' => [
                'nullable',
                Rule::in(['instruction', 'form_guide', 'demonstration', 'tips'])
            ],
            'thumbnail_url' => 'nullable|url|max:500',
            'video_quality' => [
                'nullable',
                Rule::in(['720p', '1080p', '480p'])
            ],
            'file_size_mb' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $video->update(array_filter($validated, function($value) {
            return $value !== null;
        }));

        return response()->json([
            'success' => true,
            'message' => 'Video updated successfully',
            'data' => $video->fresh()
        ]);
    }

    public function deleteVideo($videoId): JsonResponse
    {
        $video = Video::find($videoId);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        }

        $video->delete();

        return response()->json([
            'success' => true,
            'message' => 'Video deleted successfully'
        ]);
    }

    private function getVideosFromMediaService($exerciseId): array
    {
        try {
            $mediaServiceUrl = env('MEDIA_SERVICE_URL');

            if (!$mediaServiceUrl) {
                Log::warning('Media service URL not configured');
                return [];
            }

            $response = Http::timeout(5)->get($mediaServiceUrl . '/media/videos/' . $exerciseId);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('Failed to fetch videos from media service', [
                'exercise_id' => $exerciseId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching exercise videos from media service', [
                'exercise_id' => $exerciseId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
