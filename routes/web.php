<?php

use App\Http\Controllers\AreaConocimientoController;
use App\Http\Controllers\PlantillaEvaluacionController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\ProfileController;
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

Route::get('/dashboard', function () {
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
