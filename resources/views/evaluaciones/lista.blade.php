<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Evaluaciones Asignadas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen p-6 bg-gray-100">

    <div class="max-w-4xl p-8 mx-auto bg-white shadow-xl rounded-2xl">

        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <lottie-player src="https://assets2.lottiefiles.com/packages/lf20_f2vdh1ek.json"
                    background="transparent" speed="1" style="width: 90px;" loop autoplay>
                </lottie-player>

                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Evaluaciones Asignadas</h2>
                    <p class="text-gray-600">Responsable: <strong>{{ $responsable->nombre }}</strong></p>
                </div>
            </div>

            <div>
                @if(request('historico'))
                <a href="{{ url()->current() }}"
                    class="px-4 py-2 text-sm font-medium text-blue-600 transition bg-blue-100 rounded-lg hover:bg-blue-200">
                    Ver Actuales
                </a>
                @else
                <a href="{{ url()->current() }}?historico=1"
                    class="px-4 py-2 text-sm font-medium text-gray-600 transition bg-gray-200 rounded-lg hover:bg-gray-300">
                    Ver Históricos
                </a>
                @endif
            </div>
        </div>

        @if (session('success'))
        <div class="p-3 mb-4 text-green-700 bg-green-100 rounded shadow">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid gap-4 mt-4">
            @foreach($evaluaciones as $ev)
            <div class="flex items-center justify-between p-5 transition border rounded-xl hover:shadow-lg bg-gray-50">

                <div>
                    <h3 class="text-xl font-semibold text-gray-800">{{ $ev->proveedor->nombre }}</h3>

                    <p class="mt-1">
                        {!! $ev->bloqueado
                        ? '<span class="text-lg font-semibold text-green-700">✔ Evaluado</span>'
                        : '<span class="text-lg font-semibold text-yellow-600">Pendiente</span>' !!}
                    </p>
                </div>

                <div>
                    @if(!$ev->bloqueado)
                    <a href="/evaluador/{{ $responsable->token_publico }}/evaluacion/{{ $ev->id }}"
                        class="px-5 py-2 text-lg font-semibold text-white transition bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                        Evaluar
                    </a>
                    @else
                    <span class="font-semibold text-gray-400">Bloqueado</span>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

    </div>

</body>

</html>