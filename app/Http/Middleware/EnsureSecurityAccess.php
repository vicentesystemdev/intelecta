<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurityAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Fresh role/account state: Gate bypass and stale session objects are not security authority.
        abort_unless($request->user() && User::find($request->user()->id)?->canChangeLoginEmail(), 403);

        return $next($request);
    }
}
