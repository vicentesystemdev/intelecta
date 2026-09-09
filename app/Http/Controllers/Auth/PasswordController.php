<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Seguridad\Services\CuentaService;
use App\Domains\Seguridad\Services\RevocarAccesoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request, CuentaService $accounts): RedirectResponse
    {
        $validated = $request->validated();

        $user = $accounts->changeOwnPassword($request->user(), $validated['password']);
        $request->session()->regenerate();
        $request->session()->put(RevocarAccesoService::SESSION_KEY, $user->version_acceso);

        return back();
    }
}
