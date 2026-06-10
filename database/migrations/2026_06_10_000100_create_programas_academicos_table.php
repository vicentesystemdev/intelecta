<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas_academicos', function (Blueprint $table) {
            $table->id('id_prog');
            $table->string('nombre_prog');
            $table->string('codigo_prog')->nullable()->unique();
            $table->string('universidad_objetivo_prog')->nullable();
            $table->string('carrera_area_prog')->nullable();
            $table->string('modalidad_prog')->nullable();
            $table->date('fecha_inicio_prog')->nullable();
            $table->date('fecha_fin_prog')->nullable();
            $table->text('descripcion_prog')->nullable();
            $table->string('estado_prog')->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado_prog');
            $table->index('universidad_objetivo_prog');
            $table->index('modalidad_prog');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas_academicos');
    }
};
