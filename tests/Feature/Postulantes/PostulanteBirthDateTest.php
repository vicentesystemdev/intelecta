<?php

namespace Tests\Feature\Postulantes;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Support\BirthDate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostulanteBirthDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-09-07 12:00:00', 'UTC'));
        $this->seed(RolesAndUsersSeeder::class);
        $this->actingAs(User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail());
    }

    private function payload(array $extra = []): array
    {
        return array_replace([
            'nombres_post' => 'José Luis',
            'apellidos_post' => 'Muñoz',
            'gestion_post' => now()->year,
            'estado_post' => 'activo',
            'fecha_nacimiento_post' => '15/08/2005',
        ], $extra);
    }

    public static function validDates(): array
    {
        return [
            ['15/08/2005', '2005-08-15'],
            ['01/01/2000', '2000-01-01'],
            ['29/02/2008', '2008-02-29'],
            ['29/02/2012', '2012-02-29'],
            ['2005-08-15', '2005-08-15'],
            [' 15/08/2005 ', '2005-08-15'],
        ];
    }

    #[DataProvider('validDates')]
    public function test_real_dates_are_normalized_persisted_and_serialized_without_time(string $input, string $iso): void
    {
        $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => $input]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $person = Postulante::sole();
        $this->assertSame($iso, $person->fecha_nacimiento_post->format('Y-m-d'));
        $this->assertSame($iso, $person->toArray()['fecha_nacimiento_post']);
        $this->assertSame(BirthDate::age($iso), $person->toArray()['edad_actual']);
        $this->assertNull($person->edad_post);
    }

    public static function invalidDates(): array
    {
        return array_map(fn ($value) => [$value], [
            '2005/08/15', '08-15-2005', '15-08-2005', '08/15/2005',
            '1/1/2000', '31/02/2005', '32/01/2005', '15/13/2005',
            '00/12/2005', '29/02/2005', '31/04/2005', '00/01/2005',
            '15/00/2005', '29/02/2100', '2005-02-29', '15/08/0000',
            '2005-08-15T00:00:00Z', 20050815, true, ['15/08/2005'],
        ]);
    }

    #[DataProvider('invalidDates')]
    public function test_invalid_formats_calendars_and_shapes_return_spanish_422(mixed $input): void
    {
        $response = $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => $input]))
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_nacimiento_post');
        $this->assertStringNotContainsString('The ', $response->json('errors.fecha_nacimiento_post.0'));
        $this->assertDatabaseCount('postulantes', 0);
    }

    public function test_new_records_require_the_date_even_when_a_client_sends_an_age(): void
    {
        foreach ([null, '', '  '] as $empty) {
            $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => $empty, 'edad_post' => 20]))
                ->assertUnprocessable()
                ->assertJsonPath('errors.fecha_nacimiento_post.0', 'La fecha de nacimiento es obligatoria.');
        }
        $data = $this->payload(['edad_post' => 20]);
        unset($data['fecha_nacimiento_post']);
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('fecha_nacimiento_post');
    }

    public function test_future_dates_are_rejected_on_create_and_update(): void
    {
        $legacy = Postulante::create($this->payload(['fecha_nacimiento_post' => null, 'edad_post' => 18]));
        foreach ([BirthDate::today()->addDay(), BirthDate::today()->addYear()] as $future) {
            $data = $this->payload(['fecha_nacimiento_post' => $future->format('d/m/Y')]);
            $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()
                ->assertJsonPath('errors.fecha_nacimiento_post.0', 'La fecha de nacimiento no puede ser futura.');
            $this->putJson(route('postulantes.update', $legacy), $data)->assertUnprocessable()
                ->assertJsonPath('errors.fecha_nacimiento_post.0', 'La fecha de nacimiento no puede ser futura.');
        }
    }

    public function test_exact_minimum_age_boundary_before_on_and_after_the_birthday(): void
    {
        $birthday = BirthDate::today()->subYears(14);
        $anniversary = $birthday->addYears(14);
        foreach ([-1 => false, 0 => true, 1 => true] as $offset => $eligible) {
            $this->travelTo($anniversary->addDays($offset)->setHour(12));
            $response = $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => $birthday->format('d/m/Y')]));
            if ($eligible) {
                $response->assertRedirect()->assertSessionHasNoErrors();
            } else {
                $response->assertUnprocessable()->assertJsonPath('errors.fecha_nacimiento_post.0', 'El postulante debe tener al menos 14 años.');
            }
        }
    }

    public function test_maximum_is_only_a_registration_boundary_and_does_not_invalidate_aging_records(): void
    {
        $date = BirthDate::today()->subYears(81)->addDay();
        $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => $date->format('d/m/Y')]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $person = Postulante::sole();
        $this->assertSame(80, $person->edad_actual);
        $this->travel(1)->days();
        $this->assertSame(81, $person->edad_actual);
        $data = $this->payload(['fecha_nacimiento_post' => $date->format('d/m/Y')]);
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('fecha_nacimiento_post');
        $this->putJson(route('postulantes.update', $person), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($person->refresh()->edad_post);
        $this->assertSame(81, $person->edad_actual);
    }

    public function test_legacy_records_can_be_read_listed_edited_and_completed_without_destroying_historical_age(): void
    {
        $person = Postulante::create($this->payload(['fecha_nacimiento_post' => null, 'edad_post' => 40]));
        $this->get(route('postulantes.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('postulantes.data.0.edad_actual', 40)
            ->where('postulantes.data.0.fecha_nacimiento_post', null));
        foreach (['postulantes.show', 'postulantes.edit', 'admin.institucional.ficha.postulante'] as $route) {
            $this->get(route($route, $person))->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('postulante.edad_actual', 40)->where('postulante.fecha_nacimiento_post', null));
        }
        $data = $this->payload(['edad_post' => 99, 'observaciones_post' => 'Dato actualizado']);
        unset($data['fecha_nacimiento_post']);
        $this->putJson(route('postulantes.update', $person), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(40, $person->refresh()->edad_post);
        $this->assertNull($person->fecha_nacimiento_post);
        $this->assertSame('Dato actualizado', $person->observaciones_post);
        $this->putJson(route('postulantes.update', $person), $data + ['fecha_nacimiento_post' => null])->assertRedirect()->assertSessionHasNoErrors();
        $this->putJson(route('postulantes.update', $person), $this->payload())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(40, $person->refresh()->edad_post);
        $this->assertSame(21, $person->edad_actual);
        $this->get(route('postulantes.edit', $person))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('postulante.fecha_nacimiento_post', '2005-08-15')->where('postulante.edad_actual', 21));
    }

    public function test_client_age_is_ignored_and_cannot_overwrite_birth_date_truth(): void
    {
        $this->postJson(route('postulantes.store'), $this->payload(['edad_post' => ['malicious' => 999], 'edad_actual' => 999]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $person = Postulante::sole();
        $this->assertNull($person->edad_post);
        $this->assertSame(21, $person->edad_actual);
        $this->putJson(route('postulantes.update', $person), $this->payload(['edad_post' => 80]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($person->refresh()->edad_post);
        $this->assertSame(21, $person->edad_actual);
    }

    public function test_known_birth_date_cannot_be_cleared_but_can_be_omitted_on_unrelated_update(): void
    {
        $person = Postulante::create($this->payload(['fecha_nacimiento_post' => '2005-08-15']));
        $this->putJson(route('postulantes.update', $person), $this->payload(['fecha_nacimiento_post' => '']))
            ->assertUnprocessable()->assertJsonValidationErrors('fecha_nacimiento_post');
        $data = $this->payload(['celular_post' => '77712345']);
        unset($data['fecha_nacimiento_post']);
        $this->putJson(route('postulantes.update', $person), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('2005-08-15', $person->refresh()->toArray()['fecha_nacimiento_post']);
    }

    public function test_age_and_serialization_are_date_only_in_different_timezones(): void
    {
        foreach (['America/La_Paz', 'Pacific/Kiritimati', 'America/Los_Angeles'] as $timezone) {
            config(['app.timezone' => $timezone]);
            $person = new Postulante(['fecha_nacimiento_post' => '2005-08-15', 'edad_post' => 70]);
            $this->assertSame('2005-08-15', $person->toArray()['fecha_nacimiento_post']);
            $this->assertSame(21, $person->edad_actual);
        }
        $this->assertSame(16, BirthDate::age('2008-02-29', CarbonImmutable::parse('2025-02-28')));
        $this->assertSame(17, BirthDate::age('2008-02-29', CarbonImmutable::parse('2025-03-01')));
        $this->assertNull((new Postulante)->edad_actual);
    }

    public function test_list_and_academic_report_use_current_age_instead_of_stale_legacy_age(): void
    {
        Postulante::create($this->payload(['fecha_nacimiento_post' => '2005-08-15', 'edad_post' => 70]));
        $this->get(route('postulantes.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('postulantes.data.0.edad_actual', 21));
        $this->get(route('reportes-academicos.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('postulantesList.0.edad', 21));
    }

    public static function boliviaBirthdayBoundary(): array
    {
        return [
            'UTC next day, Bolivia still 22:00' => ['2026-09-08 02:00:00', 13],
            'Bolivia 23:59:59' => ['2026-09-08 03:59:59', 13],
            'Bolivia birthday at 00:00:00' => ['2026-09-08 04:00:00', 14],
        ];
    }

    #[DataProvider('boliviaBirthdayBoundary')]
    public function test_birthday_follows_bolivia_midnight_not_utc(string $utc, int $expectedAge): void
    {
        $this->travelTo(CarbonImmutable::parse($utc, 'UTC'));
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('America/La_Paz', BirthDate::today()->timezoneName);
        $this->assertSame($expectedAge, BirthDate::age('2012-09-08'));
        $person = new Postulante(['fecha_nacimiento_post' => '2012-09-08', 'edad_post' => 70]);
        $this->assertSame($expectedAge, $person->edad_actual);
        $this->assertSame('2012-09-08', $person->toArray()['fecha_nacimiento_post']);
        $this->assertSame(70, (new Postulante(['edad_post' => 70]))->edad_actual);
    }

    public function test_minimum_age_validation_uses_the_same_bolivian_birthday(): void
    {
        $data = $this->payload(['fecha_nacimiento_post' => '08/09/2012']);
        $this->travelTo(CarbonImmutable::parse('2026-09-08 03:59:59', 'UTC'));
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()
            ->assertJsonPath('errors.fecha_nacimiento_post.0', 'El postulante debe tener al menos 14 años.');
        $this->travelTo(CarbonImmutable::parse('2026-09-08 04:00:00', 'UTC'));
        $this->postJson(route('postulantes.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->postJson(route('postulantes.store'), $this->payload(['fecha_nacimiento_post' => '09/09/2012']))
            ->assertUnprocessable()->assertJsonPath('errors.fecha_nacimiento_post.0', 'El postulante debe tener al menos 14 años.');
    }

    public function test_future_date_validation_uses_bolivia_even_after_utc_midnight(): void
    {
        $person = Postulante::create($this->payload(['fecha_nacimiento_post' => null, 'edad_post' => 18]));
        $this->travelTo(CarbonImmutable::parse('2026-09-08 02:00:00', 'UTC'));
        $data = $this->payload(['fecha_nacimiento_post' => '08/09/2026']);
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()
            ->assertJsonPath('errors.fecha_nacimiento_post.0', 'La fecha de nacimiento no puede ser futura.');
        $this->putJson(route('postulantes.update', $person), $data)->assertUnprocessable()
            ->assertJsonPath('errors.fecha_nacimiento_post.0', 'La fecha de nacimiento no puede ser futura.');
    }

    public function test_maximum_remains_create_only_and_changes_at_bolivia_midnight(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-08 03:59:59', 'UTC'));
        $data = $this->payload(['fecha_nacimiento_post' => '08/09/1945']);
        $this->postJson(route('postulantes.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $person = Postulante::sole();
        $this->assertSame(80, $person->edad_actual);
        $this->travelTo(CarbonImmutable::parse('2026-09-08 04:00:00', 'UTC'));
        $this->assertSame(81, $person->edad_actual);
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('fecha_nacimiento_post');
        $this->putJson(route('postulantes.update', $person), $data)->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_leap_birthday_uses_the_bolivian_civil_day(): void
    {
        $this->travelTo(CarbonImmutable::parse('2025-03-01 03:59:59', 'UTC'));
        $this->assertSame(16, BirthDate::age('2008-02-29'));
        $this->travelTo(CarbonImmutable::parse('2025-03-01 04:00:00', 'UTC'));
        $this->assertSame(17, BirthDate::age('2008-02-29'));
        $this->travelTo(CarbonImmutable::parse('2024-02-29 03:59:59', 'UTC'));
        $this->assertSame(15, BirthDate::age('2008-02-29'));
        $this->travelTo(CarbonImmutable::parse('2024-02-29 04:00:00', 'UTC'));
        $this->assertSame(16, BirthDate::age('2008-02-29'));
    }
}
