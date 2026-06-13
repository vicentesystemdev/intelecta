<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\AsistenciaAcademicaData;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\TutorAcademico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AsistenciaAcademicaRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->with([
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,id_prog,nombre_grupo,codigo_grupo',
                'postulante:id_post,nombres_post,apellidos_post',
                'tutor:id_tutor,nombres_tutor,apellidos_tutor,especialidad_tutor',
            ])
            ->latest('fecha_asist')
            ->latest('id_asist')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function metrics(array $filters): array
    {
        $collection = $this->filteredQuery($filters)->get(['estado_asist']);
        $total = $collection->count();
        $attended = $collection->whereIn('estado_asist', ['presente', 'retraso', 'justificado'])->count();

        return [
            'registradas' => $total,
            'presentes' => $collection->where('estado_asist', 'presente')->count(),
            'ausentes' => $collection->where('estado_asist', 'ausente')->count(),
            'retrasos' => $collection->where('estado_asist', 'retraso')->count(),
            'justificados' => $collection->where('estado_asist', 'justificado')->count(),
            'promedio' => $total > 0 ? round(($attended / $total) * 100, 1) : 0,
        ];
    }

    public function roster(int $grupoId, string $fecha, string $sesion): Collection
    {
        $existing = AsistenciaAcademica::query()
            ->where('id_grupo', $grupoId)
            ->whereDate('fecha_asist', $fecha)
            ->where('sesion_asist', $sesion)
            ->get()
            ->keyBy('id_post');

        return InscripcionAcademica::query()
            ->with('postulante:id_post,nombres_post,apellidos_post,ci_post')
            ->where('id_grupo', $grupoId)
            ->where('estado_inscripcion', 'activo')
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

    public function upsertGroup(array $data): Collection
    {
        return collect($data['registros'])->map(function (array $registro) use ($data) {
            return AsistenciaAcademica::updateOrCreate(
                [
                    'id_grupo' => (int) $data['id_grupo'],
                    'id_post' => (int) $registro['id_post'],
                    'fecha_asist' => $data['fecha_asist'],
                    'sesion_asist' => $data['sesion_asist'],
                ],
                [
                    'id_prog' => (int) $data['id_prog'],
                    'id_tutor' => filled($data['id_tutor'] ?? null) ? (int) $data['id_tutor'] : null,
                    'estado_asist' => $registro['estado_asist'],
                    'observacion_asist' => filled($registro['observacion_asist'] ?? null)
                        ? trim($registro['observacion_asist'])
                        : null,
                ],
            );
        });
    }

    public function programasOptions(): Collection
    {
        return ProgramaAcademico::query()
            ->orderBy('nombre_prog')
            ->get(['id_prog', 'nombre_prog', 'codigo_prog', 'estado_prog']);
    }

    public function gruposOptions(): Collection
    {
        return GrupoAcademico::query()
            ->orderBy('nombre_grupo')
            ->get(['id_grupo', 'id_prog', 'nombre_grupo', 'codigo_grupo', 'estado_grupo']);
    }

    public function tutoresOptions(): Collection
    {
        return TutorAcademico::query()
            ->where('estado_tutor', 'activo')
            ->orderBy('apellidos_tutor')
            ->orderBy('nombres_tutor')
            ->get(['id_tutor', 'nombres_tutor', 'apellidos_tutor', 'especialidad_tutor']);
    }

    public function enrolledOptions(): Collection
    {
        return InscripcionAcademica::query()
            ->with('postulante:id_post,nombres_post,apellidos_post')
            ->where('estado_inscripcion', 'activo')
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
                'tutor:id_tutor,nombres_tutor,apellidos_tutor',
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

    private function filteredQuery(array $filters): Builder
    {
        return AsistenciaAcademica::query()
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['fecha_asist'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fecha_asist', $value))
            ->when($filters['estado_asist'] ?? null, fn (Builder $query, string $value) => $query->where('estado_asist', $value))
            ->when($filters['id_tutor'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_tutor', $value));
    }
}
