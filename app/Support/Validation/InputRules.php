<?php

namespace App\Support\Validation;

use App\Rules\InputFormat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class InputRules
{
    public const MIN_AGE = 14;

    public const MAX_AGE = 80;

    public const MAX_QUESTIONS = 1000;

    public const MAX_ATTENDANCE = 500;

    public static function options(string $field): array
    {
        static $options;
        $options ??= json_decode(file_get_contents(resource_path('input-options.json')), true, flags: JSON_THROW_ON_ERROR);

        return $options[$field];
    }

    public static function person(int $max = 120): array
    {
        return ['string', 'min:2', 'max:'.$max, new InputFormat('person')];
    }

    public static function denomination(int $max = 160): array
    {
        return ['string', 'min:2', 'max:'.$max, new InputFormat('denomination')];
    }

    public static function descriptiveText(int $min = 10, int $max = 2000): array
    {
        return ['string', 'min:'.$min, 'max:'.$max, new InputFormat('denomination')];
    }

    public static function dateOrder(string $rule, mixed $reference, string $format = 'Y-m-d'): array
    {
        // A malformed companion field is reported by its own rule, never passed to DateTime.
        $valid = Validator::make(
            ['reference' => $reference],
            ['reference' => ['bail', 'required', 'date_format:'.$format]],
        )->passes();

        return $valid ? [$rule] : [];
    }

    public static function email(int $max = 254): array
    {
        return ['string', 'email:rfc', 'max:'.$max, new InputFormat('email_domain')];
    }

    public static function document(): array
    {
        return ['string', 'min:4', 'max:30', new InputFormat('document')];
    }

    public static function phone(): array
    {
        return ['string', 'max:16', new InputFormat('phone')];
    }

    public static function code(int $max = 60): array
    {
        return ['string', 'min:2', 'max:'.$max, new InputFormat('code')];
    }

    public static function password(): array
    {
        return ['string', Password::defaults(), 'confirmed', new InputFormat('password_bytes')];
    }
}
