<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\CommsService;
use App\Services\MediaService;
use App\Services\CrossServiceCommunication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceTestController extends Controller
{
    protected AuthService $authService;
    protected CommsService $commsService;
    protected MediaService $mediaService;
    protected CrossServiceCommunication $crossService;

    public function __construct(
        AuthService $authService,
        CommsService $commsService,
        MediaService $mediaService,
        CrossServiceCommunication $crossService
    ) {
        $this->authService = $authService;
        $this->commsService = $commsService;
        $this->mediaService = $mediaService;
        $this->crossService = $crossService;
    }

    public function testAuthService(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            $user = $request->attributes->get('user');
            $userId = $user['user_id'] ?? 1;

            $tests = [
                'user_profile' => $this->authService->getUserProfile($userId, $token),
                'user_access_validation' => $this->authService->validateUserAccess($userId, $token)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Auth service test completed',
                'service' => 'auth',
                'results' => $tests,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auth service test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testCommsService(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            $user = $request->attributes->get('user');
            $userId = $user['user_id'] ?? 1;

            $notificationData = [
                'user_id' => $userId,
                'notification_type' => 'content_update',
                'title' => 'New Exercise Available',
                'message' => 'A new exercise has been added to your program'
            ];

            $achievementData = [
                'title' => 'Content Explorer',
                'message' => 'You have explored new exercise content!'
            ];

            $tests = [
                'send_notification' => $this->commsService->sendNotification($token, $notificationData),
                'send_achievement_notification' => $this->commsService->sendAchievementNotification($token, $userId, $achievementData)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Comms service test completed',
                'service' => 'comms',
                'results' => $tests,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comms service test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testMediaService(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            $exerciseId = 1; // Test exercise ID
            $videoId = 1; // Test video ID

            $mediaData = [
                'title' => 'Push-up Form Guide',
                'description' => 'Demonstration video for proper push-up form',
                'duration' => 180,
                'type' => 'instructional'
            ];

            $tests = [
                'get_exercise_videos' => $this->mediaService->getExerciseVideos($exerciseId, $token),
                'notify_media_upload' => $this->mediaService->notifyMediaUpload($exerciseId, $mediaData, $token),
                'get_video_streaming_url' => $this->mediaService->getVideoStreamingUrl($videoId, $token)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Media service test completed',
                'service' => 'media',
                'results' => $tests,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Media service test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testCrossServiceCommunication(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            $user = $request->attributes->get('user');
            $userId = $user['user_id'] ?? 1;
            $exerciseId = 1;

            $tests = [
                'get_exercise_videos' => $this->crossService->getExerciseVideos($exerciseId),
                'get_exercise_attributes_for_ml' => $this->crossService->getExerciseAttributesForML($exerciseId)->toArray(),
                'validate_user_permissions' => $this->crossService->validateUserPermissions($userId, 'content:read'),
                'notify_planning_service' => $this->crossService->notifyPlanningService(1)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Cross-service communication test completed',
                'service' => 'cross-service',
                'results' => $tests,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cross-service communication test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testAllServices(Request $request): JsonResponse
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            $allTests = [
                'auth_service' => $this->testAuthService($request)->getData(),
                'comms_service' => $this->testCommsService($request)->getData(),
                'media_service' => $this->testMediaService($request)->getData(),
                'cross_service_communication' => $this->testCrossServiceCommunication($request)->getData()
            ];

            $overallSuccess = true;
            foreach ($allTests as $test) {
                if (!$test->success) {
                    $overallSuccess = false;
                    break;
                }
            }

            return response()->json([
                'success' => $overallSuccess,
                'message' => $overallSuccess ? 'All service tests completed successfully' : 'Some service tests failed',
                'results' => $allTests,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service testing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}