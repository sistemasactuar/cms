<?php

namespace App\Http\Middleware;

use App\Models\Aportante;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAportanteIsAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $aportanteId = $request->session()->get('aportante_id');

        if (!$aportanteId) {
            return redirect()
                ->route('votaciones.portal.login')
                ->with('error', 'Debes ingresar con tu documento y contrasena para continuar.');
        }

        $aportante = Aportante::query()
            ->whereKey($aportanteId)
            ->where('activo', true)
            ->first();

        if (!$aportante) {
            $request->session()->forget('aportante_id');

            return redirect()
                ->route('votaciones.portal.login')
                ->with('error', 'Tu sesion ya no es valida. Ingresa nuevamente.');
        }

        $request->attributes->set('aportante', $aportante);

        return $next($request);
    }
}
