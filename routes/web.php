<?php

use App\Http\Controllers\AreaConocimientoController;
use App\Http\Controllers\PlantillaEvaluacionController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteAcademicoController;
use App\Http\Controllers\TemaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
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

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('Estudiante')) {
        return redirect('/');
    }
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
        Route::prefix('gestion-academica')->group(function () {
            Route::get('/docentes', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Docentes',
                    'descripcion' => 'Gestión de tutores, carga académica e intervención en evaluaciones lógico-matemáticas.',
                    'moduloRelacionado' => 'Postulantes y Evaluaciones',
                    'proximaEvolucion' => 'Monitoreo de la asignación de tutores y flujos de revisión para los bancos de preguntas preuniversitarios.'
                ]);
            })->name('admin.docentes');

            Route::get('/carreras', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Carreras',
                    'descripcion' => 'Consulta institucional de carreras postuladas, universidades asociadas y nivel de exigencia matemática.',
                    'moduloRelacionado' => 'Reportes Académicos',
                    'proximaEvolucion' => 'Control de ponderación de puntajes mínimos exigidos y perfil de postulación académica.'
                ]);
            })->name('admin.carreras');

            Route::get('/colegios', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Colegios',
                    'descripcion' => 'Seguimiento de colegios de procedencia y origen académico de postulantes.',
                    'moduloRelacionado' => 'Postulantes',
                    'proximaEvolucion' => 'Estadísticas comparativas agregadas por colegios fiscales, privados y de convenio para análisis del instituto.'
                ]);
            })->name('admin.colegios');
        });

        Route::prefix('evaluaciones')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Evaluaciones',
                    'descripcion' => 'Programación académica de evaluaciones a partir de plantillas y bancos de preguntas.',
                    'moduloRelacionado' => 'Plantillas de Evaluación',
                    'proximaEvolucion' => 'Calendarización interactiva, asignación de fechas de examen, y control de estados de pruebas.'
                ]);
            })->name('admin.evaluaciones.index');

            Route::get('/resultados', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Resultados',
                    'descripcion' => 'Concentrado académico preparado para registrar resultados y desempeño por área evaluada.',
                    'moduloRelacionado' => 'Reportes Académicos',
                    'proximaEvolucion' => 'Centralización de notas obtenidas en pruebas guiadas, cálculo de desvíos estándar e historiales.'
                ]);
            })->name('admin.evaluaciones.resultados');
        });

        Route::prefix('analisis')->group(function () {
            Route::get('/learning-analytics', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Learning Analytics',
                    'descripcion' => 'Base de análisis preparada para consolidar indicadores académicos y futura predicción de desempeño.',
                    'moduloRelacionado' => 'Reportes Académicos',
                    'proximaEvolucion' => 'Algoritmos estadísticos avanzados aplicados a la evolución lógica de los postulantes.'
                ]);
            })->name('admin.analisis.learning-analytics');

            Route::get('/riesgo-academico', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Riesgo Académico',
                    'descripcion' => 'Seguimiento preliminar de brechas lógico-matemáticas y priorización de refuerzo académico.',
                    'moduloRelacionado' => 'Postulantes en Alerta',
                    'proximaEvolucion' => 'Semáforo automático de detección de brechas académicas y sugerencia de tutorías dinámicas.'
                ]);
            })->name('admin.analisis.riesgo-academico');
        });

        Route::prefix('sistema')->group(function () {
            Route::get('/usuarios', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Usuarios',
                    'descripcion' => 'Gestión planificada de accesos institucionales para administradores, tutores y postulantes.',
                    'moduloRelacionado' => 'Configuración General',
                    'proximaEvolucion' => 'Control de cuentas activas del personal de admisión y perfiles de postulantes autorizados.'
                ]);
            })->name('admin.sistema.usuarios');

            Route::get('/roles-permisos', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Roles y Permisos',
                    'descripcion' => 'Administración de perfiles de acceso y permisos por rol institucional.',
                    'moduloRelacionado' => 'Usuarios y Seguridad',
                    'proximaEvolucion' => 'Matriz interactiva para asociar capacidades de Spatie Permission de forma visual en tiempo real.'
                ]);
            })->name('admin.sistema.roles-permisos');

            Route::get('/configuracion', function () {
                return Inertia::render('Modulos/ModuloPlanificado', [
                    'titulo' => 'Configuración',
                    'descripcion' => 'Parámetros generales del sistema y preferencias académicas de operación.',
                    'moduloRelacionado' => 'Sistema General',
                    'proximaEvolucion' => 'Ajustes del temporizador global, ponderaciones de corrección y límites de intentos en exámenes.'
                ]);
            })->name('admin.sistema.configuracion');
        });
    });

    // Ruta de evaluaciones del estudiante
    Route::get('/estudiante/evaluaciones', function () {
        if (auth()->user()->hasRole(['Super Administrador', 'Administrador', 'Docente'])) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Estudiante/Evaluaciones');
    })->middleware('verified')->name('estudiante.evaluaciones');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
