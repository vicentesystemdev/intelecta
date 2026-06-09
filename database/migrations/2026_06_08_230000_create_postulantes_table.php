<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id('id_post');
            $table->string('nombres_post');
            $table->string('apellidos_post');
            $table->string('ci_post')->nullable()->unique();
            $table->string('email_post')->nullable();
            $table->string('celular_post')->nullable();
            $table->unsignedTinyInteger('edad_post')->nullable();
            $table->foreignId('id_col')
                ->nullable()
                ->constrained('colegios', 'id_col')
                ->nullOnDelete();
            $table->foreignId('id_car')
                ->nullable()
                ->constrained('carreras', 'id_car')
                ->nullOnDelete();
            $table->string('turno_post')->nullable();
            $table->unsignedSmallInteger('gestion_post');
            $table->string('estado_post')->default('activo');
            $table->text('observaciones_post')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado_post');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
