<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Repositories\AsistenciaAcademicaRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AsistenciaAcademicaService
{
    public function __construct(private readonly AsistenciaAcademicaRepository $repository) {}

    public function index(array $filters): array
    {
        $sesion = trim($filters['sesion_asist'] ?? '') ?: 'General';
        $roster = collect();

        if (! empty($filters['id_grupo']) && ! empty($filters['fecha_asist'])) {
            $roster = $this->repository->roster(
                (int) $filters['id_grupo'],
                $filters['fecha_asist'],
                $sesion,
            );
        }

        return [
            'asistencias' => $this->repository->paginate($filters),
            'metricas' => $this->repository->metrics($filters),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
            'tutores' => $this->repository->tutoresOptions(),
            'inscripciones' => $this->repository->enrolledOptions(),
            'listaGrupo' => $roster,
            'sesionSeleccionada' => $sesion,
        ];
    }

    public function save(
        AsistenciaAcademicaData $data,
        ?AsistenciaAcademica $asistencia = null,
    ): AsistenciaAcademica {
        $grupo = GrupoAcademico::findOrFail($data->grupoId);
        $normalized = $data->withProgram($data->programaId ?? $grupo->id_prog);

        return DB::transaction(fn () => $asistencia
            ? $this->repository->update($asistencia, $normalized)
            : $this->repository->create($normalized));
    }

    public function saveGroup(array $data): Collection
    {
        $grupo = GrupoAcademico::findOrFail((int) $data['id_grupo']);
        $data['id_prog'] = filled($data['id_prog'] ?? null)
            ? (int) $data['id_prog']
            : $grupo->id_prog;
        $data['sesion_asist'] = trim($data['sesion_asist'] ?? '') ?: 'General';

        return DB::transaction(fn () => $this->repository->upsertGroup($data));
    }
}
