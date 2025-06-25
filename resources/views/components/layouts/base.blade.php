<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Importar desde Excel' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- o incluye tus assets --}}
</head>
<body class="p-6">
    {{ $slot }}
</body>
</html>
