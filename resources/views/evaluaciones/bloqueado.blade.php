<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Evaluación Completada</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center min-h-screen p-6 bg-gradient-to-b from-green-50 to-green-200">

    <div class="w-full max-w-md p-8 text-center bg-white shadow-xl rounded-2xl">

        <lottie-player src="https://assets4.lottiefiles.com/packages/lf20_puciaact.json"
            background="transparent" speed="1" style="width: 140px; margin:auto;" loop autoplay>
        </lottie-player>

        <h1 class="mb-3 text-3xl font-bold text-green-700">Evaluación Completa</h1>

        <p class="text-lg text-gray-700">
            Ya registraste y firmaste la evaluación para:
            <br>
            <strong>{{ $ev->proveedor->nombre }}</strong>
        </p>

        <a href="/evaluador/{{ $responsable->token_publico }}/lista"
           class="inline-block px-6 py-3 mt-6 text-lg font-semibold text-white transition bg-green-600 rounded-lg shadow hover:bg-green-700">
            Volver al listado
        </a>

    </div>

</body>
</html>
