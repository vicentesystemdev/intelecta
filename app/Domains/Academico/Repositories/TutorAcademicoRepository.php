<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\TutorAcademicoData;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Institucional\Models\PersonalInstitucional;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TutorAcademicoRepository
{
    public function paginate(array $filters, int $perPage = 12, bool $withAccount = false): LengthAwarePaginator
    {
        return TutorAcademico::query()
            ->with([
                ...$this->identityRelations($withAccount),
                'asignaciones' => fn ($query) => $query
                    ->where('estado_asig', 'activo')
                    ->with([
                        'programa:id_prog,nombre_prog,codigo_prog',
                        'grupo:id_grupo,nombre_grupo,codigo_grupo',
                    ])
                    ->latest('fecha_inicio_asig')
                    ->latest('id_asig'),
            ])
            ->withCount([
                'asignaciones as asignaciones_activas_count' => fn (Builder $query) => $query
                    ->where('estado_asig', 'activo'),
            ])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';
                $query->whereHas('personal', function (Builder $query) use ($pattern) {
                    $query->whereRaw('LOWER(nombres) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(apellidos) LIKE ?', [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(ci, '')) LIKE ?", [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(correo_contacto, '')) LIKE ?", [$pattern])
                        ->orWhereRaw("LOWER(COALESCE(celular, '')) LIKE ?", [$pattern]);
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
            ->orderBy(PersonalInstitucional::select('apellidos')->whereColumn('id_personal', 'tutores_academicos.personal_id'))
            ->orderBy(PersonalInstitucional::select('nombres')->whereColumn('id_personal', 'tutores_academicos.personal_id'))
            ->orderBy('id_tutor')
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

    public function findForDetail(TutorAcademico $tutor, bool $withAccount = false): TutorAcademico
    {
        return $tutor->load([
            ...$this->identityRelations($withAccount),
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
            ->orderBy(PersonalInstitucional::select('apellidos')->whereColumn('id_personal', 'tutores_academicos.personal_id'))
            ->orderBy('id_tutor')
            ->get([
                'id_tutor',
                'personal_id',
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

    public function personalOptions(): Collection
    {
        return PersonalInstitucional::query()->whereIn('estado', ['activo', 'pendiente'])
            ->whereDoesntHave('tutorAcademico')->orderBy('apellidos')->orderBy('nombres')
            ->get(['id_personal', 'nombres', 'apellidos', 'estado']);
    }

    private function identityRelations(bool $withAccount): array
    {
        return $withAccount
            ? ['personal', 'personal.cargo:id_cargo,nombre_cargo,estado', 'personal.user:id,name,email']
            : ['personal:id_personal,nombres,apellidos,ci,celular,correo_contacto,cargo_id,estado', 'personal.cargo:id_cargo,nombre_cargo,estado'];
    }
}
