@extends('tarjeta-digital.layout', ['title' => 'Descargar Tarjeta Digital'])

@section('content')
    @php
        $primaryRecord = $records->first();
        $nombreCompleto = trim(($primaryRecord->nombres ?? '') . ' ' . ($primaryRecord->apellidos ?? ''));
        $nombreCompleto = $nombreCompleto !== '' ? $nombreCompleto : 'Cliente Actuar';
        $documento = (string) $primaryRecord->cc;
        $maskedDocumento = strlen($documento) > 4
            ? str_repeat('*', max(strlen($documento) - 4, 0)) . substr($documento, -4)
            : $documento;
        $cantidadTarjetas = $records->count();
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-emerald-800">
                Consulta confirmada
            </span>

            <h2 class="mt-4 text-2xl font-bold tracking-[-0.03em] text-slate-900 md:text-3xl">
                {{ $cantidadTarjetas === 1 ? 'Tu tarjeta digital esta lista para descargar' : 'Tus tarjetas digitales estan listas para descargar' }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 md:text-base">
                Durante los proximos {{ $accessTtlMinutes }} minutos podras descargar {{ $cantidadTarjetas === 1 ? 'tu tarjeta' : 'tus tarjetas' }} desde este dispositivo.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Cliente</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $nombreCompleto }}</p>
                </div>

                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Documento</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $maskedDocumento }}</p>
                </div>

                <div class="rounded-[24px] bg-slate-50 px-5 py-5">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Tarjetas encontradas</span>
                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $cantidadTarjetas }}</p>
                </div>
            </div>

            <div class="mt-8 space-y-4">
                @foreach ($records as $record)
                    @php
                        $valorTarjeta = $record->valor_cuota ?? $record->valor_reportar ?? 0;
                    @endphp

                    <div class="rounded-[24px] border border-slate-200 bg-white px-5 py-5 shadow-[0_18px_35px_rgba(15,40,71,0.06)]">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div class="grid gap-3 sm:grid-cols-3 md:flex-1">
                                <div>
                                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Credito</span>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">{{ $record->obligacion }}</p>
                                </div>

                                <div>
                                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Valor cuota</span>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">${{ number_format((float) $valorTarjeta, 0, ',', '.') }}</p>
                                </div>

                                <div>
                                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Fecha pago</span>
                                    <p class="mt-2 text-lg font-semibold text-slate-900">
                                        {{ optional($record->fecha_vigencia)->format('d/m/Y') ?? 'No definida' }}
                                    </p>
                                </div>
                            </div>

                            <a href="{{ route('tarjeta-digital.portal.download', $record) }}" class="primary-btn inline-flex items-center justify-center px-6 text-base md:min-w-[220px]">
                                Descargar tarjeta
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('tarjeta-digital.portal.logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="secondary-btn w-full px-6 text-base sm:w-auto">
                    Hacer otra consulta
                </button>
            </form>
        </section>

        <section class="glass-card rounded-[30px] p-6 md:p-8">
            <div class="rounded-[28px] bg-[linear-gradient(135deg,#0b213c_0%,#12365f_45%,#1d5fa8_100%)] p-6 text-white shadow-[0_24px_50px_rgba(11,33,60,0.22)]">
                <span class="inline-flex rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/85">
                    Resumen
                </span>

                <div class="mt-6">
                    <p class="text-sm text-white/75">{{ $cantidadTarjetas === 1 ? 'Valor de la tarjeta' : 'Tarjetas disponibles' }}</p>
                    <p class="mt-2 text-4xl font-extrabold tracking-[-0.05em]">
                        {{ $cantidadTarjetas === 1 ? '$' . number_format((float) (($primaryRecord->valor_cuota ?? $primaryRecord->valor_reportar ?? 0)), 0, ',', '.') : $cantidadTarjetas }}
                    </p>
                </div>

                <div class="mt-6 space-y-3 text-sm text-white/80">
                    <p>La descarga se realizara como una imagen.</p>
                    <p>Puedes guardarla en tu celular o compartirla cuando la necesites.</p>
                    <p>Si el acceso vence, solo debes volver a hacer la consulta.</p>
                </div>
            </div>

            <div class="mt-6 rounded-[24px] bg-slate-50 px-5 py-5">
                <h3 class="text-base font-bold text-slate-900">Sugerencia</h3>
                <p class="mt-3 text-sm leading-6 text-slate-700">
                    Descarga tu tarjeta ahora y mantenla guardada en tu dispositivo para tenerla disponible cuando la necesites.
                </p>
            </div>
        </section>
    </div>
@endsection
