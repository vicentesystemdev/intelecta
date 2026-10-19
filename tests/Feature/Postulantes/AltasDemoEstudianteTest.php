<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\AltasDemoEstudianteService;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AltasDemoEstudianteTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    private function path(string $extension = 'enc'): string
    {
        $path = storage_path('app/private/test-altas-'.Str::uuid().'.'.$extension);
        $this->files[] = $path;

        return $path;
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function actor(): User
    {
        Role::findOrCreate('Estudiante', 'web');
        Role::findOrCreate('Super Administrador', 'web');
        $actor = User::factory()->create()->assignRole('Super Administrador');
        $this->actingAs($actor);

        return $actor;
    }

    private function manifest(int $count = 2): array
    {
        $identity = app(ConciliacionIdentidadService::class);
        $rows = [];
        for ($index = 0; $index < $count; $index++) {
            $post = Postulante::factory()->create(['ci_post' => (string) (8800000 + $index)]);
            $rows[] = ['id_post' => $post->id_post, 'nombre' => $post->nombres_post.' '.$post->apellidos_post,
                'correo' => $post->email_post, 'documento' => $post->ci_post, 'huella_expediente' => $identity->huella($post->fresh()), 'revision' => 'aprobado'];
        }

        return ['tipo' => 'altas_demo_estudiantes', 'demo' => true, 'lote_id' => (string) Str::uuid(), 'destino' => $identity->destino(), 'filas' => $rows];
    }

    private function snapshot(): array
    {
        return collect(['users', 'postulantes', 'model_has_roles', 'bitacora_sistema', 'password_reset_tokens'])->mapWithKeys(fn ($table) => [$table => hash('sha256', DB::table($table)->orderByRaw('1')->get()->toJson())])->all();
    }

    public function test_dry_run_never_creates_users_tokens_or_notifications(): void
    {
        Notification::fake();
        Role::findOrCreate('Estudiante', 'web');
        $manifest = $this->manifest();
        $before = $this->snapshot();
        $result = app(AltasDemoEstudianteService::class)->ejecutar($manifest);
        $this->assertCount(2, $result);
        $this->assertSame($before, $this->snapshot());
        Notification::assertNothingSent();
    }

    public function test_apply_is_audited_exclusively_student_encrypted_and_idempotent(): void
    {
        Notification::fake();
        $actor = $this->actor();
        $manifest = $this->manifest();
        $path = $this->path();
        $service = app(AltasDemoEstudianteService::class);
        $result = $service->ejecutar($manifest, true, $actor, 'Autorización explícita de altas DEMO de prueba', $path);
        $this->assertCount(2, $result);
        $payload = json_decode(Crypt::decryptString(file_get_contents($path)), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(2, $payload['cuentas']);
        foreach ($payload['cuentas'] as $credential) {
            $user = User::findOrFail($credential['user_id']);
            $this->assertTrue($user->cuentaActiva());
            $this->assertSame(['Estudiante'], $user->getRoleNames()->all());
            $this->assertSame($credential['id_post'], $user->postulante->id_post);
            $this->assertTrue(Password::broker()->tokenExists($user, $credential['token']));
            $this->assertFalse(str_contains(file_get_contents($path), $credential['token']));
            $this->assertFalse(str_contains(DB::table('bitacora_sistema')->get()->toJson(), $credential['token']));
        }
        Notification::assertNothingSent();
        $before = $this->snapshot();
        $deliveryHash = hash_file('sha256', $path);
        $again = $service->ejecutar($manifest, true, $actor, 'Reejecución autorizada sin regenerar accesos', $path);
        $this->assertSame(['ya_creada_sin_cambios', 'ya_creada_sin_cambios'], array_column($again, 'resultado'));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame($deliveryHash, hash_file('sha256', $path));
    }

    public function test_late_email_collision_rolls_back_every_created_account_link_token_and_audit(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest();
        User::factory()->create(['email' => $manifest['filas'][1]['correo']]);
        $before = $this->snapshot();
        $path = $this->path();
        try {
            app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Autorización de prueba con conflicto tardío', $path);
            $this->fail('El conflicto debe revertir todo el lote.');
        } catch (ValidationException) {
            $this->assertSame($before, $this->snapshot());
            $this->assertFileDoesNotExist($path);
        }
    }

    public function test_missing_durable_audit_rolls_back_all_changes(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest(1);
        $before = $this->snapshot();
        $mock = Mockery::mock(BitacoraService::class);
        $mock->shouldReceive('registrar')->andReturnNull();
        $this->app->instance(BitacoraService::class, $mock);
        $this->expectException(RuntimeException::class);
        try {
            app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Autorización de prueba sin bitácora durable', $this->path());
        } finally {
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_unapproved_changed_duplicate_or_archived_rows_are_rejected(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest(1);
        foreach (['pendiente', 'correo', 'duplicado', 'archivado'] as $case) {
            $variant = $manifest;
            if ($case === 'pendiente') {
                $variant['filas'][0]['revision'] = 'pendiente';
            } elseif ($case === 'correo') {
                $variant['filas'][0]['correo'] = 'otro@example.com';
            } elseif ($case === 'duplicado') {
                $variant['filas'][] = $variant['filas'][0];
            } else {
                Postulante::findOrFail($manifest['filas'][0]['id_post'])->delete();
            }
            $before = $this->snapshot();
            try {
                app(AltasDemoEstudianteService::class)->ejecutar($variant, true, $actor, 'Acta de revisión de filas de prueba', $this->path());
                $this->fail('Fila incompatible aceptada.');
            } catch (ValidationException) {
                $this->assertSame($before, $this->snapshot());
            }
        }
    }

    public function test_apply_requires_the_authorized_sa_runtime_identity(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest(1);
        auth()->forgetGuards();
        $this->expectException(AuthorizationException::class);
        app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Acta sin contexto administrativo de prueba', $this->path());
    }

    public function test_demo_activation_is_forbidden_in_production(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest(1);
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(AuthorizationException::class);
        app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'No se permite este proceso en producción', $this->path());
    }

    public function test_existing_delivery_file_is_preserved_and_all_database_changes_roll_back(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest(1);
        $path = $this->path();
        file_put_contents($path, 'Archivo previo que no pertenece al lote.');
        $before = $this->snapshot();
        try {
            app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Autorización de prueba sin sobrescribir archivos', $path);
            $this->fail('No debe sobrescribirse una entrega existente.');
        } catch (RuntimeException) {
            $this->assertSame($before, $this->snapshot());
            $this->assertSame('Archivo previo que no pertenece al lote.', file_get_contents($path));
        }
    }

    public function test_delivery_is_individual_audited_not_printed_and_reset_is_single_use(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest();
        $package = $this->path();
        app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Autorización explícita de paquete de prueba', $package);
        $payload = json_decode(Crypt::decryptString(file_get_contents($package)), true, flags: JSON_THROW_ON_ERROR);
        $first = $payload['cuentas'][0];
        $html = $this->path('html');
        $this->artisan('intelecta:entregar-acceso-demo', ['id_post' => $first['id_post'], '--actor' => $actor->id,
            '--paquete' => $package, '--salida' => $html, '--motivo' => 'Entrega individual autorizada de prueba'])
            ->expectsConfirmation('¿Autorizas entregar el acceso vigente al expediente '.$first['id_post'].' como SA '.$actor->id.'?', 'yes')->assertSuccessful();
        $this->assertTrue(str_contains(file_get_contents($html), $first['token']));
        $this->assertFalse(str_contains(file_get_contents($html), $payload['cuentas'][1]['token']));
        $password = 'Temporal!Aa2026-'.Str::random(12);
        $this->post('/reset-password', ['token' => $first['token'], 'email' => $first['correo'], 'password' => $password, 'password_confirmation' => $password])->assertRedirect('/login');
        $user = User::findOrFail($first['user_id']);
        $this->assertFalse(Password::broker()->tokenExists($user, $first['token']));
        $this->post('/login', ['email' => $first['correo'], 'password' => $password])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_expired_tokens_require_individual_reissue_without_resetting_password_or_other_users(): void
    {
        $actor = $this->actor();
        $manifest = $this->manifest();
        $package = $this->path();
        app(AltasDemoEstudianteService::class)->ejecutar($manifest, true, $actor, 'Acta DEMO de accesos temporales de prueba', $package);
        $payload = json_decode(Crypt::decryptString(file_get_contents($package)), true, flags: JSON_THROW_ON_ERROR);
        $first = $payload['cuentas'][0];
        $passwordHash = User::findOrFail($first['user_id'])->getRawOriginal('password');
        $otherToken = DB::table('password_reset_tokens')->where('email', $payload['cuentas'][1]['correo'])->value('token');
        $this->travel(61)->minutes();
        $this->assertFalse(Password::broker()->tokenExists(User::findOrFail($first['user_id']), $first['token']));
        $this->artisan('intelecta:entregar-acceso-demo', ['id_post' => $first['id_post'], '--actor' => $actor->id,
            '--reemitir' => true, '--salida' => $this->path('html'), '--motivo' => 'Reemisión individual autorizada por caducidad'])
            ->expectsConfirmation('¿Autorizas entregar un NUEVO acceso (invalida el anterior) al expediente '.$first['id_post'].' como SA '.$actor->id.'?', 'yes')->assertSuccessful();
        $this->assertSame($passwordHash, User::findOrFail($first['user_id'])->getRawOriginal('password'));
        $this->assertSame($otherToken, DB::table('password_reset_tokens')->where('email', $payload['cuentas'][1]['correo'])->value('token'));
    }
}
