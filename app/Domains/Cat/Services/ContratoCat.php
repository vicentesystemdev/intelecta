<?php

namespace App\Domains\Cat\Services;

use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;

class ContratoCat
{
    public const VERSION = '1';

    public const MOTOR = 'cat-d1-eap-v1';

    public const BANCO = 'MAT-CAT-DEMO-v1';

    public const PARAMETROS = 'simulados-demo-v1';

    public const HASH = 'f1abb3f562696e7a15d0a2e894bd8916bb2661fd263334f281a549f06c13d6c8';

    public function solicitud(ContextoCat $contexto): array
    {
        $s = $contexto->snapshot;
        if ($contexto->id_intento_cat < 1 || $contexto->revision !== count($contexto->historial)
            || $contexto->revision > 10 || ($contexto->versionMotor !== null && $contexto->versionMotor !== self::MOTOR)
            || ($s['version_parametros'] ?? null) !== self::PARAMETROS || ($s['demo'] ?? null) !== true) {
            $this->fallar('contexto_incompatible');
        }
        $items = array_map(function ($item) {
            $p = $item['parametros'] ?? [];
            $row = ['id_preg' => $item['id_preg'] ?? null];
            foreach (['a' => [0.1, 3], 'b' => [-4, 4], 'c' => [0, 0.5]] as $key => [$min, $max]) {
                $v = $p[$key] ?? null;
                if (! is_int($v) && ! is_float($v)) {
                    $this->fallar('parametros_invalidos');
                }
                if (! is_finite((float) $v) || $v < $min || $v > $max || ($key === 'c' && $v === 0.5)) {
                    $this->fallar('parametros_invalidos');
                }
                // Representación de transporte de seis decimales, sin cambiar el snapshot.
                // Tolera únicamente ruido IEEE-754 del almacenamiento previo.
                if (abs($v - round($v, 6)) > 1e-12) {
                    $this->fallar('precision_incompatible');
                }
                $row[$key] = round($v, 6);
            }
            if (! is_int($row['id_preg'])) {
                $this->fallar('ids_invalidos');
            }

            return $row;
        }, $s['items'] ?? []);
        usort($items, fn ($a, $b) => $a['id_preg'] <=> $b['id_preg']);
        if (array_column($items, 'id_preg') !== range(1, 16)) {
            $this->fallar('pool_incompatible');
        }
        $config = $s['configuracion'] ?? [];
        if (($config['D'] ?? null) !== 1 || ($config['calibrado'] ?? null) !== false
            || ($config['minimum'] ?? null) !== 8 || ($config['maximum'] ?? null) !== 10
            || ($config['threshold'] ?? null) !== 0.45) {
            $this->fallar('configuracion_incompatible');
        }
        $hash = $this->parametrosHash($items);
        if (! hash_equals(self::HASH, $hash)) {
            $this->fallar('parametros_hash_incompatible');
        }
        $seen = [];
        foreach ($contexto->historial as $pos => $r) {
            if (array_keys($r) !== ['id_preg', 'correcta', 'orden'] || ! is_int($r['id_preg'])
                || ! in_array($r['id_preg'], range(1, 16), true) || ! is_bool($r['correcta'])
                || $r['orden'] !== $pos + 1 || in_array($r['id_preg'], $seen, true)) {
                $this->fallar('historial_incompatible');
            }
            $seen[] = $r['id_preg'];
        }

        return ['contrato_version' => self::VERSION, 'id_intento_cat' => $contexto->id_intento_cat,
            'revision' => $contexto->revision, 'motor_version' => self::MOTOR,
            'banco' => ['banco_id' => self::BANCO, 'banco_version' => $this->bancoVersion($s),
                'parametros_version' => self::PARAMETROS, 'parametros_hash' => $hash,
                'experimental' => true, 'calibrado' => false, 'D' => 1, 'items' => $items],
            'config' => ['minimum' => 8, 'maximum' => 10, 'threshold' => 0.45], 'historial' => $contexto->historial];
    }

    public function parametrosHash(array $items): string
    {
        usort($items, fn ($a, $b) => $a['id_preg'] <=> $b['id_preg']);
        $fixed = function ($v) {
            $s = sprintf('%.6F', $v);

            return $s === '-0.000000' ? '0.000000' : $s;
        };
        $rows = array_map(fn ($r) => ['a' => $fixed($r['a']), 'b' => $fixed($r['b']), 'c' => $fixed($r['c']), 'id_preg' => (int) $r['id_preg']], $items);

        return hash('sha256', json_encode(['D' => 1, 'calibrado' => false, 'experimental' => true,
            'items' => $rows, 'parametros_version' => self::PARAMETROS], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function bancoVersion(array $snapshot): string
    {
        // Solo contenido académico. No incluye parámetros, configuración ni identidad del intento.
        $items = array_map(function ($item) {
            unset($item['parametros']);

            return $item;
        }, $snapshot['items']);

        return hash('sha256', json_encode($this->ordenar($items), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function ordenar(array $value): array
    {
        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(fn ($v) => is_array($v) ? $this->ordenar($v) : $v, $value);
    }

    private function fallar(string $codigo): never
    {
        throw new MotorCatNoDisponible('No se pudo verificar el contrato CAT.', $codigo);
    }
}
