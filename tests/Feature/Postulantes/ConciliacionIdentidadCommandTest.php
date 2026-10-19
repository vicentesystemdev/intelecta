<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConciliacionIdentidadCommandTest extends TestCase
{
    // Sin transacción exterior de RefreshDatabase: PostgreSQL exige establecer
    // aislamiento/READ ONLY antes de cualquier consulta en la transacción real.
    use DatabaseMigrations;

    private function user(string $role = 'Estudiante'): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    private function manifest(): array
    {
        $service = app(ConciliacionIdentidadService::class);
        $user = $this->user();
        $post = Postulante::factory()->create();

        return ['destino' => $service->destino(), 'filas' => [[
            'user_id' => $user->id, 'id_post' => $post->id_post, 'clasificacion' => 'A', 'revision' => 'aprobado',
            'evidencia' => 'Acta documental exclusiva de prueba sin inferencia por correo.',
            'huella_usuario' => $service->huella($user->fresh()), 'huella_expediente' => $service->huella($post->fresh()),
        ]]];
    }

    public function test_command_defaults_to_dry_run_and_apply_requires_its_receipt_and_backup(): void
    {
        $manifest = $this->manifest();
        $path = tempnam(sys_get_temp_dir(), 'intelecta-identity-test-');
        file_put_contents($path, json_encode($manifest, JSON_THROW_ON_ERROR));
        try {
            $this->artisan('intelecta:conciliar-identidades', ['manifiesto' => $path, '--aplicar' => true])->assertFailed();
            $this->artisan('intelecta:conciliar-identidades', ['manifiesto' => $path])->assertSuccessful();
            $this->assertFileExists($path.'.dry-run.json');
            $this->assertSame(0, Postulante::whereNotNull('user_id')->count());
            $this->artisan('intelecta:conciliar-identidades', ['manifiesto' => $path, '--aplicar' => true])->assertFailed();
            $manifest['filas'][0]['evidencia'] .= ' Referencia adicional.';
            file_put_contents($path, json_encode($manifest, JSON_THROW_ON_ERROR));
            $this->artisan('intelecta:conciliar-identidades', ['manifiesto' => $path, '--aplicar' => true])->assertFailed();
            $this->assertSame(0, DB::table('bitacora_sistema')->count());
        } finally {
            unlink($path);
            if (is_file($path.'.dry-run.json')) {
                unlink($path.'.dry-run.json');
            }
        }
    }

    public function test_command_requires_interactive_confirmation_even_with_review_backup_and_actor(): void
    {
        $manifest = $this->manifest();
        $actor = $this->user('Super Administrador');
        $path = tempnam(sys_get_temp_dir(), 'intelecta-approved-test-');
        $backupPath = tempnam(sys_get_temp_dir(), 'intelecta-backup-test-');
        file_put_contents($path, json_encode($manifest, JSON_THROW_ON_ERROR));
        file_put_contents($backupPath, 'Contenido sintético exclusivo de prueba.');
        file_put_contents($backupPath.'.verified.json', json_encode(['destino' => $manifest['destino'], 'archivo' => $backupPath,
            'sha256' => hash_file('sha256', $backupPath), 'restaurado_verificado' => true], JSON_THROW_ON_ERROR));
        try {
            $this->artisan('intelecta:conciliar-identidades', ['manifiesto' => $path])->assertSuccessful();
            $question = '¿Autorizas TODAS las filas de '.hash_file('sha256', $path).' en '.json_encode($manifest['destino']).' atribuidas al actor '.$actor->id.'?';
            $options = ['manifiesto' => $path, '--aplicar' => true, '--actor' => $actor->id,
                '--motivo' => 'Autorización humana exclusiva de prueba', '--respaldo' => $backupPath.'.verified.json'];
            $this->artisan('intelecta:conciliar-identidades', $options)->expectsConfirmation($question, 'no')->assertFailed();
            $this->assertSame(0, Postulante::whereNotNull('user_id')->count());
            $this->artisan('intelecta:conciliar-identidades', $options)->expectsConfirmation($question, 'yes')->assertSuccessful();
            $this->assertSame(1, Postulante::whereNotNull('user_id')->count());
        } finally {
            foreach ([$path, $path.'.dry-run.json', $backupPath, $backupPath.'.verified.json'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
