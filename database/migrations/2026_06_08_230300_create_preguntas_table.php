<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id('id_preg');
            $table->foreignId('id_tem')
                ->nullable()
                ->constrained('temas', 'id_tem')
                ->nullOnDelete();
            $table->text('enunciado_preg');
            $table->string('tipo_preg')->default('opcion_multiple');
            $table->string('dificultad_preg')->nullable();
            $table->decimal('puntaje_preg', 8, 2)->default(1);
            $table->text('explicacion_preg')->nullable();
            $table->string('estado_preg')->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_preg');
            $table->index('dificultad_preg');
            $table->index('estado_preg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
