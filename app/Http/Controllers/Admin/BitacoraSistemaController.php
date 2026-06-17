<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Seguridad\Models\BitacoraSistema;
use App\Domains\Seguridad\Services\BitacoraService;
use App\Exports\Seguridad\BitacoraSistemaExport;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BitacoraSistemaController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        if (! Schema::hasTable('bitacora_sistema')) {
            return Inertia::render('Admin/Bitacora/Index', [
                'eventos' => [
                    'data' => [],
                    'links' => [],
                    'meta' => ['total' => 0],
                ],
                'filtros' => $filters,
                'resumen' => [
                    'total' => 0,
                    'criticos' => 0,
                    'seguridad' => 0,
                    'hoy' => 0,
                ],
                'opciones' => [
                    'acciones' => [],
                    'modulos' => [],
                    'severidades' => ['info', 'aviso', 'critico', 'seguridad'],
                ],
                'permisos' => [
                    'exportar' => $request->user()->can('bitacora.exportar'),
                ],
                'estructuraDisponible' => false,
            ]);
        }

        $query = $this->query($filters);

        return Inertia::render('Admin/Bitacora/Index', [
            'eventos' => $query
                ->latest('id_bitacora')
                ->paginate(15)
                ->withQueryString(),
            'filtros' => $filters,
            'resumen' => [
                'total' => BitacoraSistema::query()->count(),
                'criticos' => BitacoraSistema::query()->where('severidad', 'critico')->count(),
                'seguridad' => BitacoraSistema::query()->where('severidad', 'seguridad')->count(),
                'hoy' => BitacoraSistema::query()->whereDate('created_at', today())->count(),
            ],
            'opciones' => [
                'acciones' => BitacoraSistema::query()->whereNotNull('accion')->distinct()->orderBy('accion')->pluck('accion'),
                'modulos' => BitacoraSistema::query()->whereNotNull('modulo')->distinct()->orderBy('modulo')->pluck('modulo'),
                'severidades' => ['info', 'aviso', 'critico', 'seguridad'],
            ],
            'permisos' => [
                'exportar' => $request->user()->can('bitacora.exportar'),
            ],
            'estructuraDisponible' => true,
        ]);
    }

    public function exportar(Request $request, BitacoraService $bitacora): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $eventos = Schema::hasTable('bitacora_sistema')
            ? $this->query($filters)->latest('id_bitacora')->get()
            : collect();

        $bitacora->registrar([
            'accion' => 'exportar_bitacora',
            'modulo' => 'Bitácora del Sistema',
            'entidad' => 'bitacora_sistema',
            'descripcion' => 'Se exportó la bitácora institucional en Excel.',
            'valores_nuevos' => [
                'filtros' => $filters,
                'eventos_exportados' => $eventos->count(),
            ],
            'severidad' => 'seguridad',
        ]);

        return Excel::download(
            new BitacoraSistemaExport($eventos),
            'bitacora_sistema_intelecta.xlsx',
        );
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'buscar' => ['nullable', 'string', 'max:160'],
            'accion' => ['nullable', 'string', 'max:80'],
            'modulo' => ['nullable', 'string', 'max:120'],
            'severidad' => ['nullable', 'in:info,aviso,critico,seguridad'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ], [
            'buscar.max' => 'La busqueda no puede superar los 160 caracteres.',
            'accion.max' => 'La accion seleccionada no es valida.',
            'modulo.max' => 'El modulo seleccionado no es valido.',
            'severidad.in' => 'Seleccione una severidad valida.',
            'fecha_desde.date' => 'La fecha inicial no es valida.',
            'fecha_hasta.date' => 'La fecha final no es valida.',
            'fecha_hasta.after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
        ]);
    }

    private function query(array $filters): Builder
    {
        return BitacoraSistema::query()
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search) {
                $pattern = '%'.mb_strtolower($search).'%';

                $query->where(function (Builder $query) use ($pattern) {
                    $query
                        ->whereRaw('LOWER(COALESCE(nombre_usuario, \'\')) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(COALESCE(correo_usuario, \'\')) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(COALESCE(descripcion, \'\')) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(COALESCE(entidad_id, \'\')) LIKE ?', [$pattern]);
                });
            })
            ->when($filters['accion'] ?? null, fn (Builder $query, string $value) => $query->where('accion', $value))
            ->when($filters['modulo'] ?? null, fn (Builder $query, string $value) => $query->where('modulo', $value))
            ->when($filters['severidad'] ?? null, fn (Builder $query, string $value) => $query->where('severidad', $value))
            ->when($filters['fecha_desde'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['fecha_hasta'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value));
    }
}
