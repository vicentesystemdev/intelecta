<?php

namespace App\Domains\Reportes\Services;

use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Resultados\Models\EvaluacionAplicada;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ReporteExportacionService
{
    private const TIPOS = [
        'rendimiento' => [
            'titulo' => 'Reporte de Rendimiento Academico',
            'descripcion' => 'Consolida el desempeno academico de los postulantes a partir de evaluaciones aplicadas, asistencia y seguimiento institucional.',
            'archivo' => 'reporte_rendimiento_academico',
            'hoja' => 'Rendimiento',
        ],
        'evaluaciones' => [
            'titulo' => 'Reporte de Evaluaciones Aplicadas',
            'descripcion' => 'Lista las evaluaciones finalizadas con puntajes calculados desde respuestas registradas.',
            'archivo' => 'reporte_evaluaciones_aplicadas',
            'hoja' => 'Evaluaciones',
        ],
        'asistencia' => [
            'titulo' => 'Reporte de Asistencia Academica',
            'descripcion' => 'Resume la asistencia por postulante, programa y grupo academico.',
            'archivo' => 'reporte_asistencia_academica',
            'hoja' => 'Asistencia',
        ],
        'habilitacion' => [
            'titulo' => 'Reporte de Habilitacion Academica',
            'descripcion' => 'Presenta el estado administrativo-academico de los postulantes segun matricula, cuotas y habilitacion.',
            'archivo' => 'reporte_habilitacion_academica',
            'hoja' => 'Habilitacion',
        ],
    ];

    public function obtenerReporte(string $tipo): array
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        $filas = $this->obtenerFilas($tipo);

        return [
            'tipo' => $tipo,
            'titulo' => self::TIPOS[$tipo]['titulo'],
            'descripcion' => self::TIPOS[$tipo]['descripcion'],
            'columnas' => $this->obtenerColumnas($tipo),
            'filas' => $filas,
            'resumen' => $this->obtenerResumen($tipo, $filas),
            'hoja' => self::TIPOS[$tipo]['hoja'],
            'generado_en' => now(),
        ];
    }

    public function obtenerNombreArchivo(string $tipo, string $extension): string
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        return self::TIPOS[$tipo]['archivo'] . '.' . ltrim($extension, '.');
    }

    public function obtenerColumnas(string $tipo): array
    {
        return match ($tipo) {
            'rendimiento' => [
                'postulante' => 'Postulante',
                'programa' => 'Programa',
                'grupo' => 'Grupo',
                'universidad' => 'Universidad objetivo',
                'carrera' => 'Carrera objetivo',
                'promedio' => 'Promedio',
                'evaluaciones' => 'Evaluaciones finalizadas',
                'asistencia' => 'Asistencia',
                'estado' => 'Estado academico',
                'ultima_evaluacion' => 'Ultima evaluacion',
            ],
            'evaluaciones' => [
                'postulante' => 'Postulante',
                'plantilla' => 'Plantilla',
                'tipo' => 'Tipo',
                'puntaje' => 'Puntaje',
                'porcentaje' => 'Porcentaje',
                'estado' => 'Estado',
                'fecha_finalizacion' => 'Fecha de finalizacion',
                'tiempo' => 'Tiempo total',
            ],
            'asistencia' => [
                'postulante' => 'Postulante',
                'programa' => 'Programa',
                'grupo' => 'Grupo',
                'presentes' => 'Presentes',
                'ausentes' => 'Ausentes',
                'retrasos' => 'Retrasos',
                'justificados' => 'Justificados',
                'porcentaje' => 'Porcentaje de asistencia',
            ],
            'habilitacion' => [
                'postulante' => 'Postulante',
                'programa' => 'Programa',
                'grupo' => 'Grupo',
                'estado_matricula' => 'Estado de matricula',
                'estado_cuotas' => 'Estado de cuotas',
                'habilitacion' => 'Habilitacion academica',
                'acceso_evaluaciones' => 'Acceso a evaluaciones',
                'observacion' => 'Observacion',
            ],
            default => [],
        };
    }

    public function obtenerFilas(string $tipo): Collection
    {
        return match ($tipo) {
            'rendimiento' => $this->filasRendimiento(),
            'evaluaciones' => $this->filasEvaluaciones(),
            'asistencia' => $this->filasAsistencia(),
            'habilitacion' => $this->filasHabilitacion(),
            default => collect(),
        };
    }

    public function obtenerResumen(string $tipo, ?Collection $filas = null): array
    {
        $filas ??= $this->obtenerFilas($tipo);

        return match ($tipo) {
            'rendimiento' => [
                'Registros' => $filas->count(),
                'Promedio institucional' => $this->promedio($filas, 'promedio') . '%',
                'Asistencia promedio' => $this->promedio($filas, 'asistencia') . '%',
            ],
            'evaluaciones' => [
                'Evaluaciones finalizadas' => $filas->count(),
                'Promedio general' => $this->promedio($filas, 'porcentaje') . '%',
                'Puntaje promedio' => $this->promedio($filas, 'puntaje'),
            ],
            'asistencia' => [
                'Registros consolidados' => $filas->count(),
                'Asistencia promedio' => $this->promedio($filas, 'porcentaje') . '%',
                'Ausencias registradas' => $filas->sum(fn (array $fila) => (int) $fila['ausentes']),
            ],
            'habilitacion' => [
                'Matriculas evaluadas' => $filas->count(),
                'Habilitados' => $filas->where('habilitacion', 'habilitado')->count(),
                'Con restriccion u observacion' => $filas->filter(fn (array $fila) => in_array($fila['habilitacion'], ['observado', 'restringido', 'temporal'], true))->count(),
            ],
            default => ['Registros' => $filas->count()],
        };
    }

    private function filasRendimiento(): Collection
    {
        $rendimientos = RendimientoPostulante::query()
            ->with([
                'postulante.carrera.universidad',
                'programa',
                'grupo',
            ])
            ->orderByDesc('promedio_general_rend')
            ->get();

        $evaluaciones = EvaluacionAplicada::query()
            ->whereIn('id_post', $rendimientos->pluck('id_post')->filter()->unique())
            ->where('estado_eval_apl', 'finalizada')
            ->get()
            ->groupBy('id_post');

        return $rendimientos->map(function (RendimientoPostulante $rendimiento) use ($evaluaciones) {
            $postulante = $rendimiento->postulante;
            $carrera = $postulante?->carrera;
            $evaluacionesPostulante = $evaluaciones->get($rendimiento->id_post, collect());

            return [
                'postulante' => $this->nombrePostulante($postulante),
                'programa' => $rendimiento->programa?->nombre_prog ?? 'Sin programa',
                'grupo' => $rendimiento->grupo?->nombre_grupo ?? 'Sin grupo',
                'universidad' => $carrera?->universidad?->sigla_uni ?: ($carrera?->universidad?->nombre_uni ?? 'Sin universidad'),
                'carrera' => $carrera?->nombre_car ?? 'Sin carrera',
                'promedio' => $this->numero($rendimiento->promedio_general_rend),
                'evaluaciones' => $evaluacionesPostulante->count(),
                'asistencia' => $this->numero($rendimiento->asistencia_porcentaje_rend),
                'estado' => $rendimiento->nivel_riesgo_rend ?? 'Sin seguimiento',
                'ultima_evaluacion' => $this->fecha($evaluacionesPostulante->max('fecha_fin_eval_apl')),
            ];
        })->values();
    }

    private function filasEvaluaciones(): Collection
    {
        return EvaluacionAplicada::query()
            ->with(['postulante', 'plantilla'])
            ->where('estado_eval_apl', 'finalizada')
            ->orderByDesc('fecha_fin_eval_apl')
            ->get()
            ->map(fn (EvaluacionAplicada $evaluacion) => [
                'postulante' => $this->nombrePostulante($evaluacion->postulante),
                'plantilla' => $evaluacion->plantilla?->nombre_plan ?? 'Sin plantilla',
                'tipo' => $evaluacion->tipo_eval_apl ?? 'Evaluacion academica',
                'puntaje' => $this->numero($evaluacion->puntaje_total_eval_apl) . ' / ' . $this->numero($evaluacion->puntaje_maximo_eval_apl),
                'porcentaje' => $this->numero($evaluacion->porcentaje_eval_apl),
                'estado' => $evaluacion->estado_eval_apl,
                'fecha_finalizacion' => $this->fecha($evaluacion->fecha_fin_eval_apl),
                'tiempo' => $this->tiempo($evaluacion->tiempo_total_segundos_eval_apl),
            ])->values();
    }

    private function filasAsistencia(): Collection
    {
        return AsistenciaAcademica::query()
            ->with(['postulante', 'programa', 'grupo'])
            ->orderByDesc('fecha_asist')
            ->get()
            ->groupBy(fn (AsistenciaAcademica $asistencia) => implode('|', [
                $asistencia->id_post,
                $asistencia->id_prog ?? 'sin-programa',
                $asistencia->id_grupo ?? 'sin-grupo',
            ]))
            ->map(function (Collection $registros) {
                $primero = $registros->first();
                $total = max($registros->count(), 1);
                $presentes = $registros->where('estado_asist', 'presente')->count();
                $retrasos = $registros->where('estado_asist', 'retraso')->count();
                $justificados = $registros->where('estado_asist', 'justificado')->count();

                return [
                    'postulante' => $this->nombrePostulante($primero?->postulante),
                    'programa' => $primero?->programa?->nombre_prog ?? 'Sin programa',
                    'grupo' => $primero?->grupo?->nombre_grupo ?? 'Sin grupo',
                    'presentes' => $presentes,
                    'ausentes' => $registros->where('estado_asist', 'ausente')->count(),
                    'retrasos' => $retrasos,
                    'justificados' => $justificados,
                    'porcentaje' => round((($presentes + $retrasos + $justificados) / $total) * 100, 2),
                ];
            })
            ->sortByDesc('porcentaje')
            ->values();
    }

    private function filasHabilitacion(): Collection
    {
        return MatriculaAcademica::query()
            ->with(['postulante', 'programa', 'grupo', 'cuotas', 'habilitacion'])
            ->orderByDesc('fecha_matricula_mat')
            ->get()
            ->map(function (MatriculaAcademica $matricula) {
                $cuotas = $matricula->cuotas;
                $estadoCuotas = $this->estadoCuotas($cuotas);
                $habilitacion = $matricula->habilitacion;

                return [
                    'postulante' => $this->nombrePostulante($matricula->postulante),
                    'programa' => $matricula->programa?->nombre_prog ?? 'Sin programa',
                    'grupo' => $matricula->grupo?->nombre_grupo ?? 'Sin grupo',
                    'estado_matricula' => $matricula->estado_matricula_mat ?? 'Sin registro',
                    'estado_cuotas' => $estadoCuotas,
                    'habilitacion' => $habilitacion?->estado_hab ?? 'Sin registro',
                    'acceso_evaluaciones' => $habilitacion?->habilitado_evaluaciones_hab === false ? 'Restringido' : 'Habilitado',
                    'observacion' => $habilitacion?->motivo_hab
                        ?: ($habilitacion?->observacion_hab ?: ($matricula->observacion_mat ?: 'Sin observacion')),
                ];
            })->values();
    }

    private function estadoCuotas(Collection $cuotas): string
    {
        if ($cuotas->isEmpty()) {
            return 'Sin cuotas registradas';
        }

        if ($cuotas->contains('estado_cuota', 'vencida')) {
            return 'Con cuotas vencidas';
        }

        if ($cuotas->contains('estado_cuota', 'pendiente')) {
            return 'Con cuotas pendientes';
        }

        if ($cuotas->every(fn ($cuota) => in_array($cuota->estado_cuota, ['pagada', 'becada', 'exenta'], true))) {
            return 'Al dia';
        }

        return Str::headline($cuotas->pluck('estado_cuota')->filter()->unique()->implode(', '));
    }

    private function nombrePostulante($postulante): string
    {
        if (! $postulante) {
            return 'Sin postulante';
        }

        return trim(($postulante->nombres_post ?? '') . ' ' . ($postulante->apellidos_post ?? '')) ?: 'Sin nombre registrado';
    }

    private function fecha($valor): string
    {
        if (! $valor) {
            return 'Sin registro';
        }

        return rescue(fn () => $valor instanceof \DateTimeInterface
            ? $valor->format('d/m/Y H:i')
            : Carbon::parse($valor)->format('d/m/Y H:i'), 'Sin registro', false);
    }

    private function tiempo(?int $segundos): string
    {
        if (! $segundos) {
            return 'Sin registro';
        }

        $minutos = intdiv($segundos, 60);
        $resto = $segundos % 60;

        return sprintf('%d min %02d s', $minutos, $resto);
    }

    private function numero($valor): float
    {
        return round((float) $valor, 2);
    }

    private function promedio(Collection $filas, string $campo): float
    {
        $valores = $filas
            ->pluck($campo)
            ->map(fn ($valor) => is_numeric($valor) ? (float) $valor : (float) Str::of((string) $valor)->before(' /')->toString())
            ->filter(fn ($valor) => $valor > 0);

        return $valores->isEmpty() ? 0.0 : round($valores->avg(), 2);
    }
}
