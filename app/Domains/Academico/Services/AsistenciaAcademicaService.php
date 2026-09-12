<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Repositories\AsistenciaAcademicaRepository;
use App\Models\User;
use App\Support\Validation\AcademicDatePolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AsistenciaAcademicaService
{
    public function __construct(
        private readonly AsistenciaAcademicaRepository $repository,
        private readonly AmbitoDocenteService $ambito,
    ) {}

    public function index(array $filters, User $user): array
    {
        $sesion = trim($filters['sesion_asist'] ?? '') ?: 'General';
        $roster = collect();

        if (! empty($filters['id_grupo'])) {
            $roster = $this->repository->roster(
                $user,
                (int) $filters['id_grupo'],
                AcademicDatePolicy::todayString(),
                $sesion,
            );
        }

        return [
            'asistencias' => $this->repository->paginate($filters, $user),
            'metricas' => $this->repository->metrics($filters, $user),
            'programas' => $this->repository->programasOptions($user),
            'grupos' => $this->repository->gruposOptions($user),
            'tutores' => $this->repository->tutoresOptions($user),
            'inscripciones' => $this->repository->enrolledOptions($user),
            'listaGrupo' => $roster,
            'sesionSeleccionada' => $sesion,
        ];
    }

    public function save(
        AsistenciaAcademicaData $data,
        User $user,
        ?AsistenciaAcademica $asistencia = null,
    ): AsistenciaAcademica {
        $permission = $asistencia ? 'asistencia.editar' : 'asistencia.crear';
        if (! $user->can($permission)) {
            throw new AuthorizationException('No cuenta con permiso para esta operación de asistencia.');
        }

        return DB::transaction(function () use ($data, $user, $asistencia): AsistenciaAcademica {
            $permission = $asistencia ? 'asistencia.editar' : 'asistencia.crear';
            $tutorAutoridad = $this->ambito->bloquearAutoridadDeGrupo(
                $user,
                $data->grupoId,
                $permission,
            );
            $grupo = GrupoAcademico::query()
                ->whereKey($data->grupoId)
                ->lockForUpdate()
                ->firstOrFail();
            if ($data->programaId && $data->programaId !== $grupo->id_prog) {
                throw ValidationException::withMessages([
                    'id_prog' => 'El grupo seleccionado no pertenece al programa indicado.',
                ]);
            }
            if ($this->ambito->cantidadInscripcionesActivasPermitidas(
                $user,
                $grupo->id_grupo,
                collect([$data->postulanteId]),
                $permission,
                true,
            ) !== 1) {
                throw new AuthorizationException('El postulante no pertenece al ámbito académico permitido.');
            }

            $lockedAttendance = null;
            if ($asistencia) {
                $lockedAttendance = AsistenciaAcademica::query()
                    ->whereKey($asistencia->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                if (! Gate::forUser($user)->allows('update', $lockedAttendance)) {
                    throw new AuthorizationException('La asistencia no pertenece al ámbito académico permitido.');
                }
            }

            $tutorId = $this->tutorPermitido($user, $data->tutorId, $permission, $tutorAutoridad);
            $fecha = $lockedAttendance?->fecha_asist?->format('Y-m-d')
                ?? AcademicDatePolicy::todayString();
            $normalized = $data->withContext($grupo->id_prog, $tutorId)->withDate($fecha);

            $duplicate = AsistenciaAcademica::withTrashed()
                ->where('id_grupo', $normalized->grupoId)
                ->where('id_post', $normalized->postulanteId)
                ->whereDate('fecha_asist', $normalized->fecha)
                ->where('sesion_asist', $normalized->sesion)
                ->when($lockedAttendance, fn (Builder $query) => $query->whereKeyNot($lockedAttendance->getKey()))
                ->lockForUpdate()
                ->exists();
            if ($duplicate) {
                throw ValidationException::withMessages([
                    'id_post' => 'La asistencia del postulante ya fue registrada para este grupo, fecha y sesión.',
                ]);
            }

            return $lockedAttendance
                ? $this->repository->update($lockedAttendance, $normalized)
                : $this->repository->create($normalized);
        });
    }

    public function saveGroup(array $data, User $user): Collection
    {
        if (! $user->can('asistencia.crear')) {
            throw new AuthorizationException('No cuenta con permiso para registrar asistencia.');
        }

        return DB::transaction(function () use ($data, $user): Collection {
            $data['fecha_asist'] = AcademicDatePolicy::todayString();
            $groupId = (int) $data['id_grupo'];
            $tutorAutoridad = $this->ambito->bloquearAutoridadDeGrupo(
                $user,
                $groupId,
                'asistencia.crear',
            );
            $grupo = GrupoAcademico::query()
                ->whereKey($groupId)
                ->lockForUpdate()
                ->firstOrFail();
            if (filled($data['id_prog'] ?? null) && (int) $data['id_prog'] !== $grupo->id_prog) {
                throw ValidationException::withMessages([
                    'id_prog' => 'El grupo seleccionado no pertenece al programa indicado.',
                ]);
            }

            $records = collect($data['registros']);
            $postulanteIds = $records->pluck('id_post')->map(fn ($id) => (int) $id)->unique()->values();
            $allowedCount = $this->ambito->cantidadInscripcionesActivasPermitidas(
                $user,
                $groupId,
                $postulanteIds,
                'asistencia.crear',
                true,
            );
            if ($allowedCount !== $postulanteIds->count()) {
                throw new AuthorizationException('Uno o más postulantes no pertenecen al ámbito académico permitido.');
            }

            $session = trim($data['sesion_asist'] ?? '') ?: 'General';
            $existing = AsistenciaAcademica::withTrashed()
                ->where('id_grupo', $groupId)
                ->whereIn('id_post', $postulanteIds)
                ->whereDate('fecha_asist', $data['fecha_asist'])
                ->where('sesion_asist', $session)
                ->lockForUpdate()
                ->get()
                ->keyBy('id_post');

            if ($existing->contains(fn (AsistenciaAcademica $item) => $item->trashed())) {
                throw ValidationException::withMessages([
                    'registros' => 'Existe un registro archivado para uno de los postulantes; requiere revisión administrativa.',
                ]);
            }
            if ($existing->isNotEmpty() && ! $user->can('asistencia.editar')) {
                throw ValidationException::withMessages([
                    'registros' => 'La sesión ya contiene asistencias. Crear no autoriza sobrescribirlas.',
                ]);
            }

            $tutorId = $this->tutorPermitido(
                $user,
                filled($data['id_tutor'] ?? null) ? (int) $data['id_tutor'] : null,
                'asistencia.crear',
                $tutorAutoridad,
            );

            return $records->map(function (array $record) use ($existing, $grupo, $groupId, $data, $session, $tutorId, $user): AsistenciaAcademica {
                $attributes = [
                    'id_prog' => $grupo->id_prog,
                    'id_grupo' => $groupId,
                    'id_post' => (int) $record['id_post'],
                    'id_tutor' => $tutorId,
                    'fecha_asist' => $data['fecha_asist'],
                    'sesion_asist' => $session,
                    'estado_asist' => $record['estado_asist'],
                    'observacion_asist' => filled($record['observacion_asist'] ?? null)
                        ? trim($record['observacion_asist'])
                        : null,
                ];
                $attendance = $existing->get((int) $record['id_post']);
                if ($attendance) {
                    if (! Gate::forUser($user)->allows('update', $attendance)) {
                        throw new AuthorizationException('La asistencia existente no pertenece al ámbito permitido.');
                    }
                    $attendance->update($attributes);

                    return $attendance->refresh();
                }

                return AsistenciaAcademica::create($attributes);
            });
        });
    }

    private function tutorPermitido(
        User $user,
        ?int $requestedTutorId,
        string $capacidad,
        ?int $tutorAutoridad,
    ): ?int {
        if (! $this->ambito->esDocenteRestringido($user, $capacidad)) {
            return $requestedTutorId;
        }

        $tutorId = $tutorAutoridad;
        if (! $tutorId || ($requestedTutorId && $requestedTutorId !== $tutorId)) {
            throw new AuthorizationException('El tutor indicado no corresponde al usuario autenticado.');
        }

        return $tutorId;
    }
}
