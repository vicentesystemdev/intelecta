<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutores_academicos', function (Blueprint $table) {
            $table->bigIncrements('id_tutor');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombres_tutor');
            $table->string('apellidos_tutor');
            $table->string('ci_tutor')->nullable();
            $table->string('celular_tutor')->nullable();
            $table->string('correo_tutor')->nullable();
            $table->string('especialidad_tutor')->nullable();
            $table->string('formacion_tutor')->nullable();
            $table->text('experiencia_tutor')->nullable();
            $table->string('estado_tutor')->default('activo');
            $table->text('observacion_tutor')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado_tutor', 'especialidad_tutor']);
            $table->unique('user_id');
            $table->unique('ci_tutor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutores_academicos');
    }
};
