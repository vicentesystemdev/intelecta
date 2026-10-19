<?php

namespace App\Domains\Cat\Services;

use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\DTOs\DecisionCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use Illuminate\Support\Facades\Http;
use Throwable;

class MotorCatHttp implements MotorCat
{
    public function __construct(private readonly ContratoCat $contrato) {}

    public function calcular(ContextoCat $contexto): DecisionCat
    {
        $request = $this->contrato->solicitud($contexto);
        $base = rtrim((string) config('services.cat.url'), '/');
        $url = parse_url($base);
        $token = (string) config('services.cat.token');
        if (! $token || ! $url || ! in_array($url['scheme'] ?? null, ['http', 'https'], true) || empty($url['host'])
            || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment'])
            || ! empty($url['path'])) {
            $this->fallar('servicio_no_configurado');
        }
        try {
            // Sin redirecciones, retries implícitos, throw() con cuerpo remoto ni logs de requests.
            $response = Http::acceptJson()->asJson()->withoutRedirecting()
                ->connectTimeout(max(1, min(10, (int) config('services.cat.connect_timeout', 2))))
                ->timeout(max(1, min(30, (int) config('services.cat.timeout', 10))))
                ->withHeaders(['X-Service-Token' => $token])
                ->post($base.'/api/integracion/cat/evaluar', $request);
        } catch (Throwable) {
            // Nunca encadenar excepciones que puedan incluir cabeceras/credenciales.
            $this->fallar('fallo_transporte');
        }
        if ($response->status() !== 200) {
            $this->fallar('http_'.$response->status());
        }
        if (strlen($response->body()) > 32768 || ! str_contains(strtolower((string) $response->header('Content-Type')), 'application/json')) {
            $this->fallar('respuesta_invalida');
        }
        try {
            $data = json_decode($response->body(), true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $this->fallar('respuesta_invalida');
        }
        if (! $this->valida($data, $request)) {
            $this->fallar('respuesta_invalida');
        }

        return new DecisionCat($data['siguiente_id_preg'], $data['terminado'], (float) $data['theta'], (float) $data['sd'],
            $data['intervalo'], $data['motivo'], $data['motor_version']);
    }

    private function valida(mixed $d, array $r): bool
    {
        $keys = ['contrato_version', 'id_intento_cat', 'revision', 'motor_version', 'banco_version', 'parametros_version',
            'theta', 'sd', 'intervalo', 'respondidas', 'siguiente_id_preg', 'terminado', 'motivo'];
        if (! is_array($d) || count($d) !== count($keys) || array_diff($keys, array_keys($d)) !== []) {
            return false;
        }
        foreach (['contrato_version', 'id_intento_cat', 'revision', 'motor_version'] as $key) {
            if ($d[$key] !== $r[$key]) {
                return false;
            }
        }
        if ($d['banco_version'] !== $r['banco']['banco_version'] || $d['parametros_version'] !== $r['banco']['parametros_version']
            || $d['respondidas'] !== count($r['historial']) || ! is_bool($d['terminado'])) {
            return false;
        }
        $number = fn ($v) => (is_int($v) || is_float($v)) && is_finite((float) $v);
        if (! $number($d['theta']) || ! $number($d['sd']) || $d['sd'] < 0 || ! is_array($d['intervalo'])
            || ! array_is_list($d['intervalo']) || count($d['intervalo']) !== 2
            || ! $number($d['intervalo'][0]) || ! $number($d['intervalo'][1]) || $d['intervalo'][0] > $d['intervalo'][1]) {
            return false;
        }
        $n = $d['respondidas'];
        if ($d['terminado']) {
            $motivo = match (true) {
                $n >= 8 && $d['sd'] <= 0.45 => 'Umbral de incertidumbre alcanzado',
                $n === 10 => 'Máximo de preguntas alcanzado',
                default => null,
            };

            return $d['siguiente_id_preg'] === null && $motivo !== null && $d['motivo'] === $motivo;
        }

        return $d['motivo'] === null && $n < 10 && ! ($n >= 8 && $d['sd'] <= 0.45)
            && is_int($d['siguiente_id_preg']) && in_array($d['siguiente_id_preg'], range(1, 16), true)
            && ! in_array($d['siguiente_id_preg'], array_column($r['historial'], 'id_preg'), true);
    }

    private function fallar(string $codigo): never
    {
        throw new MotorCatNoDisponible('El cálculo CAT no está disponible o no pudo verificarse.', $codigo);
    }
}
