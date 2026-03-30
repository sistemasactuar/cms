<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Migracion temporal desde CodeIgniter</x-slot>
            <x-slot name="description">
                Esta opcion carga datos del modulo legacy de planoc a tablas staging del proyecto nuevo.
                No toca <code>plano_cartera</code> ni <code>plano_saldos_valores</code>.
            </x-slot>

            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-3">
                <x-filament::button wire:click="dryRun">
                    Probar conexion y conteos
                </x-filament::button>

                <x-filament::button wire:click="migrateData" color="success">
                    Ejecutar migracion staging
                </x-filament::button>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Estado del staging</x-slot>

            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Base congelamiento</div>
                    <div class="mt-2 text-2xl font-semibold">{{ number_format($stagingStats['congelamientos'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Traslados</div>
                    <div class="mt-2 text-2xl font-semibold">{{ number_format($stagingStats['traslados'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Reestructuras</div>
                    <div class="mt-2 text-2xl font-semibold">{{ number_format($stagingStats['restructuras'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Reprogramaciones</div>
                    <div class="mt-2 text-2xl font-semibold">{{ number_format($stagingStats['reprogramaciones'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Sostenibilidad</div>
                    <div class="mt-2 text-2xl font-semibold">{{ number_format($stagingStats['sosemp'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </x-filament::section>

        @if (filled($lastOutput))
            <x-filament::section>
                <x-slot name="heading">Salida del proceso</x-slot>

                <pre class="overflow-x-auto rounded-xl bg-gray-950 p-4 text-xs leading-6 text-gray-100">{{ $lastOutput }}</pre>
            </x-filament::section>
        @endif
    </div>
</x-filament::page>
