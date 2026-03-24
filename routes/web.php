<?php

use App\Models\Responsable;
use Illuminate\Http\Request; //
use App\Models\EvaluacionProveedor;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalMediaController;
use App\Http\Controllers\PortalTarjetaDigitalController;
use App\Http\Controllers\PortalVotacionController;
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

/*
|--------------------------------------------------------------------------
| PORTAL DE VOTACIONES (Aportantes)
|--------------------------------------------------------------------------
*/

Route::get('/votaciones', [PortalVotacionController::class, 'login'])
    ->name('votaciones.portal.login');

Route::post('/votaciones/ingresar', [PortalVotacionController::class, 'authenticate'])
    ->name('votaciones.portal.authenticate');

Route::get('/portal-media/{path}', [PortalMediaController::class, 'show'])
    ->where('path', '.*')
    ->name('portal.media');

Route::get('/tarjeta-digital', [PortalTarjetaDigitalController::class, 'landing'])
    ->name('tarjeta-digital.portal.index');

Route::post('/tarjeta-digital/validar', [PortalTarjetaDigitalController::class, 'validateAccess'])
    ->name('tarjeta-digital.portal.validate');

Route::get('/tarjeta-digital/descarga', [PortalTarjetaDigitalController::class, 'accessPage'])
    ->name('tarjeta-digital.portal.show');

Route::get('/tarjeta-digital/descargar', [PortalTarjetaDigitalController::class, 'downloadCard'])
    ->name('tarjeta-digital.portal.download');

Route::post('/tarjeta-digital/salir', [PortalTarjetaDigitalController::class, 'clearAccess'])
    ->name('tarjeta-digital.portal.logout');

Route::middleware('aportante.auth')->group(function (): void {
    Route::get('/votaciones/panel', [PortalVotacionController::class, 'dashboard'])
        ->name('votaciones.portal.dashboard');

    Route::get('/votaciones/{votacion}/orden-del-dia', [PortalVotacionController::class, 'agenda'])
        ->name('votaciones.portal.agenda');

    Route::post('/votaciones/{votacion}/orden-del-dia', [PortalVotacionController::class, 'acceptAgenda'])
        ->name('votaciones.portal.agenda.accept');

    Route::get('/votaciones/{votacion}/votar', [PortalVotacionController::class, 'voteForm'])
        ->name('votaciones.portal.vote.form');

    Route::post('/votaciones/{votacion}/votar', [PortalVotacionController::class, 'submitVote'])
        ->name('votaciones.portal.vote.submit');

    Route::get('/votaciones/{votacion}/resultados', [PortalVotacionController::class, 'resultados'])
        ->name('votaciones.portal.resultados');

    Route::post('/votaciones/salir', [PortalVotacionController::class, 'logout'])
        ->name('votaciones.portal.logout');
});

Route::get('/votaciones/{votacion}/monitoreo', [PortalVotacionController::class, 'monitor'])
    ->name('votaciones.portal.monitor');
