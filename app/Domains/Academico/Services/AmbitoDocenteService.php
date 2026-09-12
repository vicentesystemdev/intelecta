<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Fuente única del alcance académico de un Docente.
 *
 * El vínculo autorizado siempre es User -> Personal -> Tutor -> Asignación.
 * Cargo, datos personales y roles por sí solos nunca acreditan recursos.
 */
class AmbitoDocenteService
{
    public function esGlobal(User $user, string $capacidad): bool
    {
        if ($user->hasRole('Super Administrador')) {
            return true;
        }

        return $user->roles()
            ->where('roles.name', 'Administrador')
            ->where('roles.guard_name', 'web')
            ->whereHas('permissions', fn (Builder $query) => $query
                ->where('permissions.name', $capacidad)
                ->where('permissions.guard_name', 'web'))
            ->exists();
    }

    public function esDocenteRestringido(User $user, string $capacidad): bool
    {
        return ! $this->esGlobal($user, $capacidad) && $user->hasRole('Docente');
    }

    public function grupos(Builder $query, User $user, string $capacidad = 'grupos.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->qualifyColumn('id_grupo'),
            $this->gruposAsignados($user),
        );
    }

    public function programas(Builder $query, User $user, string $capacidad = 'programas.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->qualifyColumn('id_prog'),
            $this->programasAsignados($user),
        );
    }

    public function inscripciones(
        Builder $query,
        User $user,
        string $capacidad,
        bool $soloActivas = false,
    ): Builder {
        if ($this->esGlobal($user, $capacidad)) {
            return $soloActivas
                ? $query->where('estado_inscripcion', 'activo')
                : $query;
        }

        $query->whereIn(
            $query->getModel()->qualifyColumn('id_grupo'),
            $this->gruposAsignados($user),
        );

        return $soloActivas
            ? $query->where($query->getModel()->qualifyColumn('estado_inscripcion'), 'activo')
            : $query;
    }

    public function postulantes(Builder $query, User $user, string $capacidad = 'postulantes.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->qualifyColumn('id_post'),
            $this->postulantesAsignados($user),
        );
    }

    public function asistencias(Builder $query, User $user, string $capacidad = 'asistencia.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        $query->whereIn(
            $query->getModel()->qualifyColumn('id_grupo'),
            $this->gruposAsignados($user),
        );

        $groupColumn = $query->getModel()->qualifyColumn('id_grupo');
        $postulanteColumn = $query->getModel()->qualifyColumn('id_post');

        return $query->whereExists(function (QueryBuilder $scope) use ($groupColumn, $postulanteColumn): void {
            $scope->selectRaw('1')
                ->from('inscripciones_academicas as amb_insc')
                ->whereColumn('amb_insc.id_grupo', $groupColumn)
                ->whereColumn('amb_insc.id_post', $postulanteColumn)
                ->where('amb_insc.estado_inscripcion', 'activo');
        });
    }

    public function evaluaciones(Builder $query, User $user, string $capacidad = 'resultados.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        return $query
            ->whereNotNull($query->getModel()->qualifyColumn('id_sim'))
            ->whereHas('simulacro', function (Builder $query) use ($user): void {
                $query->where(function (Builder $contexto) use ($user): void {
                    $contexto
                        ->where(function (Builder $grupo) use ($user): void {
                            $grupo->whereNotNull('simulacros_programados.id_grupo')
                                ->whereIn('simulacros_programados.id_grupo', $this->gruposAsignados($user));
                        })
                        ->orWhere(function (Builder $programa) use ($user): void {
                            $programa->whereNull('simulacros_programados.id_grupo')
                                ->whereNotNull('simulacros_programados.id_prog')
                                ->whereIn('simulacros_programados.id_prog', $this->programasAsignados($user));
                        });
                });
            });
    }

    public function rendimientos(Builder $query, User $user, string $capacidad = 'ficha-academica.ver'): Builder
    {
        if ($this->esGlobal($user, $capacidad)) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->qualifyColumn('id_grupo'),
            $this->gruposAsignados($user),
        );
    }

    public function puedeVerGrupo(
        User $user,
        GrupoAcademico|int $grupo,
        string $capacidad = 'grupos.ver',
    ): bool {
        $id = $grupo instanceof GrupoAcademico ? $grupo->getKey() : $grupo;

        return $this->grupos(GrupoAcademico::query(), $user, $capacidad)
            ->whereKey($id)
            ->exists();
    }

    public function puedeVerPrograma(
        User $user,
        ProgramaAcademico|int $programa,
        string $capacidad = 'programas.ver',
    ): bool {
        $id = $programa instanceof ProgramaAcademico ? $programa->getKey() : $programa;

        return $this->programas(ProgramaAcademico::query(), $user, $capacidad)
            ->whereKey($id)
            ->exists();
    }

    public function puedeVerPostulante(
        User $user,
        Postulante|int $postulante,
        string $capacidad = 'postulantes.ver',
    ): bool {
        $id = $postulante instanceof Postulante ? $postulante->getKey() : $postulante;

        return $this->postulantes(Postulante::query(), $user, $capacidad)
            ->whereKey($id)
            ->exists();
    }

    public function puedeVerAsistencia(
        User $user,
        AsistenciaAcademica|int $asistencia,
        string $capacidad = 'asistencia.ver',
    ): bool {
        $id = $asistencia instanceof AsistenciaAcademica ? $asistencia->getKey() : $asistencia;

        return $this->asistencias(AsistenciaAcademica::query(), $user, $capacidad)
            ->whereKey($id)
            ->exists();
    }

    public function inscripcionActivaPermitida(
        User $user,
        int $grupoId,
        int $postulanteId,
        string $capacidad,
    ): bool {
        return $this->inscripciones(
            InscripcionAcademica::query(),
            $user,
            $capacidad,
            true,
        )
            ->where('id_grupo', $grupoId)
            ->where('id_post', $postulanteId)
            ->exists();
    }

    /** @param Collection<int, int> $postulanteIds */
    public function cantidadInscripcionesActivasPermitidas(
        User $user,
        int $grupoId,
        Collection $postulanteIds,
        string $capacidad,
        bool $bloquear = false,
    ): int {
        $query = $this->inscripciones(InscripcionAcademica::query(), $user, $capacidad, true)
            ->where('id_grupo', $grupoId)
            ->whereIn('id_post', $postulanteIds);

        if ($bloquear) {
            return $query
                ->lockForUpdate()
                ->get(['id_insc', 'id_post'])
                ->pluck('id_post')
                ->unique()
                ->count();
        }

        return $query->distinct()->count('id_post');
    }

    public function tutorId(User $user, string $capacidad): ?int
    {
        if ($this->esGlobal($user, $capacidad)) {
            return null;
        }

        return TutorAcademico::query()
            ->join('personal_institucional as amb_personal', 'amb_personal.id_personal', '=', 'tutores_academicos.personal_id')
            ->where('amb_personal.user_id', $user->getKey())
            ->where('tutores_academicos.estado_tutor', 'activo')
            ->value('tutores_academicos.id_tutor');
    }

    /**
     * Bloquea las filas que acreditan el acceso docente al grupo.
     * Orden compartido con AsignacionTutorService: Tutor -> Asignación -> Grupo.
     */
    public function bloquearAutoridadDeGrupo(User $user, int $grupoId, string $capacidad): ?int
    {
        if ($this->esGlobal($user, $capacidad)) {
            return null;
        }

        $tutorId = TutorAcademico::query()
            ->join('personal_institucional as amb_personal', 'amb_personal.id_personal', '=', 'tutores_academicos.personal_id')
            ->where('amb_personal.user_id', $user->getKey())
            ->where('tutores_academicos.estado_tutor', 'activo')
            ->value('tutores_academicos.id_tutor');

        if (! $tutorId) {
            throw new AuthorizationException('El usuario no tiene un Tutor académico activo.');
        }

        $tutorVigente = TutorAcademico::query()
            ->whereKey($tutorId)
            ->where('estado_tutor', 'activo')
            ->lockForUpdate()
            ->exists();
        if (! $tutorVigente) {
            throw new AuthorizationException('El Tutor académico ya no está activo.');
        }

        $asignaciones = AsignacionTutor::query()
            ->where('id_tutor', $tutorId)
            ->where('id_grupo', $grupoId)
            ->where('estado_asig', 'activo')
            ->orderBy('id_asig')
            ->lockForUpdate()
            ->get(['id_asig']);
        if ($asignaciones->isEmpty()) {
            throw new AuthorizationException('El grupo no pertenece al ámbito académico permitido.');
        }

        return (int) $tutorId;
    }

    private function gruposAsignados(User $user): QueryBuilder
    {
        $query = $this->consultaAsignacion($user)
            ->join('grupos_academicos as amb_grupo', 'amb_grupo.id_grupo', '=', 'amb_asig.id_grupo')
            ->whereNotNull('amb_asig.id_grupo')
            ->whereNull('amb_grupo.deleted_at')
            ->select('amb_grupo.id_grupo');

        return $query;
    }

    private function programasAsignados(User $user): QueryBuilder
    {
        return $this->consultaAsignacion($user)
            ->join('programas_academicos as amb_prog', 'amb_prog.id_prog', '=', 'amb_asig.id_prog')
            ->leftJoin('grupos_academicos as amb_grupo', 'amb_grupo.id_grupo', '=', 'amb_asig.id_grupo')
            ->whereNotNull('amb_asig.id_prog')
            ->whereNull('amb_prog.deleted_at')
            ->where(function (QueryBuilder $query): void {
                $query->whereNull('amb_asig.id_grupo')
                    ->orWhere(function (QueryBuilder $grupo): void {
                        $grupo->whereColumn('amb_grupo.id_prog', 'amb_asig.id_prog')
                            ->whereNull('amb_grupo.deleted_at');
                    });
            })
            ->select('amb_prog.id_prog');
    }

    private function postulantesAsignados(User $user): QueryBuilder
    {
        return DB::table('inscripciones_academicas as amb_insc')
            ->where('amb_insc.estado_inscripcion', 'activo')
            ->whereIn('amb_insc.id_grupo', $this->gruposAsignados($user))
            ->select('amb_insc.id_post');
    }

    private function consultaAsignacion(User $user): QueryBuilder
    {
        $query = DB::table('asignaciones_tutores as amb_asig')
            ->join('tutores_academicos as amb_tutor', 'amb_tutor.id_tutor', '=', 'amb_asig.id_tutor')
            ->join('personal_institucional as amb_personal', 'amb_personal.id_personal', '=', 'amb_tutor.personal_id');

        return $this->restriccionesAsignacion($query, $user);
    }

    private function restriccionesAsignacion(QueryBuilder $scope, User $user): QueryBuilder
    {
        return $scope
            ->where('amb_personal.user_id', $user->getKey())
            ->where('amb_tutor.estado_tutor', 'activo')
            ->where('amb_asig.estado_asig', 'activo')
            ->whereNull('amb_tutor.deleted_at')
            ->whereNull('amb_asig.deleted_at');
    }
}
