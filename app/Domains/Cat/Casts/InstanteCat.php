<?php

namespace App\Domains\Cat\Casts;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/** Instantes CAT con offset explícito, incluso al escribir en timestamptz. */
class InstanteCat implements CastsAttributes
{
    public bool $withoutObjectCaching = true;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        return $value === null ? null : CarbonImmutable::parse($value, 'UTC')->utc();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $instante = $value instanceof DateTimeInterface
            ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value, 'UTC');

        // El formato SQL habitual de Eloquent omite el offset: PostgreSQL lo
        // interpretaría en la zona de su sesión, que puede diferir de Laravel.
        return $instante->utc()->format('Y-m-d H:i:s.uP');
    }
}
