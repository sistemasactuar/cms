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
        .winner-card { border: 3px solid #10b981 !important; box-shadow: 0 0 40px rgba(16, 185, 129, 0.3); position: relative; }
        .winner-badge { position: absolute; top: -18px; right: 40px; background: #10b981; color: white; padding: 6px 20px; border-radius: 999px; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); z-index: 10; }
        @keyframes pulse-winner { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        .winner-animate { animation: pulse-winner 2s infinite ease-in-out; }
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
        </div>
        <div class="text-right">
            @php
                $porcentajeParticipacion = $totalDelegadosHabilitados > 0 ? round(($totalVotosValidos / $totalDelegadosHabilitados) * 100, 2) : 0;
            @endphp
            <p class="text-slate-400 text-sm font-bold uppercase tracking-widest">Participación General</p>
            <div class="flex items-center justify-end gap-6 mt-1">
                <div class="text-right">
                    <p class="text-3xl md:text-4xl font-bold text-emerald-400">{{ $porcentajeParticipacion }}%</p>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Participación</p>
                </div>
                <div class="h-12 w-px bg-white/10 mx-2"></div>
                <div class="text-right">
                    <p class="text-5xl md:text-6xl font-black text-indigo-400">{{ $totalVotosValidos }}</p>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Votos emitidos</p>
                </div>
            </div>
        </div>
    </header>

    <main id="monitor-main" class="flex-1 overflow-y-auto relative scroll-smooth pr-2">
        <div id="scroll-container" class="grid gap-12 md:grid-cols-2 lg:grid-cols-3 transition-all duration-1000 ease-in-out pt-12">
            @foreach ($planillas as $planilla)
                @php
                    $datos = $distribucion[$planilla->id] ?? ['votos' => 0, 'porcentaje' => 0, 'cupos' => 0];
                    $isWinner = $loop->first && $datos['votos'] > 0;
                @endphp
                <div class="glass-card rounded-[40px] p-8 flex flex-col justify-between transition-all hover:scale-[1.02] {{ $isWinner ? 'winner-card winner-animate' : '' }}">
                    @if ($isWinner)
                        <div class="winner-badge">Tendencia Ganadora</div>
                    @endif
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-4 py-2 rounded-full bg-slate-800 text-slate-300 text-sm font-bold">Lista #{{ $planilla->numero }}</span>
                            <span class="px-5 py-2 rounded-full bg-indigo-500/20 text-indigo-300 text-lg font-black border border-indigo-500/30">
                                {{ $datos['cupos'] }} Delegados asignados
                            </span>
                        </div>
                        <h2 class="text-3xl font-extrabold leading-tight mb-2">{{ $planilla->nombre }}</h2>
                        <div class="h-1 w-24 rounded-full" style="background: {{ $planilla->color ?: '#6366f1' }}"></div>
                    </div>

                    <div class="mt-12">
                        <div class="flex items-end justify-between mb-4">
                            <div>
                                <p class="text-slate-500 font-bold uppercase text-xs tracking-widest mb-1">Votacion Alcanzada</p>
                                <p class="text-5xl font-black">{{ $datos['votos'] }}</p>
                            </div>
                            <p class="text-3xl font-bold text-slate-400">{{ $datos['porcentaje'] }}%</p>
                        </div>
                        <div class="w-full bg-slate-800/50 rounded-full h-8 overflow-hidden border border-white/5">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                                 style="width: {{ $datos['porcentaje'] }}%; background: linear-gradient(90deg, {{ $planilla->color ?: '#6366f1' }}, #818cf8);">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    @if ($isClosed)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md">
            <div class="text-center p-12 glass-card rounded-[50px] border-emerald-500/50">
                <div class="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/20">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-6xl font-black mb-4 tracking-tighter uppercase">Votación Cerrada</h2>
                <p class="text-slate-400 text-xl font-medium max-w-md mx-auto">El proceso ha concluido satisfactoriamente. Los resultados mostrados son finales.</p>
            </div>
        </div>
    @endif

    <footer class="mt-8 flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-widest border-t border-white/5 pt-6">
        <div>
            Ultima actualizacion: {{ now()->format('H:i:s') }}
        </div>
        <div>
            Sistema de Votaciones Actuar &bull; Monitor de Proyeccion
        </div>
    </footer>
    <script>
        // Auto-scroll logic for many planillas
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('monitor-main');
            const content = document.getElementById('scroll-container');
            
            if (content.scrollHeight > container.clientHeight) {
                let scrollPos = 0;
                setInterval(() => {
                    scrollPos += 1;
                    if (scrollPos > content.scrollHeight - container.clientHeight) {
                        scrollPos = -100; // Pause at top
                    }
                    container.scrollTop = scrollPos < 0 ? 0 : scrollPos;
                }, 50);
            }
        });
    </script>
</body>
</html>
