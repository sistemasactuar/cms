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
        .avatar-round { width: 80px; height: 80px; border-radius: 999px; display: grid; place-items: center; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 64%, #f59e0b 120%); overflow: hidden; }
    </style>
</head>
<body class="grid-bg min-h-screen p-6 md:p-12 flex flex-col">

    <header class="flex items-center justify-between mb-8 gap-8">
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

        @php
            $porcentajeParticipacion = $totalDelegadosHabilitados > 0 ? round(($totalParticipantes / $totalDelegadosHabilitados) * 100, 2) : 0;
        @endphp

        <div class="flex gap-12 text-right">
            <div>
                <p class="text-5xl md:text-6xl font-black text-emerald-400 leading-none">{{ $porcentajeParticipacion }}%</p>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-2">Participación</p>
            </div>
            <div>
                <p class="text-5xl md:text-6xl font-black text-indigo-400 leading-none">{{ $totalParticipantes }}</p>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.2em] mt-2">Votos emitidos</p>
            </div>
        </div>
    </header>

    <main class="flex-1 grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 overflow-y-auto pr-2">
        @foreach ($candidatos as $candidato)
            @php
                $porcentaje = $totalParticipantes > 0 ? round(($candidato->total_votos / $totalParticipantes) * 100, 2) : 0;
            @endphp
            <div class="glass-card rounded-[40px] p-6 flex flex-col justify-between transition-all hover:scale-[1.02]">
                <div class="flex items-start gap-4">
                    <div class="avatar-round flex-shrink-0 bg-slate-800">
                        @if ($candidato->foto_path)
                            <img src="{{ Storage::disk('public')->url($candidato->foto_path) }}" alt="Foto" class="h-full w-full object-cover">
                        @else
                            {{ \Illuminate\Support\Str::of($candidato->nombre)->explode(' ')->take(2)->map(fn ($fragment) => \Illuminate\Support\Str::substr($fragment, 0, 1))->implode('') }}
                        @endif
                    </div>
                    <div>
                        <span class="px-3 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2 inline-block">Candidato #{{ $candidato->numero ?: $loop->iteration }}</span>
                        <h3 class="text-xl font-extrabold leading-tight">{{ $candidato->nombre }}</h3>
                        <p class="text-indigo-400 text-sm font-bold mt-1">{{ $candidato->cargo ?: 'Candidato' }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="flex items-end justify-between mb-2">
                        <p class="text-4xl font-black text-white">{{ $candidato->total_votos }} <span class="text-xs text-slate-500 font-normal uppercase tracking-widest">votos</span></p>
                        <p class="text-lg font-bold text-slate-400">{{ $porcentaje }}%</p>
                    </div>
                    <div class="w-full bg-slate-800/50 rounded-full h-4 overflow-hidden border border-white/5">
                        <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                             style="width: {{ $porcentaje }}%; background: linear-gradient(90deg, #4f46e5, #6366f1);">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </main>

    <footer class="mt-8 flex items-center justify-between text-slate-500 text-xs font-bold uppercase tracking-widest border-t border-white/5 pt-6">
        <div>
            Ultima actualización: {{ now()->format('H:i:s') }}
        </div>
        <div>
            Sistema de Votaciones Actuar &bull; Monitor de Proyección
        </div>
    </footer>
</body>
</html>
