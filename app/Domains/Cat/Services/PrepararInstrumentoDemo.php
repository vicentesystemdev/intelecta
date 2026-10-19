<?php

namespace App\Domains\Cat\Services;

use App\Domains\Cat\Models\InstrumentoCat;
use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Services\ConsistenciaEvaluacionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrepararInstrumentoDemo
{
    public function __construct(private readonly ConsistenciaEvaluacionService $consistencia) {}

    public function ejecutar(): InstrumentoCat
    {
        return DB::transaction(function () {
            $existente = InstrumentoCat::where('codigo', 'MAT-CAT-DEMO-v1')->first();
            if ($existente) {
                return $existente; // No reconfigura instrumentos o intentos ya publicados.
            }
            $preguntas = Pregunta::with(['tema.area.materia', 'alternativas'])->whereIn('id_preg', range(1, 16))->orderBy('id_preg')->lockForUpdate()->get();
            if ($preguntas->pluck('id_preg')->all() !== range(1, 16)) {
                throw ValidationException::withMessages(['instrumento' => 'Faltan preguntas originales del pool 1–16.']);
            }
            foreach ($preguntas as $pregunta) {
                $this->consistencia->validarPreguntaAplicable($pregunta);
                if ($pregunta->tema?->area?->materia?->codigo_mat !== 'MAT' || $pregunta->tipo_preg !== 'opcion_multiple') {
                    throw ValidationException::withMessages(['instrumento' => 'El pool debe contener exclusivamente preguntas de Matemática de opción múltiple.']);
                }
            }
            $instrumento = InstrumentoCat::create(['codigo' => 'MAT-CAT-DEMO-v1', 'nombre' => 'Matemática CAT DEMO',
                'version' => '1', 'version_parametros' => 'simulados-demo-v1', 'demo' => true, 'estado' => 'activo',
                'duracion_minutos' => 30, 'configuracion' => ['minimum' => 8, 'maximum' => 10, 'threshold' => 0.45, 'D' => 1, 'calibrado' => false]]);
            $vinculos = [];
            foreach (range(1, 16) as $id) {
                // Diseño sintético explícito; NO usa dificultad académica ni número de alternativas.
                // a alterna 1.0/1.2/1.4/1.6, b distribuye [-2,2], c fijo experimental 0.15.
                $vinculos[$id] = ['a' => 1.0 + (($id - 1) % 4) * 0.2, 'b' => round(-2 + ($id - 1) * 4 / 15, 6),
                    'c' => 0.15, 'origen' => 'DEMO simulado determinista v1; sin calibración empírica'];
            }
            $instrumento->preguntas()->attach($vinculos);

            return $instrumento;
        });
    }
}
