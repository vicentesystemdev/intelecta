<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_evaluacion', function (Blueprint $table) {
            $table->id('id_plan');
            $table->string('nombre_plan');
            $table->text('descripcion_plan')->nullable();
            $table->text('objetivo_plan')->nullable();
            $table->unsignedSmallInteger('duracion_minutos_plan')->nullable();
            $table->string('dificultad_plan')->nullable();
            $table->string('estado_plan')->default('activa');
            $table->timestamps();
            $table->softDeletes();

            $table->index('dificultad_plan');
            $table->index('estado_plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_evaluacion');
    }
};
