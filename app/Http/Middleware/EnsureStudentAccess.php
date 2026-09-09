<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user?->hasRole('Estudiante')) {
            return $this->forbidden($request, 'No cuentas con permisos para acceder al portal estudiantil.');
        }

        // A fresh FK lookup also excludes archived records. Never fall back to contact data.
        $postulante = $user->postulante()->first();
        if (! $postulante) {
            return $this->forbidden($request, 'Tu cuenta todavía no está vinculada a un expediente académico disponible. Consulta con administración académica.');
        }

        $user->setRelation('postulante', $postulante);

        return $next($request);
    }

    private function forbidden(Request $request, string $message): Response
    {
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => $message], 403);
        }

        return Inertia::render('Errors/Forbidden', ['message' => $message])
            ->toResponse($request)->setStatusCode(403);
    }
}
