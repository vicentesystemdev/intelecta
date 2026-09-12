<?php

namespace App\Support\Validation;

use Carbon\CarbonImmutable;

final class AcademicDatePolicy
{
    public const TIMEZONE = 'America/La_Paz';

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE)->startOfDay();
    }

    public static function todayString(): string
    {
        return self::today()->toDateString();
    }

    public static function tomorrowString(): string
    {
        return self::tomorrow()->toDateString();
    }

    public static function tomorrow(): CarbonImmutable
    {
        return self::today()->addDay();
    }

    public static function isTomorrowOrLater(string $date): bool
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, self::TIMEZONE)
            ->gte(self::today()->addDay());
    }

    public static function isStrictlyAfter(string $date, string $reference): bool
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, self::TIMEZONE)
            ->gt(CarbonImmutable::createFromFormat('!Y-m-d', $reference, self::TIMEZONE));
    }
}
