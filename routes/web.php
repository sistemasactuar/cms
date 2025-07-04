<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ImportarPlanoCarteraController;
use App\Http\Controllers\ImportPlanoCarteraTxtController;
use App\Filament\Resources\Plano4111Resource\Pages\ImportarPlano4111;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/import/plano-cartera', [ImportarPlanoCarteraController::class, 'form'])->name('import.plano.cartera');
Route::post('/import/plano-cartera', [ImportarPlanoCarteraController::class, 'import'])->name('import.plano.cartera.save');
Route::get('/importar-plano-cartera', [ImportPlanoCarteraTxtController::class, 'form'])->name('import.plano.cartera');
Route::post('/importar-plano-cartera', [ImportPlanoCarteraTxtController::class, 'import'])->name('import.plano.cartera.post');

Route::get('/admin/plano4111/importar', ImportarPlano4111::class)
    ->middleware(['web', 'auth'])
    ->name('filament.admin.pages.plano4111.importar');
