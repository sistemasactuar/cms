@extends('errors::layout')

@section('code', $exception->getStatusCode())
@section('title', 'Error')

@section('icon')
<svg class="w-24 h-24 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
</svg>
@endsection

@section('message')
Ha ocurrido un error inesperado. Por favor, intenta nuevamente o contacta al soporte técnico si el problema persiste.
@endsection

@section('actions')
<div class="flex gap-4 justify-center">
    <a href="{{ url('/') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
        Volver al Inicio
    </a>
    <a href="javascript:history.back()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
        Regresar
    </a>
</div>
@endsection