<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UpdateController extends Controller
{
    public function checkForUpdate()
    {
        // Ejecuta 'git fetch' para traer los cambios sin aplicarlos
        $process = new Process(['git', 'fetch', 'origin']);
        $process->run();

        // Verifica si hay cambios pendientes
        $process = new Process(['git', 'status', '-uno']);
        $process->run();

        if (strpos($process->getOutput(), 'Your branch is behind') !== false) {
            return response()->json(['update_available' => true]);
        }

        return response()->json(['update_available' => false]);
    }

    public function applyUpdate()
    {
        $commands = [
            ['git', 'reset', '--hard'],
            ['git', 'pull', 'origin', 'main'], // Cambia 'main' si usas otra rama
            ['composer', 'install', '--no-dev', '--optimize-autoloader'],
            ['php', 'artisan', 'migrate', '--force'],
            ['php', 'artisan', 'cache:clear'],
            ['php', 'artisan', 'config:clear'],
            ['php', 'artisan', 'route:clear']
        ];

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->setTimeout(180); // 3 minutos de tiempo máximo
            $process->run();

            if (!$process->isSuccessful()) {
                return response()->json(['error' => 'Error en ' . implode(' ', $command), 'output' => $process->getErrorOutput()], 500);
            }
        }

        return response()->json(['message' => 'Actualización completada correctamente']);
    }
}
