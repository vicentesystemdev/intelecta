<?php

namespace App\Rules;

use App\Domains\Postulantes\Support\BirthDate;
use App\Support\Validation\InputRules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PostulanteBirthDate implements ValidationRule
{
    public function __construct(private readonly bool $registration = true) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! BirthDate::hasDateFormat($value)) {
            $fail(__('validation.birth_date_format'));

            return;
        }
        $date = BirthDate::canonical($value);
        if ($date === null) {
            $fail(__('validation.birth_date_invalid'));

            return;
        }
        $today = BirthDate::today();
        if ($date > $today->toDateString()) {
            $fail(__('validation.birth_date_future'));

            return;
        }
        $age = BirthDate::age($date, $today);
        if ($age < InputRules::MIN_AGE) {
            $fail(__('validation.birth_date_min', ['min' => InputRules::MIN_AGE]));
        } elseif ($this->registration && $age > InputRules::MAX_AGE) {
            // Preserve the existing admission boundary, not a lifetime validity constraint.
            $fail(__('validation.birth_date_registration_max', ['max' => InputRules::MAX_AGE]));
        }
    }
}
