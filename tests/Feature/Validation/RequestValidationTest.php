<?php

namespace Tests\Feature\Validation;

use App\Domains\Postulantes\Models\Postulante;
use App\Domains\Postulantes\Support\BirthDate;
use App\Http\Requests\NormalizedFormRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private array $ids;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ids['program'] = DB::table('programas_academicos')->insertGetId(['nombre_prog' => 'Programa académico'], 'id_prog');
        $this->ids['group'] = DB::table('grupos_academicos')->insertGetId(['id_prog' => $this->ids['program'], 'nombre_grupo' => 'Grupo A', 'capacidad_grupo' => 30], 'id_grupo');
        $this->ids['post'] = DB::table('postulantes')->insertGetId($this->postulante(), 'id_post');
        $this->ids['enrollment'] = DB::table('inscripciones_academicas')->insertGetId(['id_prog' => $this->ids['program'], 'id_grupo' => $this->ids['group'], 'id_post' => $this->ids['post']], 'id_insc');
        $this->ids['matricula'] = DB::table('matriculas_academicas')->insertGetId(['id_insc' => $this->ids['enrollment'], 'id_post' => $this->ids['post']], 'id_mat');
        $this->ids['question'] = DB::table('preguntas')->insertGetId($this->question(), 'id_preg');
    }

    private function postulante(): array
    {
        return ['nombres_post' => 'José', 'apellidos_post' => 'Muñoz', 'fecha_nacimiento_post' => BirthDate::today()->subYears(18)->toDateString(), 'gestion_post' => now()->year, 'estado_post' => 'activo'];
    }

    private function question(): array
    {
        return ['enunciado_preg' => '¿Cuánto es dos más dos?', 'tipo_preg' => 'opcion_multiple', 'puntaje_preg' => 1, 'estado_preg' => 'activo'];
    }

    /** Exercise the real FormRequest lifecycle over HTTP, isolating format tests from academic actions. */
    private function probe(string $class, array $data, string $method = 'POST', array $parameters = []): TestResponse
    {
        $class = 'App\\Http\\Requests\\'.$class;
        Route::match(['POST', 'PUT'], '/__validation', function (Request $incoming) use ($class, $parameters) {
            foreach ($parameters as $name => $value) {
                $incoming->route()->setParameter($name, $value);
            }
            /** @var NormalizedFormRequest $request */
            $request = $class::createFrom($incoming);
            $request->setContainer(app())->setRedirector(app('redirect'));
            $request->setUserResolver(fn () => new class extends User
            {
                public function canChangeLoginEmail(): bool
                {
                    return true;
                }

                public function can($abilities, $arguments = []): bool
                {
                    return true;
                }

                public function hasAnyRole(...$roles): bool
                {
                    return true;
                }

                public function hasRole($roles, ?string $guard = null): bool
                {
                    return true;
                }
            });
            $request->validateResolved();

            return response()->json($request->validated());
        });

        return $this->json($method, '/__validation', $data);
    }

    public function test_postulante_invalid_shapes_formats_limits_ids_and_states_return_422(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-08 02:00:00', 'UTC'));
        $invalid = [
            'nombres_post' => ['Vicente123', ['José'], '  ', str_repeat('a', 121)],
            'apellidos_post' => ['Muñoz!!!'], 'email_post' => ['vicente@gmail', ['a@example.com']],
            'ci_post' => ['abcdef', '@@@@', '12'], 'celular_post' => ['abc', '+1234567890123456'],
            'fecha_nacimiento_post' => [BirthDate::today()->subYears(13)->toDateString(), BirthDate::today()->subYears(81)->toDateString(), '18.5', 'ayer'], 'gestion_post' => [1999, now()->year + 2],
            'id_col' => ['texto', 999999], 'id_uni' => [['1'], 999999], 'id_car' => ['abc', 999999],
            'turno_post' => ['inventado'], 'estado_post' => ['inventado'],
            'observaciones_post' => [str_repeat('a', 2001), "texto\0inválido"],
        ];
        foreach ($invalid as $field => $values) {
            foreach ($values as $value) {
                $this->probe('Postulantes\\StorePostulanteRequest', array_replace($this->postulante(), [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
            }
        }
        foreach ([14, 80] as $age) {
            $this->probe('Postulantes\\StorePostulanteRequest', array_replace($this->postulante(), ['fecha_nacimiento_post' => BirthDate::today()->subYears($age)->toDateString()]))->assertOk();
        }
    }

    public function test_normalized_data_is_persisted_and_create_update_use_identical_rules(): void
    {
        $this->seed(RolesAndUsersSeeder::class);
        $this->actingAs(User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail());
        $data = array_replace($this->postulante(), ['nombres_post' => '  José   Ángel ', 'apellidos_post' => " O'Connor ", 'ci_post' => ' 8765432-1A ', 'email_post' => ' USUARIO+TEST@EXAMPLE.COM ', 'celular_post' => '+591 (777)-12345', 'observaciones_post' => '  ', 'turno_post' => 'Fin de Semana']);
        $this->postJson(route('postulantes.store'), $data)->assertRedirect();
        $postulante = Postulante::where('ci_post', '8765432-1A')->firstOrFail();
        $this->assertSame('José Ángel', $postulante->nombres_post);
        $this->assertSame('usuario+test@example.com', $postulante->email_post);
        $this->assertSame('+59177712345', $postulante->celular_post);
        $this->assertNull($postulante->observaciones_post);
        $this->putJson(route('postulantes.update', $postulante), $data)->assertRedirect();
        $this->putJson(route('postulantes.update', $postulante), array_replace($data, ['nombres_post' => 'Vicente123']))->assertUnprocessable()->assertJsonValidationErrors('nombres_post');
        $this->postJson(route('postulantes.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('ci_post');
    }

    public function test_malformed_question_arrays_fail_for_create_and_update(): void
    {
        foreach (['texto', null, [1], [['texto_alt' => ['array']]], ['wrong' => []]] as $alternatives) {
            foreach (['StorePreguntaRequest', 'UpdatePreguntaRequest'] as $request) {
                $this->probe('Evaluaciones\\'.$request, $this->question() + ['alternativas' => $alternatives], str_starts_with($request, 'Update') ? 'PUT' : 'POST')->assertUnprocessable();
            }
        }
        $alternatives = array_map(fn ($index) => ['texto_alt' => 'Respuesta '.($index + 1), 'letra_alt' => chr(65 + $index), 'orden_alt' => $index + 1, 'es_correcta_alt' => $index === 0], range(0, 4));
        $this->probe('Evaluaciones\\StorePreguntaRequest', $this->question() + ['alternativas' => $alternatives])->assertOk();
        $alternatives[1]['orden_alt'] = 1;
        $alternatives[2]['es_correcta_alt'] = 'yes';
        $this->probe('Evaluaciones\\StorePreguntaRequest', $this->question() + ['alternativas' => $alternatives])->assertUnprocessable()->assertJsonValidationErrors(['alternativas.1.orden_alt', 'alternativas.2.es_correcta_alt']);
    }

    public function test_template_arrays_weights_precision_and_total(): void
    {
        $base = ['nombre_plan' => 'Plantilla', 'estado_plan' => 'activa'];
        foreach (['texto', [1], [null], [['id_preg' => ['bad']]], array_fill(0, 1001, [])] as $questions) {
            $this->probe('Evaluaciones\\StorePlantillaEvaluacionRequest', $base + ['preguntas' => $questions])->assertUnprocessable();
            $this->probe('Evaluaciones\\UpdatePlantillaEvaluacionRequest', $base + ['preguntas' => $questions], 'PUT')->assertUnprocessable();
        }
        $item = ['id_preg' => $this->ids['question'], 'orden_pp' => 1, 'puntaje_pp' => 100];
        $this->probe('Evaluaciones\\StorePlantillaEvaluacionRequest', $base + ['preguntas' => [$item]])->assertOk();
        foreach ([-1, 101, '33.333', 50] as $score) {
            $this->probe('Evaluaciones\\StorePlantillaEvaluacionRequest', $base + ['preguntas' => [array_replace($item, ['puntaje_pp' => $score])]])->assertUnprocessable();
        }
    }

    public function test_amounts_and_optional_empty_fields(): void
    {
        foreach ([0, 10, '10.50', '99999999.99'] as $amount) {
            $this->probe('Institucional\\CuotaAcademicaRequest', ['id_mat' => $this->ids['matricula'], 'monto_cuota' => $amount, 'estado_cuota' => 'pendiente', 'concepto_cuota' => '   ', 'fecha_pago_cuota' => ''])->assertOk()->assertJsonPath('concepto_cuota', null)->assertJsonPath('fecha_pago_cuota', null);
        }
        foreach ([-1, 'texto', '10.501', '100000000', ['10']] as $amount) {
            $this->probe('Institucional\\CuotaAcademicaRequest', ['id_mat' => $this->ids['matricula'], 'monto_cuota' => $amount, 'estado_cuota' => 'pendiente'])->assertUnprocessable()->assertJsonValidationErrors('monto_cuota');
            $this->probe('Institucional\\MatriculaAcademicaRequest', ['id_insc' => $this->ids['enrollment'], 'monto_matricula_mat' => $amount, 'estado_matricula_mat' => 'activa'])->assertUnprocessable()->assertJsonValidationErrors('monto_matricula_mat');
        }
    }

    public function test_dates_reject_invalid_calendars_and_malformed_companion_fields_without_500(): void
    {
        $base = ['nombre_prog' => 'Programa académico', 'estado_prog' => 'activo'];
        foreach (['mañana', '2026-02-30', ['2026-09-07'], "2026-09\0-07"] as $date) {
            $this->probe('Institucional\\ProgramaAcademicoRequest', $base + ['fecha_inicio_prog' => $date, 'fecha_fin_prog' => now()->addDays(4)->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio_prog');
        }
        $this->probe('Institucional\\ProgramaAcademicoRequest', $base + ['fecha_inicio_prog' => now()->addDays(3)->toDateString(), 'fecha_fin_prog' => now()->addDays(2)->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_fin_prog');
        $this->probe('Institucional\\ProgramaAcademicoRequest', $base + ['fecha_inicio_prog' => today()->toDateString(), 'fecha_fin_prog' => today()->addDay()->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio_prog');
        $this->probe('Institucional\\ProgramaAcademicoRequest', $base + ['fecha_inicio_prog' => null, 'fecha_fin_prog' => today()->addDay()->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio_prog');
        $this->probe('Institucional\\ProgramaAcademicoRequest', $base + ['fecha_inicio_prog' => today()->addDay()->toDateString(), 'fecha_fin_prog' => today()->addDays(2)->toDateString()])->assertOk();
        $this->probe('Institucional\\SimulacroProgramadoRequest', ['id_prog' => $this->ids['program'], 'titulo_sim' => 'Simulacro', 'estado_sim' => 'programado', 'hora_inicio_sim' => ['09:00'], 'hora_fin_sim' => '10:00'])->assertUnprocessable()->assertJsonValidationErrors('hora_inicio_sim');
    }

    public function test_boolean_switches_ids_capacity_and_shared_codes(): void
    {
        $base = ['estado_hab' => 'habilitado', 'habilitado_evaluaciones_hab' => true, 'habilitado_simulacros_hab' => false, 'habilitado_reportes_hab' => 1];
        foreach ([true, false, 0, 1, '0', '1'] as $value) {
            $this->probe('Institucional\\HabilitacionAcademicaRequest', array_replace($base, ['habilitado_evaluaciones_hab' => $value]))->assertOk();
        }
        foreach (['si', 'yes', 'activo', 'abc', [], 2] as $value) {
            $this->probe('Institucional\\HabilitacionAcademicaRequest', array_replace($base, ['habilitado_evaluaciones_hab' => $value]))->assertUnprocessable()->assertJsonValidationErrors('habilitado_evaluaciones_hab');
        }
        $group = ['id_prog' => $this->ids['program'], 'nombre_grupo' => 'Grupo', 'turno_grupo' => 'Mañana', 'aula_grupo' => '214', 'nivel_grupo' => 'Preuniversitario', 'capacidad_grupo' => 1, 'estado_grupo' => 'activo', 'codigo_grupo' => ' fis-001 '];
        $this->probe('Institucional\\GrupoAcademicoRequest', $group)->assertOk()->assertJsonPath('codigo_grupo', 'FIS-001');
        $this->probe('Institucional\\GrupoAcademicoRequest', array_replace($group, ['tutor_responsable_grupo' => 'Vicente123']))->assertUnprocessable()->assertJsonValidationErrors('tutor_responsable_grupo');
        foreach ([0, 21, '1.5', 'abc', true] as $value) {
            $this->probe('Institucional\\GrupoAcademicoRequest', array_replace($group, ['capacidad_grupo' => $value]))->assertUnprocessable()->assertJsonValidationErrors('capacidad_grupo');
        }
        foreach (['abc', 999999, ['1'], true] as $value) {
            $this->probe('Institucional\\GrupoAcademicoRequest', array_replace($group, ['id_prog' => $value]))->assertUnprocessable()->assertJsonValidationErrors('id_prog');
        }
    }

    public function test_attendance_permissions_and_response_arrays_are_defensive(): void
    {
        $base = ['id_grupo' => $this->ids['group'], 'sesion_asist' => 'General'];
        foreach (['texto', [1], [null], [['id_post' => ['1']]]] as $records) {
            $this->probe('Institucional\\AsistenciaGrupoRequest', $base + ['registros' => $records])->assertUnprocessable();
        }
        $this->probe('Institucional\\AsistenciaGrupoRequest', $base + ['registros' => [['id_post' => $this->ids['post'], 'estado_asist' => 'presente']]])->assertOk();
        $this->probe('Institucional\\AsistenciaAcademicaRequest', array_replace($base, ['id_post' => $this->ids['post'], 'estado_asist' => 'presente', 'sesion_asist' => ['bad']]))->assertUnprocessable()->assertJsonValidationErrors('sesion_asist');
        $this->probe('Institucional\\AsistenciaAcademicaRequest', array_replace($base, ['id_post' => $this->ids['post'], 'estado_asist' => 'presente', 'fecha_asist' => today()->subDay()->toDateString()]))->assertUnprocessable()->assertJsonValidationErrors('fecha_asist');
        foreach (['texto', [1], [null], [['id_preg' => ['1']]]] as $responses) {
            $this->probe('Resultados\\EnviarRespuestasEvaluacionRequest', ['respuestas' => $responses])->assertUnprocessable();
        }
        $this->probe('Resultados\\EnviarRespuestasEvaluacionRequest', ['respuestas' => [['id_preg' => $this->ids['question'], 'tiempo_segundos' => 0, 'intentos' => 1]], 'tiempo_total_segundos' => 0])->assertOk();
        foreach (['texto', [['bad']], ['missing', 'missing']] as $permissions) {
            $this->probe('Admin\\UpdateRolPermisosRequest', ['permissions' => $permissions])->assertUnprocessable();
        }
        $this->probe('Admin\\UpdateRolPermisosRequest', ['permissions' => []])->assertOk();
    }

    public function test_errors_are_spanish_and_use_human_field_names(): void
    {
        $response = $this->probe('Postulantes\\StorePostulanteRequest', array_replace($this->postulante(), ['nombres_post' => 'Vicente123', 'observaciones_post' => str_repeat('a', 2001)]))->assertUnprocessable();
        $text = json_encode($response->json('errors'), JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('letras', $text);
        $this->assertStringContainsString('observaciones no puede superar 2000', $text);
        $this->assertStringNotContainsString('The ', $text);
    }
}
