<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    public function check()
    {
        $updateAvailable = cache('update_available', false);
        
        return response()->json([
            'update_available' => $updateAvailable,
            'current_version' => config('app.current_commit_hash'),
            'latest_version' => cache('latest_commit')
        ]);
    }

    public function update()
    {
        if (!cache('update_available', false)) {
            return response()->json(['error' => 'No updates available'], 400);
        }

        try {
            $process = new Process(['git', 'pull', 'origin', 'main']);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Actualizar el hash del commit actual en la configuración
            $output = $process->getOutput();
            if (preg_match('/[a-f0-9]{40}/', $output, $matches)) {
                $newCommitHash = $matches[0];
                $this->updateConfig($newCommitHash);
            }

            // Limpiar caché y optimizar
            Artisan::call('optimize:clear');

            cache(['update_available' => false], now()->addHours(1));

            return response()->json([
                'success' => true,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    protected function updateConfig($newCommitHash)
    {
        $configPath = config_path('app.php');
        $content = file_get_contents($configPath);
        $content = preg_replace(
            "/'current_commit_hash' => '.*?'/",
            "'current_commit_hash' => '{$newCommitHash}'",
            $content
        );
        file_put_contents($configPath, $content);
    }
}