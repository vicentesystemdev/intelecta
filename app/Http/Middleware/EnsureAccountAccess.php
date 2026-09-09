<?php

namespace App\Http\Middleware;

use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\RevocarAccesoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }
        $user = $request->user()->fresh();
        $version = $request->session()->get(RevocarAccesoService::SESSION_KEY);
        $recaller = explode('|', (string) $request->cookie(Auth::guard()->getRecallerName()));
        $currentRemember = Auth::viaRemember() && isset($recaller[1]) && $user?->getRememberToken()
            && hash_equals($user->getRememberToken(), $recaller[1]);
        // Existing sessions survive the additive migration (version zero only).
        // A valid current remember cookie may establish a new session at any version.
        $mayInitialize = $version === null && ($user?->version_acceso === 0 || $currentRemember);
        if (! $user || $user->estado_cuenta === EstadoCuenta::BLOQUEADA
            || (! $mayInitialize && (string) $version !== (string) $user->version_acceso)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'La sesión ya no está disponible. Inicia sesión nuevamente.'], 401)
                : redirect()->route('login')->withErrors(['email' => 'La sesión ya no está disponible. Inicia sesión nuevamente.']);
        }
        Auth::setUser($user);
        if ($mayInitialize) {
            $request->session()->put(RevocarAccesoService::SESSION_KEY, $user->version_acceso);
        }

        if (! $user->cuentaActiva() && ! $request->routeIs(
            'verification.notice', 'verification.verify', 'verification.send', 'password.update', 'password.confirm', 'password.confirm.store', 'logout',
        )) {
            return $request->expectsJson() && ! $request->header('X-Inertia')
                ? response()->json(['message' => 'Debes verificar tu correo para completar la activación de tu cuenta.'], 403)
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
