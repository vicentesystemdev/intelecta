<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas_academicas', function (Blueprint $table) {
            $table->bigIncrements('id_mat');
            $table->unsignedBigInteger('id_insc')->unique();
            $table->unsignedBigInteger('id_post');
            $table->unsignedBigInteger('id_prog')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->string('codigo_mat')->nullable()->unique();
            $table->date('fecha_matricula_mat')->nullable();
            $table->decimal('monto_matricula_mat', 10, 2)->default(0);
            $table->string('estado_matricula_mat')->default('activa');
            $table->string('tipo_beneficio_mat')->nullable();
            $table->text('observacion_mat')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_insc')->references('id_insc')->on('inscripciones_academicas')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_post')->references('id_post')->on('postulantes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_prog')->references('id_prog')->on('programas_academicos')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos_academicos')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['estado_matricula_mat', 'id_prog', 'id_grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas_academicas');
    }
};
