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
        $projectPath = base_path();
        
        // 1. Crear script batch temporal
        $scriptContent = "@echo off\n".
                        "cd /d \"{$projectPath}\"\n".
                        "\"C:\\Program Files\\Git\\bin\\git.exe\" pull origin main 2>&1\n".
                        "echo %errorlevel%";
        
        $scriptPath = storage_path('app/update_script.bat');
        file_put_contents($scriptPath, $scriptContent);
        
        // 2. Ejecutar el script
        $process = new Process(['cmd', '/c', $scriptPath]);
        $process->setTimeout(300);
        $process->run();
        
        $output = $process->getOutput();
        $exitCode = $process->getExitCode();
        
        // 3. Eliminar script temporal
        unlink($scriptPath);
        
        if ($exitCode !== 0) {
            throw new \RuntimeException("Git pull failed with code {$exitCode}: {$output}");
        }
        
        // 5. Actualizar commit en configuración
        $newCommit = trim(exec('git rev-parse HEAD'));
        $this->updateConfig($newCommit);
        
        // 6. Limpiar caché
        \Artisan::call('optimize:clear');
        cache(['update_available' => false], now()->addHours(1));
        
        return response()->json([
            'success' => true,
            'output' => $output,
            'new_commit' => $newCommit
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error durante la actualización: ' . $e->getMessage(),
            'success' => false
        ], 500);
    }
}

private function runComposerUpdate($path)
{
    $process = new Process(['composer', 'install'], $path);
    $process->setTimeout(300);
    $process->run();
    
    if (!$process->isSuccessful()) {
        throw new \RuntimeException("Composer install failed: " . $process->getErrorOutput());
    }
}
}