<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupos_academicos', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->foreignId('id_prog')
                ->constrained('programas_academicos', 'id_prog')
                ->cascadeOnDelete();
            $table->string('nombre_grupo');
            $table->string('codigo_grupo')->nullable();
            $table->string('turno_grupo')->nullable();
            $table->string('aula_grupo')->nullable();
            $table->unsignedInteger('capacidad_grupo')->default(30);
            $table->string('nivel_grupo')->nullable();
            $table->string('tutor_responsable_grupo')->nullable();
            $table->string('estado_grupo')->default('activo');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id_prog', 'codigo_grupo']);
            $table->index(['id_prog', 'estado_grupo']);
            $table->index('turno_grupo');
            $table->index('nivel_grupo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_academicos');
    }
};
