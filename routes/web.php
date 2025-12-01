<?php

use App\Models\Responsable;
use Illuminate\Http\Request; //
use App\Models\EvaluacionProveedor;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
// PASARELA
// ...

/*
|--------------------------------------------------------------------------
| PORTAL DE EVALUADORES (Responsables externos)
|--------------------------------------------------------------------------
*/

// Login del responsable
Route::get('/evaluador/{token}', function ($token) {
    $responsable = Responsable::where('token_publico', $token)->firstOrFail();
    return view('evaluaciones.login', compact('responsable'));
});

// Validación de clave
Route::post('/evaluador/{token}', function (Request $req, $token) {
    $responsable = Responsable::where('token_publico', $token)->firstOrFail();

    if ($req->clave !== $responsable->clave_portal) {
        return back()->with('error', 'Clave incorrecta');
    }

    // Guardamos sesión del responsable externo
    session(['responsable_id' => $responsable->id]);

    return redirect("/evaluador/{$token}/lista");
});

// Lista de evaluaciones asignadas
Route::get('/evaluador/{token}/lista', function (Request $req, $token) {
    $responsable = Responsable::where('token_publico', $token)->firstOrFail();

    abort_if(session('responsable_id') !== $responsable->id, 403);

    $query = EvaluacionProveedor::where('responsable_id', $responsable->id)
        ->with('proveedor');

    // Si no pide histórico, filtramos por el año actual
    if (!$req->has('historico')) {
        $query->whereYear('fecha', date('Y'));
    } else {
        // Si pide histórico, mostramos "los demás" (años anteriores)
        $query->whereYear('fecha', '!=', date('Y'));
    }

    $evaluaciones = $query->get();

    return view('evaluaciones.lista', compact('responsable', 'evaluaciones'));
});

// Formulario de evaluación individual
Route::get('/evaluador/{token}/evaluacion/{id}', function ($token, $id) {
    $responsable = Responsable::where('token_publico', $token)->firstOrFail();

    abort_if(session('responsable_id') !== $responsable->id, 403);

    $ev = EvaluacionProveedor::where('responsable_id', $responsable->id)
        ->where('id', $id)
        ->firstOrFail();

    // bloqueado = ya diligenciado
    if ($ev->bloqueado) {
        return view('evaluaciones.bloqueado', compact('responsable', 'ev'));
    }

    return view('evaluaciones.form', compact('responsable', 'ev'));
});

// Guardar evaluación individual
Route::post('/evaluador/{token}/evaluacion/{id}', function (Request $req, $token, $id) {

    $responsable = Responsable::where('token_publico', $token)->firstOrFail();
    abort_if(session('responsable_id') !== $responsable->id, 403);

    $ev = EvaluacionProveedor::where('responsable_id', $responsable->id)
        ->where('id', $id)
        ->firstOrFail();

    if ($ev->bloqueado) {
        return back()->with('error', 'Esta evaluación ya fue firmada.');
    }

    // Guardar respuestas y firma
    foreach (range(1, 11) as $i) {
        $ev->{"pregunta_$i"} = $req->input("pregunta_$i");
    }

    $ev->observaciones       = $req->observaciones;
    $ev->puntos_adicionales  = $req->puntos_adicionales;
    $ev->concepto_adicional  = $req->concepto_adicional;
    $ev->firma               = $req->firma; // base64 de canvas

    $ev->save(); // booted() calcula y bloquea

    return redirect("/evaluador/{$token}/lista")
        ->with('success', 'Evaluación registrada correctamente.');
});

Route::get('/evaluacion/{id}/pdf', [\App\Http\Controllers\EvaluacionPdfController::class, 'pdf'])
    ->name('evaluacion.pdf');
