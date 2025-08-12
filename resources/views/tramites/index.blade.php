@extends('layouts.app')

@section('title', 'Trámites en Línea')

@section('content')
    <h1 class="mb-10 text-4xl font-bold text-center text-blue-800">Trámites en Línea</h1>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <a href="/tramites/preafiliacion"
           class="p-6 transition-transform transform bg-white border border-gray-200 hover:-translate-y-1 hover:shadow-lg rounded-xl hover:bg-blue-50">
            <div class="flex items-center space-x-4">
                <div class="text-3xl text-blue-600">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Solicitud Preafiliación</h3>
                    <p class="mt-1 text-sm text-gray-600">Formulario para solicitar crédito ágil.</p>
                </div>
            </div>
        </a>

        {{-- Puedes duplicar el bloque anterior para más trámites --}}
        <!-- Ejemplo adicional -->
        <!--
        <a href="/tramites/otro"
           class="p-6 transition-transform transform bg-white border border-gray-200 hover:-translate-y-1 hover:shadow-lg rounded-xl hover:bg-blue-50">
            <div class="flex items-center space-x-4">
                <div class="text-3xl text-green-600">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Otro Trámite</h3>
                    <p class="mt-1 text-sm text-gray-600">Descripción del trámite adicional.</p>
                </div>
            </div>
        </a>
        -->
    </div>
@endsection
