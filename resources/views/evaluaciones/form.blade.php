<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Evaluación del Proveedor</title>

    <!-- Tailwind moderno -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Animaciones -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        .input-modern {
            @apply w-full border border-gray-300 rounded-lg px-4 py-2 transition shadow-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-500;
        }

        .label-modern {
            @apply font-semibold text-gray-700 mb-1 block;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-6 bg-gradient-to-br from-blue-50 to-gray-100">

    <div class="w-full max-w-3xl p-8 bg-white shadow-xl rounded-2xl animate-fadeIn">

        <!-- Encabezado con ilustración -->
        <div class="mb-6 text-center">
            <lottie-player src="https://assets8.lottiefiles.com/private_files/lf30_hk8c2g7g.json" background="transparent"
                speed="1" style="width: 150px; margin:auto;" loop autoplay>
            </lottie-player>

            <h1 class="mb-2 text-3xl font-bold text-gray-800">Evaluación del Proveedor</h1>
            <p class="text-gray-600">
                Responsable: <strong>{{ $responsable->nombre }}</strong>
            </p>
            <p class="text-gray-600">
                Proveedor: <strong>{{ $ev->proveedor->nombre }}</strong>
            </p>
        </div>

        <form method="POST"
            action="/evaluador/{{ $responsable->token_publico }}/evaluacion/{{ $ev->id }}">
            @csrf

            <!-- Sección de preguntas -->
            <div class="space-y-5">

                @php
                    $preguntas = [
                        1 => '¿Tiene precios competitivos para su servicio?',
                        2 => '¿Sus tiempos de respuesta se adecuan a nuestras necesidades?',
                        3 => '¿Suministra información técnica apropiada?',
                        4 => '¿Brinda la asesoría requerida?',
                        5 => '¿Conoce bien su servicio?',
                        6 => '¿Asiste a reuniones solicitadas específicamente?',
                        7 => '¿Plantea innovaciones y mejoras en su servicio?',
                        8 => '¿Es oportuno en la solución de quejas o reclamos?',
                        9 => '¿Ofrece garantía de los productos y/o servicios?',
                        10 => '¿Es amable en la atención del servicio?',
                        11 => '¿La calidad del servicio cumple con lo requerido?',
                    ];

                    $opciones = [
                        'na' => 'N/A',
                        0 => 'No cumple',
                        1 => 'Cumple parcialmente',
                        2 => 'Cumple',
                    ];
                @endphp

                @foreach ($preguntas as $num => $texto)
                    <div class="p-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                        <label class="label-modern">{{ $num }}. {{ $texto }}</label>
                        <select name="pregunta_{{ $num }}" class="input-modern" required>
                            <option value="">Seleccione</option>
                            @foreach ($opciones as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                <!-- Puntos adicionales -->
                <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-2">
                    <div class="p-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                        <label class="label-modern">Puntos adicionales</label>
                        <input type="number" min="0" name="puntos_adicionales" value="0" class="input-modern">
                    </div>

                    <div class="p-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                        <label class="label-modern">Por concepto de</label>
                        <input type="text" name="concepto_adicional" class="input-modern">
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="p-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                    <label class="label-modern">Observaciones</label>
                    <textarea name="observaciones" rows="3" class="input-modern"></textarea>
                </div>

                <!-- Firma moderna -->
                <div class="p-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                    <label class="label-modern">Firma del responsable</label>

                    <div class="p-3 bg-white border shadow-inner rounded-xl">
                        <canvas id="firmaCanvas" width="500" height="180"
                            class="w-full border rounded-lg shadow"></canvas>
                        <button type="button" id="clearBtn"
                            class="px-3 py-1 mt-3 text-sm bg-gray-200 rounded-lg shadow hover:bg-gray-300">
                            Limpiar firma
                        </button>
                    </div>

                    <input type="hidden" id="firmaInput" name="firma">
                </div>

                <!-- Botón principal -->
                <button
                    class="w-full py-3 mt-6 text-xl font-semibold text-white transition bg-blue-600 shadow-lg rounded-xl hover:bg-blue-700 hover:shadow-xl">
                    Guardar Evaluación ✔
                </button>

            </div>
        </form>
    </div>


    <!-- Script de firma digital -->
    <script>
        const canvas = document.getElementById('firmaCanvas');
        const ctx = canvas.getContext('2d');
        let dibujando = false;

        canvas.addEventListener('mousedown', e => {
            dibujando = true;
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
        });

        canvas.addEventListener('mousemove', e => {
            if (!dibujando) return;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';
            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.stroke();
        });

        canvas.addEventListener('mouseup', () => dibujando = false);
        canvas.addEventListener('mouseleave', () => dibujando = false);

        document.getElementById('clearBtn').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('firmaInput').value = '';
        });

        document.querySelector('form').addEventListener('submit', () => {
            document.getElementById('firmaInput').value = canvas.toDataURL('image/png');
        });
    </script>

</body>

</html>
