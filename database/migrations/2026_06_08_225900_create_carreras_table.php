<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id('id_car');
            $table->foreignId('id_uni')
                ->nullable()
                ->constrained('universidades', 'id_uni')
                ->nullOnDelete();
            $table->string('nombre_car');
            $table->string('area_car')->nullable();
            $table->string('nivel_exigencia_matematica_car')->nullable();
            $table->string('estado_car')->default('activo');
            $table->timestamps();

            $table->unique(['id_uni', 'nombre_car']);
            $table->index('estado_car');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
