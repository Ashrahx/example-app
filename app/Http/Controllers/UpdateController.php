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
        return response()->json(['error' => 'No hay actualizaciones disponibles'], 400);
    }

    try {
        // Especificar la ruta completa del directorio del proyecto
        $projectPath = base_path(); // Esto apunta al directorio raíz de Laravel
        
        $process = new Process([
            'C:\\Program Files\\Git\\bin\\git.exe', // Ruta completa a git
            'pull',
            'origin',
            'main'
        ], base_path());
        
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Resto del código de actualización...
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al actualizar: ' . $e->getMessage(),
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