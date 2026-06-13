<?php

namespace App\Domains\Academico\Repositories;

use App\Domains\Academico\DTOs\AsignacionTutorData;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\ProgramaAcademico;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AsignacionTutorRepository
{
    public function paginate(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return AsignacionTutor::query()
            ->with([
                'tutor:id_tutor,nombres_tutor,apellidos_tutor,especialidad_tutor,estado_tutor',
                'programa:id_prog,nombre_prog,codigo_prog',
                'grupo:id_grupo,id_prog,nombre_grupo,codigo_grupo',
            ])
            ->when($filters['id_tutor'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_tutor', $value))
            ->when($filters['id_prog'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_prog', $value))
            ->when($filters['id_grupo'] ?? null, fn (Builder $query, int|string $value) => $query->where('id_grupo', $value))
            ->when($filters['estado_asig'] ?? null, fn (Builder $query, string $value) => $query->where('estado_asig', $value))
            ->orderByRaw("CASE WHEN estado_asig = 'activo' THEN 0 ELSE 1 END")
            ->latest('fecha_inicio_asig')
            ->latest('id_asig')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(AsignacionTutorData $data): AsignacionTutor
    {
        return AsignacionTutor::create($data->toArray());
    }

    public function update(AsignacionTutor $asignacion, AsignacionTutorData $data): AsignacionTutor
    {
        $asignacion->update($data->toArray());

        return $asignacion->refresh();
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
}
