<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uso en rutas: ->middleware('rol:admin') o 'rol:admin,supervisor'.
 * Va después de auth:sanctum: si llega aquí, el usuario ya está autenticado.
 */
class AsegurarRol
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $rolUsuario = $request->user()?->rol?->value;

        if (! in_array($rolUsuario, $roles, true)) {
            return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
