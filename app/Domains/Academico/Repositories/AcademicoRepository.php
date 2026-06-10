<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\GrupoAcademicoData;
use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\DTOs\ProgramaAcademicoData;
use App\Domains\Academico\DTOs\SimulacroProgramadoData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Pagination\Paginator as PaginatorResolver;
use Illuminate\Support\Collection;

class AcademicoRepository
{
    public function paginateProgramas(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return ProgramaAcademico::query()
            ->withCount([
                'grupos',
                'inscripciones as inscritos_count' => fn (Builder $query) => $query->where('estado_inscripcion', 'activo'),
                'simulacros',
            ])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->where(function (Builder $query) use ($pattern) {
                    $query->whereRaw('LOWER(nombre_prog) LIKE ?', [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(codigo_prog, '')) LIKE ?", [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(carrera_area_prog, '')) LIKE ?", [$pattern]);
                });
            })
            ->when($filters['estado_prog'] ?? null, fn (Builder $query, string $value) => $query->where('estado_prog', $value))
            ->when($filters['universidad_objetivo_prog'] ?? null, fn (Builder $query, string $value) => $query->where('universidad_objetivo_prog', $value))
            ->when($filters['modalidad_prog'] ?? null, fn (Builder $query, string $value) => $query->where('modalidad_prog', $value))
            ->orderByDesc('fecha_inicio_prog')
            ->orderBy('nombre_prog')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateGrupos(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return GrupoAcademico::query()
            ->with('programa:id_prog,nombre_prog,codigo_prog')
            ->withCount([
                'inscripciones as inscritos_count' => fn (Builder $query) => $query->where('estado_inscripcion', 'activo'),
                'simulacros',
            ])
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['turno_grupo'] ?? null, fn (Builder $query, string $value) => $query->where('turno_grupo', $value))
            ->when($filters['nivel_grupo'] ?? null, fn (Builder $query, string $value) => $query->where('nivel_grupo', $value))
            ->when($filters['estado_grupo'] ?? null, fn (Builder $query, string $value) => $query->where('estado_grupo', $value))
            ->orderBy('nombre_grupo')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateInscripciones(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return InscripcionAcademica::query()
            ->with([
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,nombre_grupo,codigo_grupo,capacidad_grupo,tutor_responsable_grupo',
                'postulante:id_post,nombres_post,apellidos_post,id_col,id_car,estado_post',
                'postulante.colegio:id_col,nombre_col',
                'postulante.carrera:id_car,nombre_car',
            ])
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['estado_inscripcion'] ?? null, fn (Builder $query, string $value) => $query->where('estado_inscripcion', $value))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->whereHas('postulante', fn (Builder $query) => $query
                    ->whereRaw('LOWER(nombres_post) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(apellidos_post) LIKE ?', [$pattern]));
            })
            ->latest('id_insc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateSimulacros(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return SimulacroProgramado::query()
            ->with([
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,nombre_grupo,codigo_grupo',
                'plantilla:id_plan,nombre_plan,duracion_minutos_plan',
            ])
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['estado_sim'] ?? null, fn (Builder $query, string $value) => $query->where('estado_sim', $value))
            ->when($filters['fecha_desde'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fecha_sim', '>=', $value))
            ->when($filters['fecha_hasta'] ?? null, fn (Builder $query, string $value) => $query->whereDate('fecha_sim', '<=', $value))
            ->orderByRaw('fecha_sim IS NULL')
            ->orderBy('fecha_sim')
            ->orderBy('hora_inicio_sim')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createPrograma(ProgramaAcademicoData $data): ProgramaAcademico
    {
        return ProgramaAcademico::create($data->toArray());
    }

    public function updatePrograma(ProgramaAcademico $programa, ProgramaAcademicoData $data): ProgramaAcademico
    {
        $programa->update($data->toArray());

        return $programa->refresh();
    }

    public function createGrupo(GrupoAcademicoData $data): GrupoAcademico
    {
        return GrupoAcademico::create($data->toArray());
    }

    public function updateGrupo(GrupoAcademico $grupo, GrupoAcademicoData $data): GrupoAcademico
    {
        $grupo->update($data->toArray());

        return $grupo->refresh();
    }

    public function createInscripcion(InscripcionAcademicaData $data): InscripcionAcademica
    {
        return InscripcionAcademica::create($data->toArray());
    }

    public function updateInscripcion(InscripcionAcademica $inscripcion, InscripcionAcademicaData $data): InscripcionAcademica
    {
        $inscripcion->update($data->toArray());

        return $inscripcion->refresh();
    }

    public function createSimulacro(SimulacroProgramadoData $data): SimulacroProgramado
    {
        return SimulacroProgramado::create($data->toArray());
    }

    public function updateSimulacro(SimulacroProgramado $simulacro, SimulacroProgramadoData $data): SimulacroProgramado
    {
        $simulacro->update($data->toArray());

        return $simulacro->refresh();
    }

    public function programasOptions(bool $onlyActive = false): Collection
    {
        return ProgramaAcademico::query()
            ->when($onlyActive, fn (Builder $query) => $query->where('estado_prog', 'activo'))
            ->orderBy('nombre_prog')
            ->get(['id_prog', 'nombre_prog', 'codigo_prog', 'universidad_objetivo_prog', 'modalidad_prog']);
    }

    public function gruposOptions(?int $programaId = null, bool $onlyActive = false): Collection
    {
        return GrupoAcademico::query()
            ->withCount(['inscripciones as inscritos_count' => fn (Builder $query) => $query->where('estado_inscripcion', 'activo')])
            ->when($programaId, fn (Builder $query) => $query->where('id_prog', $programaId))
            ->when($onlyActive, fn (Builder $query) => $query->where('estado_grupo', 'activo'))
            ->orderBy('nombre_grupo')
            ->get(['id_grupo', 'id_prog', 'nombre_grupo', 'codigo_grupo', 'capacidad_grupo', 'turno_grupo']);
    }

    public function postulantesOptions(): Collection
    {
        return Postulante::query()
            ->with(['colegio:id_col,nombre_col', 'carrera:id_car,nombre_car'])
            ->where('estado_post', 'activo')
            ->orderBy('apellidos_post')
            ->orderBy('nombres_post')
            ->get(['id_post', 'nombres_post', 'apellidos_post', 'id_col', 'id_car']);
    }

    public function plantillasOptions(): Collection
    {
        return PlantillaEvaluacion::query()
            ->where('estado_plan', 'activa')
            ->orderBy('nombre_plan')
            ->get(['id_plan', 'nombre_plan', 'duracion_minutos_plan']);
    }

    public function universidadesPrograma(): Collection
    {
        return ProgramaAcademico::query()
            ->whereNotNull('universidad_objetivo_prog')
            ->distinct()
            ->orderBy('universidad_objetivo_prog')
            ->pluck('universidad_objetivo_prog');
    }

    public function modalidadesPrograma(): Collection
    {
        return ProgramaAcademico::query()
            ->whereNotNull('modalidad_prog')
            ->distinct()
            ->orderBy('modalidad_prog')
            ->pluck('modalidad_prog');
    }

    public function ranking(array $filters, int $perPage = 12): array
    {
        $collection = RendimientoPostulante::query()
            ->with([
                'postulante:id_post,nombres_post,apellidos_post,id_col,id_car',
                'postulante.colegio:id_col,nombre_col',
                'postulante.carrera:id_car,id_uni,nombre_car',
                'postulante.carrera.universidad:id_uni,nombre_uni,sigla_uni',
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,nombre_grupo,codigo_grupo',
            ])
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['nivel_riesgo_rend'] ?? null, fn (Builder $query, string $value) => $query->where('nivel_riesgo_rend', $value))
            ->orderByDesc('promedio_general_rend')
            ->orderBy('id_rend')
            ->get();

        $total = $collection->count();
        $ranked = $collection->values()->map(function (RendimientoPostulante $rendimiento, int $index) use ($total) {
            $position = $index + 1;
            $rendimiento->setAttribute('posicion', $position);
            $rendimiento->setAttribute(
                'percentil',
                $total <= 1 ? 100 : round((($total - $position) / ($total - 1)) * 100),
            );
            $rendimiento->setAttribute('recomendacion', $this->recommendation($rendimiento->nivel_riesgo_rend));

            return $rendimiento;
        });

        $currentPage = PaginatorResolver::resolveCurrentPage();
        $paginator = new Paginator(
            $ranked->forPage($currentPage, $perPage)->values(),
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()],
        );

        $group = $collection
            ->filter(fn (RendimientoPostulante $item) => $item->grupo)
            ->groupBy('id_grupo')
            ->map(fn (Collection $items) => [
                'nombre' => $items->first()->grupo->nombre_grupo,
                'promedio' => round((float) $items->avg('promedio_general_rend'), 2),
            ])
            ->sortByDesc('promedio')
            ->first();

        return [
            'ranking' => $paginator,
            'metricas' => [
                'promedioInstitucional' => round((float) $collection->avg('promedio_general_rend'), 2),
                'altoRendimiento' => $collection->where('nivel_riesgo_rend', 'Alto rendimiento')->count(),
                'seguimiento' => $collection->where('nivel_riesgo_rend', 'Seguimiento regular')->count(),
                'atencionPrioritaria' => $collection->where('nivel_riesgo_rend', 'Atención prioritaria')->count(),
                'asistenciaPromedio' => round((float) $collection->avg('asistencia_porcentaje_rend'), 2),
                'mejorPromedio' => round((float) ($collection->max('promedio_general_rend') ?? 0), 2),
                'grupoDestacado' => $group,
            ],
        ];
    }

    public function ficha(Postulante $postulante): array
    {
        $postulante->load([
            'colegio',
            'carrera.universidad',
            'inscripcionesAcademicas.programa',
            'inscripcionesAcademicas.grupo',
            'rendimientosAcademicos.programa',
            'rendimientosAcademicos.grupo',
        ]);

        $rendimiento = $postulante->rendimientosAcademicos
            ->sortByDesc('created_at')
            ->first();
        $inscripcion = $postulante->inscripcionesAcademicas
            ->sortByDesc('fecha_inscripcion')
            ->first();

        $position = null;
        $percentile = null;
        if ($rendimiento?->id_prog) {
            $ranking = RendimientoPostulante::query()
                ->where('id_prog', $rendimiento->id_prog)
                ->orderByDesc('promedio_general_rend')
                ->pluck('id_post')
                ->values();
            $index = $ranking->search($postulante->id_post);
            if ($index !== false) {
                $position = $index + 1;
                $percentile = $ranking->count() <= 1
                    ? 100
                    : round((($ranking->count() - $position) / ($ranking->count() - 1)) * 100);
            }
        }

        return [
            'postulante' => $postulante,
            'inscripcion' => $inscripcion,
            'rendimiento' => $rendimiento,
            'posicion' => $position,
            'percentil' => $percentile,
            'recomendacion' => $this->recommendation($rendimiento?->nivel_riesgo_rend),
        ];
    }

    public function dashboardMetrics(): array
    {
        return [
            'programasActivos' => ProgramaAcademico::where('estado_prog', 'activo')->count(),
            'gruposActivos' => GrupoAcademico::where('estado_grupo', 'activo')->count(),
            'postulantesInscritos' => InscripcionAcademica::where('estado_inscripcion', 'activo')->distinct('id_post')->count('id_post'),
            'proximosSimulacros' => SimulacroProgramado::whereIn('estado_sim', ['programado', 'en preparación'])
                ->whereDate('fecha_sim', '>=', today())
                ->count(),
            'promedioInstitucional' => round((float) RendimientoPostulante::avg('promedio_general_rend'), 2),
            'seguimientoPrioritario' => RendimientoPostulante::where('nivel_riesgo_rend', 'Atención prioritaria')->count(),
        ];
    }

    private function recommendation(?string $level): string
    {
        return match ($level) {
            'Alto rendimiento' => 'Mantener práctica avanzada y consolidar velocidad de resolución.',
            'Seguimiento regular' => 'Reforzar contenidos con variación sostenida y verificar progreso por materia.',
            'Atención prioritaria' => 'Priorizar tutoría académica y una ruta de nivelación focalizada.',
            default => 'Consolidar información académica para orientar el seguimiento tutorial.',
        };
    }
}
