<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_academicas', function (Blueprint $table) {
            $table->bigIncrements('id_asist');
            $table->unsignedBigInteger('id_prog')->nullable();
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_post');
            $table->unsignedBigInteger('id_tutor')->nullable();
            $table->date('fecha_asist');
            $table->string('sesion_asist')->default('General');
            $table->string('estado_asist')->default('presente');
            $table->text('observacion_asist')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_prog')->references('id_prog')->on('programas_academicos')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos_academicos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_post')->references('id_post')->on('postulantes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_tutor')->references('id_tutor')->on('tutores_academicos')->cascadeOnUpdate()->nullOnDelete();

            $table->unique(
                ['id_grupo', 'id_post', 'fecha_asist', 'sesion_asist'],
                'asistencias_grupo_post_fecha_sesion_unique',
            );
            $table->index(['id_prog', 'id_grupo', 'fecha_asist']);
            $table->index(['id_post', 'estado_asist']);
            $table->index(['id_tutor', 'fecha_asist']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_academicas');
    }
};
