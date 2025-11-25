<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Evaluación del Proveedor</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lottie -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        /* Inputs modernos */
        .input-modern {
            @apply w-full border border-gray-300 rounded-lg px-4 py-2 shadow-sm transition focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
        }

        .label-modern {
            @apply font-semibold text-gray-700 block mb-1;
        }

        /* Móviles */
        @media (max-width: 640px) {
            .input-modern {
                font-size: 18px !important;
                padding: 14px !important;
            }
            select, textarea {
                font-size: 18px !important;
            }
            label {
                font-size: 17px !important;
            }
            #firmaCanvas {
                height: 260px !important;
            }
        }
    </style>
</head>

<body class="flex justify-center min-h-screen p-4 bg-gradient-to-br from-blue-50 to-gray-100">

    <div class="w-full max-w-3xl p-6 bg-white shadow-xl rounded-2xl md:p-10">

        <!-- ENCABEZADO -->
        <div class="mb-6 text-center">
            <lottie-player src="https://assets8.lottiefiles.com/private_files/lf30_hk8c2g7g.json"
                style="width: 150px; margin:auto;"
                background="transparent" speed="1" loop autoplay>
            </lottie-player>

            <h1 class="text-3xl font-bold text-gray-800">Evaluación del Proveedor</h1>

            <p class="mt-1 text-gray-600">
                Responsable: <strong>{{ $responsable->nombre }}</strong>
            </p>
            <p class="text-gray-600">
                Proveedor: <strong>{{ $ev->proveedor->nombre }}</strong>
            </p>
        </div>

        <!-- FORMULARIO -->
        <form method="POST" action="/evaluador/{{ $responsable->token_publico }}/evaluacion/{{ $ev->id }}">
            @csrf

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

            <!-- PREGUNTAS -->
            @foreach ($preguntas as $num => $texto)
                <div class="p-4 mb-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                    <label class="label-modern">{{ $num }}. {{ $texto }}</label>
                    <select name="pregunta_{{ $num }}" required class="input-modern">
                        <option value="">Seleccione</option>
                        @foreach ($opciones as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <!-- Puntos adicionales -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

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
            <div class="p-4 mt-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                <label class="label-modern">Observaciones</label>
                <textarea name="observaciones" class="input-modern" rows="3"></textarea>
            </div>

            <!-- FIRMA -->
            <div class="p-4 mt-4 transition border bg-gray-50 rounded-xl hover:shadow-md">
                <label class="label-modern">Firma del responsable</label>

                <canvas id="firmaCanvas"
                        width="500"
                        height="180"
                        class="w-full bg-white border rounded-lg shadow">
                </canvas>

                <button type="button" id="clearBtn"
                    class="px-4 py-2 mt-3 text-sm bg-gray-200 rounded-lg shadow hover:bg-gray-300">
                    Limpiar firma
                </button>

                <input type="hidden" name="firma" id="firmaInput">
            </div>

            <!-- Botón -->
            <button
                class="w-full py-3 mt-6 text-xl font-semibold text-white transition bg-blue-600 shadow-lg hover:bg-blue-700 rounded-xl">
                Guardar evaluación ✔
            </button>

        </form>
    </div>

    <!-- SCRIPT FIRMA -->
    <script>
        const canvas = document.getElementById('firmaCanvas');
        const ctx = canvas.getContext('2d');
        let dibujando = false;

        function pos(e) {
            const r = canvas.getBoundingClientRect();
            return e.touches
                ? { x: e.touches[0].clientX - r.left, y: e.touches[0].clientY - r.top }
                : { x: e.offsetX, y: e.offsetY };
        }

        // Mouse
        canvas.addEventListener("mousedown", e => { dibujando = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
        canvas.addEventListener("mousemove", e => { if (dibujando) { const p = pos(e); ctx.lineWidth = 2; ctx.lineCap = "round"; ctx.strokeStyle = "#000"; ctx.lineTo(p.x, p.y); ctx.stroke(); }});
        canvas.addEventListener("mouseup", () => dibujando = false);

        // Touch
        canvas.addEventListener("touchstart", e => { e.preventDefault(); dibujando = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
        canvas.addEventListener("touchmove", e => { e.preventDefault(); if (dibujando) { const p = pos(e); ctx.lineWidth = 2; ctx.lineCap = "round"; ctx.strokeStyle = "#000"; ctx.lineTo(p.x, p.y); ctx.stroke(); }});
        canvas.addEventListener("touchend", () => dibujando = false);

        // Limpiar
        document.getElementById("clearBtn").addEventListener("click", () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById("firmaInput").value = "";
        });

        // Guardar firma
        document.querySelector("form").addEventListener("submit", () => {
            document.getElementById("firmaInput").value = canvas.toDataURL("image/png");
        });
    </script>

</body>
</html>
