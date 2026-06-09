<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternativas', function (Blueprint $table) {
            $table->id('id_alt');
            $table->foreignId('id_preg')
                ->constrained('preguntas', 'id_preg')
                ->cascadeOnDelete();
            $table->text('texto_alt');
            $table->string('letra_alt', 1)->nullable();
            $table->boolean('es_correcta_alt')->default(false);
            $table->unsignedTinyInteger('orden_alt')->nullable();
            $table->string('estado_alt')->default('activo');
            $table->timestamps();

            $table->unique(['id_preg', 'letra_alt']);
            $table->index(['id_preg', 'orden_alt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternativas');
    }
};
