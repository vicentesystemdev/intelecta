<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrativeAccess
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || $user->getRoleNames()->isEmpty()) {
            throw new AuthorizationException(
                'No cuentas con permisos para acceder a este módulo.',
            );
        }

        if ($user->hasAnyRole(['Estudiante', 'Postulante'])) {
            return to_route('estudiante.evaluaciones');
        }

        return $next($request);
    }
}
