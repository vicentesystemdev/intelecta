<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habilitaciones_academicas', function (Blueprint $table) {
            $table->bigIncrements('id_hab');
            $table->unsignedBigInteger('id_post');
            $table->unsignedBigInteger('id_insc')->nullable()->unique();
            $table->unsignedBigInteger('id_mat')->nullable()->unique();
            $table->string('estado_hab')->default('habilitado');
            $table->string('motivo_hab')->nullable();
            $table->date('fecha_inicio_hab')->nullable();
            $table->date('fecha_fin_hab')->nullable();
            $table->boolean('habilitado_evaluaciones_hab')->default(true);
            $table->boolean('habilitado_simulacros_hab')->default(true);
            $table->boolean('habilitado_reportes_hab')->default(true);
            $table->text('observacion_hab')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_post')->references('id_post')->on('postulantes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_insc')->references('id_insc')->on('inscripciones_academicas')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('id_mat')->references('id_mat')->on('matriculas_academicas')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['estado_hab', 'id_post']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habilitaciones_academicas');
    }
};
