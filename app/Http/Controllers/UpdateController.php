<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Response;

class UpdateController extends Controller
{
    public function update()
    {
        try {
            // Cambiar al directorio raíz de tu proyecto (si es necesario)
            // shell_exec('cd /path/to/your/project');

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
