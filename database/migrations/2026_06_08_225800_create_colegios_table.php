<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colegios', function (Blueprint $table) {
            $table->id('id_col');
            $table->string('nombre_col');
            $table->string('tipo_col')->nullable();
            $table->string('ubicacion_col')->nullable();
            $table->string('estado_col')->default('activo');
            $table->timestamps();

            $table->unique('nombre_col');
            $table->index('estado_col');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colegios');
    }
};
