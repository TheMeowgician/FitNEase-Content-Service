<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('MEDIA_SERVICE_URL', 'http://fitnease-media');
    }

    public function getExerciseVideos($exerciseId, $token)
    {
        try {
            Log::info('Getting exercise videos from media service', [
                'service' => 'fitnease-content',
                'exercise_id' => $exerciseId,
                'media_service_url' => $this->baseUrl
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/api/media/videos/' . $exerciseId);

            if ($response->successful()) {
                Log::info('Exercise videos retrieved successfully', [
                    'service' => 'fitnease-content',
                    'exercise_id' => $exerciseId
                ]);

                return $response->json();
            }

            Log::warning('Failed to get exercise videos', [
                'service' => 'fitnease-content',
                'status' => $response->status(),
                'response_body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Media service communication error', [
                'service' => 'fitnease-content',
                'error' => $e->getMessage(),
                'exercise_id' => $exerciseId,
                'media_service_url' => $this->baseUrl
            ]);

            return null;
        }
    }

    public function notifyMediaUpload($exerciseId, $mediaData, $token)
    {
        try {
            Log::info('Notifying media service of content update', [
                'service' => 'fitnease-content',
                'exercise_id' => $exerciseId,
                'media_service_url' => $this->baseUrl
            ]);

            $notificationData = [
                'exercise_id' => $exerciseId,
                'notification_type' => 'content_updated',
                'content_data' => $mediaData,
                'updated_at' => now()->toISOString(),
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/media/content-notifications', $notificationData);

            if ($response->successful()) {
                Log::info('Media upload notification sent successfully', [
                    'service' => 'fitnease-content',
                    'exercise_id' => $exerciseId
                ]);

                return $response->json();
            }

            Log::warning('Failed to notify media upload', [
                'service' => 'fitnease-content',
                'status' => $response->status(),
                'response_body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Media service communication error', [
                'service' => 'fitnease-content',
                'error' => $e->getMessage(),
                'notification_data' => $notificationData ?? [],
                'media_service_url' => $this->baseUrl
            ]);

            return null;
        }
    }

    public function getVideoStreamingUrl($videoId, $token)
    {
        try {
            Log::info('Getting video streaming URL from media service', [
                'service' => 'fitnease-content',
                'video_id' => $videoId,
                'media_service_url' => $this->baseUrl
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/api/media/stream/' . $videoId);

            if ($response->successful()) {
                Log::info('Video streaming URL retrieved successfully', [
                    'service' => 'fitnease-content',
                    'video_id' => $videoId
                ]);

                return $response->json();
            }

            Log::warning('Failed to get video streaming URL', [
                'service' => 'fitnease-content',
                'status' => $response->status(),
                'response_body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Media service communication error', [
                'service' => 'fitnease-content',
                'error' => $e->getMessage(),
                'video_id' => $videoId,
                'media_service_url' => $this->baseUrl
            ]);

            return null;
        }
    }
}