<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_tutores', function (Blueprint $table) {
            $table->bigIncrements('id_asig');
            $table->unsignedBigInteger('id_tutor');
            $table->unsignedBigInteger('id_prog')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->string('materia_referencia_asig')->nullable();
            $table->string('rol_asig')->nullable();
            $table->date('fecha_inicio_asig')->nullable();
            $table->date('fecha_fin_asig')->nullable();
            $table->string('estado_asig')->default('activo');
            $table->text('observacion_asig')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_tutor')
                ->references('id_tutor')
                ->on('tutores_academicos')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('id_prog')
                ->references('id_prog')
                ->on('programas_academicos')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreign('id_grupo')
                ->references('id_grupo')
                ->on('grupos_academicos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(['estado_asig', 'id_tutor']);
            $table->index(['id_prog', 'id_grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_tutores');
    }
};
