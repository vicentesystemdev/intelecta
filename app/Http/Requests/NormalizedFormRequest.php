<?php

namespace App\Http\Requests;

use App\Rules\InputFormat;
use App\Support\Validation\InputNormalizer;
use Illuminate\Foundation\Http\FormRequest;

abstract class NormalizedFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->replace(InputNormalizer::normalize($this->all()));
    }

    protected function validationRules(): array
    {
        // Reject each field's shape before invoking its database or dependent rules.
        return array_map(
            function ($rules): array {
                $rules = is_array($rules) ? $rules : explode('|', $rules);
                if (($index = array_search('integer', $rules, true)) !== false) {
                    // Laravel's non-strict integer rule otherwise coerces JSON true to 1.
                    array_splice($rules, $index, 0, [function ($attribute, $value, $fail): void {
                        if (is_bool($value)) {
                            $fail('El campo :attribute debe ser un número entero, no un booleano.');
                        }
                    }]);
                }
                if (($index = array_search('string', $rules, true)) !== false) {
                    array_splice($rules, $index + 1, 0, [new InputFormat('text')]);
                }

                return ['bail', ...$rules];
            },
            parent::validationRules(),
        );
    }
}
