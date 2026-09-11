<?php

namespace App\Providers;

use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use App\Policies\AsistenciaAcademicaPolicy;
use App\Policies\GrupoAcademicoPolicy;
use App\Policies\PostulantePolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(GrupoAcademico::class, GrupoAcademicoPolicy::class);
        Gate::policy(Postulante::class, PostulantePolicy::class);
        Gate::policy(AsistenciaAcademica::class, AsistenciaAcademicaPolicy::class);

        ResetPassword::toMailUsing(fn (User $user, string $token) => (new MailMessage)
            ->subject('Establecer o restablecer tu contraseña de INTELECTA')
            ->line('Usa este enlace para establecer o restablecer tu contraseña. Nunca compartas el enlace ni tu contraseña.')
            ->action('Establecer contraseña', url(route('password.reset', ['token' => $token, 'email' => $user->email], false)))
            ->line('El enlace es de un solo uso y vence en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
            ->line('Si no esperabas este mensaje, puedes ignorarlo y consultar con TI.'));
        VerifyEmail::toMailUsing(fn (User $user, string $url) => (new MailMessage)
            ->subject('Verifica tu correo de acceso a INTELECTA')
            ->line('Verifica este correo para completar la activación de tu cuenta.')
            ->action('Verificar correo', $url)
            ->line('Esta verificación no desbloquea cuentas suspendidas ni asigna roles o expedientes.'));

        Gate::before(
            fn (User $user): ?bool => $user->hasRole('Super Administrador')
                ? true
                : null,
        );

        Vite::prefetch(concurrency: 3);
    }
}
