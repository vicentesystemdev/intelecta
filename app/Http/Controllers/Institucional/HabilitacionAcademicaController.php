<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\ActualizarHabilitacionAcademicaAction;
use App\Domains\Academico\DTOs\HabilitacionAcademicaData;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Services\HabilitacionAcademicaService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\HabilitacionAcademicaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HabilitacionAcademicaController extends Controller
{
    public function index(Request $request, HabilitacionAcademicaService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_hab' => ['nullable', 'in:habilitado,observado,restringido,temporal'],
        ]);

        return Inertia::render('Institucional/HabilitacionAcademica/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function update(
        HabilitacionAcademicaRequest $request,
        HabilitacionAcademica $habilitacion,
        ActualizarHabilitacionAcademicaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $anteriores = $habilitacion->only([
            'estado_hab',
            'motivo_hab',
            'habilitado_evaluaciones_hab',
            'habilitado_simulacros_hab',
            'habilitado_reportes_hab',
            'observacion_hab',
        ]);

        $actualizada = $action->execute(
            $habilitacion,
            HabilitacionAcademicaData::fromArray($request->validated()),
        );

        $bitacora->registrar([
            'accion' => 'actualizar_habilitacion',
            'modulo' => 'Habilitación Académica',
            'entidad' => 'habilitaciones_academicas',
            'entidad_id' => $actualizada->id_hab,
            'descripcion' => 'Se actualizó la habilitación académica de un postulante.',
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $actualizada->only([
                'estado_hab',
                'motivo_hab',
                'habilitado_evaluaciones_hab',
                'habilitado_simulacros_hab',
                'habilitado_reportes_hab',
                'observacion_hab',
            ]),
            'severidad' => $actualizada->estado_hab === 'restringido' ? 'critico' : 'aviso',
        ]);

        return back()->with('success', 'Habilitación académica actualizada correctamente.');
    }
}
