@extends('layouts.app')

@section('content')
<div class="max-w-3xl px-4 py-10 mx-auto">
    <h2 class="mb-10 text-3xl font-extrabold text-center text-blue-800">Solicitud de Preafiliación</h2>

    <form method="POST" action="/tramites/preafiliacion" class="p-8 space-y-6 bg-white shadow-lg rounded-xl">
        @csrf

        {{-- Campos financieros --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-input label="Monto solicitado" name="monto_solicitado" />
            <x-input label="Cuota propuesta" name="cuota_propuesta" />
        </div>

        <x-input label="Destino" name="destino" />

        {{-- Datos personales --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-input label="Nombre" name="nombre" required />
            <x-input label="Cédula" name="cedula" required />
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-input label="Dirección" name="direccion" required />
            <x-input label="Ciudad" name="ciudad" required />
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-input label="Teléfonos" name="telefonos" />
            <x-input label="Email" name="email" type="email" />
        </div>

        {{-- Vivienda --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo de vivienda</label>
            <select name="vivienda" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="Propia">Propia</option>
                <option value="Arrendada">Arrendada</option>
                <option value="Familiar">Familiar</option>
            </select>
        </div>

        {{-- Negocio --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-input label="Actividad" name="actividad" />
            <x-input label="Antigüedad" name="antiguedad" />
        </div>

        {{-- Autorización --}}
        <div class="flex items-start gap-3 mt-4">
            <input type="checkbox" name="autorizado" id="autorizado" required class="w-5 h-5 mt-1 text-blue-600 border-gray-300 rounded">
            <label for="autorizado" class="relative text-sm leading-relaxed text-gray-700 cursor-pointer group">
                Acepto términos y condiciones
                <span class="absolute hidden group-hover:block w-[400px] z-20 text-xs text-white bg-gray-900 p-4 rounded-lg shadow-lg top-full left-0 mt-2 transition-opacity duration-300 ease-in-out">
                    <strong>AUTORIZACIÓN:</strong><br>
                    Dando cumplimiento a la Ley Estatutaria 1581 de 2012, reglamentada por el Decreto 1377 de 2013, en mi calidad de titular de la información, autorizo a ACTUAR FAMIEMPRESAS para recolectar, almacenar, usar y tratar mis datos personales... <br><br>
                    [Puedes pegar el texto completo aquí. Este tooltip está diseñado para contenerlo.]
                </span>
            </label>
        </div>

        {{-- Botón --}}
        <button type="submit" class="w-full px-4 py-3 font-semibold text-white transition duration-300 bg-blue-700 rounded-lg hover:bg-blue-800">
            Enviar Solicitud
        </button>
    </form>
</div>
@endsection
