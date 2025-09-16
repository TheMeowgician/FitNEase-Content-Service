<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommsService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('COMMS_SERVICE_URL', 'http://localhost:8001');
    }

    /**
     * Send notification via comms service
     */
    public function sendNotification($token, $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/api/comms/notification', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to send notification via comms service', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Comms service communication error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send achievement notification
     */
    public function sendAchievementNotification($token, $userId, $achievementData)
    {
        return $this->sendNotification($token, [
            'user_id' => $userId,
            'notification_type' => 'achievement',
            'title' => $achievementData['title'] ?? 'Achievement Unlocked!',
            'message' => $achievementData['message'] ?? 'You have unlocked a new achievement!'
        ]);
    }
}