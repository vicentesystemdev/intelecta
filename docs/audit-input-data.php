<?php

// Read-only preflight. Run from the repository: php docs/audit-input-data.php
use App\Rules\InputFormat;
use App\Support\Validation\InputNormalizer;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
    'users' => ['id', 'Auth\\RegisterRequest'],
    // Existing records follow update rules, including nullable legacy birth dates.
    'postulantes' => ['id_post', 'Postulantes\\UpdatePostulanteRequest'],
    'tutores_academicos' => ['id_tutor', 'Institucional\\TutorAcademicoRequest'],
    'programas_academicos' => ['id_prog', 'Institucional\\ProgramaAcademicoRequest'],
    'grupos_academicos' => ['id_grupo', 'Institucional\\GrupoAcademicoRequest'],
    'inscripciones_academicas' => ['id_insc', 'Institucional\\InscripcionAcademicaRequest'],
    'simulacros_programados' => ['id_sim', 'Institucional\\SimulacroProgramadoRequest'],
    'asignaciones_tutores' => ['id_asig', 'Institucional\\AsignacionTutorRequest'],
    'matriculas_academicas' => ['id_mat', 'Institucional\\MatriculaAcademicaRequest'],
    'cuotas_academicas' => ['id_cuota', 'Institucional\\CuotaAcademicaRequest'],
    'habilitaciones_academicas' => ['id_hab', 'Institucional\\HabilitacionAcademicaRequest'],
    'asistencias_academicas' => ['id_asist', 'Institucional\\AsistenciaAcademicaRequest'],
    'areas_conocimiento' => ['id_area', 'Evaluaciones\\StoreAreaConocimientoRequest'],
    'temas' => ['id_tem', 'Evaluaciones\\StoreTemaRequest'],
    'preguntas' => ['id_preg', 'Evaluaciones\\StorePreguntaRequest'],
    'plantillas_evaluacion' => ['id_plan', 'Evaluaciones\\StorePlantillaEvaluacionRequest'],
];

$report = ['read_only' => true, 'tables' => [], 'incompatible' => [], 'normalization_needed' => [], 'checks' => []];
foreach ($tables as $table => [$primaryKey, $class]) {
    $class = 'App\\Http\\Requests\\'.$class;
    $request = $class::create('/', 'POST');
    $request->setContainer($app);
    $columns = Schema::getColumnListing($table);
    $rules = [];
    foreach ($request->rules() as $field => $fieldRules) {
        // Never read credential columns or audit academic/identity relationships here.
        if (! in_array($field, $columns, true) || str_contains($field, 'password')) {
            continue;
        }
        $rules[$field] = ['bail'];
        foreach ($fieldRules as $rule) {
            if ($rule instanceof Closure || $rule instanceof Exists || $rule instanceof Unique) {
                continue;
            }
            if (is_string($rule) && preg_match('/^(exists:|unique:|required_without:)/', $rule)) {
                continue;
            }
            $rules[$field][] = $rule;
            if ($rule === 'string') {
                $rules[$field][] = new InputFormat('text');
            }
        }
    }
    $rows = DB::table($table)->get(array_unique([$primaryKey, ...array_keys($rules)]));
    $report['tables'][$table] = ['records' => $rows->count(), 'fields' => count($rules)];
    foreach ($rows as $row) {
        $raw = (array) $row;
        foreach (['hora_inicio_sim', 'hora_fin_sim'] as $field) {
            if (isset($raw[$field])) {
                $raw[$field] = substr($raw[$field], 0, 5);
            }
        }
        $normalized = InputNormalizer::normalize($raw);
        $validator = Validator::make($normalized, $rules);
        if ($validator->fails()) {
            $report['incompatible'][] = ['table' => $table, 'id' => $row->$primaryKey, 'errors' => $validator->errors()->toArray()];
        }
        $changed = array_keys(array_filter($raw, fn ($value, $key) => $normalized[$key] !== $value, ARRAY_FILTER_USE_BOTH));
        if ($changed) {
            $report['normalization_needed'][] = ['table' => $table, 'id' => $row->$primaryKey, 'fields' => $changed];
        }
    }
}

$migration = require __DIR__.'/../database/migrations/2026_09_07_000000_add_basic_input_checks.php';
foreach ($migration->checks() as $table => [$primaryKey, $checks]) {
    foreach ($checks as $name => $condition) {
        $report['checks'][$name] = DB::table($table)->whereRaw("NOT ($condition)")->get((array) $primaryKey)->all();
    }
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), PHP_EOL;
