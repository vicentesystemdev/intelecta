<?php

use App\Http\Controllers\Estudiante\CatDemoController;
use App\Http\Middleware\EnsureStudentAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', EnsureStudentAccess::class])->prefix('estudiante/cat')->name('estudiante.cat.')->group(function () {
    Route::get('/', [CatDemoController::class, 'index'])->name('index');
    Route::post('/instrumentos/{instrumentoCat}/iniciar', [CatDemoController::class, 'iniciar'])->whereNumber('instrumentoCat')->name('iniciar');
    Route::get('/intentos/{intentoCat}', [CatDemoController::class, 'show'])->whereNumber('intentoCat')->name('show');
    Route::post('/intentos/{intentoCat}/responder', [CatDemoController::class, 'responder'])->whereNumber('intentoCat')->name('responder');
    Route::post('/intentos/{intentoCat}/recuperar', [CatDemoController::class, 'recuperar'])->whereNumber('intentoCat')->name('recuperar');
});
