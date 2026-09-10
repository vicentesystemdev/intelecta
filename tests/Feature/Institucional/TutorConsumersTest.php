<?php

namespace Tests\Feature\Institucional;

use App\Domains\Academico\Models\AsignacionTutor;
use App\Domains\Academico\Models\AsistenciaAcademica;
use App\Domains\Academico\Models\TutorAcademico;
use App\Domains\Academico\Repositories\AcademicoRepository;
use App\Domains\Academico\Repositories\AsignacionTutorRepository;
use App\Domains\Academico\Repositories\AsistenciaAcademicaRepository;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TutorConsumersTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_consumers_and_create_update_flows_preserve_tutor_fk(): void
    {
        Notification::fake();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::role('Super Administrador')->sole());
        $url = fn ($action, $id = []) => route('admin.institucional.'.$action, $id);
        foreach (['tutores.index', 'asignacion-tutores.index', 'asistencia.index', 'grupos.index', 'ficha.index'] as $action) {
            $this->get($url($action))->assertOk();
        }
        $tutor = TutorAcademico::firstOrFail();
        $assignments = app(AsignacionTutorRepository::class)->paginate(['id_tutor' => $tutor->id_tutor]);
        $this->assertGreaterThan(0, $assignments->total());
        $this->assertSame($tutor->nombre_completo, $assignments->items()[0]->tutor->nombre_completo);
        $attendances = app(AsistenciaAcademicaRepository::class)->paginate(['id_tutor' => $tutor->id_tutor]);
        $this->assertGreaterThan(0, $attendances->total());
        $this->assertSame($tutor->nombre_completo, $attendances->items()[0]->tutor->nombre_completo);
        $assignment = AsignacionTutor::where('id_tutor', $tutor->id_tutor)->whereNotNull('id_grupo')->firstOrFail();
        $this->postJson($url('asignacion-tutores.store'), [
            'id_tutor' => $tutor->id_tutor, 'id_prog' => $assignment->id_prog, 'id_grupo' => $assignment->id_grupo,
            'estado_asig' => 'activo', 'fecha_inicio_asig' => today()->format('Y-m-d'), 'rol_asig' => 'Acompañamiento de prueba',
        ])->assertRedirect();
        $created = AsignacionTutor::latest('id_asig')->firstOrFail();
        $this->patchJson($url('asignacion-tutores.update', $created), [
            'id_tutor' => $tutor->id_tutor, 'id_prog' => $created->id_prog, 'id_grupo' => $created->id_grupo,
            'estado_asig' => 'inactivo', 'observacion_asig' => 'Prueba de edición',
        ])->assertRedirect();
        $this->assertSame($tutor->id_tutor, $created->fresh()->id_tutor);
        $attendance = AsistenciaAcademica::where('id_tutor', $tutor->id_tutor)->firstOrFail();
        $payload = [
            'id_tutor' => $tutor->id_tutor, 'id_prog' => $attendance->id_prog, 'id_grupo' => $attendance->id_grupo,
            'id_post' => $attendance->id_post, 'fecha_asist' => today()->format('Y-m-d'),
            'sesion_asist' => 'Sesión de prueba del Bloque cuatro', 'estado_asist' => 'presente',
        ];
        $this->postJson($url('asistencia.store'), $payload)->assertRedirect();
        $createdAttendance = AsistenciaAcademica::latest('id_asist')->firstOrFail();
        $this->patchJson($url('asistencia.update', $createdAttendance), [...$payload, 'estado_asist' => 'justificado'])->assertRedirect();
        $this->assertSame($tutor->id_tutor, $createdAttendance->fresh()->id_tutor);
        $student = User::role('Estudiante')->firstOrFail();
        $ficha = app(AcademicoRepository::class)->ficha($student->postulante);
        $this->assertNotEmpty($ficha['tutorAsignado']->nombre_completo);
        $this->get($url('ficha.postulante', $student->postulante))->assertOk();
        $this->actingAs($student)->get(route('estudiante.ficha'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Estudiante/MiFicha')->has('tutorAsignado.personal.nombres')->missing('tutorAsignado.nombres_tutor'));
        Notification::assertNothingSent();
    }
}
