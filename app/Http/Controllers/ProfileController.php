<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only('name'));

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['bail', 'required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        try {
            DB::transaction(function () use ($user): void {
                $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
                if ($lockedUser->postulante()->withTrashed()->exists()) {
                    throw ValidationException::withMessages([
                        'password' => 'Esta cuenta tiene un expediente académico vinculado y no puede eliminarse desde el perfil. Contacta con TI.',
                    ]);
                }
                $lockedUser->delete();
            });
        } catch (QueryException $exception) {
            // Also handle a concurrent FK conflict without logging out or returning 500.
            if (! in_array($exception->errorInfo[0] ?? null, ['23503', '23000'], true)) {
                throw $exception;
            }
            throw ValidationException::withMessages([
                'password' => 'La cuenta tiene registros vinculados y no puede eliminarse desde el perfil. Contacta con TI.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
