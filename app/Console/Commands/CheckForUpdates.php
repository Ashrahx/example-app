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
        $currentCommit = config('app.current_commit_hash');
        $this->info("Current commit: ".$currentCommit);
        
        $response = Http::get('https://api.github.com/repos/Ashrahx/example-app/commits', [
            'per_page' => 1,
            'sha' => 'main',
        ]);

        $this->info("GitHub API status: ".$response->status());
        
        if ($response->successful()) {
            $latestCommit = $response->json()[0]['sha'] ?? null;
            $this->info("Latest commit: ".$latestCommit);
            
            if ($latestCommit && $latestCommit !== $currentCommit) {
                cache([
                    'update_available' => true,
                    'latest_commit' => $latestCommit
                ], now()->addHours(1));
                
                $this->info("Update available! Cached successfully.");
                return true;
            }
        }
    } catch (\Exception $e) {
        $this->error("Error: ".$e->getMessage());
    }

    cache(['update_available' => false], now()->addHours(1));
    $this->info("No updates available");
    return false;
}
}