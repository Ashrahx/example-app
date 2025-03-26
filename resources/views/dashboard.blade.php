<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <p>HOLAAAA ESTOS SON CAMBIOS</p>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                    <div id="update-container" class="hidden">
    <button id="update-btn" class="btn btn-warning">
        <i class="fas fa-sync-alt"></i> Actualización disponible
    </button>
    <div id="update-progress" class="mt-2 hidden">
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
        <p class="text-center mt-2">Actualizando...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateContainer = document.getElementById('update-container');
    const updateBtn = document.getElementById('update-btn');
    const updateProgress = document.getElementById('update-progress');

    // Verificar actualización al cargar la página
    checkForUpdate();

    // Verificar periódicamente (cada 30 minutos)
    setInterval(checkForUpdate, 30 * 60 * 1000);

    function checkForUpdate() {
        fetch('/check-update')
            .then(response => response.json())
            .then(data => {
                if (data.update_available) {
                    updateContainer.classList.remove('hidden');
                } else {
                    updateContainer.classList.add('hidden');
                }
            });
    }

    updateBtn.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas actualizar la aplicación?')) {
            updateBtn.disabled = true;
            updateProgress.classList.remove('hidden');
            
            fetch('/do-update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Aplicación actualizada correctamente! La página se recargará.');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                    updateBtn.disabled = false;
                    updateProgress.classList.add('hidden');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                updateBtn.disabled = false;
                updateProgress.classList.add('hidden');
            });
        }
    });
});
</script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
