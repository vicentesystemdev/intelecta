<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulacros_programados', function (Blueprint $table) {
            $table->id('id_sim');
            $table->foreignId('id_prog')
                ->constrained('programas_academicos', 'id_prog')
                ->cascadeOnDelete();
            $table->foreignId('id_grupo')
                ->nullable()
                ->constrained('grupos_academicos', 'id_grupo')
                ->nullOnDelete();
            $table->foreignId('id_plantilla')
                ->nullable()
                ->constrained('plantillas_evaluacion', 'id_plan')
                ->nullOnDelete();
            $table->string('titulo_sim');
            $table->date('fecha_sim')->nullable();
            $table->time('hora_inicio_sim')->nullable();
            $table->time('hora_fin_sim')->nullable();
            $table->string('modalidad_sim')->nullable();
            $table->string('estado_sim')->default('programado');
            $table->text('observacion_sim')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_prog', 'fecha_sim']);
            $table->index(['id_grupo', 'estado_sim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulacros_programados');
    }
};
