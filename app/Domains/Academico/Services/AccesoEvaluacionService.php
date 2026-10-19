<?php

namespace App\Domains\Academico\Services;

use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\SimulacroProgramado;
use App\Domains\Postulantes\Models\Postulante;
use App\Support\Validation\AcademicDatePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AccesoEvaluacionService
{
    public function habilitacion(Postulante $postulante, ?SimulacroProgramado $simulacro = null): ?HabilitacionAcademica
    {
        if (! Schema::hasTable('habilitaciones_academicas')) {
            return null;
        }

        $inscripcionDelContexto = function (Builder $query) use ($postulante, $simulacro): void {
            $query->where('id_post', $postulante->id_post)
                ->when($simulacro, fn (Builder $contexto) => $contexto
                    ->where('id_prog', $simulacro->id_prog)
                    ->when($simulacro->id_grupo, fn (Builder $grupo) => $grupo->where('id_grupo', $simulacro->id_grupo)));
        };
        $inscripcionVigente = function (Builder $query) use ($inscripcionDelContexto): void {
            $inscripcionDelContexto($query);
            $query
                ->where('estado_inscripcion', 'activo')
                ->whereHas('programa', fn (Builder $programa) => $programa->where('estado_prog', 'activo'))
                ->where(fn (Builder $grupo) => $grupo->whereNull('id_grupo')
                    ->orWhereHas('grupo', fn (Builder $activo) => $activo->where('estado_grupo', 'activo')));
        };
        $campos = [
            'estado_hab',
            'motivo_hab',
            'habilitado_evaluaciones_hab',
            'habilitado_simulacros_hab',
            'habilitado_reportes_hab',
        ];
        $hoy = AcademicDatePolicy::todayString();
        $historicas = HabilitacionAcademica::query()
            ->where('id_post', $postulante->id_post)
            ->where(fn (Builder $matricula) => $matricula->whereNull('id_mat')
                ->orWhereHas('matricula', fn (Builder $propia) => $propia->where('id_post', $postulante->id_post)))
            ->where(fn (Builder $contexto) => $contexto
                ->where(fn (Builder $global) => $global->whereNull('id_insc')->whereNull('id_mat'))
                ->orWhereHas('inscripcion', $inscripcionDelContexto)
                ->orWhere(fn (Builder $sinInscripcion) => $sinInscripcion->whereNull('id_insc')
                    ->whereHas('matricula.inscripcion', $inscripcionDelContexto)));

        $vigente = (clone $historicas)
            ->where(fn (Builder $fecha) => $fecha->whereNull('fecha_inicio_hab')->orWhereDate('fecha_inicio_hab', '<=', $hoy))
            ->where(fn (Builder $fecha) => $fecha->whereNull('fecha_fin_hab')->orWhereDate('fecha_fin_hab', '>=', $hoy))
            ->where(fn (Builder $contexto) => $contexto
                // Una habilitación sin vínculos académicos es un ajuste global explícito.
                ->where(fn (Builder $global) => $global->whereNull('id_insc')->whereNull('id_mat'))
                ->orWhereHas('inscripcion', $inscripcionVigente)
                ->orWhere(fn (Builder $sinInscripcion) => $sinInscripcion->whereNull('id_insc')
                    ->whereHas('matricula.inscripcion', $inscripcionVigente)))
            ->latest('id_hab')
            ->first($campos);

        // Fechas legacy no acreditan un cambio de decisión administrativa. Si no
        // hay reemplazo vigente, conservar el estado registrado evita convertir
        // una restricción por deuda en permiso libre por el simple paso del tiempo.
        return $vigente ?? $historicas->latest('id_hab')->first($campos);
    }

    /** @return array{permitido: bool, motivo: ?string, habilitacion: ?HabilitacionAcademica} */
    public function evaluar(?Postulante $postulante, ?SimulacroProgramado $simulacro = null): array
    {
        if (! $postulante || $postulante->trashed() || $postulante->estado_post !== 'activo') {
            return [
                'permitido' => false,
                'motivo' => 'Tu cuenta no está vinculada a un postulante académico activo.',
                'habilitacion' => null,
            ];
        }

        $habilitacion = $this->habilitacion($postulante, $simulacro);
        $motivo = match (true) {
            $habilitacion?->habilitado_evaluaciones_hab === false => 'Tu acceso a evaluaciones se encuentra temporalmente restringido. Consulta con administración académica.',
            $simulacro && $habilitacion?->habilitado_simulacros_hab === false => 'Tu acceso a simulacros se encuentra temporalmente restringido. Consulta con administración académica.',
            default => null,
        };

        // Compatibilidad: las evaluaciones libres no exigen matrícula si no hay
        // una habilitación aplicable. No se crea ni se persiste un permiso nuevo.
        return ['permitido' => $motivo === null, 'motivo' => $motivo, 'habilitacion' => $habilitacion];
    }

    public function validar(?Postulante $postulante, ?SimulacroProgramado $simulacro = null): void
    {
        $decision = $this->evaluar($postulante, $simulacro);
        if (! $decision['permitido']) {
            throw ValidationException::withMessages([
                ! $postulante || $postulante->estado_post !== 'activo' ? 'postulante' : 'habilitacion' => $decision['motivo'],
            ]);
        }
    }
}
