<?php

namespace App\Domains\Cat\Services;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\DTOs\DecisionCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use App\Domains\Cat\Models\InstrumentoCat;
use App\Domains\Cat\Models\IntentoCat;
use App\Domains\Cat\Models\RespuestaCat;
use App\Domains\Evaluaciones\Services\ConsistenciaEvaluacionService;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IntentoCatService
{
    public function __construct(
        private readonly AccesoEvaluacionService $acceso,
        private readonly ConsistenciaEvaluacionService $consistencia,
        private readonly MotorCat $motor,
    ) {}

    public function iniciar(Postulante $postulante, int $instrumentoId): IntentoCat
    {
        $intento = DB::transaction(function () use ($postulante, $instrumentoId) {
            $postulante = Postulante::whereKey($postulante->id_post)->lockForUpdate()->firstOrFail();
            $this->acceso->validar($postulante);
            $instrumento = InstrumentoCat::whereKey($instrumentoId)->lockForUpdate()->firstOrFail();
            $abierto = IntentoCat::where('id_post', $postulante->id_post)->where('id_instrumento_cat', $instrumentoId)
                ->where('estado', 'en_progreso')->lockForUpdate()->first();
            if ($abierto && ! $this->vencer($abierto)) {
                return $abierto;
            }
            if ($instrumento->estado !== 'activo' || ! $instrumento->demo || $instrumento->codigo !== 'MAT-CAT-DEMO-v1') {
                throw ValidationException::withMessages(['instrumento' => 'El instrumento CAT DEMO no está disponible.']);
            }
            $preguntas = $instrumento->preguntas()->with(['tema.area.materia', 'alternativas' => fn ($q) => $q->orderBy('id_alt')->lockForUpdate()])->lockForUpdate()->get();
            if ($preguntas->pluck('id_preg')->all() !== range(1, 16)) {
                throw ValidationException::withMessages(['instrumento' => 'El instrumento debe conservar exactamente el pool 1–16.']);
            }
            $items = [];
            foreach ($preguntas as $pregunta) {
                $this->consistencia->validarPreguntaAplicable($pregunta);
                if ($pregunta->tema?->area?->materia?->codigo_mat !== 'MAT' || $pregunta->tipo_preg !== 'opcion_multiple') {
                    throw ValidationException::withMessages(['instrumento' => 'El pool contiene una pregunta incompatible.']);
                }
                $p = ['a' => (float) $pregunta->pivot->a, 'b' => (float) $pregunta->pivot->b, 'c' => (float) $pregunta->pivot->c, 'origen' => $pregunta->pivot->origen];
                if (! is_finite($p['a']) || $p['a'] <= 0 || ! is_finite($p['b']) || ! is_finite($p['c']) || $p['c'] < 0 || $p['c'] >= 1) {
                    throw ValidationException::withMessages(['instrumento' => 'Parámetros DEMO inválidos.']);
                }
                $items[] = ['id_preg' => $pregunta->id_preg, 'enunciado' => $pregunta->enunciado_preg,
                    'id_tem' => $pregunta->id_tem, 'tema' => $pregunta->tema->nombre_tem,
                    'materia' => $pregunta->tema->area->materia->nombre_mat,
                    'dificultad_academica' => $pregunta->dificultad_preg, 'parametros' => $p,
                    'alternativas' => $pregunta->alternativas->where('estado_alt', 'activo')->sortBy('orden_alt')->map(fn ($a) => [
                        'id_alt' => $a->id_alt, 'letra' => $a->letra_alt, 'texto' => $a->texto_alt, 'correcta' => $a->es_correcta_alt,
                    ])->values()->all()];
            }
            $esperada = ['minimum' => 8, 'maximum' => 10, 'threshold' => 0.45, 'D' => 1, 'calibrado' => false];
            if (collect($instrumento->configuracion)->sortKeys()->all() !== collect($esperada)->sortKeys()->all() || $instrumento->duracion_minutos !== 30) {
                throw ValidationException::withMessages(['instrumento' => 'Configuración DEMO inesperada.']);
            }
            $snapshot = ['nombre' => $instrumento->nombre, 'version' => $instrumento->version,
                'version_parametros' => $instrumento->version_parametros, 'configuracion' => $instrumento->configuracion,
                'demo' => true, 'items' => $items];

            $inicio = now()->toImmutable();

            return IntentoCat::create(['id_instrumento_cat' => $instrumentoId, 'id_post' => $postulante->id_post,
                'snapshot' => $snapshot, 'huella_snapshot' => hash('sha256', json_encode($this->ordenarSnapshot($snapshot), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
                'iniciado_en' => $inicio, 'vence_en' => $inicio->addMinutes($instrumento->duracion_minutos),
                'estado' => 'en_progreso', 'revision' => 0, 'calculo_pendiente' => true]);
        }, 3);

        return $this->calcularPendiente($intento);
    }

    public function recuperar(Postulante $postulante, int $id): IntentoCat
    {
        $intento = $this->consultar($postulante, $id);
        if ($intento->estado === 'en_progreso') {
            $this->acceso->validar($postulante);
        }

        return $this->calcularPendiente($intento);
    }

    public function consultar(Postulante $postulante, int $id): IntentoCat
    {
        $intento = DB::transaction(function () use ($postulante, $id) {
            $intento = $this->propio($postulante, $id, true);
            $this->vencer($intento);

            return $intento;
        });

        return $intento;
    }

    public function responder(Postulante $postulante, int $id, array $data): IntentoCat
    {
        $this->acceso->validar($postulante);
        $intento = DB::transaction(function () use ($postulante, $id, $data) {
            $intento = $this->propio($postulante, $id, true);
            $previa = $intento->respuestas()->where('revision', $data['revision'] + 1)->first();
            if ($previa) {
                abort_unless((int) $previa->id_preg === (int) $data['id_preg'] && (int) $previa->id_alt === (int) $data['id_alt'], 409, 'La revisión ya contiene otra respuesta.');

                return $intento;
            }
            if ($this->vencer($intento)) {
                return $intento;
            }
            abort_unless($intento->estado === 'en_progreso' && ! $intento->calculo_pendiente
                && $intento->revision === (int) $data['revision'] && $intento->id_preg_actual === (int) $data['id_preg'], 409, 'El estado de la pregunta cambió. Recarga el intento.');
            $item = collect($intento->snapshot['items'])->firstWhere('id_preg', $data['id_preg']);
            $alternativa = collect($item['alternativas'])->firstWhere('id_alt', $data['id_alt']);
            if (! $alternativa) {
                throw ValidationException::withMessages(['id_alt' => 'La alternativa no pertenece al ítem emitido.']);
            }
            RespuestaCat::create(['id_intento_cat' => $id, 'id_preg' => $item['id_preg'], 'id_alt' => $alternativa['id_alt'],
                'revision' => $intento->revision + 1, 'correcta' => $alternativa['correcta'], 'respondida_en' => now()]);
            $intento->update(['revision' => $intento->revision + 1, 'id_preg_actual' => null, 'calculo_pendiente' => true, 'error_motor' => null]);

            return $intento;
        });

        // La respuesta está confirmada antes de consultar el motor, sin locks abiertos.
        return $this->calcularPendiente($intento);
    }

    public function propio(Postulante $postulante, int $id, bool $lock = false): IntentoCat
    {
        return IntentoCat::where('id_post', $postulante->id_post)->whereKey($id)->when($lock, fn ($q) => $q->lockForUpdate())->firstOrFail();
    }

    public function vencer(IntentoCat $intento): bool
    {
        if ($intento->estado === 'en_progreso' && now()->greaterThanOrEqualTo($intento->vence_en)) {
            $intento->update(['estado' => 'vencida', 'finalizado_en' => $intento->vence_en, 'id_preg_actual' => null, 'calculo_pendiente' => false]);
        }

        return $intento->estado === 'vencida';
    }

    private function calcularPendiente(IntentoCat $intento): IntentoCat
    {
        [$intento, $contexto] = DB::transaction(function () use ($intento) {
            $intento = IntentoCat::whereKey($intento->id_intento_cat)->lockForUpdate()->firstOrFail();
            $this->vencer($intento);
            if ($intento->estado !== 'en_progreso' || ! $intento->calculo_pendiente) {
                return [$intento, null];
            }
            $contexto = new ContextoCat($intento->id_intento_cat, $intento->revision, $intento->snapshot,
                $intento->respuestas()->get()->map(fn ($r) => ['id_preg' => (int) $r->id_preg, 'correcta' => $r->correcta, 'orden' => $r->revision])->all(), $intento->version_motor);

            return [$intento, $contexto];
        });
        if ($contexto === null) {
            return $intento;
        }
        try {
            $decision = $this->motor->calcular($contexto);
        } catch (MotorCatNoDisponible $exception) {
            IntentoCat::whereKey($intento->id_intento_cat)->where('estado', 'en_progreso')->where('revision', $contexto->revision)
                ->where('calculo_pendiente', true)->update(['error_motor' => $exception->codigo]);

            return $intento->refresh();
        }

        return DB::transaction(function () use ($intento, $contexto, $decision) {
            $intento = IntentoCat::whereKey($intento->id_intento_cat)->lockForUpdate()->firstOrFail();
            if ($this->vencer($intento) || $intento->estado !== 'en_progreso' || ! $intento->calculo_pendiente || $intento->revision !== $contexto->revision) {
                return $intento;
            }
            if (! $this->decisionValida($contexto, $decision)) {
                $intento->update(['error_motor' => 'decision_invalida']);

                return $intento;
            }
            $intento->update(['id_preg_actual' => $decision->siguienteId, 'calculo_pendiente' => false, 'error_motor' => null,
                'version_motor' => $decision->versionMotor, 'resultado_cat' => $decision->metricas(),
                'estado' => $decision->finalizada ? 'finalizada' : 'en_progreso', 'finalizado_en' => $decision->finalizada ? now() : null]);
            if ($contexto->revision > 0) {
                $intento->respuestas()->where('revision', $contexto->revision)->update(['metricas_cat' => $decision->metricas()]);
            }

            return $intento->refresh();
        });
    }

    private function decisionValida(ContextoCat $c, DecisionCat $d): bool
    {
        if (! is_finite($d->theta) || ! is_finite($d->sd) || $d->sd < 0 || count($d->intervalo) !== 2
            || ! is_numeric($d->intervalo[0] ?? null) || ! is_numeric($d->intervalo[1] ?? null)
            || ! is_finite((float) $d->intervalo[0]) || ! is_finite((float) $d->intervalo[1]) || $d->intervalo[0] > $d->intervalo[1]
            || trim($d->versionMotor) === '' || ($c->versionMotor !== null && $c->versionMotor !== $d->versionMotor)) {
            return false;
        }
        $n = count($c->historial);
        $config = $c->snapshot['configuracion'];
        if ($d->finalizada) {
            return $d->siguienteId === null && $n >= $config['minimum'] && $n <= $config['maximum'] && filled($d->motivo)
                && ($n === $config['maximum'] || $d->sd <= $config['threshold']);
        }

        return $n < $config['maximum'] && ! ($n >= $config['minimum'] && $d->sd <= $config['threshold'])
            && in_array($d->siguienteId, array_column($c->snapshot['items'], 'id_preg'), true)
            && ! in_array($d->siguienteId, array_column($c->historial, 'id_preg'), true);
    }

    private function ordenarSnapshot(array $value): array
    {
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($v) => is_array($v) ? $this->ordenarSnapshot($v) : $v, $value);
    }
}
