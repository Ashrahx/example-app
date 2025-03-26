<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <h1>HOLAAAA CAMBIOS NUEVOS ALV</h1>
    <h1>HOLAAAA CAMBIOS NUEVOS ALV</h1>
    <h1>HOLAAAA CAMBIOS NUEVOS ALV</h1>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                    <button id="updateButton" style="display: none;" onclick="applyUpdate()">Actualización disponible</button>

<script>
async function checkForUpdate() {
    const response = await fetch('/check-update');
    const data = await response.json();

    if (data.update_available) {
        document.getElementById('updateButton').style.display = 'block';
    }
}

async function applyUpdate() {
    const button = document.getElementById('updateButton');
    button.disabled = true;
    button.innerText = 'Actualizando...';

    const response = await fetch('/apply-update', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const data = await response.json();
    
    if (data.message) {
        alert('Actualización completada');
        location.reload();
    } else {
        alert('Error en la actualización: ' + data.error);
    }

    button.disabled = false;
    button.innerText = 'Actualización disponible';
}

// Revisar actualizaciones cada 5 minutos
setInterval(checkForUpdate, 300000);
checkForUpdate();
</script>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
