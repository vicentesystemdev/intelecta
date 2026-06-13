<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_academicas', function (Blueprint $table) {
            $table->bigIncrements('id_cuota');
            $table->unsignedBigInteger('id_mat');
            $table->unsignedInteger('nro_cuota')->nullable();
            $table->string('concepto_cuota')->nullable();
            $table->decimal('monto_cuota', 10, 2)->default(0);
            $table->date('fecha_vencimiento_cuota')->nullable();
            $table->date('fecha_pago_cuota')->nullable();
            $table->string('metodo_pago_cuota')->nullable();
            $table->string('estado_cuota')->default('pendiente');
            $table->text('observacion_cuota')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mat')->references('id_mat')->on('matriculas_academicas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['id_mat', 'nro_cuota']);
            $table->index(['estado_cuota', 'fecha_vencimiento_cuota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_academicas');
    }
};
