<?php

namespace Database\Seeders;

use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\InscripcionAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Repositories\HabilitacionAcademicaRepository;
use Illuminate\Database\Seeder;

class MatriculasCuotasSeeder extends Seeder
{
    public function run(): void
    {
        $inscripciones = InscripcionAcademica::query()
            ->where('estado_inscripcion', 'activo')
            ->orderBy('id_insc')
            ->take(20)
            ->get();

        if ($inscripciones->isEmpty()) {
            return;
        }

        $habilitaciones = app(HabilitacionAcademicaRepository::class);

        foreach ($inscripciones as $index => $inscripcion) {
            $scenario = $index % 5;
            $estadoMatricula = match ($scenario) {
                2 => 'becada',
                3 => 'exenta',
                default => 'activa',
            };

            $matricula = MatriculaAcademica::updateOrCreate(
                ['id_insc' => $inscripcion->id_insc],
                [
                    'id_post' => $inscripcion->id_post,
                    'id_prog' => $inscripcion->id_prog,
                    'id_grupo' => $inscripcion->id_grupo,
                    'codigo_mat' => sprintf('MAT-2026-%05d', $inscripcion->id_insc),
                    'fecha_matricula_mat' => '2026-06-15',
                    'monto_matricula_mat' => $estadoMatricula === 'activa' ? 250 : 0,
                    'estado_matricula_mat' => $estadoMatricula,
                    'tipo_beneficio_mat' => match ($estadoMatricula) {
                        'becada' => 'Beca académica institucional',
                        'exenta' => 'Exención administrativa',
                        default => null,
                    },
                    'observacion_mat' => 'Registro administrativo interno del ciclo académico vigente.',
                ],
            );

            foreach ([1, 2, 3] as $numero) {
                $estado = match ($scenario) {
                    0 => $numero <= 2 ? 'pagada' : 'pendiente',
                    1 => $numero === 1 ? 'pagada' : ($numero === 2 ? 'vencida' : 'pendiente'),
                    2 => 'becada',
                    3 => 'exenta',
                    default => 'pagada',
                };

                CuotaAcademica::updateOrCreate(
                    ['id_mat' => $matricula->id_mat, 'nro_cuota' => $numero],
                    [
                        'concepto_cuota' => "Cuota académica {$numero}",
                        'monto_cuota' => in_array($estado, ['becada', 'exenta'], true) ? 0 : 450,
                        'fecha_vencimiento_cuota' => match ($numero) {
                            1 => '2026-06-30',
                            2 => '2026-07-31',
                            default => '2026-08-31',
                        },
                        'fecha_pago_cuota' => $estado === 'pagada'
                            ? match ($numero) {
                                1 => '2026-06-25',
                                2 => '2026-07-25',
                                default => '2026-08-25',
                            }
                            : null,
                        'metodo_pago_cuota' => $estado === 'pagada' ? 'Registro en caja' : null,
                        'estado_cuota' => $estado,
                        'observacion_cuota' => 'Control referencial de cuota para seguimiento administrativo.',
                    ],
                );
            }

            $habilitaciones->syncForMatricula($matricula->refresh());
        }
    }
}
