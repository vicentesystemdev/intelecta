<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TutorAcademicoRepository
{
    public function paginate(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return TutorAcademico::query()
            ->with('user:id,name,email')
            ->withCount([
                'asignaciones as asignaciones_activas_count' => fn (Builder $query) => $query
                    ->where('estado_asig', 'activo'),
            ])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->where(function (Builder $query) use ($pattern) {
                    $query->whereRaw('LOWER(nombres_tutor) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(apellidos_tutor) LIKE ?', [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(ci_tutor, '')) LIKE ?", [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(correo_tutor, '')) LIKE ?", [$pattern]);
                });
            })
            ->when(
                $filters['especialidad_tutor'] ?? null,
                fn (Builder $query, string $value) => $query->where('especialidad_tutor', $value),
            )
            ->when(
                $filters['estado_tutor'] ?? null,
                fn (Builder $query, string $value) => $query->where('estado_tutor', $value),
            )
            ->orderBy('apellidos_tutor')
            ->orderBy('nombres_tutor')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(TutorAcademicoData $data): TutorAcademico
    {
        return TutorAcademico::create($data->toArray());
    }

    public function update(TutorAcademico $tutor, TutorAcademicoData $data): TutorAcademico
    {
        $tutor->update($data->toArray());

        return $tutor->refresh();
    }

    public function findForDetail(TutorAcademico $tutor): TutorAcademico
    {
        return $tutor->load([
            'user:id,name,email',
            'asignaciones' => fn ($query) => $query
                ->with([
                    'programa:id_prog,nombre_prog,codigo_prog',
                    'grupo:id_grupo,nombre_grupo,codigo_grupo',
                ])
                ->latest('fecha_inicio_asig')
                ->latest('id_asig'),
        ]);
    }

    public function options(bool $onlyActive = false): Collection
    {
        return TutorAcademico::query()
            ->when($onlyActive, fn (Builder $query) => $query->where('estado_tutor', 'activo'))
            ->orderBy('apellidos_tutor')
            ->orderBy('nombres_tutor')
            ->get([
                'id_tutor',
                'nombres_tutor',
                'apellidos_tutor',
                'especialidad_tutor',
                'estado_tutor',
            ]);
    }

    public function especialidades(): Collection
    {
        return TutorAcademico::query()
            ->whereNotNull('especialidad_tutor')
            ->distinct()
            ->orderBy('especialidad_tutor')
            ->pluck('especialidad_tutor');
    }

    public function usuariosOptions(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
