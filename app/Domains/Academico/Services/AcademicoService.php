<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\GrupoAcademicoData;
use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\DTOs\ProgramaAcademicoData;
use App\Domains\Academico\DTOs\SimulacroProgramadoData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Services\ConsistenciaEvaluacionService;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use App\Support\Validation\AcademicDatePolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicoService
{
    public function __construct(
        private readonly AcademicoRepository $repository,
        private readonly ContextoAcademicoService $contexto,
        private readonly ElegibilidadInscripcionService $elegibilidadInscripcion,
        private readonly ConsistenciaEvaluacionService $consistenciaEvaluacion,
    ) {}

    public function programas(array $filters): array
    {
        return [
            'programas' => $this->repository->paginateProgramas($filters),
            'universidades' => $this->repository->universidadesPrograma(),
            'modalidades' => $this->repository->modalidadesPrograma(),
        ];
    }

    public function grupos(array $filters, User $user): array
    {
        return [
            'grupos' => $this->repository->paginateGrupos($filters, $user),
            'programas' => $this->repository->programasOptionsPara($user, 'grupos.ver'),
            'tutores' => $user->can('grupos.crear') || $user->can('grupos.editar')
                ? $this->repository->tutoresResponsablesOptions()
                : [],
        ];
    }

    public function inscripciones(array $filters): array
    {
        return [
            'inscripciones' => $this->repository->paginateInscripciones($filters),
            'programas' => $this->repository->programasOptions(true),
            'grupos' => $this->repository->gruposOptions(null, true),
            'postulantes' => $this->repository->postulantesOptions(),
        ];
    }

    public function simulacros(array $filters): array
    {
        return [
            'simulacros' => $this->repository->paginateSimulacros($filters),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
            'plantillas' => $this->repository->plantillasOptions(),
        ];
    }

    public function ranking(array $filters): array
    {
        return [
            ...$this->repository->ranking($filters),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
        ];
    }

    public function rankingPortal(Postulante $postulante): array
    {
        return $this->repository->rankingPortal($postulante);
    }

    public function ficha(Postulante $postulante, ?User $user = null): array
    {
        return $this->repository->ficha($postulante, $user);
    }

    public function fichas(array $filters, User $user): array
    {
        return [
            'fichas' => $this->repository->paginateFichas($filters, $user),
            'programas' => $this->repository->programasOptionsPara($user, 'ficha-academica.ver'),
            'grupos' => $this->repository->gruposOptionsPara($user, 'ficha-academica.ver'),
        ];
    }

    public function dashboardMetrics(): array
    {
        return $this->repository->dashboardMetrics();
    }

    public function savePrograma(ProgramaAcademicoData $data, ?ProgramaAcademico $programa = null): ProgramaAcademico
    {
        return DB::transaction(function () use ($data, $programa): ProgramaAcademico {
            if (! $programa) {
                return $this->repository->createPrograma($data);
            }

            $locked = ProgramaAcademico::query()->lockForUpdate()->findOrFail($programa->getKey());

            return $this->repository->updatePrograma($locked, $data);
        }, 3);
    }

    public function saveGrupo(GrupoAcademicoData $data, ?GrupoAcademico $grupo = null): GrupoAcademico
    {
        return DB::transaction(function () use ($data, $grupo): GrupoAcademico {
            $requiereContextoOperativo = ! $grupo
                || $grupo->id_prog !== $data->programaId
                || $data->estado === 'activo';

            if (! $grupo) {
                if ($requiereContextoOperativo) {
                    $this->contexto->programaOperativo($data->programaId, true);
                }

                return $this->repository->createGrupo($data);
            }

            $locked = GrupoAcademico::query()->lockForUpdate()->findOrFail($grupo->getKey());

            if ($locked->id_prog !== $data->programaId && $this->grupoTieneDependencias($locked)) {
                throw ValidationException::withMessages([
                    'id_prog' => 'El programa del grupo no puede cambiarse porque existen registros académicos asociados.',
                ]);
            }

            if ($requiereContextoOperativo) {
                $this->contexto->programaOperativo($data->programaId, true);
            }

            $ocupados = InscripcionAcademica::query()
                ->where('id_grupo', $locked->id_grupo)
                ->where('estado_inscripcion', 'activo')
                ->count();

            if ($data->capacidad < $ocupados) {
                throw ValidationException::withMessages([
                    'capacidad_grupo' => "La capacidad no puede ser menor que las {$ocupados} inscripciones activas actuales.",
                ]);
            }

            return $this->repository->updateGrupo($locked, $data);
        }, 3);
    }

    public function saveInscripcion(InscripcionAcademicaData $data, ?InscripcionAcademica $inscripcion = null): InscripcionAcademica
    {
        return DB::transaction(function () use ($data, $inscripcion): InscripcionAcademica {
            if (! $inscripcion) {
                $data = $data->withAdministrativeValues(AcademicDatePolicy::todayString(), 'activo');
                $this->elegibilidadInscripcion->validar($data);

                return $this->repository->createInscripcion($data);
            }

            $locked = InscripcionAcademica::query()->lockForUpdate()->findOrFail($inscripcion->getKey());
            $data = $data->withAdministrativeValues(
                $locked->fecha_inscripcion?->format('Y-m-d'),
                $data->estado,
            );

            if ($this->cambiaContextoInscripcion($locked, $data) && $this->inscripcionTieneDependencias($locked)) {
                throw ValidationException::withMessages([
                    'id_post' => 'La identidad o el contexto de la inscripción no puede cambiarse porque existen registros académicos asociados.',
                    'id_prog' => 'La identidad o el contexto de la inscripción no puede cambiarse porque existen registros académicos asociados.',
                    'id_grupo' => 'La identidad o el contexto de la inscripción no puede cambiarse porque existen registros académicos asociados.',
                ]);
            }

            $this->elegibilidadInscripcion->validar($data, $locked);

            return $this->repository->updateInscripcion($locked, $data);
        }, 3);
    }

    public function saveSimulacro(SimulacroProgramadoData $data, ?SimulacroProgramado $simulacro = null): SimulacroProgramado
    {
        return DB::transaction(function () use ($data, $simulacro): SimulacroProgramado {
            $locked = $simulacro
                ? SimulacroProgramado::query()->lockForUpdate()->findOrFail($simulacro->getKey())
                : null;
            $cambiaContexto = ! $locked
                || $locked->id_prog !== $data->programaId
                || $locked->id_grupo !== $data->grupoId
                || $locked->id_plantilla !== $data->plantillaId;
            $requiereContextoOperativo = $cambiaContexto
                || in_array($data->estado, ['programado', 'en preparación'], true);

            if ($requiereContextoOperativo) {
                if ($data->grupoId) {
                    $this->contexto->grupoOperativo($data->grupoId, $data->programaId, true);
                }

                $this->contexto->programaOperativo($data->programaId, true);

                if ($data->plantillaId) {
                    $plantilla = PlantillaEvaluacion::query()->find($data->plantillaId);

                    if (! $plantilla) {
                        throw ValidationException::withMessages([
                            'id_plantilla' => 'La plantilla seleccionada no está disponible para un nuevo simulacro.',
                        ]);
                    }

                    $this->consistenciaEvaluacion->validarPlantillaAplicable($plantilla);
                }
            }

            return $locked
                ? $this->repository->updateSimulacro($locked, $data)
                : $this->repository->createSimulacro($data);
        }, 3);
    }

    private function grupoTieneDependencias(GrupoAcademico $grupo): bool
    {
        foreach ([
            ['inscripciones_academicas', 'id_grupo'],
            ['matriculas_academicas', 'id_grupo'],
            ['simulacros_programados', 'id_grupo'],
            ['asignaciones_tutores', 'id_grupo'],
            ['asistencias_academicas', 'id_grupo'],
            ['rendimientos_postulante', 'id_grupo'],
        ] as [$tabla, $columna]) {
            if (DB::table($tabla)->where($columna, $grupo->id_grupo)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function cambiaContextoInscripcion(
        InscripcionAcademica $inscripcion,
        InscripcionAcademicaData $data,
    ): bool {
        return $inscripcion->id_post !== $data->postulanteId
            || $inscripcion->id_prog !== $data->programaId
            || $inscripcion->id_grupo !== $data->grupoId;
    }

    private function inscripcionTieneDependencias(InscripcionAcademica $inscripcion): bool
    {
        if (DB::table('matriculas_academicas')->where('id_insc', $inscripcion->id_insc)->exists()
            || DB::table('habilitaciones_academicas')->where('id_insc', $inscripcion->id_insc)->exists()) {
            return true;
        }

        if ($inscripcion->id_grupo && DB::table('asistencias_academicas')
            ->where('id_grupo', $inscripcion->id_grupo)
            ->where('id_post', $inscripcion->id_post)
            ->exists()) {
            return true;
        }

        if (DB::table('rendimientos_postulante')
            ->where('id_post', $inscripcion->id_post)
            ->where('id_prog', $inscripcion->id_prog)
            ->when($inscripcion->id_grupo, fn ($query, int $grupoId) => $query->where('id_grupo', $grupoId))
            ->exists()) {
            return true;
        }

        return DB::table('evaluaciones_aplicadas as evaluacion')
            ->join('simulacros_programados as simulacro', 'simulacro.id_sim', '=', 'evaluacion.id_sim')
            ->where('evaluacion.id_post', $inscripcion->id_post)
            ->where('simulacro.id_prog', $inscripcion->id_prog)
            ->when($inscripcion->id_grupo, fn ($query, int $grupoId) => $query->where('simulacro.id_grupo', $grupoId))
            ->exists();
    }
}
