<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table): void {
            $table->bigIncrements('id_cargo');
            $table->string('nombre_cargo', 160);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });
        // InputNormalizer collapses whitespace; database LOWER defines case-insensitive uniqueness.
        DB::statement('CREATE UNIQUE INDEX cargos_nombre_normalizado_unique ON cargos (LOWER(nombre_cargo))');
        Schema::create('personal_institucional', function (Blueprint $table): void {
            $table->bigIncrements('id_personal');
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->foreign('cargo_id')->references('id_cargo')->on('cargos')->restrictOnDelete();
            $table->string('nombres', 120);
            $table->string('apellidos', 120);
            $table->string('ci', 30)->nullable()->unique();
            $table->string('celular', 16)->nullable();
            $table->string('correo_contacto', 254)->nullable();
            $table->enum('estado', ['pendiente', 'activo', 'inactivo'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_institucional');
        Schema::dropIfExists('cargos');
    }
};
