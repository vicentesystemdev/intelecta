<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\DTOs\MatriculaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Academico\Repositories\HabilitacionAcademicaRepository;
use App\Domains\Academico\Repositories\MatriculaCuotaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatriculaCuotaService
{
    public function __construct(
        private readonly MatriculaCuotaRepository $repository,
        private readonly HabilitacionAcademicaRepository $habilitaciones,
        private readonly AcademicoRepository $academico,
        private readonly ContextoAcademicoService $contexto,
    ) {}

    public function index(array $filters): array
    {
        return [
            'matriculas' => $this->repository->paginate($filters),
            'metricas' => $this->repository->metrics(),
            'inscripciones' => $this->repository->inscripcionesOptions(),
            'programas' => $this->academico->programasOptions(),
            'grupos' => $this->academico->gruposOptions(),
        ];
    }

    public function saveMatricula(
        MatriculaAcademicaData $data,
        ?MatriculaAcademica $matricula = null,
    ): MatriculaAcademica {
        return DB::transaction(function () use ($data, $matricula) {
            $locked = $matricula
                ? MatriculaAcademica::query()->lockForUpdate()->findOrFail($matricula->getKey())
                : null;

            if ($locked && $locked->id_insc !== $data->inscripcionId) {
                throw ValidationException::withMessages([
                    'id_insc' => 'La inscripción de una matrícula existente no puede modificarse.',
                ]);
            }

            $inscripcion = InscripcionAcademica::query()
                ->whereKey($data->inscripcionId)
                ->lockForUpdate()
                ->first();

            if (! $inscripcion) {
                throw ValidationException::withMessages([
                    'id_insc' => 'La inscripción académica seleccionada no existe.',
                ]);
            }

            $estadoOperativo = in_array($data->estado, ['activa', 'becada', 'exenta'], true);

            if (! $locked || $estadoOperativo) {
                $this->validarInscripcionMatriculable($inscripcion);

                if ($locked && (
                    $locked->id_post !== $inscripcion->id_post
                    || $locked->id_prog !== $inscripcion->id_prog
                    || $locked->id_grupo !== $inscripcion->id_grupo
                )) {
                    throw ValidationException::withMessages([
                        'id_insc' => 'La matrícula no coincide con el contexto actual de su inscripción y no puede activarse.',
                    ]);
                }

                if (! $locked && MatriculaAcademica::withTrashed()->where('id_insc', $inscripcion->id_insc)->exists()) {
                    throw ValidationException::withMessages([
                        'id_insc' => 'La inscripción ya tiene una matrícula académica registrada.',
                    ]);
                }
            }

            $attributes = [
                ...$data->toArray(),
                'id_post' => $inscripcion->id_post,
                'id_prog' => $inscripcion->id_prog,
                'id_grupo' => $inscripcion->id_grupo,
                'fecha_matricula_mat' => $data->fechaMatricula ?? today()->toDateString(),
            ];

            $saved = $locked
                ? $this->repository->update($locked, $attributes)
                : $this->repository->create($attributes);

            if (! $saved->codigo_mat) {
                $saved = $this->repository->update($saved, [
                    ...$attributes,
                    'codigo_mat' => sprintf('MAT-%s-%05d', now()->format('Y'), $saved->id_mat),
                ]);
            }

            $this->habilitaciones->syncForMatricula($saved);

            return $saved;
        }, 3);
    }

    private function validarInscripcionMatriculable(InscripcionAcademica $inscripcion): void
    {
        if ($inscripcion->estado_inscripcion !== 'activo') {
            throw ValidationException::withMessages([
                'id_insc' => 'Solo una inscripción activa puede generar una nueva matrícula.',
            ]);
        }

        if ($inscripcion->id_grupo) {
            $this->contexto->grupoOperativo($inscripcion->id_grupo, $inscripcion->id_prog, true);
        }

        $this->contexto->programaOperativo($inscripcion->id_prog, true);

        $this->contexto->postulanteOperativo($inscripcion->id_post, true);
    }

    public function saveCuota(CuotaAcademicaData $data, ?CuotaAcademica $cuota = null): CuotaAcademica
    {
        return DB::transaction(function () use ($data, $cuota) {
            $saved = $cuota
                ? $this->repository->updateCuota($cuota, $data)
                : $this->repository->createCuota($data);

            $matricula = MatriculaAcademica::query()->findOrFail($saved->id_mat);
            $this->habilitaciones->syncForMatricula($matricula);

            return $saved;
        });
    }
}
