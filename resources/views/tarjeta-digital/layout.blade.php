<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tarjeta Digital' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-navy: #0f2847;
            --brand-blue: #1d5fa8;
            --brand-cyan: #69b8ff;
            --brand-gold: #f3a73d;
            --brand-cream: #f7f2eb;
            --ink: #17304f;
            --line: rgba(15, 40, 71, 0.12);
        }

        * {
            font-family: 'Lexend', sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(243, 167, 61, 0.18), transparent 20%),
                radial-gradient(circle at bottom right, rgba(105, 184, 255, 0.18), transparent 26%),
                linear-gradient(160deg, #f8fbff 0%, #edf4fb 45%, #fff8ef 100%);
            color: var(--ink);
        }

        .shell {
            width: min(1120px, calc(100% - 1.5rem));
            margin: 0 auto;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line);
            box-shadow: 0 24px 60px rgba(15, 40, 71, 0.10);
            backdrop-filter: blur(14px);
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 78% 22%, rgba(105, 184, 255, 0.35), transparent 18%),
                radial-gradient(circle at 18% 82%, rgba(243, 167, 61, 0.22), transparent 22%),
                linear-gradient(135deg, #0b213c 0%, #12365f 54%, #1d5fa8 100%);
        }

        .hero-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, rgba(255, 255, 255, 0.08), transparent 30%);
            pointer-events: none;
        }

        .brand-badge {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(246, 250, 255, 0.94));
            border: 1px solid rgba(255, 255, 255, 0.55);
            box-shadow: 0 18px 38px rgba(8, 28, 49, 0.20);
        }

        .primary-btn {
            background: linear-gradient(135deg, var(--brand-blue) 0%, #2f7fd4 60%, var(--brand-gold) 145%);
            color: white;
            box-shadow: 0 16px 32px rgba(29, 95, 168, 0.22);
        }

        .secondary-btn {
            background: white;
            color: var(--brand-blue);
            border: 1px solid rgba(29, 95, 168, 0.14);
        }

        .primary-btn,
        .secondary-btn {
            min-height: 54px;
            border-radius: 18px;
            font-weight: 700;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .primary-btn:hover,
        .secondary-btn:hover {
            transform: translateY(-1px);
        }

        .field-shell {
            border: 1px solid rgba(15, 40, 71, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
    </style>
</head>

<body class="py-6 md:py-10">
    <div class="shell">
        <header class="hero-panel rounded-[32px] px-6 py-7 text-white md:px-10 md:py-9">
            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/90">
                        Portal Clientes
                    </span>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-[-0.04em] md:text-5xl">
                        Descarga tu tarjeta digital
                    </h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80 md:text-base">
                        Valida tres datos del credito, habilita un acceso temporal y descarga la tarjeta lista para compartir o guardar.
                    </p>
                </div>

                <div class="brand-badge w-fit rounded-[28px] p-4 md:p-5">
                    <img src="{{ asset('images/LOGO-03.png') }}" alt="Actuar Famiempresas" class="h-14 w-auto md:h-16">
                </div>
            </div>
        </header>

        <main class="mt-6 md:mt-8">
            @if (session('success'))
                <div class="glass-card mb-4 rounded-[24px] border-emerald-200 bg-emerald-50/90 px-5 py-4 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="glass-card mb-4 rounded-[24px] border-rose-200 bg-rose-50/90 px-5 py-4 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>

</html>
