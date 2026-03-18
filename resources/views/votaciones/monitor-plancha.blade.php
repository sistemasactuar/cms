<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor - {{ $votacion->titulo }}</title>
    <meta http-equiv="refresh" content="5">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Lexend', sans-serif; background: #0f172a; color: white; overflow: hidden; }
        .grid-bg { background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0); background-size: 40px 40px; }
        .glass-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); }
        @keyframes pulse-slow { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        .live-indicator { animation: pulse-slow 2s infinite; }
        
        /* Winner Highlighting */
        .winner-card {
            border: 3px solid #10b981 !important;
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.3) !important;
            transform: scale(1.02);
            position: relative;
        }
        .winner-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 4px 15px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 50;
        }
    </style>
</head>
<body class="grid-bg min-h-screen p-6 md:p-12 flex flex-col">

    <header class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-6">
            <div class="bg-white p-3 rounded-2xl shadow-xl overflow-hidden h-24 w-24 flex items-center justify-center">
                <img src="{{ $votacion->logo_url }}" alt="Logo" class="max-h-full max-w-full">
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 live-indicator"></span>
                    <span class="text-emerald-400 font-bold tracking-widest text-xs uppercase">Monitoreo en Vivo</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-1">{{ $votacion->titulo }}</h1>
            </div>
            
            <div class="flex items-center gap-6 bg-white/10 p-5 rounded-[40px] border border-white/20 ml-12 shadow-2xl">
                <div class="bg-white p-3 rounded-3xl">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode(route('votaciones.portal.login')) }}" alt="QR Votar" class="w-28 h-28 md:w-32 md:h-32 shadow-inner">
                </div>
                <div class="max-w-[150px]">
                    <p class="text-[12px] text-emerald-400 font-extrabold uppercase tracking-[0.2em] leading-tight">ESCANEA PARA VOTAR</p>
                    <p class="text-[10px] text-slate-300 font-medium mt-2 leading-tight">Ahorra tiempo y entra directamente desde tu celular</p>
                </div>
            </div>
        </div>
        <div class="text-right">
            @php
                $porcentajeParticipacion = $totalDelegadosHabilitados > 0 ? round(($totalVotosValidos / $totalDelegadosHabilitados) * 100, 2) : 0;
            @endphp
            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">Participación General</p>
            <div class="flex items-center justify-end gap-6 mt-1">
                <div class="text-right">
                    <p class="text-3xl md:text-4xl font-bold text-emerald-400 leading-none">{{ $porcentajeParticipacion }}%</p>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-2">Participación</p>
                </div>
                <div class="h-12 w-px bg-white/10 mx-2"></div>
                <div class="text-right">
                    <p class="text-5xl md:text-6xl font-black text-indigo-400 leading-none">{{ $totalVotosValidos }}</p>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-2">Votos emitidos</p>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 relative overflow-hidden flex flex-col pt-12">
        <div id="scroll-container" class="flex-1 grid gap-12 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 overflow-y-auto pr-2 pb-12 transition-all duration-500">
            @foreach ($planillas as $planilla)
                @php
                    $datos = $distribucion[$planilla->id] ?? ['votos' => 0, 'porcentaje' => 0, 'cupos' => 0];
                    $esGanador = ($loop->first && $datos['votos'] > 0);
                @endphp
                <div class="glass-card rounded-[40px] p-8 flex flex-col justify-between transition-all hover:scale-[1.02] {{ $esGanador ? 'winner-card' : '' }}">
                    @if($esGanador)
                        <div class="winner-badge">GANADOR</div>
                    @endif

                    <div class="flex items-start gap-5">
                        <div class="h-20 w-20 rounded-3xl bg-white p-3 flex-shrink-0 shadow-lg flex items-center justify-center overflow-hidden">
                            @if ($planilla->logo_path)
                                <img src="{{ Storage::disk('public')->url($planilla->logo_path) }}" alt="Logo" class="max-h-full max-w-full">
                            @else
                                <span class="text-2xl font-black text-slate-800">#{{ $planilla->numero }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-full bg-white/5 text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2 inline-block">Plancha #{{ $planilla->numero }}</span>
                            <h3 class="text-2xl font-black leading-tight">{{ $planilla->nombre }}</h3>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-white/5">
                        <div class="flex items-end justify-between mb-3">
                            <div>
                                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mb-1">Resultado Actual</p>
                                <p class="text-5xl font-black text-white leading-none">{{ $datos['votos'] }} <span class="text-xs text-slate-500 font-normal uppercase tracking-widest ml-1">votos</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black text-indigo-400 leading-none">{{ $datos['porcentaje'] }}%</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center justify-between p-4 bg-emerald-500/10 rounded-2xl border border-emerald-500/20">
                            <p class="text-[10px] text-emerald-400 font-extrabold uppercase tracking-widest">Delegados asignados</p>
                            <p class="text-3xl font-black text-emerald-400 leading-none">{{ $datos['cupos'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($isClosed)
        <div class="absolute inset-x-0 bottom-12 flex justify-center z-50 pointer-events-none">
            <div class="bg-red-500 text-white px-12 py-4 rounded-full text-4xl font-black uppercase tracking-[0.2em] shadow-2xl animate-bounce border-4 border-white/20">
                VOTACIÓN CERRADA
            </div>
        </div>
        @endif
    </div>

    <footer class="mt-8 flex items-center justify-between text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] border-t border-white/5 pt-6">
        <div class="flex items-center gap-4">
            <span class="inline-block w-2 h-2 rounded-full bg-slate-700"></span>
            Ultima actualización: {{ now()->format('H:i:s') }}
        </div>
        <div>
            Sistema de Votaciones Actuar &bull; Monitor de Proyección
        </div>
    </footer>

    <script>
        const container = document.getElementById('scroll-container');
        let scrollAmount = 0;
        let direction = 1;
        
        function autoScroll() {
            if (!container) return;
            
            const maxScroll = container.scrollHeight - container.clientHeight;
            
            if (maxScroll <= 0) return;

            scrollAmount += 0.5 * direction;
            container.scrollTop = scrollAmount;

            if (scrollAmount >= maxScroll) {
                setTimeout(() => { direction = -1; }, 2000);
            } else if (scrollAmount <= 0) {
                setTimeout(() => { direction = 1; }, 2000);
            }
        }

        setInterval(autoScroll, 50);
    </script>
</body>
</html>
