<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_usuario')->nullable();
            $table->string('correo_usuario')->nullable();
            $table->string('rol_usuario')->nullable();
            $table->string('accion');
            $table->string('modulo')->nullable();
            $table->string('entidad')->nullable();
            $table->string('entidad_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('metodo_http')->nullable();
            $table->string('ruta')->nullable();
            $table->text('url')->nullable();
            $table->string('severidad')->default('info');
            $table->timestamps();

            $table->index('user_id');
            $table->index('accion');
            $table->index('modulo');
            $table->index('entidad');
            $table->index('severidad');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_sistema');
    }
};
