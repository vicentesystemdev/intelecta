<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Services\ConciliacionIdentidadService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AltasDemoCommandTest extends TestCase
{
    use DatabaseMigrations;

    public function test_command_needs_exact_dry_run_backup_actor_and_interactive_approval_and_remains_idempotent(): void
    {
        Role::findOrCreate('Estudiante', 'web');
        Role::findOrCreate('Super Administrador', 'web');
        $actor = User::factory()->create()->assignRole('Super Administrador');
        $post = Postulante::factory()->create(['ci_post' => '8800000']);
        $identity = app(ConciliacionIdentidadService::class);
        $manifest = ['tipo' => 'altas_demo_estudiantes', 'demo' => true, 'lote_id' => (string) Str::uuid(), 'destino' => $identity->destino(), 'filas' => [[
            'id_post' => $post->id_post, 'nombre' => $post->nombres_post.' '.$post->apellidos_post, 'correo' => $post->email_post, 'documento' => $post->ci_post,
            'huella_expediente' => $identity->huella($post->fresh()), 'evaluacion_permitida' => true, 'revision' => 'aprobado',
        ]]];
        $path = storage_path('app/private/test-altas-command-'.Str::uuid().'.json');
        $backup = $path.'.backup';
        $delivery = $path.'.enc';
        file_put_contents($path, json_encode($manifest, JSON_THROW_ON_ERROR));
        file_put_contents($backup, 'Respaldo sintético de prueba, no una base real.');
        file_put_contents($backup.'.verified.json', json_encode(['destino' => $identity->destino(), 'archivo' => $backup,
            'sha256' => hash_file('sha256', $backup), 'restaurado_verificado' => true], JSON_THROW_ON_ERROR));
        try {
            $this->artisan('intelecta:altas-demo-estudiantes', ['manifiesto' => $path, '--aplicar' => true])->assertFailed();
            $this->artisan('intelecta:altas-demo-estudiantes', ['manifiesto' => $path])->assertSuccessful();
            $this->assertSame(1, User::count());
            $this->assertSame(0, DB::table('password_reset_tokens')->count());
            $options = ['manifiesto' => $path, '--aplicar' => true, '--actor' => $actor->id, '--motivo' => 'Autorización explícita de alta exclusiva de prueba', '--entrega' => $delivery];
            $this->artisan('intelecta:altas-demo-estudiantes', $options)->assertFailed();
            $options['--respaldo'] = $backup.'.verified.json';
            $question = '¿Confirmas base DEMO y TODAS las altas de '.hash_file('sha256', $path).' en '.json_encode($identity->destino()).' como SA '.$actor->id.'?';
            $this->artisan('intelecta:altas-demo-estudiantes', $options)->expectsConfirmation($question, 'no')->assertFailed();
            $this->assertSame(1, User::count());
            $this->artisan('intelecta:altas-demo-estudiantes', $options)->expectsConfirmation($question, 'yes')->assertSuccessful();
            $this->assertSame(2, User::count());
            $this->assertFileExists($delivery);
            $before = hash('sha256', DB::table('password_reset_tokens')->get()->toJson());
            $this->artisan('intelecta:altas-demo-estudiantes', $options)->expectsConfirmation($question, 'yes')->assertSuccessful();
            $this->assertSame(2, User::count());
            $this->assertSame($before, hash('sha256', DB::table('password_reset_tokens')->get()->toJson()));
        } finally {
            foreach ([$path, $path.'.dry-run.json', $backup, $backup.'.verified.json', $delivery] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
