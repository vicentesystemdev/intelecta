<?php

namespace App\Domains\Evaluaciones\Services;

use App\Domains\Evaluaciones\DTOs\PreguntaData;
use App\Domains\Evaluaciones\Models\AreaConocimiento;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Evaluaciones\Models\PlantillaEvaluacion;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ConsistenciaEvaluacionService
{
    public function validarTaxonomia(PreguntaData $data): void
    {
        $materia = $data->idMateria ? Materia::query()->find($data->idMateria) : null;
        $area = $data->idArea
            ? AreaConocimiento::query()->with('materia')->find($data->idArea)
            : null;
        $tema = $data->idTem
            ? Tema::query()->with('area.materia')->find($data->idTem)
            : null;

        if ($data->idMateria && ! $materia) {
            throw ValidationException::withMessages(['id_mat' => 'La materia seleccionada no existe.']);
        }

        if ($data->idArea && ! $area) {
            throw ValidationException::withMessages(['id_area' => 'El área de conocimiento seleccionada no existe.']);
        }

        if ($data->idTem && ! $tema) {
            throw ValidationException::withMessages(['id_tem' => 'El tema académico seleccionado no existe.']);
        }

        if ($area && $materia && $area->id_mat !== $materia->id_mat) {
            throw ValidationException::withMessages([
                'id_area' => 'El área de conocimiento seleccionada no pertenece a la materia indicada.',
            ]);
        }

        if ($tema && $area && $tema->id_area !== $area->id_area) {
            throw ValidationException::withMessages([
                'id_tem' => 'El tema seleccionado no pertenece al área de conocimiento indicada.',
            ]);
        }

        if ($tema && $materia && $tema->area?->id_mat !== $materia->id_mat) {
            throw ValidationException::withMessages([
                'id_area' => 'El área de conocimiento seleccionada no pertenece a la materia indicada.',
            ]);
        }

        if (
            $data->estadoPreg === 'activo'
            && (
                ($materia && $materia->estado_mat !== 'activo')
                || ($area && ($area->estado_area !== 'activo' || $area->materia?->estado_mat !== 'activo'))
                || ($tema && (
                    $tema->estado_tem !== 'activo'
                    || $tema->area?->estado_area !== 'activo'
                    || $tema->area?->materia?->estado_mat !== 'activo'
                ))
            )
        ) {
            throw ValidationException::withMessages([
                'id_tem' => 'La clasificación académica seleccionada no está disponible para una pregunta activa.',
            ]);
        }
    }

    public function validarAlternativas(PreguntaData $data): void
    {
        if ($data->estadoPreg !== 'activo') {
            return;
        }

        $esperadas = match ($data->tipoPreg) {
            'opcion_multiple' => 5,
            'verdadero_falso' => 2,
            default => 0,
        };
        $activas = collect($data->alternativas)
            ->filter(fn (array $alternativa) => ($alternativa['estado_alt'] ?? 'activo') === 'activo');

        if ($activas->count() !== $esperadas) {
            throw ValidationException::withMessages([
                'alternativas' => "Una pregunta activa de este tipo requiere {$esperadas} alternativas activas.",
            ]);
        }

        if ($esperadas > 0 && $activas->where('es_correcta_alt', true)->count() !== 1) {
            throw ValidationException::withMessages([
                'alternativas' => 'Debe existir exactamente una alternativa activa correcta.',
            ]);
        }
    }

    public function validarPreguntasNuevas(array $preguntaIds): void
    {
        $ids = collect($preguntaIds)->map(fn ($id) => (int) $id)->unique()->values();
        $validas = Pregunta::query()
            ->whereIn('id_preg', $ids)
            ->where('estado_preg', 'activo')
            ->lockForUpdate()
            ->get(['id_preg']);

        if ($validas->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'preguntas' => 'Solo pueden agregarse preguntas activas y no archivadas a una plantilla.',
            ]);
        }
    }

    public function validarPreguntasAplicables(array $preguntaIds): Collection
    {
        $ids = collect($preguntaIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla seleccionada no tiene preguntas evaluables.',
            ]);
        }

        $preguntas = Pregunta::withTrashed()
            ->whereIn('id_preg', $ids)
            ->with(['tema.area.materia', 'alternativas'])
            ->lockForUpdate()
            ->get();

        if ($preguntas->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla contiene preguntas inexistentes o archivadas.',
            ]);
        }

        foreach ($preguntas as $pregunta) {
            try {
                $this->validarPreguntaAplicable($pregunta);
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'preguntas' => 'La plantilla contiene preguntas inactivas, archivadas o semánticamente inválidas.',
                ]);
            }
        }

        return $preguntas;
    }

    public function validarPreguntaAplicable(Pregunta $pregunta): void
    {
        $pregunta->loadMissing(['tema.area.materia', 'alternativas']);

        if ($pregunta->estado_preg !== 'activo' || $pregunta->trashed()) {
            throw ValidationException::withMessages([
                'pregunta' => 'La pregunta no está disponible para uso académico.',
            ]);
        }

        $data = PreguntaData::fromArray([
            'id_mat' => $pregunta->tema?->area?->id_mat,
            'id_area' => $pregunta->tema?->id_area,
            'id_tem' => $pregunta->id_tem,
            'enunciado_preg' => $pregunta->enunciado_preg,
            'tipo_preg' => $pregunta->tipo_preg,
            'puntaje_preg' => $pregunta->puntaje_preg,
            'estado_preg' => 'activo',
            'alternativas' => $pregunta->alternativas->map(fn ($alternativa) => [
                'texto_alt' => $alternativa->texto_alt,
                'letra_alt' => $alternativa->letra_alt,
                'es_correcta_alt' => $alternativa->es_correcta_alt,
                'orden_alt' => $alternativa->orden_alt,
                'estado_alt' => $alternativa->estado_alt,
            ])->all(),
        ]);

        $this->validarTaxonomia($data);
        $this->validarAlternativas($data);
    }

    public function validarPlantillaAplicable(PlantillaEvaluacion $plantilla): Collection
    {
        if ($plantilla->estado_plan !== 'activa' || $plantilla->trashed()) {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla seleccionada no está disponible para aplicación.',
            ]);
        }

        $preguntas = $plantilla->preguntas()
            ->withTrashed()
            ->with(['tema.area.materia', 'alternativas'])
            ->lockForUpdate()
            ->get();

        if ($preguntas->isEmpty()) {
            throw ValidationException::withMessages([
                'plantilla' => 'La plantilla seleccionada no tiene preguntas evaluables.',
            ]);
        }

        foreach ($preguntas as $pregunta) {
            try {
                $this->validarPreguntaAplicable($pregunta);
            } catch (ValidationException) {
                throw ValidationException::withMessages([
                    'plantilla' => 'La plantilla contiene preguntas inactivas, archivadas o semánticamente inválidas.',
                ]);
            }
        }

        return $preguntas;
    }
}
