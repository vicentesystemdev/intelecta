<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Only scalar invariants; no academic eligibility or state transitions. */
    public function checks(): array
    {
        return [
            'postulantes' => ['id_post', [
                'intelecta_postulantes_edad_check' => 'edad_post BETWEEN 14 AND 80',
                'intelecta_postulantes_gestion_check' => 'gestion_post BETWEEN 2000 AND 32767',
            ]],
            'grupos_academicos' => ['id_grupo', [
                'intelecta_grupos_capacidad_check' => 'capacidad_grupo BETWEEN 1 AND 500',
            ]],
            'preguntas' => ['id_preg', [
                'intelecta_preguntas_puntaje_check' => 'puntaje_preg BETWEEN 0.01 AND 100',
                'intelecta_preguntas_tiempo_check' => 'tiempo_estimado_seg_preg BETWEEN 15 AND 7200',
            ]],
            'alternativas' => ['id_alt', [
                'intelecta_alternativas_orden_check' => 'orden_alt BETWEEN 1 AND 5',
            ]],
            'plantillas_evaluacion' => ['id_plan', [
                'intelecta_plantillas_duracion_check' => 'duracion_minutos_plan BETWEEN 1 AND 480',
            ]],
            'plantilla_preguntas' => [['id_plan', 'id_preg'], [
                'intelecta_plantilla_preguntas_orden_check' => 'orden_pp BETWEEN 1 AND 1000',
                'intelecta_plantilla_preguntas_puntaje_check' => 'puntaje_pp BETWEEN 0.01 AND 100',
            ]],
            'programas_academicos' => ['id_prog', [
                'intelecta_programas_fechas_check' => 'fecha_fin_prog >= fecha_inicio_prog',
            ]],
            'asignaciones_tutores' => ['id_asig', [
                'intelecta_asignaciones_fechas_check' => 'fecha_fin_asig >= fecha_inicio_asig',
            ]],
            'habilitaciones_academicas' => ['id_hab', [
                'intelecta_habilitaciones_fechas_check' => 'fecha_fin_hab >= fecha_inicio_hab',
            ]],
            'simulacros_programados' => ['id_sim', [
                'intelecta_simulacros_horas_check' => 'hora_fin_sim > hora_inicio_sim',
            ]],
            'matriculas_academicas' => ['id_mat', [
                'intelecta_matriculas_monto_check' => 'monto_matricula_mat BETWEEN 0 AND 99999999.99',
            ]],
            'cuotas_academicas' => ['id_cuota', [
                'intelecta_cuotas_monto_check' => 'monto_cuota BETWEEN 0 AND 99999999.99',
                'intelecta_cuotas_numero_check' => 'nro_cuota BETWEEN 1 AND 120',
            ]],
            'evaluaciones_aplicadas' => ['id_eval_apl', [
                'intelecta_evaluaciones_porcentaje_check' => 'porcentaje_eval_apl BETWEEN 0 AND 100',
            ]],
            'rendimientos_postulante' => ['id_rend', [
                'intelecta_rendimientos_asistencia_check' => 'asistencia_porcentaje_rend BETWEEN 0 AND 100',
            ]],
        ];
    }

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // PostgreSQL migrations are transactional. Never repair or delete existing records.
        foreach ($this->checks() as $table => [$primaryKey, $checks]) {
            foreach ($checks as $name => $condition) {
                $ids = DB::table($table)->whereRaw("NOT ($condition)")->get((array) $primaryKey);
                if ($ids->isNotEmpty()) {
                    throw new RuntimeException("No se puede aplicar $name. Registros incompatibles en $table: ".$ids->toJson());
                }
            }
        }

        foreach ($this->checks() as $table => [$primaryKey, $checks]) {
            foreach ($checks as $name => $condition) {
                DB::statement("ALTER TABLE \"$table\" ADD CONSTRAINT \"$name\" CHECK ($condition)");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->checks() as $table => [$primaryKey, $checks]) {
            foreach ($checks as $name => $condition) {
                DB::statement("ALTER TABLE \"$table\" DROP CONSTRAINT IF EXISTS \"$name\"");
            }
        }
    }
};
