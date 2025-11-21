<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación de Proveedor - {{ $evaluacion->proveedor->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="py-8 bg-gray-100">
<div class="max-w-xl p-6 mx-auto bg-white rounded shadow-md">
    <h1 class="mb-4 text-2xl font-bold text-center">Evaluación de {{ $evaluacion->proveedor->nombre }}</h1>

    @if (session('success'))
        <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('success') }}</div>
    @endif

    <form method="POST" action="">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <label>Puntualidad <input type="number" name="puntualidad" min="1" max="5" class="w-full p-2 border rounded" value="{{ old('puntualidad', $evaluacion->puntualidad) }}"></label>
            <label>Calidad <input type="number" name="calidad" min="1" max="5" class="w-full p-2 border rounded" value="{{ old('calidad', $evaluacion->calidad) }}"></label>
            <label>Cumplimiento <input type="number" name="cumplimiento" min="1" max="5" class="w-full p-2 border rounded" value="{{ old('cumplimiento', $evaluacion->cumplimiento) }}"></label>
            <label>Atención <input type="number" name="atencion" min="1" max="5" class="w-full p-2 border rounded" value="{{ old('atencion', $evaluacion->atencion) }}"></label>
        </div>

        <div class="mt-4">
            <label>Observaciones</label>
            <textarea name="observaciones" class="w-full p-2 border rounded" rows="4">{{ old('observaciones', $evaluacion->observaciones) }}</textarea>
        </div>

        <button class="w-full py-2 mt-4 text-white bg-blue-600 rounded hover:bg-blue-700">Enviar evaluación</button>
    </form>
</div>
</body>
</html>
