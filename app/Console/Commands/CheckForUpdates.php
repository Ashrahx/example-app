<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckForUpdates extends Command
{
    protected $signature = 'app:check-for-updates';
    protected $description = 'Check for GitHub repository updates';

    public function handle()
    {
        try {
            $response = Http::get('https://api.github.com/repos/Ashrahx/example-app/commits', [
                'per_page' => 1,
                'sha' => config('app.current_commit_hash'),
            ]);

            if ($response->successful()) {
                $latestCommit = $response->json()[0]['sha'] ?? null;
                $currentCommit = config('app.current_commit_hash');

                if ($latestCommit && $latestCommit !== $currentCommit) {
                    cache(['update_available' => true, 'latest_commit' => $latestCommit], now()->addHours(1));
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking for updates: ' . $e->getMessage());
        }

        cache(['update_available' => false], now()->addHours(1));
        return false;
    }
}