<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_aplicadas', function (Blueprint $table) {
            $table->id('id_eval_apl');
            $table->foreignId('id_post')
                ->constrained('postulantes', 'id_post')
                ->cascadeOnDelete();
            $table->foreignId('id_plantilla')
                ->constrained('plantillas_evaluacion', 'id_plan')
                ->restrictOnDelete();
            $table->foreignId('id_sim')
                ->nullable()
                ->constrained('simulacros_programados', 'id_sim')
                ->nullOnDelete();
            $table->string('codigo_eval_apl')->nullable()->unique();
            $table->string('tipo_eval_apl')->nullable();
            $table->timestamp('fecha_inicio_eval_apl')->nullable();
            $table->timestamp('fecha_fin_eval_apl')->nullable();
            $table->string('estado_eval_apl')->default('en_progreso');
            $table->decimal('puntaje_total_eval_apl', 8, 2)->default(0);
            $table->decimal('puntaje_maximo_eval_apl', 8, 2)->default(100);
            $table->decimal('porcentaje_eval_apl', 5, 2)->default(0);
            $table->unsignedInteger('tiempo_total_segundos_eval_apl')->nullable();
            $table->unsignedSmallInteger('intentos_eval_apl')->default(1);
            $table->text('observacion_eval_apl')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_post', 'estado_eval_apl']);
            $table->index(['id_plantilla', 'fecha_fin_eval_apl']);
            $table->index(['estado_eval_apl', 'fecha_fin_eval_apl']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_aplicadas');
    }
};
