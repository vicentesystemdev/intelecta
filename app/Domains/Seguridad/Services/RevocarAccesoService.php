<?php

namespace App\Domains\Seguridad\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class RevocarAccesoService
{
    public const SESSION_KEY = 'auth.version_acceso';

    /** Caller must lock the user inside a transaction. No driver-specific session scan. */
    public function execute(User $user, bool $resetTokens = true): void
    {
        if ($resetTokens) {
            Password::broker()->deleteToken($user);
        }
        $user->forceFill([
            'version_acceso' => $user->version_acceso + 1,
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
        // All other drivers, and in-flight database sessions, are invalidated by version.
        // The middleware terminates a revoked current session on its next request.
    }
}
