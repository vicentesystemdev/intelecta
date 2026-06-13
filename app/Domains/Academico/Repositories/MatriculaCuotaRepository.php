<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MatriculaCuotaRepository
{
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return MatriculaAcademica::query()
            ->with([
                'postulante:id_post,nombres_post,apellidos_post,id_col,id_car',
                'postulante.colegio:id_col,nombre_col',
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,nombre_grupo,codigo_grupo',
                'cuotas' => fn ($query) => $query
                    ->orderBy('nro_cuota')
                    ->orderBy('fecha_vencimiento_cuota'),
                'habilitacion:id_hab,id_mat,estado_hab,habilitado_evaluaciones_hab',
            ])
            ->withCount([
                'cuotas',
                'cuotas as cuotas_pagadas_count' => fn (Builder $query) => $query->where('estado_cuota', 'pagada'),
                'cuotas as cuotas_pendientes_count' => fn (Builder $query) => $query->where('estado_cuota', 'pendiente'),
                'cuotas as cuotas_vencidas_count' => fn (Builder $query) => $query->where('estado_cuota', 'vencida'),
            ])
            ->withSum([
                'cuotas as saldo_pendiente' => fn (Builder $query) => $query
                    ->whereIn('estado_cuota', ['pendiente', 'vencida']),
            ], 'monto_cuota')
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when(
                $filters['estado_matricula_mat'] ?? null,
                fn (Builder $query, string $value) => $query->where('estado_matricula_mat', $value),
            )
            ->when($filters['estado_cuota'] ?? null, fn (Builder $query, string $value) => $query
                ->whereHas('cuotas', fn (Builder $query) => $query->where('estado_cuota', $value)))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->where(function (Builder $query) use ($pattern) {
                    $query->whereRaw("LOWER(COALESCE(codigo_mat, '')) LIKE ?", [$pattern])
                        ->orWhereHas('postulante', fn (Builder $query) => $query
                            ->whereRaw('LOWER(nombres_post) LIKE ?', [$pattern])
                            ->orWhereRaw('LOWER(apellidos_post) LIKE ?', [$pattern]));
                });
            })
            ->latest('fecha_matricula_mat')
            ->latest('id_mat')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): MatriculaAcademica
    {
        return MatriculaAcademica::create($attributes);
    }

    public function update(MatriculaAcademica $matricula, array $attributes): MatriculaAcademica
    {
        $matricula->update($attributes);

        return $matricula->refresh();
    }

    public function createCuota(CuotaAcademicaData $data): CuotaAcademica
    {
        return CuotaAcademica::create($data->toArray());
    }

    public function updateCuota(CuotaAcademica $cuota, CuotaAcademicaData $data): CuotaAcademica
    {
        $cuota->update($data->toArray());

        return $cuota->refresh();
    }

    public function inscripcion(int $id): InscripcionAcademica
    {
        return InscripcionAcademica::query()->findOrFail($id);
    }

    public function inscripcionesOptions(): Collection
    {
        return InscripcionAcademica::query()
            ->with([
                'postulante:id_post,nombres_post,apellidos_post',
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,nombre_grupo,codigo_grupo',
            ])
            ->where('estado_inscripcion', 'activo')
            ->orderByDesc('fecha_inscripcion')
            ->get(['id_insc', 'id_post', 'id_prog', 'id_grupo', 'fecha_inscripcion']);
    }

    public function options(): Collection
    {
        return MatriculaAcademica::query()
            ->with('postulante:id_post,nombres_post,apellidos_post')
            ->orderByDesc('id_mat')
            ->get(['id_mat', 'id_post', 'codigo_mat', 'estado_matricula_mat']);
    }

    public function metrics(): array
    {
        return [
            'matriculasActivas' => MatriculaAcademica::where('estado_matricula_mat', 'activa')->count(),
            'matriculasObservadas' => MatriculaAcademica::where('estado_matricula_mat', 'observada')->count(),
            'cuotasPendientes' => CuotaAcademica::where('estado_cuota', 'pendiente')->count(),
            'cuotasVencidas' => CuotaAcademica::where('estado_cuota', 'vencida')->count(),
            'beneficiados' => MatriculaAcademica::whereIn('estado_matricula_mat', ['becada', 'exenta'])->count(),
        ];
    }
}
