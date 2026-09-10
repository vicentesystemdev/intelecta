<?php

namespace App\Models;

use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Seguridad\Enums\EstadoCuenta;
use App\Domains\Seguridad\Services\CuentaService;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'version_acceso'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function postulante(): HasOne
    {
        return $this->hasOne(Postulante::class, 'user_id');
    }

    public function personalInstitucional(): HasOne
    {
        return $this->hasOne(PersonalInstitucional::class, 'user_id');
    }

    public function canManageOrganization(string $permission): bool
    {
        return $this->cuentaActiva()
            && $this->hasAnyRole(['Administrador', 'Super Administrador'])
            && $this->can($permission);
    }

    // Transitional TI authorization, independent of the shared CRUD permissions.
    public function canChangeLoginEmail(): bool
    {
        return $this->hasRole('Super Administrador') && $this->cuentaActiva();
    }

    public function cuentaActiva(): bool
    {
        return $this->estado_cuenta === EstadoCuenta::ACTIVA && $this->hasVerifiedEmail();
    }

    public function markEmailAsVerified(): bool
    {
        return app(CuentaService::class)->verify($this);
    }

    public function homeRoute(): string
    {
        if (! $this->cuentaActiva()) {
            return route('verification.notice');
        }

        if ($this->hasAnyRole(['Super Administrador', 'Administrador'])) {
            return route('dashboard');
        }
        if ($this->hasRole('Docente')) {
            foreach (['preguntas.ver' => 'preguntas.index', 'plantillas.ver' => 'plantillas-evaluacion.index', 'materias.ver' => 'admin.evaluaciones.materias', 'areas.ver' => 'areas-conocimiento.index', 'temas.ver' => 'temas.index', 'preguntas.crear' => 'preguntas.create'] as $permission => $route) {
                if ($this->can($permission)) {
                    return route($route);
                }
            }

            return url('/');
        }

        return $this->hasRole('Estudiante') ? route('estudiante.evaluaciones') : url('/');
    }

    public function rolesLabel(): string
    {
        return $this->getRoleNames()->sort()->values()->implode(' + ');
    }

    public function accessContext(): string
    {
        if (! $this->cuentaActiva()) {
            return 'account';
        }

        return match (true) {
            $this->hasAnyRole(['Super Administrador', 'Administrador']) => 'academic',
            $this->hasRole('Docente') => 'teacher',
            $this->hasRole('Estudiante') => 'student',
            default => 'account',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado_cuenta' => EstadoCuenta::class,
            'version_acceso' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
