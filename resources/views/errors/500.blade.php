@extends('errors::layout')

@section('code', '500')
@section('title', 'Error del Servidor')

@section('icon')
<svg class="w-24 h-24 mx-auto text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
</svg>
@endsection

@section('message')
Algo salió mal en el servidor. Nuestro equipo ha sido notificado y estamos trabajando para resolver el problema. Por favor, intenta nuevamente más tarde.
@endsection

@section('actions')
<div class="flex gap-4 justify-center">
    <a href="{{ url('/') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
        Volver al Inicio
    </a>
    <a href="javascript:location.reload()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
        Reintentar
    </a>
</div>
@endsection