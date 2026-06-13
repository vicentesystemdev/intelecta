<?php

namespace Database\Seeders;

use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\InscripcionAcademica;
use Illuminate\Database\Seeder;

class AsistenciasAcademicasSeeder extends Seeder
{
    public function run(): void
    {
        $inscripciones = InscripcionAcademica::query()
            ->where('estado_inscripcion', 'activo')
            ->whereNotNull('id_grupo')
            ->orderBy('id_grupo')
            ->orderBy('id_post')
            ->take(60)
            ->get();

        if ($inscripciones->isEmpty()) {
            return;
        }

        $fechas = ['2026-06-08', '2026-06-09', '2026-06-10', '2026-06-11', '2026-06-12'];
        $estados = ['presente', 'presente', 'presente', 'retraso', 'justificado', 'ausente'];
        $tutoresPorGrupo = AsignacionTutor::query()
            ->where('estado_asig', 'activo')
            ->whereNotNull('id_grupo')
            ->orderByDesc('id_asig')
            ->get()
            ->unique('id_grupo')
            ->keyBy('id_grupo');

        foreach ($inscripciones as $index => $inscripcion) {
            foreach ($fechas as $dayIndex => $fecha) {
                $estado = $estados[($index + $dayIndex) % count($estados)];
                $attributes = [
                    'id_grupo' => $inscripcion->id_grupo,
                    'id_post' => $inscripcion->id_post,
                    'fecha_asist' => $fecha,
                    'sesion_asist' => 'General',
                ];
                $values = [
                    'id_prog' => $inscripcion->id_prog,
                    'id_tutor' => $tutoresPorGrupo->get($inscripcion->id_grupo)?->id_tutor,
                    'estado_asist' => $estado,
                    'observacion_asist' => match ($estado) {
                        'retraso' => 'Ingreso posterior al inicio de la sesión.',
                        'justificado' => 'Inasistencia respaldada por coordinación académica.',
                        'ausente' => 'Ausencia registrada para seguimiento tutorial.',
                        default => null,
                    },
                ];

                $registro = AsistenciaAcademica::withTrashed()->firstOrNew($attributes);
                $registro->fill($values);
                $registro->deleted_at = null;
                $registro->save();
            }
        }
    }
}
