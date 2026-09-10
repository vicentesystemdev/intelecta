<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class InputFormat implements ValidationRule
{
    public function __construct(private readonly string $format) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = is_string($value) && match ($this->format) {
            'text' => mb_check_encoding($value, 'UTF-8') && ! preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value),
            'person' => preg_match("/^[\pL\pM]+(?:[ '\x{2019}\-][\pL\pM]+)*$/u", $value) === 1,
            'document' => preg_match('/^(?=.*[0-9])[A-Za-z0-9]+(?:[ .\/-][A-Za-z0-9]+)*$/D', $value) === 1,
            'phone' => preg_match('/^\+?[0-9]{7,15}$/D', $value) === 1,
            'denomination' => preg_match('/\pL/u', $value) === 1,
            'code' => preg_match('/^[A-Z0-9]+(?:[_-][A-Z0-9]+)*$/D', $value) === 1,
            // Laravel handles RFC validation; public addresses also need a dotted domain.
            'email_domain' => str_contains(substr($value, (int) strrpos($value, '@') + 1), '.') && ! str_ends_with($value, '.'),
            'password_bytes' => strlen($value) <= 72,
            default => false,
        };

        if (! $valid) {
            $fail(match ($this->format) {
                'text' => 'El campo :attribute contiene caracteres de control no permitidos.',
                'person' => 'El campo :attribute solo puede contener letras, espacios, guiones y apóstrofes.',
                'document' => 'El campo :attribute debe contener números y solo letras, espacios, puntos, barras o guiones como complemento.',
                'phone' => 'El campo :attribute debe contener entre 7 y 15 dígitos y puede comenzar con +.',
                'denomination' => 'El campo :attribute debe contener letras; no puede ser exclusivamente numérico.',
                'code' => 'El campo :attribute solo puede contener letras mayúsculas, números, guiones y guiones bajos.',
                'email_domain' => 'Ingrese un correo electrónico válido con un dominio completo.',
                'password_bytes' => 'La contraseña no puede superar 72 bytes; los caracteres Unicode pueden ocupar varios bytes.',
                default => 'El campo :attribute no tiene un formato válido.',
            });
        }
    }
}
