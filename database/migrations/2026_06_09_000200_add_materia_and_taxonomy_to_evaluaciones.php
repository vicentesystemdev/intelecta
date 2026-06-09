<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('areas_conocimiento', function (Blueprint $table) {
            $table->foreignId('id_mat')
                ->nullable()
                ->after('id_area')
                ->constrained('materias', 'id_mat')
                ->nullOnDelete();
        });

        Schema::table('preguntas', function (Blueprint $table) {
            $table->string('subtema_preg')->nullable()->after('id_tem');
            $table->string('exigencia_preg')->nullable()->after('dificultad_preg');
            $table->string('habilidad_preg')->nullable()->after('exigencia_preg');
            $table->unsignedInteger('tiempo_estimado_seg_preg')->nullable()->after('habilidad_preg');
            $table->text('relacion_ingenieria_preg')->nullable()->after('tiempo_estimado_seg_preg');
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn([
                'subtema_preg',
                'exigencia_preg',
                'habilidad_preg',
                'tiempo_estimado_seg_preg',
                'relacion_ingenieria_preg',
            ]);
        });

        Schema::table('areas_conocimiento', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_mat');
        });
    }
};
