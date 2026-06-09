<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id('id_mat');
            $table->string('codigo_mat', 10)->unique();
            $table->string('nombre_mat');
            $table->text('descripcion_mat')->nullable();
            $table->string('color_mat', 30)->nullable();
            $table->string('icono_mat', 60)->nullable();
            $table->string('estado_mat')->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
