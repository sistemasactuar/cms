<x-filament::page>
    <form wire:submit.prevent="generar" class="space-y-4">
        {{ $this->form }}

        <x-filament::button type="submit">Generar Informe</x-filament::button>

        @if ($graficas)
            <x-filament::button wire:click="exportar" color="success">Exportar a Excel</x-filament::button>

            <x-filament::card>
                <h3 class="text-lg font-bold">Obligaciones por Modalidad</h3>
                <ul>
                    @foreach($graficas['modalidad'] as $modalidad => $total)
                        <li>{{ $modalidad }}: {{ $total }}</li>
                    @endforeach
                </ul>
                @if (!empty($graficoMontoModalidad))
                    <img src="data:image/png;base64,{{ $graficoMontoModalidad }}" alt="Monto por Modalidad" class="mt-4 rounded-lg shadow" />
                @endif
            </x-filament::card>

            <x-filament::card>
                <h3 class="text-lg font-bold">Clasificación</h3>
                <ul>
                    @foreach($graficas['clasificacion'] as $clasificacion => $total)
                        <li>{{ $clasificacion }}: {{ $total }}</li>
                    @endforeach
                </ul>
                @if (!empty($graficoSaldoClasificacion))
                    <img src="data:image/png;base64,{{ $graficoSaldoClasificacion }}" alt="Saldo por Clasificación" class="mt-4 rounded-lg shadow" />
                @endif
            </x-filament::card>

            <x-filament::card>
                <h3 class="text-lg font-bold">Saldo por Nivel de Riesgo (calif_aplicada)</h3>
                <ul>
                    @foreach($graficas['riesgo_valor'] as $riesgo => $monto)
                        <li>{{ $riesgo }}: ${{ number_format($monto, 0, ',', '.') }}</li>
                    @endforeach
                </ul>
                @if (!empty($graficoSaldoRiesgo))
                    <img src="data:image/png;base64,{{ $graficoSaldoRiesgo }}" alt="Saldo por Riesgo" class="mt-4 rounded-lg shadow" />
                @endif
            </x-filament::card>
        @endif
    </form>
</x-filament::page>
