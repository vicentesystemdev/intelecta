<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\DTOs\MatriculaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Academico\Repositories\HabilitacionAcademicaRepository;
use App\Domains\Academico\Repositories\MatriculaCuotaRepository;
use Illuminate\Support\Facades\DB;

class MatriculaCuotaService
{
    public function __construct(
        private readonly MatriculaCuotaRepository $repository,
        private readonly HabilitacionAcademicaRepository $habilitaciones,
        private readonly AcademicoRepository $academico,
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
            $inscripcion = $this->repository->inscripcion($data->inscripcionId);
            $attributes = [
                ...$data->toArray(),
                'id_post' => $inscripcion->id_post,
                'id_prog' => $inscripcion->id_prog,
                'id_grupo' => $inscripcion->id_grupo,
                'fecha_matricula_mat' => $data->fechaMatricula ?? today()->toDateString(),
            ];

            $saved = $matricula
                ? $this->repository->update($matricula, $attributes)
                : $this->repository->create($attributes);

            if (! $saved->codigo_mat) {
                $saved = $this->repository->update($saved, [
                    ...$attributes,
                    'codigo_mat' => sprintf('MAT-%s-%05d', now()->format('Y'), $saved->id_mat),
                ]);
            }

            $this->habilitaciones->syncForMatricula($saved);

            return $saved;
        });
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
