<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Corporación Actuar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 flex items-center justify-center p-6">
    <div class="max-w-2xl w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/LOGO-03.png') }}" alt="Corporación Actuar" class="h-20 mx-auto mb-4">
            <h1 class="text-6xl font-bold text-gray-800 mb-2">@yield('code')</h1>
            <h2 class="text-2xl font-semibold text-gray-700">@yield('title')</h2>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <div class="text-center">
                <div class="mb-6">
                    @yield('icon')
                </div>
                <p class="text-lg text-gray-600 mb-8">
                    @yield('message')
                </p>
                @yield('actions')
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Corporación Actuar Famiempresas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>

</html>