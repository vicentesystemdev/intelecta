<?php

namespace Tests\Feature\Reportes;

use App\Models\User;
use Database\Seeders\RolesAndUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportesExportacionTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndUsersSeeder::class);
        $this->administrator = User::where('email', RolesAndUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $this->student = User::where('email', RolesAndUsersSeeder::STUDENT_EMAIL)->firstOrFail();
    }

    public function test_administrator_can_download_rendimiento_pdf(): void
    {
        $response = $this->actingAs($this->administrator)
            ->get(route('reportes.pdf', 'rendimiento'))
            ->assertOk();

        $this->assertStringContainsString(
            'reporte_rendimiento_academico.pdf',
            $response->headers->get('content-disposition', ''),
        );
    }

    public function test_administrator_can_download_evaluaciones_excel(): void
    {
        $response = $this->actingAs($this->administrator)
            ->get(route('reportes.excel', 'evaluaciones'))
            ->assertOk();

        $this->assertStringContainsString(
            'reporte_evaluaciones_aplicadas.xlsx',
            $response->headers->get('content-disposition', ''),
        );
    }

    public function test_student_cannot_download_administrative_pdf_reports(): void
    {
        $this->actingAs($this->student)
            ->get(route('reportes.pdf', 'rendimiento'))
            ->assertForbidden();
    }

    public function test_student_cannot_download_administrative_excel_reports(): void
    {
        $this->actingAs($this->student)
            ->get(route('reportes.excel', 'evaluaciones'))
            ->assertForbidden();
    }

    public function test_invalid_report_type_returns_not_found(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('reportes.pdf', 'no-disponible'))
            ->assertNotFound();
    }

    public function test_temas_academicos_index_still_responds(): void
    {
        $this->actingAs($this->administrator)
            ->get(route('temas.index'))
            ->assertOk();
    }
}
