<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StableIdentityTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'Estudiante'): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    public function test_null_identity_and_matching_email_do_not_link_a_record(): void
    {
        $user = $this->user();
        $postulante = Postulante::factory()->create(['email_post' => $user->email]);
        Postulante::factory()->create();
        $this->assertNull($user->postulante);
        $this->assertNull($postulante->user);
        $this->assertNull($postulante->user_id);
    }

    public function test_both_relations_use_the_foreign_key(): void
    {
        $user = $this->user();
        $postulante = Postulante::factory()->withUser($user)->create();
        $this->assertTrue($user->postulante->is($postulante));
        $this->assertTrue($postulante->user->is($user));
        $this->assertNotSame($user->email, $postulante->email_post);
        $this->assertFalse($postulante->isFillable('user_id'));
    }

    public function test_unique_also_reserves_an_archived_link(): void
    {
        $user = $this->user();
        Postulante::factory()->withUser($user)->create()->delete();
        $this->expectException(QueryException::class);
        Postulante::factory()->withUser($user)->create();
    }

    public function test_fk_rejects_a_missing_user(): void
    {
        $this->expectException(QueryException::class);
        Postulante::factory()->create(['user_id' => 999999]);
    }

    public function test_delete_is_restricted_and_never_cascades(): void
    {
        $user = $this->user();
        $postulante = Postulante::factory()->withUser($user)->create();
        try {
            DB::transaction(fn () => $user->delete());
            $this->fail('La FK debe impedir la eliminación.');
        } catch (QueryException) {
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('postulantes', ['id_post' => $postulante->id_post, 'user_id' => $user->id]);
        }
    }

    public function test_profile_delete_of_linked_or_archived_record_is_controlled(): void
    {
        $user = $this->user();
        $postulante = Postulante::factory()->withUser($user)->create();
        foreach ([false, true] as $archive) {
            if ($archive) {
                $postulante->delete();
            }
            $this->actingAs($user)->deleteJson('/profile', ['password' => 'password'])
                ->assertUnprocessable()->assertJsonValidationErrors('password');
            $this->assertAuthenticatedAs($user);
            $this->assertDatabaseHas('users', ['id' => $user->id]);
            $this->assertDatabaseHas('postulantes', ['id_post' => $postulante->id_post]);
        }
    }

    public function test_explicit_link_is_audited_and_does_not_modify_contact_or_roles(): void
    {
        $actor = $this->user('Super Administrador');
        $user = $this->user();
        $postulante = Postulante::factory()->create();
        $email = $postulante->email_post;
        app(VincularUsuarioPostulanteAction::class)->execute($user->id, $postulante->id_post, $actor, 'Revisión documental de prueba confirmada');
        $this->assertSame($user->id, $postulante->fresh()->user_id);
        $this->assertSame($email, $postulante->fresh()->email_post);
        $this->assertTrue($user->fresh()->hasRole('Estudiante'));
        $this->assertDatabaseHas('bitacora_sistema', ['accion' => 'vincular_identidad', 'user_id' => $actor->id, 'entidad_id' => (string) $postulante->id_post]);
    }

    public function test_ordinary_admin_cannot_link(): void
    {
        $actor = $this->user('Administrador');
        $this->expectException(AuthorizationException::class);
        app(VincularUsuarioPostulanteAction::class)->execute($this->user()->id, Postulante::factory()->create()->id_post, $actor, 'Revisión documental de prueba');
    }

    public function test_link_rejects_existing_archived_incompatible_and_missing_targets(): void
    {
        $actor = $this->user('Super Administrador');
        $user = $this->user();
        $linked = Postulante::factory()->withUser($user)->create();
        $freeUser = $this->user();
        $freePostulante = Postulante::factory()->create();
        $archived = Postulante::factory()->create();
        $archived->delete();
        $cases = [
            [$user->id, $freePostulante->id_post],
            [$freeUser->id, $linked->id_post],
            [$freeUser->id, $archived->id_post],
            [$actor->id, $freePostulante->id_post],
            [999999, $freePostulante->id_post],
            [$freeUser->id, 999999],
        ];
        $linked->delete();
        foreach ($cases as [$userId, $postulanteId]) {
            try {
                app(VincularUsuarioPostulanteAction::class)->execute($userId, $postulanteId, $actor, 'Revisión documental de prueba');
                $this->fail('El vínculo debe ser rechazado.');
            } catch (ValidationException) {
                $this->assertNull($freePostulante->fresh()->user_id);
            }
        }
        $this->assertDatabaseCount('bitacora_sistema', 0);
    }

    public function test_command_requires_confirmation_and_explicit_responsible_actor(): void
    {
        $actor = $this->user('Super Administrador');
        $user = $this->user();
        $postulante = Postulante::factory()->create();
        $arguments = ['user_id' => $user->id, 'id_post' => $postulante->id_post, '--actor' => $actor->id, '--motivo' => 'Revisión documental de prueba'];
        $question = '¿Confirmas el vínculo User '.$user->id.' ↔ Postulante '.$postulante->id_post.' en la BD '.config('database.connections.'.config('database.default').'.database').'?';
        $this->artisan('intelecta:vincular-postulante', $arguments)->expectsConfirmation($question, 'no')->assertFailed();
        $this->assertNull($postulante->fresh()->user_id);
        $this->artisan('intelecta:vincular-postulante', $arguments)->expectsConfirmation($question, 'yes')->assertSuccessful();
        $this->assertSame($user->id, $postulante->fresh()->user_id);
        $this->artisan('intelecta:vincular-postulante', ['user_id' => $user->id, 'id_post' => $postulante->id_post])->assertFailed();
    }
}
