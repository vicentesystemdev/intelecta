<?php

namespace App\Console\Commands;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\AltasDemoEstudianteService;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Domains\Seguridad\Services\CuentaService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Throwable;

class EntregarAccesoDemo extends Command
{
    protected $signature = 'intelecta:entregar-acceso-demo {id_post : Expediente DEMO ya creado y auditado}
        {--actor= : SA autorizado para esta entrega individual}
        {--paquete= : Paquete cifrado generado por las altas}
        {--salida= : HTML privado NUEVO de entrega individual, contiene un secreto temporal}
        {--reemitir : Invalidar el token anterior y emitir uno nuevo para este expediente}
        {--motivo= : Referencia trazable de entrega o reemisión}';

    protected $description = 'Entrega individual auditada de un acceso DEMO; nunca imprime tokens ni contraseñas';

    public function handle(AltasDemoEstudianteService $service, CuentaService $accounts, ConciliacionIdentidadService $identity, BitacoraService $audit): int
    {
        config(['cache.default' => 'array']);
        $written = false;
        $path = (string) $this->option('salida');
        try {
            if (! app()->environment('local', 'testing')) {
                throw new RuntimeException('Entrega DEMO solo en local/testing.');
            }
            $actorId = filter_var($this->option('actor'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $postId = filter_var($this->argument('id_post'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $actor = $actorId ? User::find($actorId) : null;
            if (! $actor || ! $postId) {
                throw new RuntimeException('Indica actor SA y expediente explícitos.');
            }
            Auth::onceUsingId($actor->id);
            $service->authorize($actor, (string) $this->option('motivo'));
            $service->privateNewPath($path);
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'html') {
                throw new RuntimeException('La entrega individual debe ser un archivo .html privado nuevo.');
            }
            if (! $this->confirm('¿Autorizas entregar '.($this->option('reemitir') ? 'un NUEVO acceso (invalida el anterior)' : 'el acceso vigente').' al expediente '.$postId.' como SA '.$actor->id.'?', false)) {
                return self::FAILURE;
            }
            $accounts->transaction(function () use ($service, $identity, $audit, $postId, $actor, $path, &$written): void {
                $post = Postulante::query()->lockForUpdate()->findOrFail($postId);
                $user = $post->user;
                if (! $user || ! $user->cuentaActiva() || $user->getRoleNames()->all() !== ['Estudiante'] || ! $service->marker($post)) {
                    throw new RuntimeException('Solo se entregan accesos de cuentas DEMO creadas por el lote auditado, activas y exclusivamente Estudiante.');
                }
                if ($this->option('reemitir')) {
                    $record = $service->credential($user, $post, Password::broker()->createToken($user));
                } else {
                    $package = (string) $this->option('paquete');
                    if (! is_file($package)) {
                        throw new RuntimeException('Falta paquete cifrado; para accesos caducados solicita reemisión individual.');
                    }
                    $payload = json_decode(Crypt::decryptString(file_get_contents($package)), true, flags: JSON_THROW_ON_ERROR);
                    if (($payload['destino'] ?? null) !== $identity->destino()) {
                        throw new RuntimeException('El paquete corresponde a otra conexión.');
                    }
                    $record = collect($payload['cuentas'])->firstWhere('id_post', $postId);
                }
                if (! $record || $record['user_id'] !== $user->id || $record['correo'] !== $user->email
                    || ! Password::broker()->tokenExists($user, $record['token'])) {
                    throw new RuntimeException('Acceso caducado, consumido o sustituido; no se entrega. Solicita reemisión individual.');
                }
                $before = DB::table('bitacora_sistema')->max('id_bitacora') ?? 0;
                $audit->registrar(['user_id' => $actor->id, 'nombre_usuario' => $actor->name, 'correo_usuario' => $actor->email,
                    'rol_usuario' => $actor->rolesLabel(), 'accion' => 'entregar_acceso_demo', 'modulo' => 'Usuarios', 'entidad' => 'postulantes',
                    'entidad_id' => $postId, 'descripcion' => (string) $this->option('motivo'), 'severidad' => 'seguridad',
                    'valores_nuevos' => ['user_id' => $user->id, 'reemitido' => (bool) $this->option('reemitir')]]);
                if (! DB::table('bitacora_sistema')->where('id_bitacora', '>', $before)->where('accion', 'entregar_acceso_demo')->where('user_id', $actor->id)->where('entidad_id', (string) $postId)->exists()) {
                    throw new RuntimeException('No se confirmó auditoría de entrega; operación revertida.');
                }
                $escape = fn ($text) => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $url = route('password.reset', ['token' => $record['token'], 'email' => $user->email]);
                $html = '<!doctype html><html lang="es"><meta charset="utf-8"><title>Acceso DEMO individual</title><body><h1>Acceso DEMO individual</h1><p>'
                    .$escape($user->name).'</p><p>Cuenta: '.$escape($user->email).'</p><p><a href="'.$escape($url).'">Establecer mi contraseña</a></p><p>Caduca: '
                    .$escape($record['caduca']).'. Un solo uso. No compartir, publicar ni adjuntar a Git. Eliminar esta entrega tras su uso.</p><p>Las restricciones académicas y financieras se conservan.</p></body></html>';
                $handle = fopen($path, 'x');
                if (! $handle) {
                    throw new RuntimeException('No se pudo crear la entrega individual.');
                }
                $written = true;
                try {
                    if (fwrite($handle, $html) !== strlen($html)) {
                        throw new RuntimeException('Entrega incompleta; rollback.');
                    }
                } finally {
                    fclose($handle);
                }
                @chmod($path, 0600);
            });
            $this->info('Entrega individual privada creada. Contiene un enlace secreto temporal; no se imprime su contenido.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if ($written && is_file($path)) {
                unlink($path);
            }
            $this->error($exception instanceof RuntimeException && ! $exception instanceof QueryException
                ? $exception->getMessage() : 'Entrega rechazada sin confirmar; no se muestran datos sensibles.');

            return self::FAILURE;
        } finally {
            Auth::forgetGuards();
        }
    }
}
