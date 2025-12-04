@extends('errors::layout')

@section('code', '403')
@section('title', 'Acceso Denegado')

@section('icon')
<svg class="w-24 h-24 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
</svg>
@endsection

@section('message')
No tienes permiso para acceder a este recurso. Si crees que esto es un error, por favor contacta al administrador del sistema.
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