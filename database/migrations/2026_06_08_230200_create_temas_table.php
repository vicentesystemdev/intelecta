<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temas', function (Blueprint $table) {
            $table->id('id_tem');
            $table->foreignId('id_area')
                ->constrained('areas_conocimiento', 'id_area')
                ->cascadeOnDelete();
            $table->string('nombre_tem');
            $table->text('descripcion_tem')->nullable();
            $table->string('nivel_tem')->nullable();
            $table->string('estado_tem')->default('activo');
            $table->timestamps();

            $table->unique(['id_area', 'nombre_tem']);
            $table->index('estado_tem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
