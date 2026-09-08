<?php

namespace App\Support\Validation;

final class InputNormalizer
{
    public static function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            // Credentials are opaque: never trim, lowercase or collapse their spaces.
            if (in_array($key, ['password', 'password_confirmation', 'current_password', 'token'], true)) {
                continue;
            }
            if (is_array($value)) {
                $data[$key] = self::normalize($value);
            } elseif (is_string($value)) {
                $value = preg_replace('/^\s+|\s+$/u', '', $value) ?? $value;
                if (! preg_match('/email|correo|descripcion|observacion|enunciado|explicacion|objetivo|experiencia|relacion_ingenieria|respuesta_texto|texto_alt/', (string) $key)) {
                    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
                }
                if (preg_match('/email|correo/', (string) $key)) {
                    $value = mb_strtolower($value);
                }
                if (str_starts_with((string) $key, 'codigo_')) {
                    $value = mb_strtoupper($value);
                }
                if (preg_match('/celular|telefono/', (string) $key)) {
                    $value = preg_replace('/[\s()\-]/u', '', $value) ?? $value;
                }
                $data[$key] = $value === '' ? null : $value;
            }
        }

        return $data;
    }
}
