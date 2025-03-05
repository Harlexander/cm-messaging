<?php

namespace App\Services;

use App\Models\KcHandle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KingsChatService
{
    protected string $apiUrl;
    protected string $accessToken;

    public function __construct()
    {
        $this->apiUrl = config('services.kingschat.url', 'https://connect.kingsch.at/api');
        $this->accessToken = KcHandle::first()->access_token;
    }

    /**
     * Send a message to a KingsChat user
     */
    public function sendMessage(string $userIdentifier, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->accessToken}"
            ])->post("https://connect.kingsch.at/api/users/{$userIdentifier}/new_message", [
                'message' => [
                    'body' => [
                        'text' => [
                            'body' => $message
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => "Error: " . json_encode($response->json())
            ];

        } catch (\Exception $e) {
            Log::error('KingsChat API Error', [
                'error' => $e->getMessage(),
                'user' => $userIdentifier
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 