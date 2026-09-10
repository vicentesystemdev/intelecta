<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->canManageOrganization($permission)) {
            throw new AuthorizationException('No tienes autorización para gestionar la organización institucional.');
        }

        return $next($request);
    }
}
