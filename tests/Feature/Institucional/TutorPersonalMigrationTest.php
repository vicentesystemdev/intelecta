<?php

namespace Tests\Feature\Institucional;

use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Services\MigrarIdentidadTutorService;
use App\Domains\Institucional\Models\Cargo;
use App\Domains\Institucional\Models\PersonalInstitucional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TutorPersonalMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function legacy(array $values = []): int
    {
        if (! Schema::hasColumn('tutores_academicos', 'user_id')) {
            (require database_path('migrations/2026_09_11_000100_finalize_tutor_personal_identity.php'))->down();
        }

        return DB::table('tutores_academicos')->insertGetId(array_replace([
            'nombres_tutor' => 'María Elena', 'apellidos_tutor' => 'Quispe Rojas', 'estado_tutor' => 'activo',
            'ci_tutor' => '1234567', 'celular_tutor' => '72010001', 'correo_tutor' => 'contacto@example.com',
            'created_at' => '2020-01-02 12:00:00', 'updated_at' => '2021-03-04 12:00:00',
            'especialidad_tutor' => 'Física', 'formacion_tutor' => 'Ingeniería', 'experiencia_tutor' => 'Experiencia original',
        ], $values), 'id_tutor');
    }

    public function test_dry_run_then_migration_preserves_explicit_user_ids_and_exact_data_idempotently(): void
    {
        $user = User::factory()->create();
        $id = $this->legacy(['user_id' => $user->id]);
        $before = (array) DB::table('tutores_academicos')->where('id_tutor', $id)->first();
        $service = app(MigrarIdentidadTutorService::class);
        $this->assertSame(0, $service->execute()['conflictos']);
        $this->assertDatabaseCount('personal_institucional', 0);
        $this->assertNull(DB::table('tutores_academicos')->where('id_tutor', $id)->value('personal_id'));
        $this->assertSame(0, $service->execute(false)['conflictos']);
        $person = PersonalInstitucional::sole();
        $this->assertSame($user->id, $person->user_id);
        $this->assertNull($person->cargo_id);
        $this->assertSame('pendiente', $person->estado->value);
        foreach (MigrarIdentidadTutorService::FIELDS as $source => $target) {
            $this->assertSame($before[$source], $person->{$target});
        }
        $after = (array) DB::table('tutores_academicos')->where('id_tutor', $id)->first();
        unset($before['personal_id'], $after['personal_id']);
        $this->assertSame($before, $after);
        $snapshot = $person->getAttributes();
        $service->execute(false);
        $this->assertDatabaseCount('personal_institucional', 1);
        $this->assertSame($snapshot, $person->fresh()->getAttributes());
        $service->assertReadyToFinalize();
        (require database_path('migrations/2026_09_11_000100_finalize_tutor_personal_identity.php'))->up();
        $this->assertFalse(Schema::hasColumn('tutores_academicos', 'user_id'));
        $this->assertSame($id, TutorAcademico::sole()->id_tutor);
        $this->assertSame($user->id, TutorAcademico::sole()->personal->user->id);
        $this->assertSame('finalizada', $service->execute(false)['fase']);
    }

    public function test_missing_user_is_not_resolved_by_matching_email_and_archived_tutor_is_migrated(): void
    {
        User::factory()->create(['email' => 'contacto@example.com']);
        $id = $this->legacy(['deleted_at' => '2022-01-01 00:00:00']);
        app(MigrarIdentidadTutorService::class)->execute(false);
        $person = PersonalInstitucional::sole();
        $this->assertNull($person->user_id);
        $this->assertSame($id, $person->tutorAcademico->id_tutor);
        $this->assertTrue($person->tutorAcademico->trashed());
        $this->assertSame(0, TutorAcademico::count());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_existing_incomplete_person_is_completed_without_changing_cargo_or_state(): void
    {
        $user = User::factory()->create();
        $person = PersonalInstitucional::factory()->inactive()->create(['user_id' => $user->id, 'cargo_id' => Cargo::factory()->create()->id_cargo]);
        $this->legacy(['user_id' => $user->id]);
        $service = app(MigrarIdentidadTutorService::class);
        $this->assertSame('INCOMPLETO', $service->execute()['registros'][0]['clasificacion']);
        $service->execute(false);
        $this->assertDatabaseCount('personal_institucional', 1);
        $this->assertSame('1234567', $person->fresh()->ci);
        $this->assertSame('inactivo', $person->fresh()->estado->value);
        $this->assertSame($person->cargo_id, $person->fresh()->cargo_id);
        $this->assertSame('COMPATIBLE', $service->execute()['registros'][0]['clasificacion']);
    }

    public function test_identity_conflict_stops_entire_batch_without_overwriting_or_partial_writes(): void
    {
        $user = User::factory()->create();
        $person = PersonalInstitucional::factory()->create(['user_id' => $user->id, 'nombres' => 'Otra Persona']);
        $this->legacy(['ci_tutor' => '7654321']);
        $this->legacy(['user_id' => $user->id]);
        $report = app(MigrarIdentidadTutorService::class)->execute(false);
        $this->assertSame(1, $report['conflictos']);
        $this->assertSame('detenida_sin_escrituras', $report['fase']);
        $this->assertSame('Otra Persona', $person->fresh()->nombres);
        $this->assertSame(2, DB::table('tutores_academicos')->whereNull('personal_id')->count());
        $this->assertDatabaseCount('personal_institucional', 1);
        $this->expectException(\RuntimeException::class);
        (require database_path('migrations/2026_09_11_000100_finalize_tutor_personal_identity.php'))->up();
    }

    public function test_ci_owned_by_unlinked_person_is_conflict_not_an_identity_match(): void
    {
        PersonalInstitucional::factory()->create(['ci' => '1234567']);
        $this->legacy();
        $report = app(MigrarIdentidadTutorService::class)->execute(false);
        $this->assertSame(1, $report['conflictos']);
        $this->assertContains('CI reservado por otra identidad', $report['registros'][0]['errores']);
        $this->assertDatabaseCount('personal_institucional', 1);
    }

    public function test_invalid_legacy_values_are_not_truncated_or_normalized(): void
    {
        $this->legacy(['nombres_tutor' => str_repeat('a', 121), 'celular_tutor' => 'incorrecto', 'correo_tutor' => 'sin-correo']);
        $report = app(MigrarIdentidadTutorService::class)->execute(false);
        $this->assertSame(1, $report['conflictos']);
        $this->assertCount(3, $report['registros'][0]['errores']);
        $this->assertDatabaseCount('personal_institucional', 0);
    }

    public function test_mismatched_transition_link_is_rejected(): void
    {
        $user = User::factory()->create();
        $person = PersonalInstitucional::factory()->create();
        $this->legacy(['user_id' => $user->id, 'personal_id' => $person->id_personal]);
        $this->assertSame(1, app(MigrarIdentidadTutorService::class)->execute(false)['conflictos']);
        $this->assertNull($person->fresh()->user_id);
    }

    public function test_schema_rollback_reconstructs_identity_without_removing_personal(): void
    {
        $tutor = TutorAcademico::factory()->withPersonal()->create();
        (require database_path('migrations/2026_09_11_000100_finalize_tutor_personal_identity.php'))->down();
        $this->assertSame($tutor->nombre_completo, trim(DB::table('tutores_academicos')->value('nombres_tutor').' '.DB::table('tutores_academicos')->value('apellidos_tutor')));
        $this->assertDatabaseCount('personal_institucional', 1);
        (require database_path('migrations/2026_09_11_000100_finalize_tutor_personal_identity.php'))->up();
        $this->assertFalse(Schema::hasColumn('tutores_academicos', 'nombres_tutor'));
    }
}
