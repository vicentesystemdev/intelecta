<?php

namespace App\Http\Controllers\Estudiante;

use App\Domains\Academico\Services\AccesoEvaluacionService;
use App\Domains\Cat\Models\InstrumentoCat;
use App\Domains\Cat\Models\IntentoCat;
use App\Domains\Cat\Services\IntentoCatService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cat\ResponderCatRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class CatDemoController extends Controller
{
    public function __construct(private readonly IntentoCatService $intentos, private readonly AccesoEvaluacionService $acceso) {}

    public function index(Request $request): Response
    {
        $ready = Schema::hasTable('instrumentos_cat') && Schema::hasTable('intentos_cat');

        return Inertia::render('Estudiante/CatDemo', [
            'disponible' => $ready,
            'acceso' => $this->decisionAcceso($request),
            'instrumentos' => $ready ? InstrumentoCat::where('estado', 'activo')->where('demo', true)->get(['id_instrumento_cat', 'nombre', 'duracion_minutos']) : [],
            'historial' => $ready ? IntentoCat::where('id_post', $request->user()->postulante->id_post)
                ->latest('id_intento_cat')->limit(20)->get(['id_intento_cat', 'estado', 'revision', 'iniciado_en', 'vence_en']) : [],
            'intento' => null,
        ]);
    }

    public function show(Request $request, int $intentoCat): Response
    {
        $intento = $this->intentos->consultar($request->user()->postulante, $intentoCat);
        $acceso = $this->decisionAcceso($request);
        $item = $intento->estado === 'en_progreso' && $acceso['permitido'] && ! $intento->calculo_pendiente
            ? collect($intento->snapshot['items'])->firstWhere('id_preg', $intento->id_preg_actual) : null;
        // Lista explícita: nunca serializar snapshot, clave o parámetros hacia Inertia.
        $publico = $item ? ['id_preg' => $item['id_preg'], 'enunciado' => $item['enunciado'], 'tema' => $item['tema'],
            'alternativas' => array_map(fn ($a) => ['id_alt' => $a['id_alt'], 'letra' => $a['letra'], 'texto' => $a['texto']], $item['alternativas'])] : null;

        return Inertia::render('Estudiante/CatDemo', ['disponible' => true, 'acceso' => $acceso, 'instrumentos' => [], 'historial' => [],
            'intento' => ['id_intento_cat' => $intento->id_intento_cat, 'nombre' => $intento->snapshot['nombre'], 'estado' => $intento->estado,
                'revision' => $intento->revision, 'calculo_pendiente' => $intento->calculo_pendiente,
                'vence_en' => $intento->vence_en->toIso8601String(), 'segundos_restantes' => max(0, (int) now()->diffInSeconds($intento->vence_en, false)),
                'minimum' => $intento->snapshot['configuracion']['minimum'], 'maximum' => $intento->snapshot['configuracion']['maximum'],
                'pregunta' => $publico, 'resultado_cat' => $intento->estado === 'finalizada' ? $intento->resultado_cat : null,
                'aciertos' => $intento->estado === 'en_progreso' ? null : $intento->respuestas()->where('correcta', true)->count()]]);
    }

    public function iniciar(Request $request, int $instrumentoCat): RedirectResponse
    {
        $intento = $this->intentos->iniciar($request->user()->postulante, $instrumentoCat);

        return to_route('estudiante.cat.show', $intento->id_intento_cat);
    }

    public function responder(ResponderCatRequest $request, int $intentoCat): RedirectResponse
    {
        $this->intentos->responder($request->user()->postulante, $intentoCat, $request->validated());

        return to_route('estudiante.cat.show', $intentoCat);
    }

    public function recuperar(Request $request, int $intentoCat): RedirectResponse
    {
        $this->intentos->recuperar($request->user()->postulante, $intentoCat);

        return to_route('estudiante.cat.show', $intentoCat);
    }

    private function decisionAcceso(Request $request): array
    {
        $d = $this->acceso->evaluar($request->user()->postulante);

        return ['permitido' => $d['permitido'], 'motivo' => $d['motivo']];
    }
}
