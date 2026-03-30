<x-filament::page>
    <div x-data="{ activeTab: 'general' }" class="space-y-6">

        {{-- Search Section --}}
        <div class="flex flex-col items-center justify-center p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Consultar Cliente</h2>
            <form wire:submit.prevent="buscar" class="w-full max-w-2xl flex gap-x-4">
                <div class="flex-1">
                    <label for="identificacion" class="sr-only">Número de Documento</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <x-heroicon-m-magnifying-glass class="w-5 h-5 text-gray-400" />
                        </div>
                        <input
                            wire:model.defer="identificacion"
                            type="text"
                            id="identificacion"
                            class="block w-full rounded-lg border-0 py-3 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-gray-950 dark:text-white dark:ring-white/10 dark:focus:ring-primary-500"
                            placeholder="Ingrese el número de documento del cliente...">
                    </div>
                    @error('identificacion') <span class="mt-2 text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Buscar</span>
                    <span wire:loading><x-heroicon-m-arrow-path class="animate-spin h-5 w-5" /></span>
                </button>
            </form>
        </div>

        @if($searched && !$loading && !$cliente)
        <div class="p-6 text-center bg-white rounded-lg shadow dark:bg-gray-800">
            <x-heroicon-o-face-frown class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No encontrado</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontró información para el documento ingresado.</p>
        </div>
        @endif

        @if($cliente)
        {{-- Client Header Summary --}}
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $infoBasica['PRIMER_NOMBRE_CLIENTE'] ?? $infoBasica['PRIMER_NOMBRE'] ?? '' }}
                    {{ $infoBasica['PRIMER_APELLIDO_CLIENTE'] ?? $infoBasica['PRIMER_APELLIDO'] ?? 'Cliente' }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    ID: {{ $identificacion }}
                </p>
            </div>
            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-400/10 dark:text-green-400">
                Activo
            </span>
        </div>

        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                @foreach(['general' => 'General', 'contacto' => 'Contacto', 'laboral' => 'Laboral', 'obligaciones' => 'Obligaciones', 'otros' => 'Otros'] as $key => $label)
                <button
                    @click="activeTab = '{{ $key }}'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400 dark:border-primary-400': activeTab === '{{ $key }}', 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== '{{ $key }}' }"
                    class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    {{ $label }}
                </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Contents --}}
        <div class="mt-6">

            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Información Básica</x-slot>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 lg:grid-cols-3">
                        @if($infoBasica)
                        @foreach($infoBasica as $key => $value)
                        @if(is_string($value) || is_numeric($value))
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ ucwords(strtolower(str_replace('_', ' ', $key))) }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value }}</dd>
                        </div>
                        @endif
                        @endforeach
                        @else
                        <div class="col-span-3 text-sm text-gray-500">No disponible</div>
                        @endif
                    </dl>
                </x-filament::section>
            </div>

            {{-- Contacto Tab --}}
            <div x-show="activeTab === 'contacto'" style="display: none;">
                <x-filament::section>
                    <x-slot name="heading">Información de Contacto</x-slot>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        @if($direccion)
                        @foreach($direccion as $key => $value)
                        @if(is_string($value) || is_numeric($value))
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ ucwords(strtolower(str_replace('_', ' ', $key))) }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value }}</dd>
                        </div>
                        @endif
                        @endforeach
                        @else
                        <div class="text-sm text-gray-500">No hay datos de dirección</div>
                        @endif
                    </dl>
                </x-filament::section>
            </div>

            {{-- Laboral Tab --}}
            <div x-show="activeTab === 'laboral'" style="display: none;">
                <x-filament::section>
                    <x-slot name="heading">Información Laboral</x-slot>
                    <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-4 rounded-md overflow-auto border dark:border-gray-700">
                    {{ json_encode($infoLaboral, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                    </pre>
                </x-filament::section>
            </div>

            {{-- Obligaciones Tab --}}
            <div x-show="activeTab === 'obligaciones'" style="display: none;">
                <x-filament::section>
                    <x-slot name="heading">Obligaciones Financieras</x-slot>
                    @if(count($obligaciones) > 0)
                    <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    @foreach(array_keys($obligaciones[0]) as $header)
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                        {{ ucwords(strtolower(str_replace('_', ' ', $header))) }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($obligaciones as $obl)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    @foreach($obl as $val)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(is_array($val))
                                        <span class="text-xs font-mono text-gray-500">{{ json_encode($val) }}</span>
                                        @else
                                        {{ $val }}
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-10">
                        <x-heroicon-o-document-currency-dollar class="mx-auto h-12 w-12 text-gray-300" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Sin Obligaciones</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">El cliente no tiene obligaciones registradas.</p>
                    </div>
                    @endif
                </x-filament::section>
            </div>

            {{-- Otros Tab --}}
            <div x-show="activeTab === 'otros'" style="display: none;" class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Vinculación</x-slot>
                    <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-4 rounded-md overflow-auto border dark:border-gray-700">
                    {{ json_encode($vinculacion, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                    </pre>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Estatutaria</x-slot>
                    <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-4 rounded-md overflow-auto border dark:border-gray-700">
                    {{ json_encode($estatutaria, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                    </pre>
                </x-filament::section>
            </div>

        </div>
        @endif
    </div>
</x-filament::page>
