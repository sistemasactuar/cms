<form wire:submit.prevent="importarArchivo" enctype="multipart/form-data">
    <div class="space-y-4">
        <input
            type="file"
            wire:model="archivo"
            accept=".xlsx,.xls"
            class="block w-full p-2 border border-gray-300 rounded"
        />

        @error('archivo')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div wire:loading wire:target="archivo" class="text-sm text-gray-500">
            Cargando archivo...
        </div>

        <button type="submit" class="px-4 py-2 font-bold text-white rounded bg-primary-600 hover:bg-primary-500">
            Importar
        </button>
    </div>
</form>
