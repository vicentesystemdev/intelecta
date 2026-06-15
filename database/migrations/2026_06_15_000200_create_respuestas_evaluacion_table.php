<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas_evaluacion', function (Blueprint $table) {
            $table->id('id_resp_eval');
            $table->foreignId('id_eval_apl')
                ->constrained('evaluaciones_aplicadas', 'id_eval_apl')
                ->cascadeOnDelete();
            $table->foreignId('id_preg')
                ->constrained('preguntas', 'id_preg')
                ->restrictOnDelete();
            $table->foreignId('id_alt')
                ->nullable()
                ->constrained('alternativas', 'id_alt')
                ->nullOnDelete();
            $table->text('respuesta_texto_resp')->nullable();
            $table->boolean('es_correcta_resp')->default(false);
            $table->decimal('puntaje_obtenido_resp', 8, 2)->default(0);
            $table->decimal('puntaje_maximo_resp', 8, 2)->default(0);
            $table->unsignedInteger('tiempo_segundos_resp')->nullable();
            $table->unsignedSmallInteger('intentos_resp')->default(1);
            $table->unsignedSmallInteger('orden_resp')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id_eval_apl', 'id_preg']);
            $table->index(['id_preg', 'es_correcta_resp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_evaluacion');
    }
};
