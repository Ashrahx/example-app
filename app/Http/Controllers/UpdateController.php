<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Response;

class UpdateController extends Controller
{
    public function showUpdateButton()
    {
        // Ejecutar git fetch para obtener los últimos cambios
        shell_exec('git fetch origin');

        // Obtener los hashes de las últimas confirmaciones locales y remotas
        $localCommit = trim(shell_exec('git rev-parse HEAD')); 
        $remoteCommit = trim(shell_exec('git rev-parse origin/main')); // Cambia 'main' si usas otra rama

        // Verificar si los commits locales y remotos son diferentes
        $hasChanges = ($localCommit !== $remoteCommit); // true si hay cambios, false si no

        // Pasar la variable a la vista
        dd($hasChanges);
        return view('dashboard', compact('hasChanges'));
    }

    public function update()
    {
        try {
            // Hacer pull de los cambios de GitHub
            shell_exec('git pull origin main'); // Asumiendo que trabajas con la rama main

            // Actualizar las dependencias de Composer
            shell_exec('composer install --no-interaction --prefer-dist');

            // Ejecutar migraciones si es necesario
            Artisan::call('migrate');

            // Limpiar caché de Laravel (si necesario)
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return response()->json(['message' => 'Sistema actualizado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Hubo un error al actualizar el sistema: ' . $e->getMessage()], 500);
        }
    }
}
