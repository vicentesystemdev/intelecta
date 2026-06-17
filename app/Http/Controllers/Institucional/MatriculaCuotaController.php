<?php

namespace App\Http\Controllers\Institucional;

use App\Domains\Academico\Actions\GuardarCuotaAcademicaAction;
use App\Domains\Academico\Actions\GuardarMatriculaAcademicaAction;
use App\Domains\Academico\DTOs\CuotaAcademicaData;
use App\Domains\Academico\DTOs\MatriculaAcademicaData;
use App\Domains\Academico\Models\CuotaAcademica;
use App\Domains\Academico\Models\MatriculaAcademica;
use App\Domains\Academico\Services\MatriculaCuotaService;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Institucional\CuotaAcademicaRequest;
use App\Http\Requests\Institucional\MatriculaAcademicaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MatriculaCuotaController extends Controller
{
    public function index(Request $request, MatriculaCuotaService $service): Response
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'id_prog' => ['nullable', 'integer', 'exists:programas_academicos,id_prog'],
            'id_grupo' => ['nullable', 'integer', 'exists:grupos_academicos,id_grupo'],
            'estado_matricula_mat' => ['nullable', 'in:activa,observada,inactiva,becada,exenta'],
            'estado_cuota' => ['nullable', 'in:pendiente,pagada,vencida,becada,exenta'],
        ]);

        return Inertia::render('Institucional/MatriculasCuotas/Index', [
            ...$service->index($filters),
            'filtros' => $filters,
        ]);
    }

    public function storeMatricula(
        MatriculaAcademicaRequest $request,
        GuardarMatriculaAcademicaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $matricula = $action->execute(MatriculaAcademicaData::fromArray($request->validated()));

        $bitacora->registrar([
            'accion' => 'crear_matricula',
            'modulo' => 'Matrículas y Cuotas',
            'entidad' => 'matriculas_academicas',
            'entidad_id' => $matricula->id_mat,
            'descripcion' => 'Se registró una matrícula académica.',
            'valores_nuevos' => $matricula->only([
                'id_mat',
                'id_insc',
                'id_post',
                'id_prog',
                'id_grupo',
                'estado_matricula_mat',
                'monto_matricula_mat',
            ]),
        ]);

        return back()->with('success', 'Matrícula académica registrada correctamente.');
    }

    public function updateMatricula(
        MatriculaAcademicaRequest $request,
        MatriculaAcademica $matricula,
        GuardarMatriculaAcademicaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $anteriores = $matricula->only([
            'estado_matricula_mat',
            'monto_matricula_mat',
            'tipo_beneficio_mat',
            'observacion_mat',
        ]);
        $actualizada = $action->execute(MatriculaAcademicaData::fromArray($request->validated()), $matricula);

        $bitacora->registrar([
            'accion' => 'editar_matricula',
            'modulo' => 'Matrículas y Cuotas',
            'entidad' => 'matriculas_academicas',
            'entidad_id' => $actualizada->id_mat,
            'descripcion' => 'Se actualizó una matrícula académica.',
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $actualizada->only([
                'estado_matricula_mat',
                'monto_matricula_mat',
                'tipo_beneficio_mat',
                'observacion_mat',
            ]),
            'severidad' => 'aviso',
        ]);

        return back()->with('success', 'Matrícula académica actualizada correctamente.');
    }

    public function storeCuota(
        CuotaAcademicaRequest $request,
        GuardarCuotaAcademicaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $cuota = $action->execute(CuotaAcademicaData::fromArray($request->validated()));

        $bitacora->registrar([
            'accion' => 'crear_cuota',
            'modulo' => 'Matrículas y Cuotas',
            'entidad' => 'cuotas_academicas',
            'entidad_id' => $cuota->id_cuota,
            'descripcion' => 'Se registró una cuota académica.',
            'valores_nuevos' => $cuota->only([
                'id_cuota',
                'id_mat',
                'nro_cuota',
                'monto_cuota',
                'fecha_vencimiento_cuota',
                'estado_cuota',
            ]),
        ]);

        return back()->with('success', 'Cuota académica registrada correctamente.');
    }

    public function updateCuota(
        CuotaAcademicaRequest $request,
        CuotaAcademica $cuota,
        GuardarCuotaAcademicaAction $action,
        BitacoraService $bitacora,
    ): RedirectResponse {
        $anteriores = $cuota->only([
            'monto_cuota',
            'fecha_vencimiento_cuota',
            'fecha_pago_cuota',
            'estado_cuota',
            'observacion_cuota',
        ]);
        $actualizada = $action->execute(CuotaAcademicaData::fromArray($request->validated()), $cuota);

        $bitacora->registrar([
            'accion' => 'editar_cuota',
            'modulo' => 'Matrículas y Cuotas',
            'entidad' => 'cuotas_academicas',
            'entidad_id' => $actualizada->id_cuota,
            'descripcion' => 'Se actualizó una cuota académica.',
            'valores_anteriores' => $anteriores,
            'valores_nuevos' => $actualizada->only([
                'monto_cuota',
                'fecha_vencimiento_cuota',
                'fecha_pago_cuota',
                'estado_cuota',
                'observacion_cuota',
            ]),
            'severidad' => in_array($actualizada->estado_cuota, ['vencida'], true) ? 'aviso' : 'info',
        ]);

        return back()->with('success', 'Cuota académica actualizada correctamente.');
    }
}
