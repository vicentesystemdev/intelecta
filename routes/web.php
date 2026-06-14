<?php

use App\Http\Controllers\Admin\RolPermisoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AreaConocimientoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Institucional\AsignacionTutorController;
use App\Http\Controllers\Institucional\AsistenciaAcademicaController;
use App\Http\Controllers\Institucional\FichaAcademicaController;
use App\Http\Controllers\Institucional\GrupoAcademicoController;
use App\Http\Controllers\Institucional\HabilitacionAcademicaController;
use App\Http\Controllers\Institucional\InscripcionAcademicaController;
use App\Http\Controllers\Institucional\MatriculaCuotaController;
use App\Http\Controllers\Institucional\ProgramaAcademicoController;
use App\Http\Controllers\Institucional\RankingAcademicoController;
use App\Http\Controllers\Institucional\SimulacroProgramadoController;
use App\Http\Controllers\Institucional\TutorAcademicoController;
use App\Http\Controllers\PlantillaEvaluacionController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteAcademicoController;
use App\Http\Controllers\TemaController;
use App\Domains\Academico\Models\HabilitacionAcademica;
use App\Domains\Academico\Models\RendimientoPostulante;
use App\Domains\Evaluaciones\Models\Materia;
use App\Domains\Institucional\Models\Carrera;
use App\Domains\Institucional\Models\Colegio;
use App\Domains\Postulantes\Models\Postulante;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/evaluaciones-postulante', function () {
    return Inertia::render('Public/EvaluacionesPostulante');
})->name('evaluaciones-postulante');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('areas-conocimiento')->name('areas-conocimiento.')->group(function () {
        Route::get('/', [AreaConocimientoController::class, 'index'])->middleware('permission:areas.ver')->name('index');
        Route::get('/crear', [AreaConocimientoController::class, 'create'])->middleware('permission:areas.crear')->name('create');
        Route::post('/', [AreaConocimientoController::class, 'store'])->middleware('permission:areas.crear')->name('store');
        Route::get('/{area}/editar', [AreaConocimientoController::class, 'edit'])->middleware('permission:areas.editar')->name('edit');
        Route::put('/{area}', [AreaConocimientoController::class, 'update'])->middleware('permission:areas.editar')->name('update');
    });

    Route::prefix('temas')->name('temas.')->group(function () {
        Route::get('/', [TemaController::class, 'index'])->middleware('permission:temas.ver')->name('index');
        Route::get('/crear', [TemaController::class, 'create'])->middleware('permission:temas.crear')->name('create');
        Route::post('/', [TemaController::class, 'store'])->middleware('permission:temas.crear')->name('store');
        Route::get('/{tema}/editar', [TemaController::class, 'edit'])->middleware('permission:temas.editar')->name('edit');
        Route::put('/{tema}', [TemaController::class, 'update'])->middleware('permission:temas.editar')->name('update');
    });

    Route::prefix('preguntas')->name('preguntas.')->group(function () {
        Route::get('/', [PreguntaController::class, 'index'])->middleware('permission:preguntas.ver')->name('index');
        Route::get('/crear', [PreguntaController::class, 'create'])->middleware('permission:preguntas.crear')->name('create');
        Route::post('/', [PreguntaController::class, 'store'])->middleware('permission:preguntas.crear')->name('store');
        Route::get('/{pregunta}', [PreguntaController::class, 'show'])->middleware('permission:preguntas.ver')->name('show');
        Route::get('/{pregunta}/editar', [PreguntaController::class, 'edit'])->middleware('permission:preguntas.editar')->name('edit');
        Route::put('/{pregunta}', [PreguntaController::class, 'update'])->middleware('permission:preguntas.editar')->name('update');
        Route::patch('/{pregunta}/estado', [PreguntaController::class, 'cambiarEstado'])->middleware('permission:preguntas.eliminar')->name('cambiar-estado');
    });

    Route::prefix('plantillas-evaluacion')->name('plantillas-evaluacion.')->group(function () {
        Route::get('/', [PlantillaEvaluacionController::class, 'index'])->middleware('permission:plantillas.ver')->name('index');
        Route::get('/crear', [PlantillaEvaluacionController::class, 'create'])->middleware('permission:plantillas.crear')->name('create');
        Route::post('/', [PlantillaEvaluacionController::class, 'store'])->middleware('permission:plantillas.crear')->name('store');
        Route::get('/{plantilla}', [PlantillaEvaluacionController::class, 'show'])->middleware('permission:plantillas.ver')->name('show');
        Route::get('/{plantilla}/editar', [PlantillaEvaluacionController::class, 'edit'])->middleware('permission:plantillas.editar')->name('edit');
        Route::put('/{plantilla}', [PlantillaEvaluacionController::class, 'update'])->middleware('permission:plantillas.editar')->name('update');
        Route::patch('/{plantilla}/estado', [PlantillaEvaluacionController::class, 'cambiarEstado'])->middleware('permission:plantillas.eliminar')->name('cambiar-estado');
    });

    Route::prefix('postulantes')->name('postulantes.')->group(function () {
        Route::get('/', [PostulanteController::class, 'index'])
            ->middleware('permission:postulantes.ver')
            ->name('index');
        Route::get('/crear', [PostulanteController::class, 'create'])
            ->middleware('permission:postulantes.crear')
            ->name('create');
        Route::post('/', [PostulanteController::class, 'store'])
            ->middleware('permission:postulantes.crear')
            ->name('store');
        Route::get('/{postulante}', [PostulanteController::class, 'show'])
            ->middleware('permission:postulantes.ver')
            ->name('show');
        Route::get('/{postulante}/editar', [PostulanteController::class, 'edit'])
            ->middleware('permission:postulantes.editar')
            ->name('edit');
        Route::put('/{postulante}', [PostulanteController::class, 'update'])
            ->middleware('permission:postulantes.editar')
            ->name('update');
        Route::patch('/{postulante}/estado', [PostulanteController::class, 'cambiarEstado'])
            ->middleware('permission:postulantes.eliminar')
            ->name('cambiar-estado');
    });

    Route::get('/reportes-academicos', [ReporteAcademicoController::class, 'index'])
        ->middleware('permission:reportes.ver')
        ->name('reportes-academicos.index');

    // Rutas planificadas del panel administrativo
    Route::prefix('admin')->group(function () {
        Route::prefix('institucional')
            ->middleware('verified')
            ->name('admin.institucional.')
            ->group(function () {
                Route::get('/programas', [ProgramaAcademicoController::class, 'index'])
                    ->name('programas.index');
                Route::post('/programas', [ProgramaAcademicoController::class, 'store'])
                    ->name('programas.store');
                Route::put('/programas/{programa}', [ProgramaAcademicoController::class, 'update'])
                    ->name('programas.update');

                Route::get('/grupos', [GrupoAcademicoController::class, 'index'])
                    ->name('grupos.index');
                Route::post('/grupos', [GrupoAcademicoController::class, 'store'])
                    ->name('grupos.store');
                Route::put('/grupos/{grupo}', [GrupoAcademicoController::class, 'update'])
                    ->name('grupos.update');

                Route::get('/inscripciones', [InscripcionAcademicaController::class, 'index'])
                    ->name('inscripciones.index');
                Route::post('/inscripciones', [InscripcionAcademicaController::class, 'store'])
                    ->name('inscripciones.store');
                Route::patch('/inscripciones/{inscripcion}', [InscripcionAcademicaController::class, 'update'])
                    ->name('inscripciones.update');

                Route::get('/simulacros', [SimulacroProgramadoController::class, 'index'])
                    ->name('simulacros.index');
                Route::post('/simulacros', [SimulacroProgramadoController::class, 'store'])
                    ->name('simulacros.store');
                Route::put('/simulacros/{simulacro}', [SimulacroProgramadoController::class, 'update'])
                    ->name('simulacros.update');

                Route::get('/ranking', [RankingAcademicoController::class, 'index'])
                    ->name('ranking.index');
                Route::get('/ficha-academica', [FichaAcademicaController::class, 'index'])
                    ->name('ficha.index');
                Route::get('/ficha-academica/{postulante}', [FichaAcademicaController::class, 'show'])
                    ->name('ficha.postulante');

                Route::get('/tutores', [TutorAcademicoController::class, 'index'])
                    ->name('tutores.index');
                Route::post('/tutores', [TutorAcademicoController::class, 'store'])
                    ->name('tutores.store');
                Route::get('/tutores/{tutor}', [TutorAcademicoController::class, 'show'])
                    ->name('tutores.show');
                Route::patch('/tutores/{tutor}', [TutorAcademicoController::class, 'update'])
                    ->name('tutores.update');

                Route::get('/asignacion-tutores', [AsignacionTutorController::class, 'index'])
                    ->name('asignacion-tutores.index');
                Route::post('/asignacion-tutores', [AsignacionTutorController::class, 'store'])
                    ->name('asignacion-tutores.store');
                Route::patch('/asignacion-tutores/{asignacion}', [AsignacionTutorController::class, 'update'])
                    ->name('asignacion-tutores.update');

                Route::get('/matriculas-cuotas', [MatriculaCuotaController::class, 'index'])
                    ->name('matriculas-cuotas.index');
                Route::post('/matriculas-cuotas', [MatriculaCuotaController::class, 'storeMatricula'])
                    ->name('matriculas-cuotas.store');
                Route::patch('/matriculas-cuotas/{matricula}', [MatriculaCuotaController::class, 'updateMatricula'])
                    ->name('matriculas-cuotas.update');
                Route::post('/cuotas', [MatriculaCuotaController::class, 'storeCuota'])
                    ->name('cuotas.store');
                Route::patch('/cuotas/{cuota}', [MatriculaCuotaController::class, 'updateCuota'])
                    ->name('cuotas.update');

                Route::get('/habilitacion-academica', [HabilitacionAcademicaController::class, 'index'])
                    ->name('habilitacion.index');
                Route::patch('/habilitacion-academica/{habilitacion}', [HabilitacionAcademicaController::class, 'update'])
                    ->name('habilitacion.update');

                Route::get('/asistencia', [AsistenciaAcademicaController::class, 'index'])
                    ->name('asistencia.index');
                Route::post('/asistencia', [AsistenciaAcademicaController::class, 'store'])
                    ->name('asistencia.store');
                Route::post('/asistencia/grupo', [AsistenciaAcademicaController::class, 'storeGroup'])
                    ->name('asistencia.store-grupo');
                Route::patch('/asistencia/{asistencia}', [AsistenciaAcademicaController::class, 'update'])
                    ->name('asistencia.update');
            });

        Route::prefix('gestion-academica')->group(function () {
            Route::get('/docentes', function () {
                return Inertia::render('Modulos/TutoresAcademicos');
            })->name('admin.docentes');

            Route::get('/carreras', function () {
                $items = Carrera::query()
                    ->with('universidad:id_uni,nombre_uni,sigla_uni')
                    ->withCount('postulantes')
                    ->orderBy('nombre_car')
                    ->get();

                return Inertia::render('Catalogos/Index', [
                    'tipo' => 'carreras',
                    'items' => $items,
                    'metricas' => [
                        'total' => $items->count(),
                        'activos' => $items->where('estado_car', 'activo')->count(),
                        'vinculados' => $items->where('postulantes_count', '>', 0)->count(),
                        'postulantes' => $items->sum('postulantes_count'),
                    ],
                ]);
            })->name('admin.carreras');

            Route::get('/colegios', function () {
                $items = Colegio::query()
                    ->withCount('postulantes')
                    ->orderBy('nombre_col')
                    ->get();

                return Inertia::render('Catalogos/Index', [
                    'tipo' => 'colegios',
                    'items' => $items,
                    'metricas' => [
                        'total' => $items->count(),
                        'activos' => $items->where('estado_col', 'activo')->count(),
                        'vinculados' => $items->where('postulantes_count', '>', 0)->count(),
                        'postulantes' => $items->sum('postulantes_count'),
                    ],
                ]);
            })->name('admin.colegios');
        });

        Route::prefix('evaluaciones')->group(function () {
            Route::redirect('/areas', '/areas-conocimiento')
                ->name('admin.evaluaciones.areas');
            Route::redirect('/temas', '/temas')
                ->name('admin.evaluaciones.temas');
            Route::redirect('/preguntas', '/preguntas')
                ->name('admin.evaluaciones.preguntas');
            Route::redirect('/plantillas', '/plantillas-evaluacion')
                ->name('admin.evaluaciones.plantillas');

            Route::get('/materias', function () {
                $items = Materia::query()
                    ->with(['areas' => fn ($query) => $query->withCount('temas')])
                    ->withCount('areas')
                    ->orderBy('nombre_mat')
                    ->get()
                    ->map(function (Materia $materia) {
                        $materia->setAttribute('temas_count', $materia->areas->sum('temas_count'));

                        return $materia;
                    });

                return Inertia::render('Catalogos/Index', [
                    'tipo' => 'materias',
                    'items' => $items,
                    'metricas' => [
                        'total' => $items->count(),
                        'activos' => $items->where('estado_mat', 'activo')->count(),
                        'vinculados' => $items->where('areas_count', '>', 0)->count(),
                        'postulantes' => $items->sum('temas_count'),
                    ],
                ]);
            })->name('admin.evaluaciones.materias');

            Route::get('/', function () {
                return Inertia::render('Modulos/CentroEvaluaciones');
            })->name('admin.evaluaciones.index');

            Route::get('/resultados', function () {
                return Inertia::render('Modulos/ResultadosSeguimiento');
            })->name('admin.evaluaciones.resultados');
        });

        Route::prefix('analisis')->group(function () {
            Route::get('/learning-analytics', function () {
                return Inertia::render('Analisis/LearningAnalytics');
            })->name('admin.analisis.learning-analytics');

            Route::get('/riesgo-academico', function () {
                $registros = RendimientoPostulante::query()
                    ->with([
                        'postulante:id_post,nombres_post,apellidos_post,id_car',
                        'postulante.carrera:id_car,nombre_car',
                        'programa:id_prog,nombre_prog',
                        'grupo:id_grupo,nombre_grupo',
                    ])
                    ->orderByRaw("CASE nivel_riesgo_rend WHEN 'Atención prioritaria' THEN 0 WHEN 'Seguimiento regular' THEN 1 ELSE 2 END")
                    ->orderBy('promedio_general_rend')
                    ->take(24)
                    ->get();

                return Inertia::render('Analisis/RiesgoAcademico', [
                    'registros' => $registros,
                    'metricas' => [
                        'total' => $registros->count(),
                        'prioritarios' => $registros->where('nivel_riesgo_rend', 'Atención prioritaria')->count(),
                        'seguimiento' => $registros->where('nivel_riesgo_rend', 'Seguimiento regular')->count(),
                        'favorables' => $registros->where('nivel_riesgo_rend', 'Alto rendimiento')->count(),
                    ],
                ]);
            })->name('admin.analisis.riesgo-academico');
        });

        Route::prefix('sistema')->group(function () {
            Route::get('/usuarios', [UsuarioController::class, 'index'])
                ->middleware('permission:usuarios.ver')
                ->name('admin.sistema.usuarios');
            Route::post('/usuarios', [UsuarioController::class, 'store'])
                ->middleware('permission:usuarios.crear')
                ->name('admin.sistema.usuarios.store');
            Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
                ->middleware('permission:usuarios.editar')
                ->name('admin.sistema.usuarios.update');

            Route::get('/roles-permisos', [RolPermisoController::class, 'index'])
                ->middleware('permission:roles.ver')
                ->name('admin.sistema.roles-permisos');
            Route::put('/roles-permisos/{rol}', [RolPermisoController::class, 'update'])
                ->middleware('permission:roles.editar')
                ->name('admin.sistema.roles-permisos.update');

            Route::get('/configuracion', function () {
                return Inertia::render('Sistema/Configuracion/Index');
            })->name('admin.sistema.configuracion');
        });
    });

    // Ruta de evaluaciones del estudiante
    Route::get('/estudiante/evaluaciones', function () {
        if (auth()->user()->hasRole(['Super Administrador', 'Administrador', 'Docente'])) {
            return redirect()->route('dashboard');
        }
        $habilitacion = null;

        if (
            Schema::hasTable('postulantes')
            && Schema::hasTable('habilitaciones_academicas')
        ) {
            $postulante = Postulante::query()
                ->where('email_post', auth()->user()->email)
                ->first();

            if ($postulante) {
                $habilitacion = HabilitacionAcademica::query()
                    ->where('id_post', $postulante->id_post)
                    ->latest('id_hab')
                    ->first([
                        'estado_hab',
                        'motivo_hab',
                        'habilitado_evaluaciones_hab',
                        'habilitado_simulacros_hab',
                        'habilitado_reportes_hab',
                    ]);
            }
        }

        return Inertia::render('Estudiante/Evaluaciones', [
            'habilitacionAcademica' => $habilitacion,
        ]);
    })->middleware('verified')->name('estudiante.evaluaciones');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
