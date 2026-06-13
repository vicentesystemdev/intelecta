<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\HabilitacionAcademicaData;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class HabilitacionAcademicaRepository
{
    public function paginate(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return HabilitacionAcademica::query()
            ->with([
                'postulante:id_post,nombres_post,apellidos_post,id_col,id_car',
                'postulante.colegio:id_col,nombre_col',
                'inscripcion.programa:id_prog,nombre_prog,codigo_prog',
                'inscripcion.grupo:id_grupo,nombre_grupo,codigo_grupo',
                'matricula:id_mat,codigo_mat,estado_matricula_mat',
            ])
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query
                ->whereHas('inscripcion', fn (Builder $query) => $query->where('id_prog', $value)))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query
                ->whereHas('inscripcion', fn (Builder $query) => $query->where('id_grupo', $value)))
            ->when($filters['estado_hab'] ?? null, fn (Builder $query, string $value) => $query->where('estado_hab', $value))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->whereHas('postulante', fn (Builder $query) => $query
                    ->whereRaw('LOWER(nombres_post) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(apellidos_post) LIKE ?', [$pattern]));
            })
            ->orderByRaw("CASE estado_hab WHEN 'restringido' THEN 0 WHEN 'observado' THEN 1 WHEN 'temporal' THEN 2 ELSE 3 END")
            ->latest('id_hab')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function update(HabilitacionAcademica $habilitacion, HabilitacionAcademicaData $data): HabilitacionAcademica
    {
        $habilitacion->update($data->toArray());

        return $habilitacion->refresh();
    }

    public function syncForMatricula(MatriculaAcademica $matricula): HabilitacionAcademica
    {
        $matricula->loadMissing('cuotas');
        $hasOverdue = $matricula->cuotas->contains('estado_cuota', 'vencida');
        $hasPending = $matricula->cuotas->contains('estado_cuota', 'pendiente');
        $benefit = in_array($matricula->estado_matricula_mat, ['becada', 'exenta'], true);

        [$status, $reason, $enabled] = match (true) {
            $matricula->estado_matricula_mat === 'inactiva' => [
                'restringido',
                'Matrícula académica inactiva.',
                false,
            ],
            $benefit => [
                'habilitado',
                'Beneficio académico vigente.',
                true,
            ],
            $hasOverdue => [
                'restringido',
                'Regularización administrativa pendiente por cuota vencida.',
                false,
            ],
            $hasPending || $matricula->estado_matricula_mat === 'observada' => [
                'observado',
                'Seguimiento administrativo preventivo.',
                true,
            ],
            default => [
                'habilitado',
                'Matrícula y cuotas sin observaciones vigentes.',
                true,
            ],
        };

        return HabilitacionAcademica::updateOrCreate(
            ['id_insc' => $matricula->id_insc],
            [
                'id_post' => $matricula->id_post,
                'id_mat' => $matricula->id_mat,
                'estado_hab' => $status,
                'motivo_hab' => $reason,
                'fecha_inicio_hab' => $matricula->fecha_matricula_mat,
                'habilitado_evaluaciones_hab' => $enabled,
                'habilitado_simulacros_hab' => $enabled,
                'habilitado_reportes_hab' => true,
                'observacion_hab' => 'Estado actualizado desde el control administrativo interno.',
            ],
        );
    }

    public function metrics(): array
    {
        return [
            'habilitados' => HabilitacionAcademica::where('estado_hab', 'habilitado')->count(),
            'observados' => HabilitacionAcademica::where('estado_hab', 'observado')->count(),
            'restringidos' => HabilitacionAcademica::where('estado_hab', 'restringido')->count(),
            'temporales' => HabilitacionAcademica::where('estado_hab', 'temporal')->count(),
        ];
    }
}
