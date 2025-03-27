<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario de Aplicaciones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" href="{{ asset('/images/ISO-3-03.png') }}" type="image/x-icon">

    <style>
        .header-bg {
            background: linear-gradient(135deg, #002f6c, #004aad);
        }
        .estado-activo { background-color: #4caf50; }
        .estado-mantenimiento { background-color: #ff9800; }
        .estado-obsoleto { background-color: #f44336; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="header-bg text-white py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-3xl font-bold">Inventario de Aplicaciones</h1>
            <img src="/images/LOGO-03.png" alt="Logo" class="h-12">
        </div>
    </header>

    <div class="container mx-auto py-10 px-4" x-data="inventarioApp()">
    <input type="text" x-model="search" placeholder="Buscar aplicación..." 
           class="border px-4 py-2 mb-6 w-full rounded-md shadow-sm text-sm">
        
    <div class="overflow-x-auto">    
        <table class="min-w-full bg-white shadow-md rounded-lg text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Nombre</th>
                    <th class="py-3 px-4 text-left">Descripción</th>
                    <th class="py-3 px-4 text-left">Versión</th>
                    <th class="py-3 px-4 text-left">Estado</th>
                    <th class="py-3 px-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
    <template x-for="app in filteredApps" :key="app.id">
        <tr class="border-t hover:bg-gray-100 transition">
            <td class="py-3 px-4 font-semibold" x-text="app.name"></td>
            <td class="py-3 px-4" x-text="app.description"></td>
            <td class="py-3 px-4" x-text="app.version"></td>
            <td class="py-3 px-4">
                <span class="px-3 py-1 text-white rounded-full"
                      :class="{
                        'estado-activo': app.status === 'Activo',
                        'estado-mantenimiento': app.status === 'Mantenimiento',
                        'estado-obsoleto': app.status === 'Obsoleto'
                      }"
                      x-text="app.status"></span>
            </td>
            <td class="py-3 px-4 text-center">
                <template x-if="app.publico">
                    <a :href="app.url" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-700 transition" target="_blank">Ir</a>
                </template>
            </td>
        </tr>
    </template>
    <script>
    function inventarioApp() {
        return {
            search: '',
            apps: @json($apps),
            get filteredApps() {
                return this.apps.filter(app =>
                    app.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    app.description.toLowerCase().includes(this.search.toLowerCase()) ||
                    app.status.toLowerCase().includes(this.search.toLowerCase())
                );
            }
        }
    }
</script>

</tbody>


        </table>
    </div>
</div>
</body>
</html>
