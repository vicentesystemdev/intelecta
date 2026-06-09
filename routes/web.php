<?php

use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\ProfileController;
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
