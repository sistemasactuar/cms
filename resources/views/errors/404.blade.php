@extends('errors::layout')

@section('code', '404')
@section('title', 'Página No Encontrada')

@section('icon')
<svg class="w-24 h-24 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
</svg>
@endsection

@section('message')
La página que buscas no existe o ha sido movida. Verifica la URL o regresa al inicio.
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