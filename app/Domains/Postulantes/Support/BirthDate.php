<?php

namespace App\Domains\Postulantes\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class BirthDate
{
    private const ACADEMIC_TIMEZONE = 'America/La_Paz';

    /** The civil day used only for postulante birth dates and age eligibility. */
    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::today(self::ACADEMIC_TIMEZONE);
    }

    /** A calendar date, never a timestamp or an estimate derived from legacy age. */
    public static function canonical(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        if (preg_match('/\A([0-9]{2})\/([0-9]{2})\/([0-9]{4})\z/', $value, $parts)) {
            [, $day, $month, $year] = $parts;
        } elseif (preg_match('/\A([0-9]{4})-([0-9]{2})-([0-9]{2})\z/', $value, $parts)) {
            [, $year, $month, $day] = $parts;
        } else {
            return null;
        }

        return checkdate((int) $month, (int) $day, (int) $year) ? "$year-$month-$day" : null;
    }

    public static function hasDateFormat(string $value): bool
    {
        return (bool) preg_match('/\A(?:[0-9]{2}\/[0-9]{2}\/[0-9]{4}|[0-9]{4}-[0-9]{2}-[0-9]{2})\z/', $value);
    }

    /** Completed years; February 29 birthdays advance on March 1 in non-leap years. */
    public static function age(string $value, ?CarbonInterface $today = null): int
    {
        $date = self::canonical($value) ?? throw new InvalidArgumentException('Invalid birth date.');
        $today ??= self::today();

        return $today->year - (int) substr($date, 0, 4) - (int) ($today->format('m-d') < substr($date, 5));
    }
}
