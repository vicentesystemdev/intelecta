<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\DTOs\InscripcionAcademicaData;
use App\Domains\Academico\Models\GrupoAcademico;
use App\Domains\Academico\Models\InscripcionAcademica;
use Illuminate\Validation\ValidationException;

class ElegibilidadInscripcionService
{
    public function __construct(private readonly ContextoAcademicoService $contexto) {}

    public function validar(
        InscripcionAcademicaData $data,
        ?InscripcionAcademica $actual = null,
    ): void {
        $esNueva = $actual === null;
        $cambiaContexto = $esNueva
            || $actual->id_prog !== $data->programaId
            || $actual->id_grupo !== $data->grupoId
            || $actual->id_post !== $data->postulanteId;
        $seraActiva = $data->estado === 'activo';

        if (! $esNueva && ! $cambiaContexto && ! $seraActiva) {
            return;
        }

        $grupo = $data->grupoId
            ? $this->contexto->grupoOperativo($data->grupoId, $data->programaId, true)
            : null;

        $this->contexto->programaOperativo($data->programaId, true);

        $this->contexto->postulanteOperativo($data->postulanteId, true);
        $this->validarDuplicado($data, $actual);

        if ($seraActiva && $grupo) {
            $this->validarCupo($grupo, $actual);
        }
    }

    private function validarDuplicado(
        InscripcionAcademicaData $data,
        ?InscripcionAcademica $actual,
    ): void {
        $duplicada = InscripcionAcademica::query()
            ->where('id_prog', $data->programaId)
            ->where('id_post', $data->postulanteId)
            ->when($actual, fn ($query) => $query->where('id_insc', '<>', $actual->getKey()))
            ->exists();

        if ($duplicada) {
            throw ValidationException::withMessages([
                'id_post' => 'El postulante ya tiene una inscripción registrada en este programa académico.',
            ]);
        }
    }

    private function validarCupo(GrupoAcademico $grupo, ?InscripcionAcademica $actual): void
    {
        $ocupados = InscripcionAcademica::query()
            ->where('id_grupo', $grupo->id_grupo)
            ->where('estado_inscripcion', 'activo')
            ->when($actual, fn ($query) => $query->where('id_insc', '<>', $actual->getKey()))
            ->count();

        if ($ocupados >= $grupo->capacidad_grupo) {
            throw ValidationException::withMessages([
                'id_grupo' => 'El grupo seleccionado no dispone de cupos.',
            ]);
        }
    }
}
