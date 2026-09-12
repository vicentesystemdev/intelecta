<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\AsignacionTutorData;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\AsignacionTutorRepository;
use App\Domains\Academico\Repositories\TutorAcademicoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionTutorService
{
    public function __construct(
        private readonly AsignacionTutorRepository $repository,
        private readonly TutorAcademicoRepository $tutores,
        private readonly ContextoAcademicoService $contexto,
    ) {}

    public function index(array $filters): array
    {
        return [
            'asignaciones' => $this->repository->paginate($filters),
            'tutores' => $this->tutores->options(),
            'programas' => $this->repository->programasOptions(),
            'grupos' => $this->repository->gruposOptions(),
        ];
    }

    public function save(AsignacionTutorData $data, ?AsignacionTutor $asignacion = null): AsignacionTutor
    {
        return DB::transaction(function () use ($data, $asignacion): AsignacionTutor {
            $resolved = $this->resolverPrograma($data);
            $tutorIds = collect([$resolved->tutorId, $asignacion?->id_tutor])
                ->filter()
                ->unique()
                ->sort()
                ->values();
            $tutoresBloqueados = TutorAcademico::query()
                ->whereIn('id_tutor', $tutorIds)
                ->orderBy('id_tutor')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_tutor');
            $locked = $asignacion
                ? AsignacionTutor::query()->lockForUpdate()->findOrFail($asignacion->getKey())
                : null;
            $requiereEntidadesOperativas = ! $locked
                || $locked->id_tutor !== $resolved->tutorId
                || $locked->id_prog !== $resolved->programaId
                || $locked->id_grupo !== $resolved->grupoId
                || $resolved->estado === 'activo';

            if ($requiereEntidadesOperativas) {
                $tutor = $tutoresBloqueados->get($resolved->tutorId);

                if (! $tutor || $tutor->estado_tutor !== 'activo') {
                    throw ValidationException::withMessages([
                        'id_tutor' => 'El tutor no está disponible para una nueva asignación.',
                    ]);
                }

                if (! $resolved->programaId) {
                    throw ValidationException::withMessages([
                        'id_prog' => 'La asignación debe identificar un programa académico.',
                    ]);
                }

            }

            if ($resolved->estado === 'activo') {
                $this->validarSolapamiento($resolved, $locked);
            }

            if ($requiereEntidadesOperativas) {
                if ($resolved->grupoId) {
                    $this->contexto->grupoOperativo(
                        $resolved->grupoId,
                        $resolved->programaId,
                        true,
                    );
                }

                $this->contexto->programaOperativo($resolved->programaId, true);
            }

            return $locked
                ? $this->repository->update($locked, $resolved)
                : $this->repository->create($resolved);
        }, 3);
    }

    private function resolverPrograma(AsignacionTutorData $data): AsignacionTutorData
    {
        if (! $data->grupoId || $data->programaId) {
            return $data;
        }

        $grupo = GrupoAcademico::query()->find($data->grupoId);

        if (! $grupo) {
            throw ValidationException::withMessages([
                'id_grupo' => 'El grupo seleccionado no está disponible para nuevas operaciones.',
            ]);
        }

        return $data->withProgramaId($grupo->id_prog);
    }

    private function validarSolapamiento(
        AsignacionTutorData $data,
        ?AsignacionTutor $actual,
    ): void {
        $materia = mb_strtolower(trim($data->materiaReferencia ?? ''));
        $query = AsignacionTutor::query()
            ->where('estado_asig', 'activo')
            ->where('id_tutor', $data->tutorId)
            ->where('id_prog', $data->programaId)
            ->where('id_grupo', $data->grupoId)
            ->whereRaw("LOWER(TRIM(COALESCE(materia_referencia_asig, ''))) = ?", [$materia])
            ->when($actual, fn ($query) => $query->where('id_asig', '<>', $actual->id_asig))
            ->when($data->fechaFin, fn ($query, string $fecha) => $query->where(
                fn ($query) => $query
                    ->whereNull('fecha_inicio_asig')
                    ->orWhereDate('fecha_inicio_asig', '<=', $fecha),
            ))
            ->when($data->fechaInicio, fn ($query, string $fecha) => $query->where(
                fn ($query) => $query
                    ->whereNull('fecha_fin_asig')
                    ->orWhereDate('fecha_fin_asig', '>=', $fecha),
            ))
            ->lockForUpdate();

        if ($query->first(['id_asig'])) {
            throw ValidationException::withMessages([
                'id_tutor' => 'El tutor ya tiene una asignación activa solapada para el mismo contexto académico.',
            ]);
        }
    }
}
