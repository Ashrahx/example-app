<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <h1>HOLAAAAA PEDROOOOOO</h1>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                    @if(isset($hasChanges) && $hasChanges)
                        <form id="updateButton" action="{{ route('update.system') }}" method="get" style="display: none;">
                            <button type="submit" class="btn btn-primary">Actualizar Sistema</button>
                        </form>
                    @else
                        <p>No hay cambios disponibles en GitHub.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        setInterval(function() {
    fetch("{{ route('check.update') }}")
        .then(response => response.json())
        .then(data => {
            if (data.hasChanges) {
                // Mostrar el botón si hay cambios
                document.getElementById('updateButton').style.display = 'block';
            } else {
                // Ocultar el botón si no hay cambios
                document.getElementById('updateButton').style.display = 'none';
            }
        });
}, 60000);  // Comprobar cada 60 segundos

    </script>
</x-app-layout>
