<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventario de Aplicaciones - Actuar Famiempresas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="icon" href="{{ asset('/images/ISO-3-03.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }

        .header-gradient {
            background: linear-gradient(135deg, #001a3d 0%, #003875 50%, #0056b3 100%);
            box-shadow: 0 10px 40px rgba(0, 26, 61, 0.2);
        }

        .search-container {
            position: relative;
        }

        .search-input {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 24px 16px 56px;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 8px 24px rgba(0, 86, 179, 0.15);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .app-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            overflow: hidden;
        }

        .app-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #0056b3, #003875);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .app-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 86, 179, 0.15);
            border-color: #0056b3;
        }

        .app-card:hover::before {
            transform: scaleX(1);
        }

        .app-name {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .app-description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
            min-height: 42px;
        }

        .app-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .version-badge {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #475569;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-activo {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .status-mantenimiento {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .status-obsoleto {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0056b3 0%, #003875 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 86, 179, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .modal-backdrop {
            background: rgba(0, 26, 61, 0.7);
            backdrop-filter: blur(8px);
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .resource-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 1px solid #e2e8f0;
        }

        .resource-link:hover {
            background: #f1f5f9;
            transform: translateX(4px);
            border-color: #cbd5e1;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 28px;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-state svg {
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
            opacity: 0.3;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header-gradient text-white py-8 mb-12">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold mb-2" style="letter-spacing: -0.02em;">Inventario de Aplicaciones</h1>
                    <p class="text-blue-200 text-sm font-medium">Sistema de Gestión Corporativa</p>
                </div>
                <img src="/images/LOGO-03.png" alt="Logo Actuar Famiempresas" class="h-16 drop-shadow-lg">
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-6 pb-16" x-data="inventarioApp()">
        <!-- Search Bar -->
        <div class="search-container mb-12 max-w-2xl mx-auto">
            <svg class="search-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                type="text"
                x-model="search"
                placeholder="Buscar aplicaciones por nombre, descripción o estado..."
                class="search-input w-full">
        </div>

        <!-- Apps Grid -->
        <div class="grid-container" x-show="filteredApps.length > 0">
            <template x-for="app in filteredApps" :key="app.id">
                <div class="app-card">
                    <h2 class="app-name" x-text="app.name"></h2>
                    <p class="app-description" x-text="app.description"></p>

                    <div class="app-meta">
                        <span class="version-badge">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span x-text="'v' + app.version"></span>
                        </span>

                        <span class="status-badge"
                            :class="{
                                'status-activo': app.status === 'Activo',
                                'status-mantenimiento': app.status === 'Mantenimiento',
                                'status-obsoleto': app.status === 'Obsoleto'
                            }">
                            <span class="status-dot"></span>
                            <span x-text="app.status"></span>
                        </span>
                    </div>

                    <div class="action-buttons">
                        <template x-if="app.publico">
                            <a :href="app.url" class="btn btn-primary" target="_blank">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Acceder
                            </a>
                        </template>
                        <template x-if="(app.manuals && app.manuals.length > 0) || (app.videos && app.videos.length > 0)">
                            <button @click="openModal(app)" class="btn btn-secondary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                Recursos
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredApps.length === 0" class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-xl font-semibold mb-2">No se encontraron aplicaciones</h3>
            <p>Intenta con otros términos de búsqueda</p>
        </div>

        <!-- Modal para Recursos -->
        <div x-show="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-backdrop"
            x-cloak
            @keydown.escape.window="closeModal()">

            <div class="modal-content max-w-3xl w-full max-h-[85vh] overflow-y-auto" @click.away="closeModal()">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-8 border-b border-gray-100">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-1" x-text="selectedApp?.name"></h3>
                        <p class="text-sm text-gray-500">Recursos disponibles</p>
                    </div>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-8">
                    <!-- Manuales -->
                    <template x-if="selectedApp?.manuals && selectedApp.manuals.length > 0">
                        <div class="mb-8">
                            <h4 class="font-bold text-lg mb-4 flex items-center text-gray-900">
                                <svg class="h-6 w-6 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Manuales de Usuario
                            </h4>
                            <div class="space-y-3">
                                <template x-for="manual in selectedApp.manuals" :key="manual.file">
                                    <a :href="'/storage/' + manual.file" target="_blank" class="resource-link">
                                        <svg class="h-5 w-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-700 font-medium flex-1" x-text="manual.name"></span>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Videos -->
                    <template x-if="selectedApp?.videos && selectedApp.videos.length > 0">
                        <div>
                            <h4 class="font-bold text-lg mb-4 flex items-center text-gray-900">
                                <svg class="h-6 w-6 mr-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Videos Tutoriales
                            </h4>
                            <div class="space-y-3">
                                <template x-for="video in selectedApp.videos" :key="video.url">
                                    <a :href="video.url" target="_blank" class="resource-link">
                                        <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-700 font-medium flex-1" x-text="video.name"></span>
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="p-8 border-t border-gray-100 flex justify-end">
                    <button @click="closeModal()" class="btn" style="background: #f1f5f9; color: #475569;">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</body>

</html>