<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\AmbitoDocenteService;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AsistenciaAcademicaRepository
{
    public function __construct(private readonly AmbitoDocenteService $ambito) {}

    public function paginate(array $filters, User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->filteredQuery($filters, $user)
            ->with([
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,id_prog,nombre_grupo,codigo_grupo',
                'postulante:id_post,nombres_post,apellidos_post',
            ]);

        if (! $this->ambito->esDocenteRestringido($user, 'asistencia.ver')) {
            $query->with('tutor:id_tutor,personal_id,especialidad_tutor');
        }

        return $query
            ->latest('fecha_asist')
            ->latest('id_asist')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function metrics(array $filters, User $user): array
    {
        $metrics = $this->filteredQuery($filters, $user)
            ->selectRaw('COUNT(*) AS registradas')
            ->selectRaw("SUM(CASE WHEN estado_asist = 'presente' THEN 1 ELSE 0 END) AS presentes")
            ->selectRaw("SUM(CASE WHEN estado_asist = 'ausente' THEN 1 ELSE 0 END) AS ausentes")
            ->selectRaw("SUM(CASE WHEN estado_asist = 'retraso' THEN 1 ELSE 0 END) AS retrasos")
            ->selectRaw("SUM(CASE WHEN estado_asist = 'justificado' THEN 1 ELSE 0 END) AS justificados")
            ->first();
        $total = (int) ($metrics?->registradas ?? 0);
        $attended = (int) ($metrics?->presentes ?? 0)
            + (int) ($metrics?->retrasos ?? 0)
            + (int) ($metrics?->justificados ?? 0);

        return [
            'registradas' => $total,
            'presentes' => (int) ($metrics?->presentes ?? 0),
            'ausentes' => (int) ($metrics?->ausentes ?? 0),
            'retrasos' => (int) ($metrics?->retrasos ?? 0),
            'justificados' => (int) ($metrics?->justificados ?? 0),
            'promedio' => $total > 0 ? round(($attended / $total) * 100, 1) : 0,
        ];
    }

    public function roster(User $user, int $grupoId, string $fecha, string $sesion): Collection
    {
        $existing = $this->ambito->asistencias(AsistenciaAcademica::query(), $user)
            ->where('id_grupo', $grupoId)
            ->whereDate('fecha_asist', $fecha)
            ->where('sesion_asist', $sesion)
            ->get()
            ->keyBy('id_post');

        return $this->ambito->inscripciones(InscripcionAcademica::query(), $user, 'asistencia.ver', true)
            ->with('postulante:id_post,nombres_post,apellidos_post')
            ->where('id_grupo', $grupoId)
            ->orderBy('id_post')
            ->get()
            ->map(function (InscripcionAcademica $inscripcion) use ($existing) {
                $asistencia = $existing->get($inscripcion->id_post);

                return [
                    'id_insc' => $inscripcion->id_insc,
                    'id_post' => $inscripcion->id_post,
                    'postulante' => $inscripcion->postulante,
                    'asistencia' => $asistencia,
                    'estado_asist' => $asistencia?->estado_asist ?? 'presente',
                    'observacion_asist' => $asistencia?->observacion_asist,
                ];
            });
    }

    public function create(AsistenciaAcademicaData $data): AsistenciaAcademica
    {
        return AsistenciaAcademica::create($data->toArray());
    }

    public function update(
        AsistenciaAcademica $asistencia,
        AsistenciaAcademicaData $data,
    ): AsistenciaAcademica {
        $asistencia->update($data->toArray());

        return $asistencia->refresh();
    }

    public function programasOptions(User $user): Collection
    {
        return $this->ambito->programas(ProgramaAcademico::query(), $user, 'asistencia.ver')
            ->orderBy('nombre_prog')
            ->get(['id_prog', 'nombre_prog', 'codigo_prog', 'estado_prog']);
    }

    public function gruposOptions(User $user): Collection
    {
        return $this->ambito->grupos(GrupoAcademico::query(), $user, 'asistencia.ver')
            ->orderBy('nombre_grupo')
            ->get(['id_grupo', 'id_prog', 'nombre_grupo', 'codigo_grupo', 'estado_grupo']);
    }

    public function tutoresOptions(User $user): Collection
    {
        if ($this->ambito->esDocenteRestringido($user, 'asistencia.ver')) {
            return TutorAcademico::query()
                ->without('personal')
                ->with('personal:id_personal,nombres,apellidos')
                ->whereKey($this->ambito->tutorId($user, 'asistencia.ver'))
                ->get(['id_tutor', 'personal_id']);
        }

        return app(TutorAcademicoRepository::class)->options(true);
    }

    public function enrolledOptions(User $user): Collection
    {
        return $this->ambito->inscripciones(InscripcionAcademica::query(), $user, 'asistencia.ver', true)
            ->with('postulante:id_post,nombres_post,apellidos_post')
            ->whereNotNull('id_grupo')
            ->orderBy('id_grupo')
            ->orderBy('id_post')
            ->get(['id_insc', 'id_prog', 'id_grupo', 'id_post']);
    }

    public function studentSummary(int $postulanteId): array
    {
        $records = AsistenciaAcademica::query()
            ->with([
                'grupo:id_grupo,nombre_grupo,codigo_grupo',
                'tutor:id_tutor,personal_id',
            ])
            ->where('id_post', $postulanteId)
            ->latest('fecha_asist')
            ->latest('id_asist')
            ->get();
        $total = $records->count();
        $attended = $records->whereIn('estado_asist', ['presente', 'retraso', 'justificado'])->count();

        return [
            'porcentaje' => $total > 0 ? round(($attended / $total) * 100, 1) : 0,
            'total' => $total,
            'presentes' => $records->where('estado_asist', 'presente')->count(),
            'ausentes' => $records->where('estado_asist', 'ausente')->count(),
            'retrasos' => $records->where('estado_asist', 'retraso')->count(),
            'justificados' => $records->where('estado_asist', 'justificado')->count(),
            'ultimas' => $records->take(8)->values(),
        ];
    }

    public function averageByGroup(): Collection
    {
        return AsistenciaAcademica::query()
            ->selectRaw("id_grupo, ROUND(100.0 * SUM(CASE WHEN estado_asist IN ('presente', 'retraso', 'justificado') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 1) AS porcentaje_asistencia")
            ->with('grupo:id_grupo,nombre_grupo,codigo_grupo')
            ->groupBy('id_grupo')
            ->orderByDesc('porcentaje_asistencia')
            ->get();
    }

    public function lowAttendance(float $threshold = 75): Collection
    {
        return AsistenciaAcademica::query()
            ->selectRaw("id_post, COUNT(*) AS total, ROUND(100.0 * SUM(CASE WHEN estado_asist IN ('presente', 'retraso', 'justificado') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 1) AS porcentaje_asistencia")
            ->with('postulante:id_post,nombres_post,apellidos_post')
            ->groupBy('id_post')
            ->havingRaw("100.0 * SUM(CASE WHEN estado_asist IN ('presente', 'retraso', 'justificado') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) < ?", [$threshold])
            ->orderBy('porcentaje_asistencia')
            ->get();
    }

    public function absencesByProgram(): Collection
    {
        return AsistenciaAcademica::query()
            ->selectRaw('id_prog, COUNT(*) AS ausencias')
            ->with('programa:id_prog,nombre_prog,codigo_prog')
            ->where('estado_asist', 'ausente')
            ->groupBy('id_prog')
            ->orderByDesc('ausencias')
            ->get();
    }

    public function statusDistribution(): Collection
    {
        return AsistenciaAcademica::query()
            ->selectRaw('estado_asist, COUNT(*) AS total')
            ->groupBy('estado_asist')
            ->orderByDesc('total')
            ->get();
    }

    private function filteredQuery(array $filters, User $user): Builder
    {
        return $this->ambito->asistencias(AsistenciaAcademica::query(), $user)
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['fecha_asist'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fecha_asist', $value))
            ->when($filters['estado_asist'] ?? null, fn (Builder $query, string $value) => $query->where('estado_asist', $value))
            ->when($filters['id_tutor'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_tutor', $value));
    }
}
