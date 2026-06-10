<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones_academicas', function (Blueprint $table) {
            $table->id('id_insc');
            $table->foreignId('id_prog')
                ->constrained('programas_academicos', 'id_prog')
                ->cascadeOnDelete();
            $table->foreignId('id_grupo')
                ->nullable()
                ->constrained('grupos_academicos', 'id_grupo')
                ->nullOnDelete();
            $table->foreignId('id_post')
                ->constrained('postulantes', 'id_post')
                ->cascadeOnDelete();
            $table->date('fecha_inscripcion')->nullable();
            $table->string('estado_inscripcion')->default('activo');
            $table->text('observacion_inscripcion')->nullable();
            $table->timestamps();

            $table->unique(['id_prog', 'id_post']);
            $table->index(['id_grupo', 'estado_inscripcion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones_academicas');
    }
};
