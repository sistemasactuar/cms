<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acceso Responsable</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center min-h-screen p-6 bg-gradient-to-br from-blue-100 to-blue-300">

    <div class="w-full max-w-md p-8 bg-white shadow-xl rounded-2xl">

        <div class="mb-6 text-center">
            <lottie-player src="https://assets4.lottiefiles.com/packages/lf20_puciaact.json"
                background="transparent" speed="1" style="width: 150px; margin:auto;" loop autoplay>
            </lottie-player>

            <h1 class="mt-2 text-2xl font-bold text-gray-800">Portal de Evaluación</h1>
            <p class="text-sm text-gray-600">Responsable: <strong>{{ $responsable->nombre }}</strong></p>
        </div>

        @if (session('error'))
            <div class="p-3 mb-4 text-center text-red-600 bg-red-100 rounded shadow">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST">
            @csrf

            <label class="block mb-4">
                <span class="font-semibold text-gray-700">Ingrese su clave</span>
                <input type="password" name="clave"
                    class="w-full px-4 py-3 border rounded-lg shadow focus:ring-2 focus:ring-blue-400" required>
            </label>

            <button
                class="w-full py-3 text-lg font-semibold text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                Entrar
            </button>

        </form>
    </div>

</body>
</html>
