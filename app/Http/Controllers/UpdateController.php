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
        
        // 1. Configurar Git adecuadamente
        $this->setupGitConfig($projectPath);
        
        // 2. Resetear cualquier cambio local que pueda causar conflictos
        $this->runGitCommand($projectPath, ['reset', '--hard', 'HEAD']);
        
        // 3. Limpiar el directorio de trabajo
        $this->runGitCommand($projectPath, ['clean', '-fd']);
        
        // 4. Hacer pull con estrategia para evitar conflictos
        $output = $this->runGitCommand($projectPath, ['pull', 'origin', 'main', '--no-rebase', '--strategy-option=theirs']);
        
        // 5. Actualizar dependencias
        $this->runComposerUpdate($projectPath);
        
        // 6. Actualizar configuración
        $newCommit = $this->runGitCommand($projectPath, ['rev-parse', 'HEAD']);
        $this->updateConfig(trim($newCommit));
        
        return response()->json([
            'success' => true,
            'output' => $output
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Fallo en la actualización: ' . $e->getMessage(),
            'success' => false
        ], 500);
    }
}

private function setupGitConfig($path)
{
    // Configurar Git para no interactuar con credenciales
    $this->runGitCommand($path, ['config', '--local', 'core.askpass', 'echo']);
    $this->runGitCommand($path, ['config', '--local', 'credential.helper', 'store']);
}

private function runGitCommand($path, array $commands)
{
    $process = new Process(
        array_merge(['C:\\Program Files\\Git\\bin\\git.exe'], $commands),
        $path,
        ['GIT_TERMINAL_PROMPT' => '0'], // Deshabilitar prompts
        null,
        300
    );
    
    $process->run();
    
    if (!$process->isSuccessful()) {
        throw new \RuntimeException(
            "Error en git " . implode(' ', $commands) . ": " . 
            $process->getErrorOutput() ?: $process->getOutput()
        );
    }
    
    return $process->getOutput();
}

private function runComposerUpdate($path)
{
    $process = new Process(['composer', 'install'], $path);
    $process->setTimeout(300);
    $process->run();
    
    if (!$process->isSuccessful()) {
        throw new \RuntimeException("Error en composer install: " . $process->getErrorOutput());
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