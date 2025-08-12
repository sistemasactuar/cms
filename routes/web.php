<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\ImportarPlanoCarteraController;
use App\Http\Controllers\ImportPlanoCarteraTxtController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/import/plano-cartera', [ImportarPlanoCarteraController::class, 'form'])->name('import.plano.cartera');
Route::post('/import/plano-cartera', [ImportarPlanoCarteraController::class, 'import'])->name('import.plano.cartera.save');
Route::get('/importar-plano-cartera', [ImportPlanoCarteraTxtController::class, 'form'])->name('import.plano.cartera');
Route::post('/importar-plano-cartera', [ImportPlanoCarteraTxtController::class, 'import'])->name('import.plano.cartera.post');

//pasarela multitramite para clientes
Route::get('/tramites', [TramiteController::class, 'index']);
Route::get('/tramites/{tipo}', [TramiteController::class, 'form']);
Route::post('/tramites/{tipo}', [TramiteController::class, 'store']);
