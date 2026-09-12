<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\ProgramaAcademico;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ContextoAcademicoService
{
    public function programaOperativo(int $programaId, bool $lock = false): ProgramaAcademico
    {
        $programa = $this->withLock(
            ProgramaAcademico::query()->whereKey($programaId),
            $lock,
        )->first();

        if (! $programa || $programa->estado_prog !== 'activo') {
            throw ValidationException::withMessages([
                'id_prog' => 'El programa académico seleccionado no está disponible para nuevas operaciones.',
            ]);
        }

        return $programa;
    }

    public function grupoOperativo(int $grupoId, int $programaId, bool $lock = false): GrupoAcademico
    {
        $grupo = $this->withLock(
            GrupoAcademico::query()->whereKey($grupoId),
            $lock,
        )->first();

        if (! $grupo || $grupo->estado_grupo !== 'activo') {
            throw ValidationException::withMessages([
                'id_grupo' => 'El grupo seleccionado no está disponible para nuevas operaciones.',
            ]);
        }

        if ($grupo->id_prog !== $programaId) {
            throw ValidationException::withMessages([
                'id_grupo' => 'El grupo seleccionado no pertenece al programa académico indicado.',
            ]);
        }

        return $grupo;
    }

    public function postulanteOperativo(int $postulanteId, bool $lock = false): Postulante
    {
        $postulante = $this->withLock(
            Postulante::query()->whereKey($postulanteId),
            $lock,
        )->first();

        if (! $postulante || $postulante->estado_post !== 'activo') {
            throw ValidationException::withMessages([
                'id_post' => 'El postulante seleccionado no está disponible para nuevas operaciones académicas.',
            ]);
        }

        return $postulante;
    }

    private function withLock(Builder $query, bool $lock): Builder
    {
        return $lock ? $query->lockForUpdate() : $query;
    }
}
