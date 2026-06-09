<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_preguntas', function (Blueprint $table) {
            $table->foreignId('id_plan')
                ->constrained('plantillas_evaluacion', 'id_plan')
                ->cascadeOnDelete();
            $table->foreignId('id_preg')
                ->constrained('preguntas', 'id_preg')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('orden_pp')->nullable();
            $table->decimal('puntaje_pp', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['id_plan', 'id_preg']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_preguntas');
    }
};
