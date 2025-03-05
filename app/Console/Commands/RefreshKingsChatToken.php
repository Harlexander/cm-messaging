<?php

namespace App\Console\Commands;

use App\Models\KcHandle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshKingsChatToken extends Command
{
    protected $signature = 'kingschat:refresh-token';
    protected $description = 'Refresh KingsChat access token using stored refresh token';

    public function handle(): int
    {
        try {
            $handle = KcHandle::first();

            if (!$handle || !$handle->refresh_token) {
                $this->error('No KingsChat handle or refresh token found.');
                return Command::FAILURE;
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(config('services.kingschat.url') . '/oauth2/token', [
                'client_id' => $handle->client_id,
                'grant_type' => 'refresh_token',
                'refresh_token' => $handle->refresh_token
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to refresh token: ' . $response->body());
            }

            $data = $response->json();

            $handle->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $handle->refresh_token
            ]);

            $this->info('KingsChat token refreshed successfully.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error('Failed to refresh KingsChat token', [
                'error' => $e->getMessage()
            ]);

            $this->error('Failed to refresh token: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 