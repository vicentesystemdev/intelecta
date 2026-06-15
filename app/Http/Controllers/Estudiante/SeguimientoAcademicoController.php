<?php

namespace App\Http\Controllers\Estudiante;

use App\Domains\Academico\Services\AcademicoService;
use App\Domains\Postulantes\Models\Postulante;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeguimientoAcademicoController extends Controller
{
    public function ficha(Request $request, AcademicoService $service): Response
    {
        $this->ensureStudent($request);
        $postulante = $this->postulante($request);

        if (! $postulante) {
            return Inertia::render('Estudiante/MiFicha', [
                'postulanteVinculado' => false,
            ]);
        }

        return Inertia::render('Estudiante/MiFicha', [
            'postulanteVinculado' => true,
            ...$service->ficha($postulante),
        ]);
    }

    public function ranking(Request $request, AcademicoService $service): Response
    {
        $this->ensureStudent($request);
        $postulante = $this->postulante($request);

        return Inertia::render('Estudiante/Ranking', [
            'postulanteVinculado' => (bool) $postulante,
            ...($postulante ? $service->rankingPortal($postulante) : []),
        ]);
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(['Estudiante', 'Postulante']),
            403,
            'No cuentas con permisos para acceder a este módulo.',
        );
    }

    private function postulante(Request $request): ?Postulante
    {
        return Postulante::query()
            ->where('email_post', $request->user()->email)
            ->first();
    }
}
