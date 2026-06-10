<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendimientos_postulante', function (Blueprint $table) {
            $table->id('id_rend');
            $table->foreignId('id_post')
                ->constrained('postulantes', 'id_post')
                ->cascadeOnDelete();
            $table->foreignId('id_prog')
                ->nullable()
                ->constrained('programas_academicos', 'id_prog')
                ->nullOnDelete();
            $table->foreignId('id_grupo')
                ->nullable()
                ->constrained('grupos_academicos', 'id_grupo')
                ->nullOnDelete();
            $table->decimal('promedio_general_rend', 5, 2)->default(0);
            $table->decimal('promedio_matematica_rend', 5, 2)->nullable();
            $table->decimal('promedio_fisica_rend', 5, 2)->nullable();
            $table->decimal('promedio_quimica_rend', 5, 2)->nullable();
            $table->decimal('promedio_paa_rend', 5, 2)->nullable();
            $table->decimal('asistencia_porcentaje_rend', 5, 2)->nullable();
            $table->string('nivel_riesgo_rend')->nullable();
            $table->text('observacion_rend')->nullable();
            $table->timestamps();

            $table->unique(['id_post', 'id_prog']);
            $table->index(['id_prog', 'promedio_general_rend']);
            $table->index(['id_grupo', 'nivel_riesgo_rend']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendimientos_postulante');
    }
};
