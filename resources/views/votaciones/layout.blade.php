@php
    $pageTitle = $title ?? 'Portal de votaciones';
    $pageSubtitle = $subtitle ?? 'Participa de forma clara, segura y guiada.';
    $currentStep = $step ?? 1;
    $resolvedLogo = $logoUrl ?? asset('images/LOGO-03.png');
    $steps = [
        1 => 'Ingreso',
        2 => 'Orden del dia',
        3 => 'Votacion',
    ];
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-purple-900: #562f80;
            --brand-purple-700: #7446a1;
            --brand-purple-500: #9065ba;
            --brand-orange-500: #f68a1f;
            --brand-orange-300: #ffb45c;
            --brand-cream: #f4f0ef;
            --ink-900: #3d2259;
            --ink-700: #665678;
            --line: rgba(116, 70, 161, 0.14);
        }

        * {
            font-family: 'Lexend', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(246, 138, 31, 0.15), transparent 26%),
                radial-gradient(circle at bottom right, rgba(116, 70, 161, 0.16), transparent 30%),
                linear-gradient(160deg, #fcfaf8 0%, #f7f1ff 48%, #fff7ef 100%);
            color: var(--ink-900);
        }

        .shell {
            width: min(1180px, calc(100% - 1.5rem));
            margin: 0 auto;
        }

        .hero-card,
        .surface-card,
        .vote-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line);
            box-shadow: 0 20px 48px rgba(86, 47, 128, 0.08);
            backdrop-filter: blur(12px);
        }

        .hero-banner {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 82% 18%, rgba(255, 180, 92, 0.38), transparent 18%),
                radial-gradient(circle at 10% 90%, rgba(255, 255, 255, 0.08), transparent 22%),
                linear-gradient(135deg, #4d296f 0%, #7446a1 54%, #8e5fbc 76%, #f68a1f 132%);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(115deg, rgba(255, 255, 255, 0.05), transparent 32%),
                radial-gradient(circle at 76% 34%, rgba(255, 255, 255, 0.1), transparent 20%);
            pointer-events: none;
        }

        .hero-logo-shell {
            position: relative;
            display: grid;
            height: 110px;
            width: 110px;
            place-items: center;
            border-radius: 32px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(249, 241, 255, 0.96));
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow:
                0 26px 42px rgba(61, 34, 89, 0.24),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            padding: 14px;
            overflow: hidden;
        }

        .hero-logo-shell::before {
            content: '';
            position: absolute;
            inset: 10px;
            border-radius: 24px;
            border: 1px solid rgba(116, 70, 161, 0.12);
            pointer-events: none;
        }

        .hero-logo-shell img {
            position: relative;
            z-index: 1;
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(86, 47, 128, 0.12));
        }

        .mini-logo-card {
            display: grid;
            min-height: 96px;
            width: 96px;
            place-items: center;
            border-radius: 26px;
            border: 1px solid rgba(116, 70, 161, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #faf4ff 100%);
            box-shadow: 0 18px 36px rgba(86, 47, 128, 0.08);
            padding: 14px;
        }

        .mini-logo-card img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .step-pill {
            border: 1px solid rgba(255, 255, 255, 0.26);
            background: rgba(116, 70, 161, 0.06);
        }

        .step-pill.is-active {
            background: #ffffff;
            color: var(--ink-900);
            border-color: transparent;
            box-shadow: 0 12px 30px rgba(86, 47, 128, 0.16);
        }

        .primary-btn,
        .secondary-btn {
            min-height: 56px;
            border-radius: 18px;
            font-weight: 700;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--brand-purple-900) 0%, var(--brand-purple-700) 54%, var(--brand-orange-500) 120%);
            color: white;
            box-shadow: 0 16px 32px rgba(116, 70, 161, 0.24);
        }

        .secondary-btn {
            background: white;
            color: var(--brand-purple-700);
            border: 1px solid rgba(116, 70, 161, 0.18);
        }

        .primary-btn:hover,
        .secondary-btn:hover {
            transform: translateY(-1px);
        }

        .selection-card {
            position: relative;
            display: block;
            border-radius: 24px;
            border: 2px solid rgba(116, 70, 161, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #fbf8ff 100%);
            padding: 1.2rem;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
            cursor: pointer;
        }

        .selection-card:hover {
            transform: translateY(-2px);
            border-color: rgba(116, 70, 161, 0.3);
            box-shadow: 0 18px 34px rgba(116, 70, 161, 0.14);
        }

        .selection-card.is-selected {
            border-color: var(--brand-orange-500);
            box-shadow: 0 20px 36px rgba(246, 138, 31, 0.18);
            background: linear-gradient(180deg, #ffffff 0%, #fff4e8 100%);
        }

        .selection-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 22px;
            border-top: 6px solid var(--accent, var(--brand-purple-700));
            pointer-events: none;
        }

        .avatar-round {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand-purple-900) 0%, var(--brand-purple-700) 64%, var(--brand-orange-500) 120%);
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            overflow: hidden;
        }

        .badge-soft {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            padding: .55rem .9rem;
            font-size: .82rem;
            font-weight: 700;
        }

        .badge-soft.blue {
            background: #f1e8ff;
            color: #5d2f88;
        }

        .badge-soft.green {
            background: #fff2df;
            color: #a14d00;
        }

        .badge-soft.orange {
            background: #ffe7cc;
            color: #c15d00;
        }

        .badge-soft.red {
            background: #f8e7ff;
            color: #7b2cbf;
        }

        .sticky-action {
            position: sticky;
            bottom: 16px;
            z-index: 20;
        }

        .order-day-content h1,
        .order-day-content h2,
        .order-day-content h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: .85rem;
            color: var(--brand-purple-900);
        }

        .order-day-content p,
        .order-day-content li {
            color: var(--ink-700);
            line-height: 1.85;
            font-size: 1rem;
        }

        .order-day-content ul,
        .order-day-content ol {
            padding-left: 1.3rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .mobile-stack {
                grid-template-columns: 1fr !important;
            }

            .selection-card {
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="min-h-screen">
    <div class="relative py-6 md:py-8">
        <header class="shell mb-6">
            <div class="hero-card overflow-hidden rounded-[30px]">
                <div class="hero-banner grid gap-6 p-5 text-white md:grid-cols-[1.2fr_.8fr] md:p-8">
                    <div class="flex items-start gap-4">
                        <div class="hero-logo-shell shrink-0">
                            <img src="{{ $resolvedLogo }}" alt="Logo votacion">
                        </div>
                        <div class="space-y-2">
                            <span class="badge-soft blue bg-white/20 text-white">Portal de Votaciones</span>
                            <h1 class="text-2xl font-extrabold tracking-tight md:text-4xl">{{ $pageTitle }}</h1>
                            <p class="max-w-2xl text-sm leading-7 text-sky-100 md:text-base">{{ $pageSubtitle }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between gap-4 md:items-end">
                        @if(isset($aportante) && $aportante)
                            <div class="rounded-[24px] bg-white/12 px-5 py-4 text-left shadow-lg">
                                <p class="text-xs font-semibold uppercase tracking-[.22em] text-orange-100">Aportante conectado</p>
                                <p class="mt-2 text-lg font-bold">{{ $aportante->nombre }}</p>
                                <p class="text-sm text-orange-50">Documento {{ $aportante->documento }}</p>
                            </div>
                            <form method="POST" action="{{ route('votaciones.portal.logout') }}">
                                @csrf
                                <button class="secondary-btn w-full px-5 md:w-auto">Cerrar sesion</button>
                            </form>
                        @else
                            <div class="rounded-[24px] bg-white/12 px-5 py-4 text-left shadow-lg">
                                <p class="text-xs font-semibold uppercase tracking-[.22em] text-orange-100">Proceso guiado</p>
                                <p class="mt-2 text-lg font-bold">Pensado para computador y celular</p>
                                <p class="text-sm text-orange-50">Botones grandes, pasos visibles y lectura simple.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid gap-3 border-t border-slate-100 bg-white/90 p-4 md:grid-cols-3 md:p-5">
                    @foreach($steps as $key => $label)
                        <div class="step-pill {{ $currentStep === $key ? 'is-active' : '' }} rounded-[18px] px-4 py-3 text-sm font-semibold {{ $currentStep === $key ? '' : 'text-slate-500' }}">
                            <span
                                class="mr-2 inline-grid h-7 w-7 place-items-center rounded-full"
                                style="{{ $currentStep === $key ? 'background: var(--brand-purple-900); color: white;' : 'background: #fff1de; color: var(--brand-purple-700);' }}"
                            >{{ $key }}</span>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>
        </header>

        <main class="shell">
            @if (session('success'))
                <div class="surface-card mb-5 rounded-[24px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="surface-card mb-5 rounded-[24px] border border-rose-200 bg-rose-50 px-5 py-4 text-rose-900">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="surface-card mb-5 rounded-[24px] border border-amber-200 bg-amber-50 px-5 py-4 text-amber-950">
                    <p class="font-bold">Revisa estos puntos antes de continuar:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
