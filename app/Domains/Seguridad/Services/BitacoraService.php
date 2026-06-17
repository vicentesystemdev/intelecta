<?php

namespace App\Domains\Seguridad\Services;

use App\Domains\Seguridad\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BitacoraService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'remember_token',
        'access_token',
        'refresh_token',
        'secret',
        'api_key',
        'private_key',
    ];

    public function __construct(private readonly Request $request)
    {
    }

    public function registrar(array $data): void
    {
        try {
            if (! Schema::hasTable('bitacora_sistema')) {
                return;
            }

            $user = $this->request->user();

            BitacoraSistema::create([
                'user_id' => $data['user_id'] ?? $user?->getKey(),
                'nombre_usuario' => $data['nombre_usuario'] ?? $user?->name,
                'correo_usuario' => $data['correo_usuario'] ?? $user?->email,
                'rol_usuario' => $data['rol_usuario'] ?? $user?->getRoleNames()->first(),
                'accion' => $data['accion'] ?? 'accion_no_definida',
                'modulo' => $data['modulo'] ?? null,
                'entidad' => $data['entidad'] ?? null,
                'entidad_id' => isset($data['entidad_id']) ? (string) $data['entidad_id'] : null,
                'descripcion' => $data['descripcion'] ?? null,
                'valores_anteriores' => $this->sanitize($data['valores_anteriores'] ?? null),
                'valores_nuevos' => $this->sanitize($data['valores_nuevos'] ?? null),
                'ip' => $data['ip'] ?? $this->request->ip(),
                'user_agent' => $data['user_agent'] ?? $this->request->userAgent(),
                'metodo_http' => $data['metodo_http'] ?? $this->request->method(),
                'ruta' => $data['ruta'] ?? optional($this->request->route())->getName(),
                'url' => $data['url'] ?? $this->request->fullUrl(),
                'severidad' => $data['severidad'] ?? 'info',
            ]);
        } catch (Throwable $exception) {
            Log::warning('No fue posible registrar bitacora institucional.', [
                'error' => $exception->getMessage(),
                'accion' => $data['accion'] ?? null,
                'modulo' => $data['modulo'] ?? null,
            ]);
        }
    }

    private function sanitize(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            return $value;
        }

        $clean = [];

        foreach ($value as $key => $item) {
            $keyString = is_string($key) ? mb_strtolower($key) : $key;

            if (is_string($keyString) && in_array($keyString, self::SENSITIVE_KEYS, true)) {
                $clean[$key] = '[dato protegido]';
                continue;
            }

            $clean[$key] = $this->sanitize($item);
        }

        return $clean;
    }
}
