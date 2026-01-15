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

        .estado-activo {
            background-color: #4caf50;
        }

        .estado-mantenimiento {
            background-color: #ff9800;
        }

        .estado-obsoleto {
            background-color: #f44336;
        }
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
                            <td class="py-3 px-4 text-center flex justify-center space-x-2">
                                <template x-if="app.publico">
                                    <a :href="app.url" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-700 transition" target="_blank">Ir</a>
                                </template>
                                <template x-if="(app.manuals && app.manuals.length > 0) || (app.videos && app.videos.length > 0)">
                                    <button @click="openModal(app)" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-800 transition">
                                        Recursos
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </template>
                    <script>
                        function inventarioApp() {
                            return {
                                search: '',
                                apps: @json($apps),
                                selectedApp: null,
                                isModalOpen: false,
                                get filteredApps() {
                                    return this.apps.filter(app =>
                                        app.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                        app.description.toLowerCase().includes(this.search.toLowerCase()) ||
                                        app.status.toLowerCase().includes(this.search.toLowerCase())
                                    );
                                },
                                openModal(app) {
                                    this.selectedApp = app;
                                    this.isModalOpen = true;
                                },
                                closeModal() {
                                    this.isModalOpen = false;
                                    this.selectedApp = null;
                                }
                            }
                        }
                    </script>

                </tbody>


            </table>
        </div>

        <!-- Modal para Recursos -->
        <div x-show="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
            x-cloak
            @keydown.escape.window="closeModal()">

            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.away="closeModal()">
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-xl font-bold text-blue-800" x-text="'Recursos - ' + (selectedApp?.name || '')"></h3>
                    <button @click="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Manuales -->
                    <template x-if="selectedApp?.manuals && selectedApp.manuals.length > 0">
                        <div class="mb-6">
                            <h4 class="font-bold text-lg mb-3 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Manuales
                            </h4>
                            <ul class="space-y-2">
                                <template x-for="manual in selectedApp.manuals" :key="manual.file">
                                    <li>
                                        <a :href="'/storage/' + manual.file" target="_blank" class="text-blue-600 hover:underline flex items-center">
                                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span x-text="manual.name"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Videos -->
                    <template x-if="selectedApp?.videos && selectedApp.videos.length > 0">
                        <div>
                            <h4 class="font-bold text-lg mb-3 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Videos
                            </h4>
                            <ul class="space-y-2">
                                <template x-for="video in selectedApp.videos" :key="video.url">
                                    <li>
                                        <a :href="video.url" target="_blank" class="text-red-600 hover:underline flex items-center">
                                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            <span x-text="video.name"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>

                <div class="p-6 border-t flex justify-end">
                    <button @click="closeModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</body>

</html>