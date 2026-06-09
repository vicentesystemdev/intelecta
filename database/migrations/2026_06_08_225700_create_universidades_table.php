<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universidades', function (Blueprint $table) {
            $table->id('id_uni');
            $table->string('nombre_uni');
            $table->string('sigla_uni')->nullable();
            $table->string('tipo_uni')->nullable();
            $table->string('departamento_uni')->nullable();
            $table->string('nivel_exigencia_matematica_uni')->nullable();
            $table->string('estado_uni')->default('activo');
            $table->timestamps();

            $table->unique('nombre_uni');
            $table->unique('sigla_uni');
            $table->index('estado_uni');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universidades');
    }
};
