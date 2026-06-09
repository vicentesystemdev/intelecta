<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas_conocimiento', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre_area')->unique();
            $table->text('descripcion_area')->nullable();
            $table->string('estado_area')->default('activo');
            $table->timestamps();

            $table->index('estado_area');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas_conocimiento');
    }
};
