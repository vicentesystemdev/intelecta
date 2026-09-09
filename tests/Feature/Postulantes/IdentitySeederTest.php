<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Models\Postulante;
use App\Models\User;
use Database\Seeders\BaseLimpiaAvalanchaSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdentitySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_install_has_explicit_links(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseCount('postulantes', 72);
        $this->assertSame(3, Postulante::whereNotNull('user_id')->count());
        $this->assertSame(69, Postulante::whereNull('user_id')->count());
        foreach (User::role('Estudiante')->get() as $user) {
            $this->assertNotNull($user->postulante);
            $this->assertSame($user->id, $user->postulante->user_id);
        }
    }

    public function test_seeder_does_not_link_an_existing_legacy_fixture(): void
    {
        $postulante = Postulante::factory()->create(['ci_post' => '9100000', 'email_post' => 'valeria.nina@postulante.avalancha.edu.bo']);
        $this->seed(BaseLimpiaAvalanchaSeeder::class);
        $this->assertNull($postulante->fresh()->user_id);
        $this->assertSame(2, Postulante::whereNotNull('user_id')->count());
    }
}
